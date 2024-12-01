<?php

namespace Tests\Unit\Booking;

use App\Booking\ScheduleAvailability\Providers\ServiceScheduleProvider;
use App\Booking\ScheduleAvailability\ScheduleAvailability;
use App\Booking\ScheduleAvailability\ScheduleAvailabilityCalculator;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Schedule;
use App\Models\ScheduleExclusion;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ScheduleAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected Service $service;
    protected ServiceScheduleProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = Service::factory()->create();
        $this->provider = new ServiceScheduleProvider($this->service);
    }

    #[Test]
    public function it_checks_service_schedule_availability()
    {
        Schedule::factory()->for($this->service, 'schedulable')
            ->create([
                'day_of_week' => Carbon::now()->dayOfWeek,
                'starts_at' => '09:00',
                'ends_at' => '17:00'
            ]);

        $availability = new ScheduleAvailability($this->provider, new ScheduleAvailabilityCalculator());

        $result = $availability->forPeriod(Carbon::now()->startOfDay(), Carbon::now()->endOfMonth());

        $this->assertNotEmpty($result);
    }

    #[Test]
    public function it_applies_service_exclusions()
    {
        Schedule::factory()->for($this->service, 'schedulable')
            ->create([
                'day_of_week' => Carbon::now()->dayOfWeek,
                'starts_at' => '09:00',
                'ends_at' => '17:00',
            ]);

        Schedule::factory()->for($this->service, 'schedulable')
            ->create([
                'day_of_week' => Carbon::now()->addDay()->dayOfWeek,
                'starts_at' => '09:00',
                'ends_at' => '17:00',
            ]);

        ScheduleExclusion::factory()->for($this->service, 'excludable')
            ->create([
                'starts_at' => Carbon::now()->addDay()->setTime(10, 0),
                'ends_at' => Carbon::now()->addDay()->setTime(10, 59)
            ]);

        $availability = new ScheduleAvailability($this->provider, new ScheduleAvailabilityCalculator());
        $result = collect($availability->forPeriod(Carbon::now()->startOfDay(), Carbon::now()->addDay()->endOfDay()));

        $this->assertTrue($result->first()->contains(Carbon::now()->addDay()->setTime(9, 0)));
        $this->assertTrue($result->get(1)->contains(Carbon::now()->addDay()->setTime(11, 0)));
        $this->assertCount(2, $result);
    }
}

<?php

namespace Tests\Unit\Models;

use App\Models\Service;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_service_schedule()
    {
        $service = Service::factory()->create();
        $schedule = Schedule::factory()->for($service, 'schedulable')->create([
            'day_of_week' => Carbon::now()->dayOfWeek,
            'starts_at' => '09:00',
            'ends_at' => '17:00'
        ]);

        $this->assertDatabaseHas('schedules', [
            'schedulable_id' => $service->id,
            'schedulable_type' => Service::class,
            'day_of_week' => Carbon::now()->dayOfWeek
        ]);
    }
}

<?php

namespace Tests\Unit\Booking;

use App\Booking\DateCollection;
use App\Booking\ServiceAvailability\SingleSlotServiceAvailability;
use App\Booking\ServiceAvailability\SingleSlotWithEmployeeAvailability;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ServiceSlotAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected Service $service;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a sample service with default attributes
        $this->service = Service::factory()->create([
            'duration' => 60, // Service duration in minutes
            'number_of_slots' => 1, // Single-slot service
        ]);

        // Create an employee
        $this->employee = Employee::factory()->hasSchedules(1, [
            'day_of_week' => now()->dayOfWeek,
            'starts_at' => now()->startOfDay()->toTimeString(),
            'ends_at' => now()->endOfDay()->toTimeString()
        ])->create();
    }

    #[Test]
    public function it_returns_availability_for_single_slot_service_with_employee()
    {
        $availability = app()->makeWith(SingleSlotWithEmployeeAvailability::class, [
            'employees' => collect([$this->employee]),
            'service' => $this->service
        ]);

        // Define the date range for availability
        $startsAt = Carbon::now()->startOfDay();
        $endsAt = Carbon::now()->endOfDay();

        $result = $availability->forPeriod($startsAt, $endsAt);

        $this->assertInstanceOf(DateCollection::class, $result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function it_returns_availability_for_single_slot_service_without_employee()
    {
        $service = Service::factory()
            ->hasSchedules(1, [
                'day_of_week' => now()->dayOfWeek,
                'starts_at' => now()->startOfDay()->toTimeString(),
                'ends_at' => now()->endOfDay()->toTimeString()
            ])
            ->create([
                'duration' => 60, // Service duration in minutes
                'number_of_slots' => 1, // Single-slot service
            ]);

        $availability = app()->makeWith(SingleSlotServiceAvailability::class, ['service' => $service]);;

        // Define the date range for availability
        $startsAt = Carbon::now()->startOfDay();
        $endsAt = Carbon::now()->endOfDay();

        $result = $availability->forPeriod($startsAt, $endsAt);

        $this->assertInstanceOf(DateCollection::class, $result);
        $this->assertNotEmpty($result);
    }

    #[Test]
    public function it_excludes_times_based_on_existing_appointments()
    {
        // Create an appointment during working hours
        Appointment::factory()->create([
            'employee_id' => $this->employee->id,
            'service_id' => $this->service->id,
            'starts_at' => Carbon::now()->setTime(10, 0),
            'ends_at' => Carbon::now()->setTime(11, 0),
        ]);

        $availability = app()->makeWith(SingleSlotWithEmployeeAvailability::class, [
            'employees' => collect([$this->employee]),
            'service' => $this->service
        ]);

        // Define the date range for availability
        $startsAt = Carbon::now()->startOfDay();
        $endsAt = Carbon::now()->endOfDay();

        $result = $availability->forPeriod($startsAt, $endsAt);

        // Check that the booked slot is excluded
        $bookedSlot = $result->first()->slots->first(fn($slot) => $slot->time->format('H:i') === '10:00');
        $this->assertNull($bookedSlot);
    }
}

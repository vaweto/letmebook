<?php

namespace App\Booking\ServiceAvailability;

use App\Booking\Date;
use App\Booking\DateCollection;
use App\Booking\ScheduleAvailability\ScheduleAvailabilityWithEmployee;
use App\Booking\Slot;
use App\Booking\SlotGenerator;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

class MultipleSlotServiceWithEmployeesAvailability
{
    protected Collection $employees;
    protected Service $service;

    public function __construct(Collection $employees, Service $service)
    {
        $this->employees = $employees;
        $this->service = $service;
    }

    /**
     * Generate available slots for a specific period.
     */
    public function forPeriod(Carbon $startsAt, Carbon $endsAt): DateCollection
    {
        // Generate a collection of slots for the given date range.
        $range = (new SlotGenerator($startsAt, $endsAt))->generate($this->service->duration);

        // Loop through each employee to check their availability.
        $this->employees->each(function (Employee $employee) use ($startsAt, $endsAt, &$range) {
            // Retrieve the employee's available periods.
            $periods = (new ScheduleAvailabilityWithEmployee($employee, $this->service))->forPeriod($startsAt, $endsAt);

            // Remove periods that overlap with existing appointments.
            $periods = $this->removeAppointments($periods, $employee);

            // Add available employees to each slot within their availability periods.
            foreach ($periods as $period) {
                $this->addAvailableEmployeeForPeriod($range, $period, $employee);
            }
        });

        // Remove slots that do not have enough availability.
        $range = $this->removeFullyBookedSlots($range);

        return $range;
    }

    /**
     * Add available employees to the slots within their available periods.
     */
    protected function addAvailableEmployeeForPeriod(DateCollection $range, Period $period, Employee $employee): void
    {
        $range->each(function (Date $date) use ($period, $employee) {
            $date->slots->each(function (Slot $slot) use ($period, $employee) {
                // Check if the slot falls within the employee's available period.
                if ($period->contains($slot->time)) {
                    $slot->addEmployee($employee);
                }
            });
        });
    }

    /**
     * Remove slots that have reached the maximum capacity.
     */
    protected function removeFullyBookedSlots(DateCollection $range): DateCollection
    {
        return $range->filter(function (Date $date) {
            $date->slots = $date->slots->filter(function (Slot $slot) {
                // Check if the slot is fully booked based on the service capacity.
                return $slot->employees->count() < $this->service->number_of_slots;
            });

            return $date->slots->isNotEmpty();
        });
    }

    /**
     * Remove appointments from the available periods for each employee.
     */
    protected function removeAppointments(PeriodCollection $periods, Employee $employee): PeriodCollection
    {
        $employee->appointments()->whereNull('cancelled_at')->each(function (Appointment $appointment) use (&$periods) {
            $periods = $periods->subtract(
                Period::make(
                    start: $appointment->starts_at->copy()->subMinutes($this->service->duration),
                    end: $appointment->ends_at->copy(),
                    precision: Precision::MINUTE(),
                    boundaries: Boundaries::EXCLUDE_ALL()
                )
            );
        });

        return $periods;
    }
}

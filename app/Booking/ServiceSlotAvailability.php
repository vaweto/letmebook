<?php

namespace App\Booking;

use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

/**
 * Class responsible for determining the availability of service slots
 * based on employee schedules, service durations, and existing appointments.
 */
class ServiceSlotAvailability
{
    /**
     * @param Collection $employees - Collection of employees
     * @param Service $service - The service for which availability is checked
     */
    public function __construct(protected Collection $employees, protected Service $service) {}

    /**
     * Generates available slots for a given time period.
     *
     * @param Carbon $startsAt - Start date of the availability period
     * @param Carbon $endsAt - End date of the availability period
     * @return DateCollection - Collection of available dates with slots
     */
    public function forPeriod(Carbon $startsAt, Carbon $endsAt)
    {
        // Generate a collection of slots for each day in the given period, based on the service duration.
        $range = (new SlotGenerator($startsAt, $endsAt))->generate($this->service->duration);

        // Iterate through each employee to check their availability within the specified period.
        $this->employees->each(function (Employee $employee) use ($startsAt, $endsAt, &$range) {
            // Get available periods for the employee based on their schedule.
            $periods = (new ScheduleAvailability($employee, $this->service))->forPeriod($startsAt, $endsAt);

            // Subtract any existing appointments from the available periods.
            $periods = $this->removeAppointments($periods, $employee);

            // Assign employees to available slots within the periods.
            foreach ($periods as $period) {
                $this->addAvailableEmployeeForPeriod($range, $period, $employee);
            }
        });

        // Remove any slots that do not have any available employees.
        $range = $this->removeEmptySlots($range);

        return $range;
    }

    /**
     * Adds an available employee to slots within a given period.
     *
     * @param DateCollection $range - Collection of dates with slots
     * @param Period $period - Available time period
     * @param Employee $employee - Employee available for the slot
     */
    protected function addAvailableEmployeeForPeriod(DateCollection $range, Period $period, Employee $employee)
    {
        // Loop through each date in the range and check if the slot time falls within the period.
        $range->each(function (Date $date) use ($period, $employee) {
            $date->slots->each(function (Slot $slot) use ($period, $employee) {
                if ($period->contains($slot->time)) {
                    // If the slot time is within the period, assign the employee to that slot.
                    $slot->addEmployee($employee);
                }
            });
        });
    }

    /**
     * Removes slots that have no employees assigned to them.
     *
     * @param DateCollection $range - Collection of dates with slots
     * @return DateCollection - Filtered collection with non-empty slots
     */
    protected function removeEmptySlots(DateCollection $range)
    {
        return $range->filter(function (Date $date) {
            // Filter out slots that have no employees available.
            $date->slots = $date->slots->filter(fn(Slot $slot) => $slot->hasEmployees());

            // Return only dates that have at least one slot with an employee.
            return $date->slots->isNotEmpty();
        });
    }

    /**
     * Removes periods that overlap with existing appointments.
     *
     * @param PeriodCollection $periods - Available periods for the employee
     * @param Employee $employee - The employee whose appointments are checked
     * @return PeriodCollection - Updated collection of periods with appointments removed
     */
    protected function removeAppointments(PeriodCollection $periods, Employee $employee)
    {
        // Get all appointments for the employee that are not cancelled.
        $employee->appointments()->whereNull('cancelled_at')->each(function (Appointment $appointment) use (&$periods) {
            // Subtract the appointment time from the available periods.
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

<?php

namespace App\Booking;

use App\Models\Employee;
use App\Models\ScheduleExclusion;
use App\Models\Service;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

// Handles the availability of an employee based on their schedules and service requirements.
class ScheduleAvailability
{
    protected PeriodCollection $periods;

    public function __construct(protected Employee $employee, protected Service $service)
    {
        // Initializes an empty collection of periods.
        $this->periods = new PeriodCollection();
    }

    /**
     * Calculates the employee's availability for a given period.
     *
     * @param Carbon $startAt The start date of the range.
     * @param Carbon $endsAt The end date of the range.
     * @return PeriodCollection A collection of available periods.
     */
    public function forPeriod(Carbon $startAt, Carbon $endsAt): PeriodCollection
    {
        // Loops through each day within the specified date range.
        collect(CarbonPeriod::create($startAt, $endsAt)->days())->each(function (Carbon $date) {
            // Adds availability from the employee's schedule for the given date.
            $this->addAvailabilityFormSchedule($date);

            // Subtracts periods based on the employee's schedule exclusions.
            $this->employee->scheduleExclusions->each(function (ScheduleExclusion $scheduleExclusion) {
                $this->subtractScheduleExclusion($scheduleExclusion);
            });

            // Excludes any past time slots for the current day.
            $this->excludeTimePassedToday();
        });

        return $this->periods;
    }

    /**
     * Adds available time periods based on the employee's schedule.
     */
    protected function addAvailabilityFormSchedule(Carbon $date)
    {
        // Finds a schedule that covers the given date.
        if (!$schedule = $this->employee->schedules->where('starts_at', '<=', $date)->where('ends_at', '>=', $date)->first()) {
            return;
        }

        // Fetches working hours for the date. If none found, returns.
        if (![$startsAt, $endsAt] = $schedule->getWorkingHoursForDate($date)) {
            return;
        }

        // Adds the available period, adjusted for service duration.
        $this->periods = $this->periods->add(
            Period::make(
                start: $date->copy()->setTimeFromTimeString($startsAt),
                end: $date->copy()->setTimeFromTimeString($endsAt)->subMinutes($this->service->duration),
                precision: Precision::MINUTE()
            )
        );
    }

    /**
     * Removes periods that are marked as schedule exclusions.
     */
    protected function subtractScheduleExclusion(ScheduleExclusion $scheduleExclusion)
    {
        // Subtracts the exclusion period from the available periods.
        $this->periods = $this->periods->subtract(
            Period::make(
                start: $scheduleExclusion->starts_at,
                end: $scheduleExclusion->ends_at,
                precision: Precision::MINUTE()
            )
        );
    }

    /**
     * Removes time slots that have already passed today.
     */
    protected function excludeTimePassedToday()
    {
        $this->periods = $this->periods->subtract(
            Period::make(
                start: now()->startOfDay(),
                end: now()->endOfHour(),
                precision: Precision::MINUTE()
            )
        );
    }
}

<?php

namespace App\Booking\ScheduleAvailability;

use App\Models\ScheduleExclusion;
use Carbon\Carbon;
use Spatie\Period\PeriodCollection;

interface ScheduleAvailabilityInterface
{
    /**
     * Calculates the employee's availability for a given period.
     *
     * @param  Carbon  $startAt  The start date of the range.
     * @param  Carbon  $endsAt  The end date of the range.
     * @return PeriodCollection A collection of available periods.
     */
    public function forPeriod(Carbon $startAt, Carbon $endsAt): PeriodCollection;
}

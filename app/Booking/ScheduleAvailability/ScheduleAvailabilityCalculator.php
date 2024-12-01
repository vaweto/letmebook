<?php

namespace App\Booking\ScheduleAvailability;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

class ScheduleAvailabilityCalculator
{
    protected PeriodCollection $periods;

    public function __construct()
    {
        $this->periods = new PeriodCollection();
    }

    public function initializePeriods(): void
    {
        $this->periods = new PeriodCollection();
    }

    public function getPeriods(): PeriodCollection
    {
        return $this->periods;
    }

    public function addAvailabilityPeriod(Carbon $start, Carbon $end): void
    {
        $this->periods = $this->periods->add(
            Period::make(
                start: $start,
                end: $end,
                precision: Precision::MINUTE()
            )
        );
    }

    public function subtractExclusionPeriod(Carbon $start, Carbon $end): void
    {
        $this->periods = $this->periods->subtract(
            Period::make(
                start: $start,
                end: $end,
                precision: Precision::MINUTE()
            )
        );
    }

    public function excludeTimePassedToday(): void
    {
        $this->periods = $this->periods->subtract(
            Period::make(
                start: now()->startOfDay(),
                end: now()->endOfHour(),
                precision: Precision::MINUTE()
            )
        );
    }

    public function processDateRange(Carbon $startAt, Carbon $endsAt, callable $callback): void
    {
        collect(CarbonPeriod::create($startAt, $endsAt)->days())->each(function (Carbon $date) use ($callback) {
            $callback($date);
        });
    }
}

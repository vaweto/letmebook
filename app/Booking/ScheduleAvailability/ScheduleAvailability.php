<?php

namespace App\Booking\ScheduleAvailability;

use App\Booking\ScheduleAvailability\Providers\ScheduleProviderInterface;
use Carbon\Carbon;
use Spatie\Period\PeriodCollection;

class ScheduleAvailability implements ScheduleAvailabilityInterface
{
    public function __construct(
        protected ScheduleProviderInterface $provider,
        protected ScheduleAvailabilityCalculator $calculator,
    ) {
        $this->calculator->initializePeriods();
    }

    public function forPeriod(Carbon $startAt, Carbon $endsAt): PeriodCollection
    {
        $this->calculator->processDateRange($startAt, $endsAt, function (Carbon $date) {
            $this->addAvailabilityFromSchedule($date);
            $this->applyScheduleExclusions();
            $this->calculator->excludeTimePassedToday();
        });

        return $this->calculator->getPeriods();
    }

    protected function addAvailabilityFromSchedule(Carbon $date): void
    {
        $schedule = $this->provider->getSchedules()->first(fn ($schedule) =>
            (int)$schedule->day_of_week === $date->dayOfWeek() || $schedule->date === $date->toDateString()
        );

        if (!$schedule || ![$startsAt, $endsAt] = $schedule->getWorkingHoursForDate($date)) {
            return;
        }

        $this->calculator->addAvailabilityPeriod(
            $date->copy()->setTimeFromTimeString($startsAt),
            $date->copy()->setTimeFromTimeString($endsAt)->subMinutes($this->provider->getServiceDuration())
        );
    }

    protected function applyScheduleExclusions(): void
    {
        $this->provider->getScheduleExclusions()->each(fn ($exclusion) =>
        $this->calculator->subtractExclusionPeriod($exclusion->starts_at, $exclusion->ends_at)
        );
    }
}

<?php

namespace App\Booking;

use App\Models\Employee;
use App\Models\ScheduleExclusion;
use App\Models\Service;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

class ScheduleAvailability
{
    protected PeriodCollection $periods;

    public function __construct(protected Employee $employee, protected Service $service)
    {
        $this->periods = new PeriodCollection();
    }

    public function forPeriod(Carbon $startAt, Carbon $endsAt): PeriodCollection
    {
         collect(CarbonPeriod::create($startAt, $endsAt)->days())
            ->each(function (Carbon $date){
                $this->addAvailabilityFormSchedule($date);

                $this->employee->scheduleExclusions->each(function (ScheduleExclusion $scheduleExclusion) {
                    $this->subtractScheduleExclusion($scheduleExclusion);
                });

                $this->excludeTimePassedToday();
            });
         return $this->periods;
    }

    protected function addAvailabilityFormSchedule(Carbon $date)
    {
        if(
            !$schedule = $this->employee->schedules->where('starts_at', '<=', $date)->where('ends_at', '>=', $date)->first()
        ) {
            return;
        };

        if(![$startsAt, $endsAt] = $schedule->getWorkingHoursForDate($date)) {
            return;
        }

        $this->periods = $this->periods->add(
              Period::make(
                  start: $date->copy()->setTimeFromTimeString($startsAt),
                  end: $date->copy()->setTimeFromTimeString($endsAt)->subMinutes($this->service->duration),
                  precision: Precision::MINUTE()
              )
        );

    }

    protected function subtractScheduleExclusion(ScheduleExclusion $scheduleExclusion)
    {
        $this->periods = $this->periods->subtract(
            Period::make(
                start: $scheduleExclusion->starts_at,
                end: $scheduleExclusion->ends_at,
                precision: Precision::MINUTE()
            )
        );
    }

    protected function excludeTimePassedToday()
    {
        $this->periods = $this->periods->subtract(
            Period::make(
                start:now()->startOfDay(),
                end: now()->endOfHour(),
                precision: Precision::MINUTE()
            )
        );
    }
}

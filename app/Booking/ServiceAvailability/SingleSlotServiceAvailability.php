<?php

namespace App\Booking\ServiceAvailability;

use App\Booking\Date;
use App\Booking\DateCollection;
use App\Booking\ScheduleAvailability\Providers\ServiceScheduleProvider;
use App\Booking\ScheduleAvailability\ScheduleAvailability;
use App\Booking\ScheduleAvailability\ScheduleAvailabilityCalculator;
use App\Booking\Slot;
use App\Booking\SlotGenerator;
use App\Models\Service;
use Carbon\Carbon;
use Spatie\Period\Period;

class SingleSlotServiceAvailability implements BookingServiceInterface
{
    /**
     * @var ServiceScheduleProvider
     */
    private ServiceScheduleProvider $scheduleProvider;

    public function __construct(protected Service $service, protected ScheduleAvailabilityCalculator $scheduleAvailabilityCalculator) {
        $this->scheduleProvider = new ServiceScheduleProvider($this->service);
    }

    public function forPeriod(Carbon $startsAt, Carbon $endsAt): DateCollection
    {
        $range = (new SlotGenerator($startsAt, $endsAt))->generate($this->service->duration);
        $periods = (new ScheduleAvailability(
                provider: $this->scheduleProvider,
                calculator: $this->scheduleAvailabilityCalculator
            ))
            ->forPeriod($startsAt, $endsAt);

        foreach ($periods as $period) {
            $this->markAvailableSlots($range, $period);
        }

        return $range;
    }

    protected function markAvailableSlots(DateCollection $range, Period $period): void
    {
        $range->each(function (Date $date) use ($period) {
            $date->slots->each(function (Slot $slot) use ($period) {
                if ($period->contains($slot->time)) {
                    $slot->available = true;
                }
            });
        });
    }
}

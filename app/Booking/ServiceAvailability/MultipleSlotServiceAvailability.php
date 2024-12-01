<?php

namespace App\Booking\ServiceAvailability;

use App\Booking\Date;
use App\Booking\DateCollection;
use App\Booking\ScheduleAvailability\ScheduleAvailabilityWithEmployee;
use App\Booking\Slot;
use App\Booking\SlotGenerator;
use App\Models\Service;
use Carbon\Carbon;
use Spatie\Period\Period;

class MultipleSlotServiceAvailability implements BookingServiceInterface
{
    public function __construct(protected Service $service) {}

    public function forPeriod(Carbon $startsAt, Carbon $endsAt): \App\Booking\DateCollection
    {
        $range = (new SlotGenerator($startsAt, $endsAt))->generate($this->service->duration);
        $periods = (new ScheduleAvailabilityWithEmployee(null, $this->service))->forPeriod($startsAt, $endsAt);

        foreach ($periods as $period) {
            $this->addBookingSlots($range, $period);
        }

        return $range;
    }

    protected function addBookingSlots(DateCollection $range, Period $period): void
    {
        $range->each(function (Date $date) use ($period) {
            $date->slots->each(function (Slot $slot) use ($period) {
                if ($period->contains($slot->time)) {
                    $slot->incrementBookingCount();
                }
            });
        });
    }
}

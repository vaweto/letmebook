<?php

namespace App\Booking;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

// Responsible for generating a collection of dates and their associated time slots.
class SlotGenerator
{
    public function __construct(protected Carbon $startsAt, protected Carbon $endsAt) {}

    /**
     * Generates a collection of dates with time slots based on a given interval.
     *
     * @param int $interval The interval in minutes (e.g., 30 minutes).
     * @return DateCollection A collection of Date objects with slots.
     */
    public function generate(int $interval): DateCollection
    {
        $collection = new DateCollection();

        // Creates a period for each day between the start and end dates.
        $days = CarbonPeriod::create($this->startsAt, '1 day', $this->endsAt);

        foreach ($days as $day) {
            $date = new Date($day);

            // Creates time slots for the entire day at the specified interval.
            $times = CarbonPeriod::create($day->copy()->startOfDay(), $interval . ' minutes', $day->copy()->endOfDay());

            foreach ($times as $time) {
                $date->addSlot(new Slot($time));
            }

            $collection->push($date);
        }

        return $collection;
    }
}

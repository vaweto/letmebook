<?php

namespace App\Booking;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Date
{
    public Collection $slots;

    // Constructor initializes the date and an empty collection of slots
    public function __construct(public Carbon $date)
    {
        $this->slots = collect();
    }

    /**
     * Adds a new slot to the current date's collection of slots.
     *
     * @param Slot $slot The slot object to be added.
     */
    public function addSlot(Slot $slot)
    {
        $this->slots->push($slot);
    }

    /**
     * Checks if a specific time slot exists on this date.
     *
     * @param string $timeToCheck The time to check in 'HH:mm' format.
     * @return bool True if the slot exists, false otherwise.
     */
    public function containsSlot(string $timeToCheck)
    {
        // Checks if the slots collection has a slot matching the given time
        return $this->slots->contains(function (Slot $slot) use ($timeToCheck) {
            return $slot->time->toTimeString('minutes') === $timeToCheck;
        });
    }
}

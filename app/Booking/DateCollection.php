<?php

namespace App\Booking;

use Illuminate\Support\Collection;

class DateCollection extends Collection
{
    /**
     * Returns the first available date that has time slots.
     *
     * @return Date|null
     */
    public function firstAvailableDate()
    {
        return $this->first(fn(Date $date) => $date->slots->isNotEmpty());
    }

    /**
     * Finds a date within the collection by its string representation (YYYY-MM-DD).
     *
     * @param string $dateToCheck The date to look for.
     * @return Date|null
     */
    public function forDate(string $dateToCheck)
    {
        return $this->first(fn(Date $date) => $date->date->toDateString() === $dateToCheck);
    }
}

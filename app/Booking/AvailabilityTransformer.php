<?php

namespace App\Booking;

use App\Models\Employee;

class AvailabilityTransformer
{
    // The constructor accepts a DateCollection object representing available dates and times
    public function __construct(protected DateCollection $availability)
    {
    }

    /**
     * Converts the availability data to a string representation.
     * The output format is a JSON-like structure that includes:
     * - 'date': The date in 'YYYY-MM-DD' format
     * - 'slots': An array of time slots, each containing:
     *   - 'time': The slot time in 'HH:mm' format
     *   - 'employee': A list of employees available at that time slot
     */
    public function __toString(): string
    {
        // Transform each date in the collection to an array format
        return $this->availability->map(function (Date $date) {
            return [
                'date' => $date->date->toDateString(),
                'slots' => $date->slots->map(function (Slot $slot) {
                    return [
                        'time' => $slot->time->toTimeString('minutes'),
                        'employee' => $slot->employees->map(function (Employee $employee) {
                            return $employee->slug;
                        })->values()
                    ];
                })->values()
            ];
        })->values();
    }
}

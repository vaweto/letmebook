<?php

namespace App\Booking;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Slot
{
    public Collection $employees;
    public int $bookings = 0; // Track the current number of bookings

    public function __construct(public Carbon $time, public int $maxCapacity = 1)
    {
        $this->employees = collect();
    }

    public function addEmployee(Employee $employee)
    {
        $this->employees->push($employee);
    }

    public function hasEmployees(): bool
    {
        return $this->employees->isNotEmpty();
    }

    public function isFullyBooked(): bool
    {
        return $this->bookings >= $this->maxCapacity;
    }

    public function book()
    {
        if (!$this->isFullyBooked()) {
            $this->bookings++;
        }
    }
}

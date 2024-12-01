<?php

namespace App\Booking\ServiceAvailability;

use App\Booking\DateCollection;
use Illuminate\Support\Carbon;

interface BookingServiceInterface
{
    public function forPeriod(Carbon $startsAt, Carbon $endsAt): DateCollection;
}

<?php

namespace App\Booking\ScheduleAvailability\Providers;

use Illuminate\Support\Collection;

interface ScheduleProviderInterface
{
    public function getSchedules(): Collection;
    public function getScheduleExclusions(): Collection;

    public function getServiceDuration(): int;
}

<?php

namespace App\Booking\ScheduleAvailability\Providers;

use App\Models\Service;
use Illuminate\Support\Collection;

class ServiceScheduleProvider implements ScheduleProviderInterface
{
    public function __construct(protected Service $service) {}

    public function getSchedules(): Collection
    {
        return $this->service->schedules;
    }

    public function getScheduleExclusions(): Collection
    {
        return $this->service->scheduleExclusions;
    }

    public function getServiceDuration(): int
    {
        return $this->service->duration;
    }
}

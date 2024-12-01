<?php

namespace App\Booking\ScheduleAvailability\Providers;

use App\Models\Employee;
use App\Models\Service;
use Illuminate\Support\Collection;

class EmployeeScheduleProvider implements ScheduleProviderInterface
{
    public function __construct(protected Employee $employee, protected Service $service) {}

    public function getSchedules(): Collection
    {
        return $this->employee->schedules;
    }

    public function getScheduleExclusions(): Collection
    {
        return $this->employee->scheduleExclusions;
    }

    public function getServiceDuration(): int
    {
        return $this->service->duration;
    }
}

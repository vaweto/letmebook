<?php

namespace App\Booking\Factory;

use App\Booking\ServiceAvailability\BookingServiceInterface;
use App\Booking\ServiceAvailability\MultipleSlotServiceWithEmployeesAvailability;
use App\Booking\ServiceAvailability\SingleSlotWithEmployeeAvailability;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class BookingServiceFactory
{
    public static function create(Collection $employees, Service $service): BookingServiceInterface
    {
        return match ($service->type) {
            Service::TYPE_SINGLE => new SingleSlotWithEmployeeAvailability($employees, $service),
            Service::TYPE_MULTI => new MultipleSlotServiceWithEmployeesAvailability($employees, $service),
            default => throw new \InvalidArgumentException("Unsupported service type"),
        };
    }
}

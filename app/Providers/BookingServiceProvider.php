<?php

namespace App\Providers;

use App\Booking\ScheduleAvailability\ScheduleAvailabilityCalculator;
use Illuminate\Support\ServiceProvider;

class BookingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ScheduleAvailabilityCalculator::class, fn() => new ScheduleAvailabilityCalculator());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}

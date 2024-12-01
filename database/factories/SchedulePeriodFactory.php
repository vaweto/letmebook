<?php

namespace Database\Factories;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchedulePeriod>
 */
class SchedulePeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $startDate = Carbon::now();
        $endDate = (clone $startDate)->addYear();

        return [
            'schedule_id' => Schedule::factory(),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ];
    }

    /**
     * Set a custom date range for the schedule period.
     */
    public function customPeriod(Carbon $startDate, Carbon $endDate)
    {
        return $this->state(fn() => [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ]);
    }
}

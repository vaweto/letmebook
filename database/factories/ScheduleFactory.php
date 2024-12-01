<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'schedulable_id' => Service::factory()->create()->id,
            'schedulable_type' => Service::class,
            'day_of_week' => $this->faker->numberBetween(0, 6), // 0 = Sunday, 6 = Saturday
            'is_recurring' => 1,
            'starts_at' => '09:00',
            'ends_at' => '17:00',
        ];
    }
}

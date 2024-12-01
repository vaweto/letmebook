<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduleExclusion>
 */
class ScheduleExclusionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $startsAt = Carbon::now()->addDays($this->faker->numberBetween(1, 10))->setTime($this->faker->numberBetween(9, 16), 0);
        $endsAt = (clone $startsAt)->addMinutes(60);

        return [
            'excludable_id' => Service::factory()->create()->id,
            'excludable_type' => Service::class,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];
    }
}

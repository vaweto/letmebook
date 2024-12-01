<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(12),
            'price' => 3000,
            'duration' => $this->faker->randomElement([30, 45, 60]), // Duration in minutes
            'number_of_slots' => $this->faker->randomElement([1, 3, 5]), // Single or multiple slots
        ];
    }
}

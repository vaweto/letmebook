<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition()
    {
        $startsAt = Carbon::now()->addDays($this->faker->numberBetween(1, 30))->setTime($this->faker->numberBetween(9, 16), 0);
        $endsAt = (clone $startsAt)->addMinutes($this->faker->randomElement([30, 45, 60]));

        return [
            'employee_id' => Employee::factory(),
            'service_id' => Service::factory(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'cancelled_at' => null,
            'name' => 'vagelis',
            'email' => 'vagelis@test.gr'
        ];
    }
}

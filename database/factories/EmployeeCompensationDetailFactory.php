<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeeCompensationDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeCompensationDetail>
 */
class EmployeeCompensationDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'daily_rate' => fake()->randomFloat(2, 500, 900),
            'night_differential_rate' => fake()->randomFloat(2, 50, 150),
            'sss_number' => fake()->bothify('SSS-########'),
            'philhealth_number' => fake()->bothify('PH-########'),
            'pagibig_number' => fake()->bothify('HDMF-########'),
        ];
    }
}

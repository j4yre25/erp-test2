<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'employee_number' => fake()->unique()->bothify('EMP-####'),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'contact_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'employee_type' => 'guard',
            'employment_status' => 'active',
        ];
    }
}

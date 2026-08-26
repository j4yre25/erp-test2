<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Deployment;
use App\Models\Employee;
use App\Models\PayrollLine;
use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PayrollLine>
 */
class PayrollLineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => fn (): int => Employee::factory()->create()->id,
            'payroll_period_id' => function (): int {
                $client = Client::create([
                    'name' => fake()->company(),
                    'payroll_period' => 'semi_monthly',
                    'cutoff_type' => 'fixed_days',
                    'first_cutoff_day' => 15,
                    'second_cutoff_day' => 30,
                    'payroll_frequency' => 'semi_monthly',
                ]);

                return PayrollPeriod::create([
                    'client_id' => $client->id,
                    'start_date' => '2026-08-01',
                    'end_date' => '2026-08-15',
                    'status' => 'closed',
                ])->id;
            },
            'deployment_id' => function (array $attributes): int {
                $payrollPeriod = PayrollPeriod::findOrFail($attributes['payroll_period_id']);

                return Deployment::create([
                    'client_id' => $payrollPeriod->client_id,
                    'employee_id' => $attributes['employee_id'],
                    'post_name' => fake()->jobTitle(),
                    'start_date' => $payrollPeriod->start_date,
                    'billing_rate' => fake()->randomFloat(2, 700, 1200),
                ])->id;
            },
            'regular_hours' => 8,
            'overtime_hours' => 0,
            'night_diff_hours' => 0,
            'base_pay' => fake()->randomFloat(2, 500, 900),
            'night_diff_pay' => 0,
            'employer_sss' => fake()->randomFloat(2, 50, 150),
            'employer_philhealth' => fake()->randomFloat(2, 25, 100),
            'employer_pagibig' => fake()->randomFloat(2, 50, 100),
            'gross_amount' => fake()->randomFloat(2, 500, 1200),
            'billable_amount' => fake()->randomFloat(2, 700, 1500),
        ];
    }
}

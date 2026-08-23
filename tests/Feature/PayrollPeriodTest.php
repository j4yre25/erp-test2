<?php

use App\Models\Client;
use App\Models\Deployment;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollPeriodStatus;
use App\Models\Role;
use App\Models\User;

function actingAsPayroll(): User
{
    $role = Role::query()->firstOrCreate(['role_name' => 'payroll']);
    $user = User::factory()->create(['role_id' => $role->id]);
    test()->actingAs($user);

    return $user;
}

test('payroll user can open a new payroll period for a client', function () {
    actingAsPayroll();

    $client = Client::query()->create(['name' => 'Mega Mall']);

    $response = $this->post(route('payroll-periods.store'), [
        'client_id' => $client->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    $period = PayrollPeriod::query()->first();

    $response->assertRedirect(route('payroll-periods.show', $period));

    expect($period->isOpen())->toBeTrue()
        ->and($period->client_id)->toBe($client->id);
});

test('closing a payroll period computes pay lines with regular, night diff, employer contributions, and billables', function () {
    actingAsPayroll();

    $client = Client::query()->create(['name' => 'City Bank Tower']);

    $guard = Employee::factory()->create([
        'employee_type' => 'guard',
        'employment_status' => 'active',
        'availability_status' => 'deployed',
    ]);

    $guard->compensationDetail()->create([
        'daily_rate' => 640.00, // hourly: 80.00
        'night_differential_rate' => 8.00,
        'sss_number' => 'SSS-1234',
        'philhealth_number' => 'PH-5678',
        'pagibig_number' => 'PAG-9012',
    ]);

    $deployment = Deployment::query()->create([
        'client_id' => $client->id,
        'employee_id' => $guard->id,
        'post_name' => 'Main Lobby',
        'start_date' => '2026-08-01',
        'billing_rate' => 1200.00, // hourly billing: 150.00
        'status' => 'active',
    ]);

    $openStatus = PayrollPeriodStatus::query()->firstOrCreate(['code' => 'open'], ['name' => 'Open']);

    $period = PayrollPeriod::query()->create([
        'client_id' => $client->id,
        'payroll_period_status_id' => $openStatus->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    $response = $this->post(route('payroll-periods.close', $period));

    $response->assertRedirect(route('payroll-periods.show', $period));

    $period->refresh();
    expect($period->isClosed())->toBeTrue();

    $this->assertDatabaseHas('payroll_lines', [
        'payroll_period_id' => $period->id,
        'deployment_id' => $deployment->id,
        'employee_id' => $guard->id,
    ]);

    $line = $period->payrollLines()->first();
    expect((float) $line->base_pay)->toBeGreaterThan(0)
        ->and((float) $line->employer_sss)->toBeGreaterThan(0)
        ->and((float) $line->employer_philhealth)->toBeGreaterThan(0)
        ->and((float) $line->employer_pagibig)->toBeGreaterThan(0)
        ->and((float) $line->gross_amount)->toBeGreaterThan(0)
        ->and((float) $line->billable_amount)->toBeGreaterThan(0);
});

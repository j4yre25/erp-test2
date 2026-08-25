<?php

use App\Models\Client;
use App\Models\DailyTimeRecord;
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

function actingAsAr(): User
{
    $role = Role::query()->firstOrCreate(['role_name' => 'ar']);
    $user = User::factory()->create(['role_id' => $role->id]);
    test()->actingAs($user);

    return $user;
}

function actingAsGm(): User
{
    $role = Role::query()->firstOrCreate(['role_name' => 'general manager']);
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

test('opening an exact duplicate payroll period returns validation error instead of 500', function () {
    actingAsPayroll();

    $client = Client::query()->create(['name' => 'Mega Mall']);
    $openStatus = PayrollPeriodStatus::query()->firstOrCreate(['code' => 'open'], ['name' => 'Open']);

    PayrollPeriod::query()->create([
        'client_id' => $client->id,
        'payroll_period_status_id' => $openStatus->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    $response = $this->post(route('payroll-periods.store'), [
        'client_id' => $client->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    $response->assertSessionHasErrors('end_date');
});

test('opening an overlapping payroll period for the same client fails validation', function () {
    actingAsPayroll();

    $client = Client::query()->create(['name' => 'Mega Mall']);
    $openStatus = PayrollPeriodStatus::query()->firstOrCreate(['code' => 'open'], ['name' => 'Open']);

    PayrollPeriod::query()->create([
        'client_id' => $client->id,
        'payroll_period_status_id' => $openStatus->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    // Overlapping period (August 10 to August 20)
    $response = $this->post(route('payroll-periods.store'), [
        'client_id' => $client->id,
        'start_date' => '2026-08-10',
        'end_date' => '2026-08-20',
    ]);

    $response->assertSessionHasErrors('end_date');
});

test('payroll period duration cannot exceed 31 days', function () {
    actingAsPayroll();

    $client = Client::query()->create(['name' => 'Mega Mall']);

    $response = $this->post(route('payroll-periods.store'), [
        'client_id' => $client->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-09-15', // 46 days
    ]);

    $response->assertSessionHasErrors('end_date');
});

test('payroll period end_date must be after_or_equal start_date', function () {
    actingAsPayroll();

    $client = Client::query()->create(['name' => 'Mega Mall']);

    $response = $this->post(route('payroll-periods.store'), [
        'client_id' => $client->id,
        'start_date' => '2026-08-15',
        'end_date' => '2026-08-01',
    ]);

    $response->assertSessionHasErrors('end_date');
});

test('AR and GM roles cannot open or close payroll periods', function () {
    $client = Client::query()->create(['name' => 'Delta Tower']);
    $openStatus = PayrollPeriodStatus::query()->firstOrCreate(['code' => 'open'], ['name' => 'Open']);
    $period = PayrollPeriod::query()->create([
        'client_id' => $client->id,
        'payroll_period_status_id' => $openStatus->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    // AR role attempts to create and close
    actingAsAr();
    $this->post(route('payroll-periods.store'), [
        'client_id' => $client->id,
        'start_date' => '2026-08-16',
        'end_date' => '2026-08-31',
    ])->assertForbidden();

    $this->post(route('payroll-periods.close', $period))->assertForbidden();

    // GM role attempts to create and close
    actingAsGm();
    $this->post(route('payroll-periods.store'), [
        'client_id' => $client->id,
        'start_date' => '2026-08-16',
        'end_date' => '2026-08-31',
    ])->assertForbidden();

    $this->post(route('payroll-periods.close', $period))->assertForbidden();
});

test('closing an already closed period returns graceful error toast instead of 500', function () {
    actingAsPayroll();

    $client = Client::query()->create(['name' => 'Delta Tower']);
    $closedStatus = PayrollPeriodStatus::query()->firstOrCreate(['code' => 'closed'], ['name' => 'Closed']);
    $period = PayrollPeriod::query()->create([
        'client_id' => $client->id,
        'payroll_period_status_id' => $closedStatus->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    // Close action directly or via controller
    $response = $this->post(route('payroll-periods.close', $period));

    // Policy blocks non-open period with 403, and if action is called directly it throws InvalidArgumentException
    $response->assertForbidden();
});

test('closing a payroll period computes pay lines with seeded DTRs, exact OT (1.25x), ND, and statutory deductions', function () {
    actingAsPayroll();

    $client = Client::query()->create(['name' => 'City Bank Tower']);

    $guard = Employee::factory()->create([
        'employee_type' => 'guard',
        'employment_status' => 'active',
        'availability_status' => 'deployed',
    ]);

    $guard->compensationDetail()->create([
        'daily_rate' => 800.00, // hourly: 100.00
        'night_differential_rate' => 10.00,
        'sss_number' => 'SSS-1234',
        'philhealth_number' => 'PH-5678',
        'pagibig_number' => 'PAG-9012',
    ]);

    $deployment = Deployment::query()->create([
        'client_id' => $client->id,
        'employee_id' => $guard->id,
        'post_name' => 'Main Lobby',
        'start_date' => '2026-08-01',
        'billing_rate' => 1600.00, // hourly billing: 200.00
        'status' => 'active',
    ]);

    $openStatus = PayrollPeriodStatus::query()->firstOrCreate(['code' => 'open'], ['name' => 'Open']);

    $period = PayrollPeriod::query()->create([
        'client_id' => $client->id,
        'payroll_period_status_id' => $openStatus->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    // Seed 2 DTRs with Reg, OT, and ND
    DailyTimeRecord::query()->create([
        'payroll_period_id' => $period->id,
        'deployment_id' => $deployment->id,
        'work_date' => '2026-08-01',
        'regular_hours' => 8.00,
        'overtime_hours' => 2.00,
        'night_diff_hours' => 4.00,
        'status' => 'approved',
    ]);

    DailyTimeRecord::query()->create([
        'payroll_period_id' => $period->id,
        'deployment_id' => $deployment->id,
        'work_date' => '2026-08-02',
        'regular_hours' => 8.00,
        'overtime_hours' => 0.00,
        'night_diff_hours' => 0.00,
        'status' => 'approved',
    ]);

    // Total: Reg = 16h, OT = 2h, ND = 4h
    // Hourly rate = 100.00
    // Base pay = (16 * 100) + (2 * 100 * 1.25) = 1600 + 250 = 1850.00
    // ND pay = 4 * 10 = 40.00
    // Gross = 1850 + 40 = 1890.00
    // Employer SSS = 1850 * 0.095 = 175.75
    // Employer PhilHealth = 1850 * 0.025 = 46.25
    // Employer Pag-IBIG = min(200, 1850 * 0.02) = 37.00
    // Billing hourly = 200.00
    // Billable = (16 * 200) + (2 * 200 * 1.25) + 40 = 3200 + 500 + 40 = 3740.00

    $response = $this->post(route('payroll-periods.close', $period));
    $response->assertRedirect(route('payroll-periods.show', $period));

    $period->refresh();
    expect($period->isClosed())->toBeTrue();

    $line = $period->payrollLines()->first();
    expect((float) $line->regular_hours)->toBe(16.0)
        ->and((float) $line->overtime_hours)->toBe(2.0)
        ->and((float) $line->night_diff_hours)->toBe(4.0)
        ->and((float) $line->base_pay)->toBe(1850.00)
        ->and((float) $line->night_diff_pay)->toBe(40.00)
        ->and((float) $line->gross_amount)->toBe(1890.00)
        ->and((float) $line->employer_sss)->toBe(175.75)
        ->and((float) $line->employer_philhealth)->toBe(46.25)
        ->and((float) $line->employer_pagibig)->toBe(37.00)
        ->and((float) $line->billable_amount)->toBe(3740.00);
});

test('closing a payroll period properly derives night differential between 22:00 and 06:00 from timestamps', function () {
    actingAsPayroll();

    $client = Client::query()->create(['name' => 'Metro Logistics Hub']);

    $guard = Employee::factory()->create([
        'employee_type' => 'guard',
        'employment_status' => 'active',
        'availability_status' => 'deployed',
    ]);

    $guard->compensationDetail()->create([
        'daily_rate' => 800.00, // hourly: 100.00
        'night_differential_rate' => 10.00,
    ]);

    $deployment = Deployment::query()->create([
        'client_id' => $client->id,
        'employee_id' => $guard->id,
        'post_name' => 'Gate 1',
        'start_date' => '2026-08-01',
        'billing_rate' => 1600.00,
        'status' => 'active',
    ]);

    $openStatus = PayrollPeriodStatus::query()->firstOrCreate(['code' => 'open'], ['name' => 'Open']);

    $period = PayrollPeriod::query()->create([
        'client_id' => $client->id,
        'payroll_period_status_id' => $openStatus->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    // Shift: 18:00 to 02:00 (8 hours shift, 4 hours ND: 22:00 to 02:00)
    DailyTimeRecord::query()->create([
        'payroll_period_id' => $period->id,
        'deployment_id' => $deployment->id,
        'work_date' => '2026-08-01',
        'time_in' => '2026-08-01 18:00:00',
        'time_out' => '2026-08-02 02:00:00',
        'regular_hours' => 8.00,
        'overtime_hours' => 0.00,
        'night_diff_hours' => 0.00, // left as 0 to test timestamp derivation
        'status' => 'approved',
    ]);

    $response = $this->post(route('payroll-periods.close', $period));
    $response->assertRedirect(route('payroll-periods.show', $period));

    $period->refresh();
    $line = $period->payrollLines()->first();
    expect((float) $line->night_diff_hours)->toBe(4.0)
        ->and((float) $line->night_diff_pay)->toBe(40.00);
});

test('inactive deployments and inactive employees are excluded from payroll computation', function () {
    actingAsPayroll();

    $client = Client::query()->create(['name' => 'Plaza Center']);

    // Active guard with inactive deployment
    $guard1 = Employee::factory()->create(['employment_status' => 'active']);
    $guard1->compensationDetail()->create(['daily_rate' => 600.00]);
    Deployment::query()->create([
        'client_id' => $client->id,
        'employee_id' => $guard1->id,
        'start_date' => '2026-08-01',
        'billing_rate' => 1200.00,
        'status' => 'inactive', // inactive deployment
    ]);

    // Inactive guard with active deployment
    $guard2 = Employee::factory()->create(['employment_status' => 'inactive']);
    $guard2->compensationDetail()->create(['daily_rate' => 600.00]);
    Deployment::query()->create([
        'client_id' => $client->id,
        'employee_id' => $guard2->id,
        'start_date' => '2026-08-01',
        'billing_rate' => 1200.00,
        'status' => 'active',
    ]);

    // Active guard with active deployment
    $guard3 = Employee::factory()->create(['employment_status' => 'active']);
    $guard3->compensationDetail()->create(['daily_rate' => 600.00]);
    $activeDeployment = Deployment::query()->create([
        'client_id' => $client->id,
        'employee_id' => $guard3->id,
        'start_date' => '2026-08-01',
        'billing_rate' => 1200.00,
        'status' => 'active',
    ]);

    $openStatus = PayrollPeriodStatus::query()->firstOrCreate(['code' => 'open'], ['name' => 'Open']);
    $period = PayrollPeriod::query()->create([
        'client_id' => $client->id,
        'payroll_period_status_id' => $openStatus->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    $this->post(route('payroll-periods.close', $period));

    expect($period->payrollLines()->count())->toBe(1)
        ->and($period->payrollLines()->first()->employee_id)->toBe($guard3->id);
});

test('mid-period deployment proration computes correct days without DTRs', function () {
    actingAsPayroll();

    $client = Client::query()->create(['name' => 'Harbor Point']);

    $guard = Employee::factory()->create([
        'employee_type' => 'guard',
        'employment_status' => 'active',
    ]);

    $guard->compensationDetail()->create([
        'daily_rate' => 800.00, // hourly: 100.00
    ]);

    // Deployed starting August 11 during August 1 - 15 period (5 active days: Aug 11, 12, 13, 14, 15)
    $deployment = Deployment::query()->create([
        'client_id' => $client->id,
        'employee_id' => $guard->id,
        'start_date' => '2026-08-11',
        'billing_rate' => 1600.00,
        'status' => 'active',
    ]);

    $openStatus = PayrollPeriodStatus::query()->firstOrCreate(['code' => 'open'], ['name' => 'Open']);
    $period = PayrollPeriod::query()->create([
        'client_id' => $client->id,
        'payroll_period_status_id' => $openStatus->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    $this->post(route('payroll-periods.close', $period));

    $line = $period->payrollLines()->first();
    // 5 days * 8 hours = 40 regular hours
    expect((float) $line->regular_hours)->toBe(40.0)
        ->and((float) $line->base_pay)->toBe(4000.00); // 40h * 100.00
});

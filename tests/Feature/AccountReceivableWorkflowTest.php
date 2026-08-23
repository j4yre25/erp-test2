<?php

use App\Models\AccountReceivable;
use App\Models\AccountReceivableStatus;
use App\Models\Client;
use App\Models\Deployment;
use App\Models\Employee;
use App\Models\PayrollLine;
use App\Models\PayrollPeriod;
use App\Models\PayrollPeriodStatus;
use App\Models\Role;
use App\Models\User;

function createTestUserWithRole(string $roleName): User
{
    $role = Role::query()->firstOrCreate(['role_name' => $roleName]);

    return User::factory()->create(['role_id' => $role->id]);
}

function getArStatus(string $code): AccountReceivableStatus
{
    return AccountReceivableStatus::query()->firstOrCreate(['code' => $code], ['name' => ucfirst($code)]);
}

function getPeriodStatus(string $code): PayrollPeriodStatus
{
    return PayrollPeriodStatus::query()->firstOrCreate(['code' => $code], ['name' => ucfirst($code)]);
}

function setupClosedPeriodWithLines(): array
{
    $client = Client::query()->create(['name' => 'Apex Tower']);

    $guard = Employee::factory()->create([
        'employee_type' => 'guard',
        'employment_status' => 'active',
        'availability_status' => 'deployed',
    ]);

    $guard->compensationDetail()->create([
        'daily_rate' => 600.00,
        'night_differential_rate' => 7.50,
    ]);

    $deployment = Deployment::query()->create([
        'client_id' => $client->id,
        'employee_id' => $guard->id,
        'start_date' => '2026-08-01',
        'billing_rate' => 1200.00,
        'status' => 'active',
    ]);

    $period = PayrollPeriod::query()->create([
        'client_id' => $client->id,
        'payroll_period_status_id' => getPeriodStatus('closed')->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
    ]);

    $line = PayrollLine::query()->create([
        'payroll_period_id' => $period->id,
        'deployment_id' => $deployment->id,
        'employee_id' => $guard->id,
        'regular_hours' => 120,
        'overtime_hours' => 0,
        'night_diff_hours' => 0,
        'base_pay' => 9000.00,
        'night_diff_pay' => 0,
        'employer_sss' => 855.00,
        'employer_philhealth' => 225.00,
        'employer_pagibig' => 180.00,
        'gross_amount' => 9000.00,
        'billable_amount' => 18000.00,
    ]);

    return compact('client', 'period', 'guard', 'deployment', 'line');
}

test('AR can generate draft SOA from closed payroll period', function () {
    $arUser = createTestUserWithRole('ar');
    test()->actingAs($arUser);

    ['period' => $period] = setupClosedPeriodWithLines();

    $response = $this->post(route('statement-of-accounts.store'), [
        'payroll_period_id' => $period->id,
        'account_receivable_date' => '2026-08-16',
        'tax_amount' => 2160.00, // 12% VAT
    ]);

    $soa = AccountReceivable::query()->first();
    $response->assertRedirect(route('statement-of-accounts.show', $soa));

    expect($soa->isDraft())->toBeTrue()
        ->and((float) $soa->subtotal)->toBe(18000.00)
        ->and((float) $soa->tax_amount)->toBe(2160.00)
        ->and((float) $soa->total_amount)->toBe(20160.00);
});

test('AR can edit SOA while in draft mode', function () {
    $arUser = createTestUserWithRole('ar');
    test()->actingAs($arUser);

    ['client' => $client, 'period' => $period] = setupClosedPeriodWithLines();

    $soa = AccountReceivable::query()->create([
        'client_id' => $client->id,
        'payroll_period_id' => $period->id,
        'account_receivable_status_id' => getArStatus('draft')->id,
        'account_receivable_number' => 'SOA-TEST-001',
        'account_receivable_date' => '2026-08-16',
        'subtotal' => 18000.00,
        'tax_amount' => 0.00,
        'total_amount' => 18000.00,
        'running_balance' => 18000.00,
    ]);

    $response = $this->patch(route('statement-of-accounts.update', $soa), [
        'account_receivable_date' => '2026-08-17',
        'subtotal' => 18000.00,
        'tax_amount' => 2160.00,
    ]);

    $response->assertRedirect(route('statement-of-accounts.show', $soa));

    $soa->refresh();
    expect((float) $soa->tax_amount)->toBe(2160.00)
        ->and((float) $soa->total_amount)->toBe(20160.00);
});

test('AR can submit draft SOA to GM for approval', function () {
    $arUser = createTestUserWithRole('ar');
    test()->actingAs($arUser);

    ['client' => $client, 'period' => $period] = setupClosedPeriodWithLines();

    $soa = AccountReceivable::query()->create([
        'client_id' => $client->id,
        'payroll_period_id' => $period->id,
        'account_receivable_status_id' => getArStatus('draft')->id,
        'account_receivable_number' => 'SOA-TEST-002',
        'account_receivable_date' => '2026-08-16',
        'subtotal' => 18000.00,
        'tax_amount' => 2160.00,
        'total_amount' => 20160.00,
        'running_balance' => 20160.00,
    ]);

    $response = $this->post(route('statement-of-accounts.submit', $soa));

    $response->assertRedirect(route('statement-of-accounts.show', $soa));

    $soa->refresh();
    expect($soa->isSubmitted())->toBeTrue()
        ->and($soa->submitted_by)->toBe($arUser->id)
        ->and($soa->submitted_at)->not->toBeNull();
});

test('AR cannot edit SOA after it has been submitted', function () {
    $arUser = createTestUserWithRole('ar');
    test()->actingAs($arUser);

    ['client' => $client, 'period' => $period] = setupClosedPeriodWithLines();

    $soa = AccountReceivable::query()->create([
        'client_id' => $client->id,
        'payroll_period_id' => $period->id,
        'account_receivable_status_id' => getArStatus('submitted')->id,
        'account_receivable_number' => 'SOA-TEST-003',
        'account_receivable_date' => '2026-08-16',
        'subtotal' => 18000.00,
        'tax_amount' => 2160.00,
        'total_amount' => 20160.00,
        'running_balance' => 20160.00,
        'submitted_by' => $arUser->id,
        'submitted_at' => now(),
    ]);

    $this->patch(route('statement-of-accounts.update', $soa), [
        'account_receivable_date' => '2026-08-17',
        'subtotal' => 19000.00,
        'tax_amount' => 2280.00,
    ])->assertForbidden();
});

test('Payroll roles cannot submit or approve billing', function () {
    $payrollUser = createTestUserWithRole('payroll');
    test()->actingAs($payrollUser);

    ['client' => $client, 'period' => $period] = setupClosedPeriodWithLines();

    $soa = AccountReceivable::query()->create([
        'client_id' => $client->id,
        'payroll_period_id' => $period->id,
        'account_receivable_status_id' => getArStatus('draft')->id,
        'account_receivable_number' => 'SOA-TEST-004',
        'account_receivable_date' => '2026-08-16',
        'subtotal' => 18000.00,
        'tax_amount' => 2160.00,
        'total_amount' => 20160.00,
        'running_balance' => 20160.00,
    ]);

    $this->post(route('statement-of-accounts.submit', $soa))->assertForbidden();

    $soa->update(['account_receivable_status_id' => getArStatus('submitted')->id]);
    $this->post(route('gm.statement-of-accounts.approve', $soa), [
        'due_date' => '2026-09-01',
    ])->assertForbidden();
});

test('AR roles cannot approve their own SOA submissions', function () {
    $arUser = createTestUserWithRole('ar');
    test()->actingAs($arUser);

    ['client' => $client, 'period' => $period] = setupClosedPeriodWithLines();

    $soa = AccountReceivable::query()->create([
        'client_id' => $client->id,
        'payroll_period_id' => $period->id,
        'account_receivable_status_id' => getArStatus('submitted')->id,
        'account_receivable_number' => 'SOA-TEST-005',
        'account_receivable_date' => '2026-08-16',
        'subtotal' => 18000.00,
        'tax_amount' => 2160.00,
        'total_amount' => 20160.00,
        'running_balance' => 20160.00,
        'submitted_by' => $arUser->id,
        'submitted_at' => now(),
    ]);

    $this->post(route('gm.statement-of-accounts.approve', $soa), [
        'due_date' => '2026-09-01',
    ])->assertForbidden();
});

test('GM roles cannot create or directly edit SOAs', function () {
    $gmUser = createTestUserWithRole('general manager');
    test()->actingAs($gmUser);

    ['client' => $client, 'period' => $period] = setupClosedPeriodWithLines();

    $this->post(route('statement-of-accounts.store'), [
        'payroll_period_id' => $period->id,
        'account_receivable_date' => '2026-08-16',
    ])->assertForbidden();

    $soa = AccountReceivable::query()->create([
        'client_id' => $client->id,
        'payroll_period_id' => $period->id,
        'account_receivable_status_id' => getArStatus('draft')->id,
        'account_receivable_number' => 'SOA-TEST-006',
        'account_receivable_date' => '2026-08-16',
        'subtotal' => 18000.00,
        'tax_amount' => 2160.00,
        'total_amount' => 20160.00,
        'running_balance' => 20160.00,
    ]);

    $this->patch(route('statement-of-accounts.update', $soa), [
        'account_receivable_date' => '2026-08-17',
        'subtotal' => 20000.00,
        'tax_amount' => 2400.00,
    ])->assertForbidden();
});

test('GM can reject submitted SOA with reason and revert it to draft for revision', function () {
    $arUser = createTestUserWithRole('ar');
    $gmUser = createTestUserWithRole('general manager');

    ['client' => $client, 'period' => $period] = setupClosedPeriodWithLines();

    $soa = AccountReceivable::query()->create([
        'client_id' => $client->id,
        'payroll_period_id' => $period->id,
        'account_receivable_status_id' => getArStatus('submitted')->id,
        'account_receivable_number' => 'SOA-TEST-007',
        'account_receivable_date' => '2026-08-16',
        'subtotal' => 18000.00,
        'tax_amount' => 2160.00,
        'total_amount' => 20160.00,
        'running_balance' => 20160.00,
        'submitted_by' => $arUser->id,
        'submitted_at' => now(),
    ]);

    test()->actingAs($gmUser);

    $response = $this->post(route('gm.statement-of-accounts.reject', $soa), [
        'rejection_reason' => 'Missing overtime verification documents.',
    ]);

    $response->assertRedirect(route('gm.statement-of-accounts.index'));

    $soa->refresh();
    expect($soa->isRejected() || $soa->isDraft())->toBeTrue()
        ->and($soa->rejected_by)->toBe($gmUser->id)
        ->and($soa->rejection_reason)->toBe('Missing overtime verification documents.');

    // AR can now edit again
    test()->actingAs($arUser);
    $this->patch(route('statement-of-accounts.update', $soa), [
        'account_receivable_date' => '2026-08-18',
        'subtotal' => 18000.00,
        'tax_amount' => 2160.00,
    ])->assertRedirect(route('statement-of-accounts.show', $soa));
});

test('GM can approve submitted SOA, set due date, and book it as official receivable', function () {
    $arUser = createTestUserWithRole('ar');
    $gmUser = createTestUserWithRole('general manager');

    ['client' => $client, 'period' => $period] = setupClosedPeriodWithLines();

    $soa = AccountReceivable::query()->create([
        'client_id' => $client->id,
        'payroll_period_id' => $period->id,
        'account_receivable_status_id' => getArStatus('submitted')->id,
        'account_receivable_number' => 'SOA-TEST-008',
        'account_receivable_date' => '2026-08-16',
        'subtotal' => 18000.00,
        'tax_amount' => 2160.00,
        'total_amount' => 20160.00,
        'running_balance' => 0.00,
        'submitted_by' => $arUser->id,
        'submitted_at' => now(),
    ]);

    test()->actingAs($gmUser);

    $response = $this->post(route('gm.statement-of-accounts.approve', $soa), [
        'due_date' => '2026-09-05',
    ]);

    $response->assertRedirect(route('gm.statement-of-accounts.index'));

    $soa->refresh();
    expect($soa->isApproved())->toBeTrue()
        ->and($soa->due_date->toDateString())->toBe('2026-09-05')
        ->and($soa->approved_by)->toBe($gmUser->id)
        ->and((float) $soa->running_balance)->toBe(20160.00);
});

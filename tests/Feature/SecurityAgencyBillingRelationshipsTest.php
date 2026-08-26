<?php

use App\Models\AccountReceivable;
use App\Models\AccountReceivableEntry;
use App\Models\Client;
use App\Models\DailyTimeRecord;
use App\Models\Deployment;
use App\Models\Employee;
use App\Models\EmployeeCompensationDetail;
use App\Models\Payment;
use App\Models\PayrollLine;
use App\Models\PayrollPeriod;
use App\Models\RateType;
use App\Models\Role;
use App\Models\TenderType;
use App\Models\User;
use App\Models\UserRateHistory;

test('security agency billing records follow the payroll period to receivable entry chain', function () {
    $arRole = Role::create([
        'role_name' => 'Account Receivable',
    ]);

    $gmRole = Role::create([
        'role_name' => 'General Manager',
    ]);

    $arUser = User::factory()->create([
        'role_id' => $arRole->id,
    ]);

    $gmUser = User::factory()->create([
        'role_id' => $gmRole->id,
    ]);

    $employee = Employee::create([
        'employee_number' => 'SG-2026-0001',
        'first_name' => 'Juan',
        'last_name' => 'Dela Cruz',
        'contact_number' => '09171234567',
        'address' => 'Manila',
        'employee_type' => 'guard',
    ]);

    $compensationDetail = EmployeeCompensationDetail::create([
        'employee_id' => $employee->id,
        'daily_rate' => 650,
        'night_differential_rate' => 75,
        'sss_number' => 'SSS-001',
        'philhealth_number' => 'PH-001',
        'pagibig_number' => 'HDMF-001',
    ]);

    $client = Client::create([
        'name' => 'Acme Client',
        'billing_address' => '123 Billing Street',
        'payroll_period' => 'semi_monthly',
        'cutoff_type' => 'fixed_days',
        'first_cutoff_day' => 15,
        'second_cutoff_day' => 30,
        'payroll_frequency' => 'semi_monthly',
        'company_address' => '456 Company Avenue',
        'contact_person' => 'Maria Reyes',
    ]);

    $payrollPeriod = PayrollPeriod::create([
        'client_id' => $client->id,
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-15',
        'status' => 'closed',
    ]);

    $deployment = Deployment::create([
        'client_id' => $client->id,
        'employee_id' => $employee->id,
        'post_name' => 'Main Gate',
        'start_date' => '2026-08-01',
        'billing_rate' => 750,
    ]);

    $rateType = RateType::create([
        'code' => 'regular_rate',
        'name' => 'Regular Rate',
    ]);

    $rateHistory = UserRateHistory::create([
        'employee_id' => $employee->id,
        'rate_type_id' => $rateType->id,
        'amount' => 650,
        'effective_from' => '2026-08-01',
        'effective_until' => '2026-08-15',
    ]);

    $dailyTimeRecord = DailyTimeRecord::create([
        'payroll_period_id' => $payrollPeriod->id,
        'deployment_id' => $deployment->id,
        'work_date' => '2026-08-15',
        'time_in' => '2026-08-15 08:00:00',
        'time_out' => '2026-08-15 20:00:00',
        'regular_hours' => 8,
        'overtime_hours' => 4,
        'status' => 'approved',
    ]);

    $payrollLine = PayrollLine::create([
        'payroll_period_id' => $payrollPeriod->id,
        'deployment_id' => $deployment->id,
        'employee_id' => $employee->id,
        'regular_hours' => 8,
        'overtime_hours' => 4,
        'night_diff_hours' => 2,
        'base_pay' => 650,
        'night_diff_pay' => 150,
        'employer_sss' => 70,
        'employer_philhealth' => 32.50,
        'employer_pagibig' => 100,
        'gross_amount' => 800,
        'billable_amount' => 1500,
    ]);

    $accountReceivable = AccountReceivable::create([
        'client_id' => $client->id,
        'payroll_period_id' => $payrollPeriod->id,
        'account_receivable_number' => 'AR-2026-0001',
        'account_receivable_date' => '2026-08-15',
        'due_date' => '2026-08-30',
        'subtotal' => 1500,
        'tax_amount' => 180,
        'total_amount' => 1680,
        'running_balance' => 1680,
        'status' => 'approved',
        'submitted_by' => $arUser->id,
        'submitted_at' => '2026-08-16 09:00:00',
        'approved_by' => $gmUser->id,
        'approved_at' => '2026-08-16 13:00:00',
    ]);

    $tenderType = TenderType::create([
        'code' => 'bank_transfer',
        'name' => 'Bank Transfer',
    ]);

    $payment = Payment::create([
        'tender_type_id' => $tenderType->id,
        'payment_date' => '2026-08-20',
        'amount' => 1680,
        'reference_number' => 'PAY-2026-0001',
    ]);

    $accountReceivableEntry = AccountReceivableEntry::create([
        'account_receivable_id' => $accountReceivable->id,
        'payment_id' => $payment->id,
        'amount_applied' => 1680,
    ]);

    expect($arUser->role->is($arRole))->toBeTrue()
        ->and($gmUser->role->is($gmRole))->toBeTrue()
        ->and($employee->compensationDetail->is($compensationDetail))->toBeTrue()
        ->and($employee->rateHistories->first()->is($rateHistory))->toBeTrue()
        ->and($rateHistory->rateType->is($rateType))->toBeTrue()
        ->and($client->deployments->first()->is($deployment))->toBeTrue()
        ->and($client->payrollPeriods->first()->is($payrollPeriod))->toBeTrue()
        ->and($payrollPeriod->dailyTimeRecords->first()->is($dailyTimeRecord))->toBeTrue()
        ->and($payrollPeriod->payrollLines->first()->is($payrollLine))->toBeTrue()
        ->and($deployment->dailyTimeRecords->first()->is($dailyTimeRecord))->toBeTrue()
        ->and($deployment->payrollLines->first()->is($payrollLine))->toBeTrue()
        ->and($deployment->employee->is($employee))->toBeTrue()
        ->and($payrollLine->employee->is($employee))->toBeTrue()
        ->and($payrollPeriod->accountReceivables->first()->is($accountReceivable))->toBeTrue()
        ->and($accountReceivable->submitter->is($arUser))->toBeTrue()
        ->and($accountReceivable->approver->is($gmUser))->toBeTrue()
        ->and($accountReceivable->accountReceivableEntries->first()->is($accountReceivableEntry))->toBeTrue()
        ->and($accountReceivableEntry->payment->is($payment))->toBeTrue()
        ->and($payment->tenderType->is($tenderType))->toBeTrue()
        ->and($accountReceivableEntry->accountReceivable->payrollPeriod->client->is($client))->toBeTrue();
});

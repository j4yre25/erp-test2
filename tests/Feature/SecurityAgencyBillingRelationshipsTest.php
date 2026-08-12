<?php

use App\Models\Client;
use App\Models\DailyTimeRecord;
use App\Models\Deployment;
use App\Models\Invoice;
use App\Models\PostingPayment;
use App\Models\Role;
use App\Models\User;

test('security agency billing records follow the deployment to payment chain', function () {
    $role = Role::create([
        'role_name' => 'Security Guard',
    ]);

    $user = User::factory()->create([
        'role_id' => $role->id,
    ]);

    $client = Client::create([
        'name' => 'Acme Client',
        'billing_address' => '123 Billing Street',
        'payroll_period' => 'Semi-monthly',
        'company_address' => '456 Company Avenue',
    ]);

    $deployment = Deployment::create([
        'client_id' => $client->id,
        'user_id' => $user->id,
        'post_name' => 'Main Gate',
        'start_date' => '2026-08-01',
        'billing_rate' => 750,
    ]);

    $invoice = Invoice::create([
        'client_id' => $client->id,
        'deployment_id' => $deployment->id,
        'invoice_number' => 'INV-2026-0001',
        'invoice_date' => '2026-08-15',
        'due_date' => '2026-08-30',
        'subtotal' => 1500,
        'tax_amount' => 180,
        'total_amount' => 1680,
    ]);

    $dailyTimeRecord = DailyTimeRecord::create([
        'deployment_id' => $deployment->id,
        'invoice_id' => $invoice->id,
        'work_date' => '2026-08-15',
        'time_in' => '2026-08-15 08:00:00',
        'time_out' => '2026-08-15 20:00:00',
        'regular_hours' => 8,
        'overtime_hours' => 4,
        'status' => 'approved',
    ]);

    $postingPayment = PostingPayment::create([
        'invoice_id' => $invoice->id,
        'payment_date' => '2026-08-20',
        'amount' => 1680,
        'payment_method' => 'Bank Transfer',
        'reference_number' => 'PAY-2026-0001',
    ]);

    expect($user->role->is($role))->toBeTrue()
        ->and($client->deployments->first()->is($deployment))->toBeTrue()
        ->and($deployment->dailyTimeRecords->first()->is($dailyTimeRecord))->toBeTrue()
        ->and($dailyTimeRecord->invoice->is($invoice))->toBeTrue()
        ->and($invoice->postingPayments->first()->is($postingPayment))->toBeTrue()
        ->and($postingPayment->invoice->deployment->client->is($client))->toBeTrue();
});

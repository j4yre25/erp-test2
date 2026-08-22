<?php

use App\Models\Client;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;

function actingAsRole(string $roleName): User
{
    $role = Role::query()->firstOrCreate(['role_name' => $roleName]);

    $user = User::factory()->create(['role_id' => $role->id]);

    test()->actingAs($user);

    return $user;
}

test('guests are redirected to the login page for master data routes', function () {
    $this->get(route('clients.index'))->assertRedirect(route('login'));
    $this->get(route('guards.index'))->assertRedirect(route('login'));
    $this->get(route('deployments.index'))->assertRedirect(route('login'));
    $this->get(route('user-roles.index'))->assertRedirect(route('login'));
});

test('admin can manage clients and user roles but not guards or deployments', function () {
    actingAsRole('admin');

    $this->get(route('clients.index'))->assertOk();
    $this->get(route('user-roles.index'))->assertOk();

    $this->get(route('guards.index'))->assertForbidden();
    $this->get(route('deployments.index'))->assertForbidden();
});

test('payroll can manage guards but not clients or deployments', function () {
    actingAsRole('payroll');

    $this->get(route('guards.index'))->assertOk();

    $this->get(route('clients.index'))->assertForbidden();
    $this->get(route('deployments.index'))->assertForbidden();
    $this->get(route('user-roles.index'))->assertForbidden();
});

test('guard supervisor can manage deployments but not clients or guards', function () {
    actingAsRole('guard supervisor');

    $this->get(route('deployments.index'))->assertOk();

    $this->get(route('clients.index'))->assertForbidden();
    $this->get(route('guards.index'))->assertForbidden();
    $this->get(route('user-roles.index'))->assertForbidden();
});

test('guard supervisor assigning a guard flips the guard to deployed', function () {
    actingAsRole('guard supervisor');

    $client = Client::query()->create(['name' => 'Acme Corp']);
    $guard = Employee::factory()->create();

    $this->post(route('deployments.store'), [
        'client_id' => $client->id,
        'employee_id' => $guard->id,
        'start_date' => now()->toDateString(),
        'billing_rate' => 1500,
        'status' => 'active',
    ])->assertRedirect(route('deployments.index'));

    expect($guard->fresh()->availability_status)->toBe('deployed');

    $this->assertDatabaseHas('deployments', [
        'client_id' => $client->id,
        'employee_id' => $guard->id,
        'status' => 'active',
    ]);
});

test('payroll cannot assign a guard to a client', function () {
    actingAsRole('payroll');

    $client = Client::query()->create(['name' => 'Acme Corp']);
    $guard = Employee::factory()->create();

    $this->post(route('deployments.store'), [
        'client_id' => $client->id,
        'employee_id' => $guard->id,
        'start_date' => now()->toDateString(),
        'billing_rate' => 1500,
        'status' => 'active',
    ])->assertForbidden();
});

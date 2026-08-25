<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = collect(['admin', 'guard supervisor', 'payroll', 'ar', 'general manager'])
            ->mapWithKeys(fn (string $roleName) => [
                $roleName => Role::query()->firstOrCreate(['role_name' => $roleName]),
            ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@socopa.test',
            'password' => 'password',
            'role_id' => $roles['admin']->id,
        ]);

        User::factory()->create([
            'name' => 'Guard Supervisor',
            'email' => 'guardsupervisor@socopa.test',
            'password' => 'password',
            'role_id' => $roles['guard supervisor']->id,
        ]);

        User::factory()->create([
            'name' => 'Payroll Officer',
            'email' => 'payroll@socopa.test',
            'password' => 'password',
            'role_id' => $roles['payroll']->id,
        ]);

        User::factory()->create([
            'name' => 'AR Officer',
            'email' => 'ar@socopa.test',
            'password' => 'password',
            'role_id' => $roles['ar']->id,
        ]);

        User::factory()->create([
            'name' => 'General Manager',
            'email' => 'gm@socopa.test',
            'password' => 'password',
            'role_id' => $roles['general manager']->id,
        ]);

        $this->call(GuardAndPayrollSeeder::class);
    }
}

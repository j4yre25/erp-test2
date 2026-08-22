<?php

namespace App\Actions\Deployments;

use App\Models\Deployment;
use App\Models\Employee;
use InvalidArgumentException;

class AssignGuardAction
{
    public function execute(array $data): Deployment
    {
        /** @var Employee $guard */
        $guard = Employee::query()
            ->whereKey($data['employee_id'])
            ->where('employee_type', 'guard')
            ->firstOrFail();

        $this->ensureGuardCanBeDeployed($guard);

        $deployment = Deployment::query()->create([
            'client_id' => $data['client_id'],
            'employee_id' => $guard->id,
            'post_name' => trim((string) ($data['post_name'] ?? '')) ?: null,
            'start_date' => $data['start_date'],
            'billing_rate' => $data['billing_rate'],
            'status' => 'active',
        ]);

        $guard->forceFill([
            'availability_status' => 'deployed',
        ])->save();

        return $deployment;
    }

    protected function ensureGuardCanBeDeployed(Employee $guard): void
    {
        if ($guard->employment_status !== 'active') {
            throw new InvalidArgumentException('Only active guards can be deployed.');
        }

        if ($guard->availability_status !== 'available') {
            throw new InvalidArgumentException('Only available guards can be deployed.');
        }
    }
}

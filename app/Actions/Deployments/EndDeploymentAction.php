<?php

namespace App\Actions\Deployments;

use App\Models\Deployment;

class EndDeploymentAction
{
    public function execute(Deployment $deployment, ?string $endDate = null): Deployment
    {
        $deployment->forceFill([
            'end_date' => $endDate ?: now()->toDateString(),
            'status' => 'ended',
        ])->save();

        $deployment->employee->forceFill([
            'availability_status' => 'available',
        ])->save();

        return $deployment;
    }
}

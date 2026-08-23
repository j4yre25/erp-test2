<?php

namespace App\Actions\Billing;

use App\Models\AccountReceivable;
use App\Models\AccountReceivableStatus;
use App\Models\User;
use InvalidArgumentException;

class RejectStatementOfAccountAction
{
    public function execute(AccountReceivable $accountReceivable, User $user, string $reason): AccountReceivable
    {
        if (! $accountReceivable->isSubmitted()) {
            throw new InvalidArgumentException('Only submitted Statements of Account can be rejected.');
        }

        $rejectedStatus = AccountReceivableStatus::query()->firstOrCreate(['code' => 'rejected'], ['name' => 'Rejected']);

        $accountReceivable->forceFill([
            'account_receivable_status_id' => $rejectedStatus->id,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ])->save();

        return $accountReceivable->fresh(['client', 'payrollPeriod', 'rejecter', 'submitter', 'status']);
    }
}

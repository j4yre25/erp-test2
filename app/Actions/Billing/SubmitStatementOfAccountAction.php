<?php

namespace App\Actions\Billing;

use App\Models\AccountReceivable;
use App\Models\AccountReceivableStatus;
use App\Models\User;
use InvalidArgumentException;

class SubmitStatementOfAccountAction
{
    public function execute(AccountReceivable $accountReceivable, User $user): AccountReceivable
    {
        if (! $accountReceivable->isDraft() && ! $accountReceivable->isRejected()) {
            throw new InvalidArgumentException('Only draft or rejected Statements of Account can be submitted for approval.');
        }

        $submittedStatus = AccountReceivableStatus::query()->firstOrCreate(['code' => 'submitted'], ['name' => 'Submitted']);

        $accountReceivable->forceFill([
            'account_receivable_status_id' => $submittedStatus->id,
            'submitted_by' => $user->id,
            'submitted_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ])->save();

        return $accountReceivable->fresh(['client', 'payrollPeriod', 'submitter', 'status']);
    }
}

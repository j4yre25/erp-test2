<?php

namespace App\Actions\Billing;

use App\Models\AccountReceivable;
use App\Models\AccountReceivableStatus;
use App\Models\User;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class ApproveStatementOfAccountAction
{
    public function execute(AccountReceivable $accountReceivable, User $user, string $dueDate): AccountReceivable
    {
        if (! $accountReceivable->isSubmitted()) {
            throw new InvalidArgumentException('Only submitted Statements of Account can be approved.');
        }

        $approvedStatus = AccountReceivableStatus::query()->firstOrCreate(['code' => 'approved'], ['name' => 'Approved']);

        $accountReceivable->forceFill([
            'account_receivable_status_id' => $approvedStatus->id,
            'due_date' => Carbon::parse($dueDate)->toDateString(),
            'approved_by' => $user->id,
            'approved_at' => now(),
            'running_balance' => $accountReceivable->total_amount,
        ])->save();

        return $accountReceivable->fresh(['client', 'payrollPeriod', 'approver', 'submitter', 'status']);
    }
}

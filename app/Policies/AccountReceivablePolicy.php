<?php

namespace App\Policies;

use App\Models\AccountReceivable;
use App\Models\User;

class AccountReceivablePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('ar', 'account-receivable', 'general-manager', 'gm', 'admin');
    }

    public function view(User $user, AccountReceivable $accountReceivable): bool
    {
        return $user->hasRole('ar', 'account-receivable', 'general-manager', 'gm', 'admin');
    }

    public function create(User $user): bool
    {
        // Payroll and GM roles cannot create SOAs; AR and Admin can create
        return $user->hasRole('ar', 'account-receivable', 'admin');
    }

    public function update(User $user, AccountReceivable $accountReceivable): bool
    {
        // AR roles cannot edit after submission; GM cannot directly edit
        return $user->hasRole('ar', 'account-receivable', 'admin')
            && ($accountReceivable->isDraft() || $accountReceivable->isRejected());
    }

    public function delete(User $user, AccountReceivable $accountReceivable): bool
    {
        return $user->hasRole('ar', 'account-receivable', 'admin')
            && ($accountReceivable->isDraft() || $accountReceivable->isRejected());
    }

    public function submit(User $user, AccountReceivable $accountReceivable): bool
    {
        // Payroll roles cannot submit billing; only AR or Admin can submit draft SOAs
        return $user->hasRole('ar', 'account-receivable', 'admin')
            && ($accountReceivable->isDraft() || $accountReceivable->isRejected());
    }

    public function approve(User $user, AccountReceivable $accountReceivable): bool
    {
        // Payroll and AR roles cannot approve billing. AR cannot approve their own submissions.
        if (! $user->hasRole('general-manager', 'gm', 'admin')) {
            return false;
        }

        // Must be submitted to be approved
        if (! $accountReceivable->isSubmitted()) {
            return false;
        }

        // AR cannot approve their own submission
        if ($accountReceivable->submitted_by && $accountReceivable->submitted_by === $user->id && ! $user->hasRole('admin')) {
            return false;
        }

        return true;
    }

    public function reject(User $user, AccountReceivable $accountReceivable): bool
    {
        // Only GM or Admin can reject a submitted SOA
        return $user->hasRole('general-manager', 'gm', 'admin')
            && $accountReceivable->isSubmitted();
    }
}

<?php

namespace App\Policies;

use App\Models\PayrollPeriod;
use App\Models\User;

class PayrollPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('payroll', 'admin');
    }

    public function view(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $user->hasRole('payroll', 'admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('payroll', 'admin');
    }

    public function update(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $user->hasRole('payroll', 'admin') && $payrollPeriod->isOpen();
    }

    public function delete(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $user->hasRole('payroll', 'admin') && $payrollPeriod->isOpen();
    }

    public function close(User $user, PayrollPeriod $payrollPeriod): bool
    {
        return $user->hasRole('payroll', 'admin') && $payrollPeriod->isOpen();
    }
}

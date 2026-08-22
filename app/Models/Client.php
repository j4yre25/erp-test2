<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'billing_address', 'payroll_period', 'cutoff_type', 'first_cutoff_day', 'second_cutoff_day', 'payroll_frequency', 'company_address', 'contact_person'])]
class Client extends Model
{
    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    public function payrollPeriods(): HasMany
    {
        return $this->hasMany(PayrollPeriod::class);
    }

    public function accountReceivables(): HasMany
    {
        return $this->hasMany(AccountReceivable::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'first_cutoff_day' => 'integer',
            'second_cutoff_day' => 'integer',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'description'])]
class PayrollPeriodStatus extends Model
{
    public function payrollPeriods(): HasMany
    {
        return $this->hasMany(PayrollPeriod::class);
    }
}

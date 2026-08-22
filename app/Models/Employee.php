<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'employee_number', 'first_name', 'last_name', 'contact_number', 'address', 'employee_type', 'employment_status', 'availability_status'])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    protected $attributes = [
        'employment_status' => 'active',
        'availability_status' => 'available',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function compensationDetail(): HasOne
    {
        return $this->hasOne(EmployeeCompensationDetail::class);
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(Deployment::class);
    }

    public function rateHistories(): HasMany
    {
        return $this->hasMany(UserRateHistory::class);
    }

    public function payrollLines(): HasMany
    {
        return $this->hasMany(PayrollLine::class);
    }
}

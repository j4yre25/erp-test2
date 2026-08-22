<?php

namespace App\Models;

use Database\Factories\PayrollLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['payroll_period_id', 'deployment_id', 'employee_id', 'regular_hours', 'overtime_hours', 'night_diff_hours', 'base_pay', 'night_diff_pay', 'employer_sss', 'employer_philhealth', 'employer_pagibig', 'gross_amount', 'billable_amount'])]
class PayrollLine extends Model
{
    /** @use HasFactory<PayrollLineFactory> */
    use HasFactory;

    protected $attributes = [
        'regular_hours' => 0,
        'overtime_hours' => 0,
        'night_diff_hours' => 0,
        'base_pay' => 0,
        'night_diff_pay' => 0,
        'employer_sss' => 0,
        'employer_philhealth' => 0,
        'employer_pagibig' => 0,
        'gross_amount' => 0,
        'billable_amount' => 0,
    ];

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'regular_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'night_diff_hours' => 'decimal:2',
            'base_pay' => 'decimal:2',
            'night_diff_pay' => 'decimal:2',
            'employer_sss' => 'decimal:2',
            'employer_philhealth' => 'decimal:2',
            'employer_pagibig' => 'decimal:2',
            'gross_amount' => 'decimal:2',
            'billable_amount' => 'decimal:2',
        ];
    }
}

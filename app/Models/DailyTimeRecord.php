<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $work_date
 * @property Carbon|null $time_in
 * @property Carbon|null $time_out
 */
#[Fillable(['payroll_period_id', 'deployment_id', 'work_date', 'time_in', 'time_out', 'regular_hours', 'overtime_hours', 'status'])]
class DailyTimeRecord extends Model
{
    protected $attributes = [
        'status' => 'pending',
        'regular_hours' => 0,
        'overtime_hours' => 0,
    ];

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'time_in' => 'datetime',
            'time_out' => 'datetime',
            'regular_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
        ];
    }
}

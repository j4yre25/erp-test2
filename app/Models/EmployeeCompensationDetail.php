<?php

namespace App\Models;

use Database\Factories\EmployeeCompensationDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'daily_rate', 'night_differential_rate', 'sss_number', 'philhealth_number', 'pagibig_number'])]
class EmployeeCompensationDetail extends Model
{
    /** @use HasFactory<EmployeeCompensationDetailFactory> */
    use HasFactory;

    protected $attributes = [
        'daily_rate' => 0,
        'night_differential_rate' => 0,
    ];

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
            'daily_rate' => 'decimal:2',
            'night_differential_rate' => 'decimal:2',
        ];
    }
}

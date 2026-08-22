<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $effective_from
 * @property Carbon|null $effective_until
 */
#[Fillable(['employee_id', 'rate_type_id', 'amount', 'effective_from', 'effective_until', 'remarks'])]
class UserRateHistory extends Model
{
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function rateType(): BelongsTo
    {
        return $this->belongsTo(RateType::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }
}

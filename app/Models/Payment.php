<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $payment_date
 */
#[Fillable(['tender_type_id', 'payment_date', 'amount', 'reference_number', 'remarks', 'status'])]
class Payment extends Model
{
    protected $attributes = [
        'status' => 'posted',
    ];

    public function tenderType(): BelongsTo
    {
        return $this->belongsTo(TenderType::class);
    }

    public function accountReceivableEntries(): HasMany
    {
        return $this->hasMany(AccountReceivableEntry::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }
}

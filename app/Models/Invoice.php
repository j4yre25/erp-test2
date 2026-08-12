<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $invoice_date
 * @property Carbon|null $due_date
 */
#[Fillable(['client_id', 'deployment_id', 'invoice_number', 'invoice_date', 'due_date', 'subtotal', 'tax_amount', 'total_amount', 'status'])]
class Invoice extends Model
{
    protected $attributes = [
        'status' => 'draft',
        'subtotal' => 0,
        'tax_amount' => 0,
        'total_amount' => 0,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }

    public function dailyTimeRecords(): HasMany
    {
        return $this->hasMany(DailyTimeRecord::class);
    }

    public function postingPayments(): HasMany
    {
        return $this->hasMany(PostingPayment::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }
}

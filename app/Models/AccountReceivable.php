<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $client_id
 * @property int $payroll_period_id
 * @property int|null $account_receivable_status_id
 * @property string $account_receivable_number
 * @property Carbon $account_receivable_date
 * @property Carbon|null $due_date
 * @property-read AccountReceivableStatus|null $status
 */
#[Fillable(['client_id', 'payroll_period_id', 'account_receivable_status_id', 'account_receivable_number', 'account_receivable_date', 'due_date', 'subtotal', 'tax_amount', 'total_amount', 'running_balance', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason'])]
class AccountReceivable extends Model
{
    protected $attributes = [
        'subtotal' => 0,
        'tax_amount' => 0,
        'total_amount' => 0,
        'running_balance' => 0,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AccountReceivableStatus::class, 'account_receivable_status_id');
    }

    public function accountReceivableEntries(): HasMany
    {
        return $this->hasMany(AccountReceivableEntry::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isDraft(): bool
    {
        return $this->status?->code === 'draft' || $this->account_receivable_status_id === null;
    }

    public function isSubmitted(): bool
    {
        return $this->status?->code === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status?->code === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status?->code === 'rejected';
    }

    public function scopeDraft(Builder $query): void
    {
        $query->whereHas('status', fn (Builder $q) => $q->where('code', 'draft'));
    }

    public function scopeSubmitted(Builder $query): void
    {
        $query->whereHas('status', fn (Builder $q) => $q->where('code', 'submitted'));
    }

    public function scopeApproved(Builder $query): void
    {
        $query->whereHas('status', fn (Builder $q) => $q->where('code', 'approved'));
    }

    public function scopeRejected(Builder $query): void
    {
        $query->whereHas('status', fn (Builder $q) => $q->where('code', 'rejected'));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_receivable_date' => 'date',
            'due_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
        ];
    }
}

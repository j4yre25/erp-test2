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
 * @property int|null $payroll_period_status_id
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property-read PayrollPeriodStatus|null $status
 */
#[Fillable(['client_id', 'payroll_period_status_id', 'start_date', 'end_date'])]
class PayrollPeriod extends Model
{
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriodStatus::class, 'payroll_period_status_id');
    }

    public function dailyTimeRecords(): HasMany
    {
        return $this->hasMany(DailyTimeRecord::class);
    }

    public function accountReceivables(): HasMany
    {
        return $this->hasMany(AccountReceivable::class);
    }

    public function payrollLines(): HasMany
    {
        return $this->hasMany(PayrollLine::class);
    }

    public function isOpen(): bool
    {
        return $this->status?->code === 'open' || $this->payroll_period_status_id === null;
    }

    public function isClosed(): bool
    {
        return $this->status?->code === 'closed';
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereHas('status', fn (Builder $q) => $q->where('code', 'open'));
    }

    public function scopeClosed(Builder $query): void
    {
        $query->whereHas('status', fn (Builder $q) => $q->where('code', 'closed'));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }
}

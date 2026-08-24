<?php

namespace App\Rules;

use App\Models\PayrollPeriod;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Translation\PotentiallyTranslatedString;

class NoOverlappingPayrollPeriod implements ValidationRule
{
    public function __construct(
        protected mixed $clientId,
        protected mixed $startDate,
        protected ?int $ignorePeriodId = null,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->clientId || ! $this->startDate || ! $value) {
            return;
        }

        $startDate = Carbon::parse($this->startDate)->toDateString();
        $endDate = Carbon::parse($value)->toDateString();

        $hasExactDuplicate = PayrollPeriod::query()
            ->where('client_id', $this->clientId)
            ->when($this->ignorePeriodId, fn ($query) => $query->where('id', '!=', $this->ignorePeriodId))
            ->whereDate('start_date', $startDate)
            ->whereDate('end_date', $endDate)
            ->exists();

        if ($hasExactDuplicate) {
            $fail(__('A payroll period with this exact start and end date already exists for this client.'));

            return;
        }

        $hasOverlap = PayrollPeriod::query()
            ->where('client_id', $this->clientId)
            ->when($this->ignorePeriodId, fn ($query) => $query->where('id', '!=', $this->ignorePeriodId))
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->whereDate('start_date', '<=', $endDate)
                    ->whereDate('end_date', '>=', $startDate);
            })
            ->exists();

        if ($hasOverlap) {
            $fail(__('The selected date range overlaps with an existing payroll period for this client.'));
        }
    }
}

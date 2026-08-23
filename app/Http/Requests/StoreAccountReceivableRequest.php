<?php

namespace App\Http\Requests;

use App\Models\AccountReceivable;
use App\Models\PayrollPeriod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountReceivableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', AccountReceivable::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payroll_period_id' => [
                'required',
                'integer',
                Rule::exists('payroll_periods', 'id'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $period = PayrollPeriod::query()->with('status')->find($value);
                    if (! $period || ! $period->isClosed()) {
                        $fail(__('The selected payroll period must be closed.'));
                    }
                },
                Rule::unique('account_receivables', 'payroll_period_id'),
            ],
            'account_receivable_number' => ['nullable', 'string', 'max:255', Rule::unique('account_receivables', 'account_receivable_number')],
            'account_receivable_date' => ['required', 'date'],
            'tax_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\PayrollPeriod;
use App\Rules\NoOverlappingPayrollPeriod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StorePayrollPeriodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', PayrollPeriod::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $clientId = $this->input('client_id');
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');

        return [
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'start_date' => [
                'required',
                'date',
                Rule::unique('payroll_periods', 'start_date')
                    ->where(fn ($query) => $query->where('client_id', $clientId)->where('end_date', $endDate)),
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
                new NoOverlappingPayrollPeriod($clientId, $startDate),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.unique' => __('A payroll period with this exact start and end date already exists for this client.'),
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');

            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate);
                $end = Carbon::parse($endDate);

                if ($start->diffInDays($end) + 1 > 31) {
                    $validator->errors()->add('end_date', __('A payroll period cannot exceed 31 days.'));
                }
            }
        });
    }
}

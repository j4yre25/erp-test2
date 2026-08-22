<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Employee::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_number' => ['required', 'string', 'max:255', Rule::unique('employees', 'employee_number')],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:5000'],
            'employment_status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'availability_status' => ['required', 'string', Rule::in(['available', 'deployed', 'inactive'])],
            'daily_rate' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'night_differential_rate' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'sss_number' => ['nullable', 'string', 'max:255'],
            'philhealth_number' => ['nullable', 'string', 'max:255'],
            'pagibig_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}

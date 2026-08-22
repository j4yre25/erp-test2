<?php

namespace App\Http\Requests;

use App\Models\Deployment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeploymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Deployment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'employee_id' => [
                'required',
                'integer',
                Rule::exists('employees', 'id')
                    ->where('employee_type', 'guard')
                    ->where('employment_status', 'active')
                    ->where('availability_status', 'available'),
            ],
            'post_name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'billing_rate' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['required', 'string', Rule::in(['active'])],
        ];
    }
}

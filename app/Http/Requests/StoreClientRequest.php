<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Client::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('clients', 'name')],
            'billing_address' => ['nullable', 'string', 'max:5000'],
            'company_address' => ['nullable', 'string', 'max:5000'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'payroll_period' => ['nullable', 'string', 'max:255'],
            'cutoff_type' => ['nullable', 'string', 'max:255'],
            'first_cutoff_day' => ['nullable', 'integer', 'between:1,31'],
            'second_cutoff_day' => ['nullable', 'integer', 'between:1,31', 'different:first_cutoff_day'],
            'payroll_frequency' => ['nullable', 'string', 'max:255'],
        ];
    }
}

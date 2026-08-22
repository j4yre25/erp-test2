<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeploymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('deployment'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'post_name' => ['nullable', 'string', 'max:255'],
            'end_date' => ['nullable', 'date'],
            'billing_rate' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['required', 'string', Rule::in(['active', 'ended'])],
        ];
    }
}

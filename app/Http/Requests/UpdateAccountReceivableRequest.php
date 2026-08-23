<?php

namespace App\Http\Requests;

use App\Models\AccountReceivable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountReceivableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var AccountReceivable $accountReceivable */
        $accountReceivable = $this->route('account_receivable') ?? $this->route('statement_of_account');

        return $this->user()->can('update', $accountReceivable);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_receivable_date' => ['required', 'date'],
            'subtotal' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'tax_amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }
}

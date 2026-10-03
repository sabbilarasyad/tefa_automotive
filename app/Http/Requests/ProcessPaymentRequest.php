<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcessPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('processPayment', $this->route('invoice')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
    * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'method' => ['required', 'string', Rule::in(['CASH', 'TRANSFER', 'QRIS', 'OTHER'])],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'confirm_incomplete_documentation' => ['sometimes', 'boolean'],
        ];
    }
}

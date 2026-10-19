<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SparepartStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'qty' => ['required', 'numeric', 'min:0.001'],
            'type' => ['required', Rule::in(['MANUAL_IN', 'ADJUSTMENT', 'RETURN'])],
            'reason' => ['required', 'string', 'max:255'],
            'reference_type' => ['nullable', 'string', 'max:50'],
            'reference_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'qty.required' => 'Jumlah wajib diisi.',
            'qty.numeric' => 'Jumlah harus berupa angka.',
            'qty.min' => 'Jumlah minimal 0.001.',
            'type.required' => 'Tipe penyesuaian wajib dipilih.',
            'type.in' => 'Tipe penyesuaian tidak valid.',
            'reason.required' => 'Alasan wajib diisi.',
            'reason.max' => 'Alasan maksimal 255 karakter.',
        ];
    }
}
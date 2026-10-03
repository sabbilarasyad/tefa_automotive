<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RestockSparepartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'qty' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'qty.required' => 'Jumlah restock wajib diisi.',
            'qty.numeric' => 'Jumlah restock harus berupa angka.',
            'qty.min' => 'Jumlah restock minimal 0.001.',
            'reason.required' => 'Alasan restock wajib diisi.',
            'reason.max' => 'Alasan restock maksimal 255 karakter.',
        ];
    }
}
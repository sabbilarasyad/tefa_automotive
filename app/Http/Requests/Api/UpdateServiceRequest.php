<?php

namespace App\Http\Requests\Api;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');
        return $this->user()?->can('manageServices', $service) ?? false;
    }

    public function rules(): array
    {
        $serviceId = $this->route('service');

        return [
            'code' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('services', 'code')->ignore($serviceId)],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'documentation_required' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode jasa wajib diisi.',
            'code.unique' => 'Kode jasa sudah digunakan.',
            'name.required' => 'Nama jasa wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh negatif.',
        ];
    }
}
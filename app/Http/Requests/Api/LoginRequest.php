<?php

namespace App\Http\Requests\Api;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.max' => 'Username maksimal 100 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'device_name.string' => 'Nama perangkat harus berupa teks.',
            'device_name.max' => 'Nama perangkat maksimal 100 karakter.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(ApiResponse::error(
            'Validasi gagal.',
            $validator->errors()->toArray(),
            422,
        ));
    }
}
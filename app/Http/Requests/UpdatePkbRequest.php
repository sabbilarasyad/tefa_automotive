<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePkbRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit', $this->route('pkb')) ?? false;
    }

    public function rules(): array
    {
        return [
            'complaint' => ['sometimes', 'required', 'string'],
            'inspection_result' => ['sometimes', 'nullable', 'string'],
            'service_date' => ['sometimes', 'required', 'date'],
        ];
    }
}

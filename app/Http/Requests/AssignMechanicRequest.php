<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignMechanicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assignMechanic', $this->route('pkb')) ?? false;
    }

    public function rules(): array
    {
        return [
            'mechanic_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('is_active', true),
            ],
        ];
    }
}

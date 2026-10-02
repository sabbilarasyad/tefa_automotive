<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartPkbWorkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('startWork', $this->route('pkb')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        ];
    }
}

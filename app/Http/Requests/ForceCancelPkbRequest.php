<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForceCancelPkbRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('forceCancel', $this->route('pkb')) ?? false;
    }

    public function rules(): array
    {
        return [
            'cancel_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}

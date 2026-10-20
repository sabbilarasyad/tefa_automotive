<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ReminderSendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('reminder.manage') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSparepartUsageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('updateWorkProgress', $this->route('pkb')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'spareparts' => ['required', 'array', 'min:1'],
            'spareparts.*.pkb_sparepart_id' => ['required', 'integer', 'distinct', 'exists:pkb_spareparts,id'],
            'spareparts.*.qty_used' => ['required', 'numeric', 'min:0', 'decimal:0,3'],
        ];
    }
}

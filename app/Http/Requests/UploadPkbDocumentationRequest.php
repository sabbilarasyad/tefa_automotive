<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UploadPkbDocumentationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('uploadDocumentation', $this->route('pkb')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pkb_service_id' => ['nullable', 'integer', 'exists:pkb_services,id'],
            'pkb_sparepart_id' => ['nullable', 'integer', 'exists:pkb_spareparts,id'],
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['pkb_service_id', 'pkb_sparepart_id'])) {
                return;
            }

            $hasService = $this->input('pkb_service_id') !== null;
            $hasSparepart = $this->input('pkb_sparepart_id') !== null;

            if ($hasService === $hasSparepart) {
                $validator->errors()->add('item', 'Pilih tepat satu item jasa atau sparepart.');
            }
        }];
    }
}

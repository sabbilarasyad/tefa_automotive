<?php

namespace App\Http\Requests;

use App\Models\Pkb;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePkbRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Pkb::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'required_without:customer.name', 'integer', 'exists:customers,id'],
            'customer' => ['nullable', 'array'],
            'customer.name' => ['required_without:customer_id', 'string', 'max:150'],
            'customer.phone' => ['required_without:customer_id', 'string', 'max:30'],
            'customer.email' => ['nullable', 'email', 'max:150'],
            'customer.address' => ['nullable', 'string'],
            'vehicle_id' => ['nullable', 'required_without:vehicle.plate_number', 'integer', 'exists:vehicles,id'],
            'vehicle' => ['nullable', 'array'],
            'vehicle.plate_number' => ['required_without:vehicle_id', 'string', 'max:20'],
            'vehicle.brand' => ['required_without:vehicle_id', 'string', 'max:80'],
            'vehicle.model' => ['required_without:vehicle_id', 'string', 'max:100'],
            'vehicle.year' => ['nullable', 'integer', 'between:1900,2100'],
            'vehicle.color' => ['nullable', 'string', 'max:50'],
            'vehicle.vin' => ['nullable', 'string', 'max:100', 'unique:vehicles,vin'],
            'vehicle.engine_number' => ['nullable', 'string', 'max:100'],
            'vehicle.current_km' => ['nullable', 'integer', 'min:0'],
            'complaint' => ['required', 'string'],
            'inspection_result' => ['nullable', 'string'],
            'service_date' => ['nullable', 'date'],
            'mechanic_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'services' => ['required', 'array', 'min:1'],
            'services.*.service_id' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'services.*.qty' => ['required', 'numeric', 'gt:0', 'decimal:0,3'],
            'spareparts' => ['nullable', 'array'],
            'spareparts.*.sparepart_id' => ['required', 'integer', 'distinct', 'exists:spareparts,id'],
            'spareparts.*.qty_planned' => ['required', 'numeric', 'gt:0', 'decimal:0,3'],
        ];
    }
}

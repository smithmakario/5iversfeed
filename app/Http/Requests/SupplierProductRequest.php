<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplier = $this->route('supplier');
        $product = $this->route('formulation');

        return [
            'feed_type_id' => ['required', 'exists:feed_types,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('formulations', 'sku')
                    ->where('supplier_id', $supplier->id)
                    ->ignore($product?->id),
            ],
            'description' => ['nullable', 'string'],
            'protein_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fiber_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'moisture_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ingredients' => ['nullable', 'string'],
            'unit' => ['required', 'in:kg,bag,ton'],
            'unit_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}

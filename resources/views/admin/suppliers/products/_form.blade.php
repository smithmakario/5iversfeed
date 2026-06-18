<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ isset($product) ? 'Edit' : 'Add' }} Product — {{ $supplier->company_name }}</h2>
    </x-slot>
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form
                    method="POST"
                    action="{{ isset($product) ? route('admin.suppliers.products.update', [$supplier, $product]) : route('admin.suppliers.products.store', $supplier) }}"
                    class="space-y-4"
                >
                    @csrf
                    @isset($product)
                        @method('PUT')
                    @endisset
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Feed Type" />
                            <select name="feed_type_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                @foreach ($feedTypes as $type)
                                    <option value="{{ $type->id }}" @selected(old('feed_type_id', $product->feed_type_id ?? '') == $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label value="Brand" />
                            <select name="brand_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id ?? '') == $brand->id)>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="sku" value="SKU" />
                            <x-text-input id="sku" name="sku" class="block mt-1 w-full" :value="old('sku', $product->sku ?? '')" required />
                        </div>
                        <div>
                            <x-input-label for="name" value="Name" />
                            <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $product->name ?? '')" required />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="description" value="Description" />
                        <textarea id="description" name="description" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach (['protein_percentage' => 'Protein %', 'fiber_percentage' => 'Fiber %', 'moisture_percentage' => 'Moisture %', 'fat_percentage' => 'Fat %'] as $field => $label)
                            <div>
                                <x-input-label :for="$field" :value="$label" />
                                <x-text-input :id="$field" :name="$field" type="number" step="0.01" class="block mt-1 w-full" :value="old($field, $product->$field ?? '')" />
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <x-input-label for="ingredients" value="Ingredients" />
                        <textarea id="ingredients" name="ingredients" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('ingredients', $product->ingredients ?? '') }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label value="Unit" />
                            <select name="unit" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach (['bag', 'kg', 'ton'] as $unit)
                                    <option value="{{ $unit }}" @selected(old('unit', $product->unit ?? 'bag') === $unit)>{{ strtoupper($unit) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="unit_weight_kg" value="Unit Weight (kg)" />
                            <x-text-input id="unit_weight_kg" name="unit_weight_kg" type="number" step="0.01" class="block mt-1 w-full" :value="old('unit_weight_kg', $product->unit_weight_kg ?? '')" />
                        </div>
                        <div>
                            <x-input-label for="price_per_unit" value="Price per Unit" />
                            <x-text-input id="price_per_unit" name="price_per_unit" type="number" step="0.01" class="block mt-1 w-full" :value="old('price_per_unit', $product->price_per_unit ?? 0)" required />
                        </div>
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) class="rounded border-gray-300">
                        <span class="text-sm">Active</span>
                    </label>
                    <div class="flex gap-3">
                        <x-primary-button>Save Product</x-primary-button>
                        <a href="{{ route('admin.suppliers.products.index', $supplier) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-sm">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

@php
    $isEdit = isset($purchaseOrder);
    $initialItems = old('items', $isEdit
        ? $purchaseOrder->items->map(fn ($item) => [
            'formulation_id' => (string) $item->formulation_id,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
        ])->values()->all()
        : [['formulation_id' => '', 'quantity' => 1, 'unit_price' => '']]
    );
    $productOptions = $formulations->map(fn ($f) => [
        'id' => (string) $f->id,
        'supplier_id' => (string) $f->supplier_id,
        'label' => $f->sku.' — '.$f->name,
        'price' => $f->price_per_unit,
    ])->values();
@endphp
<x-admin-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ $isEdit ? 'Edit' : 'Create' }} Purchase Order</h2></x-slot>
    <div class="py-8" x-data="poForm()">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <form method="POST" action="{{ $isEdit ? route('admin.purchase-orders.update', $purchaseOrder) : route('admin.purchase-orders.store') }}">
                    @csrf @if($isEdit) @method('PUT') @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <x-input-label value="Supplier" />
                            <select name="supplier_id" x-model="supplierId" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select supplier</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" @selected(old('supplier_id', $purchaseOrder->supplier_id ?? '') == $s->id)>{{ $s->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if (! $isEdit)
                        <div>
                            <x-input-label value="Issue on Save" />
                            <select name="status" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="draft" @selected(old('status', 'draft') === 'draft')>Save as Draft</option>
                                <option value="submitted" @selected(old('status') === 'submitted')>Issue to Supplier</option>
                            </select>
                        </div>
                        @endif
                        <div><x-input-label for="order_date" value="Order Date" /><x-text-input id="order_date" name="order_date" type="date" class="block mt-1 w-full" :value="old('order_date', optional($purchaseOrder->order_date ?? now())->format('Y-m-d'))" required /></div>
                        <div><x-input-label for="expected_delivery_date" value="Expected Delivery" /><x-text-input id="expected_delivery_date" name="expected_delivery_date" type="date" class="block mt-1 w-full" :value="old('expected_delivery_date', optional($purchaseOrder->expected_delivery_date ?? null)?->format('Y-m-d'))" /></div>
                        <div><x-input-label for="tax_amount" value="Tax Amount" /><x-text-input id="tax_amount" name="tax_amount" type="number" step="0.01" class="block mt-1 w-full" :value="old('tax_amount', $purchaseOrder->tax_amount ?? 0)" /></div>
                    </div>

                    <h3 class="font-semibold mb-3">Payment Terms</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <x-input-label value="Payment Option" />
                            <select name="payment_option" x-model="paymentOption" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                @foreach($paymentOptions as $option)
                                    <option value="{{ $option->value }}" @selected(old('payment_option', isset($purchaseOrder) ? $purchaseOrder->payment_option->value : 'one_off') === $option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('payment_option')" class="mt-2" />
                        </div>
                        <div x-show="needsUpfront" x-cloak>
                            <x-input-label for="upfront_amount" value="Upfront Amount (₦)" />
                            <x-text-input id="upfront_amount" name="upfront_amount" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="old('upfront_amount', isset($purchaseOrder) ? $purchaseOrder->upfront_amount : '')" />
                            <x-input-error :messages="$errors->get('upfront_amount')" class="mt-2" />
                        </div>
                        <div x-show="needsCreditTimeline" x-cloak class="md:col-span-2">
                            <x-input-label value="Credit Repayment Timeline" />
                            <select name="credit_repayment_timeline_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select repayment period</option>
                                @foreach($creditTimelines as $timeline)
                                    <option value="{{ $timeline->id }}" @selected(old('credit_repayment_timeline_id', isset($purchaseOrder) ? $purchaseOrder->credit_repayment_timeline_id : '') == $timeline->id)>{{ $timeline->displayLabel() }}</option>
                                @endforeach
                            </select>
                            @if($creditTimelines->isEmpty())
                                <p class="text-sm text-amber-600 mt-1">No credit timelines configured. <a href="{{ route('admin.settings.credit-timelines.create') }}" class="underline">Add one in Settings</a>.</p>
                            @endif
                            <x-input-error :messages="$errors->get('credit_repayment_timeline_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mb-4"><x-input-label for="notes" value="Notes" /><textarea id="notes" name="notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('notes', $purchaseOrder->notes ?? '') }}</textarea></div>

                    <h3 class="font-semibold mb-3">Line Items</h3>
                    <p class="text-sm text-gray-500 mb-4" x-show="!supplierId">Select a supplier to choose products.</p>
                    <template x-for="(item, index) in items" :key="index">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3 border-b pb-3">
                            <div class="md:col-span-2">
                                <select :name="`items[${index}][formulation_id]`" x-model="item.formulation_id" class="w-full border-gray-300 rounded-md shadow-sm" :disabled="!supplierId" required>
                                    <option value="">Select product</option>
                                    <template x-for="product in productsForSupplier" :key="product.id">
                                        <option :value="product.id" x-text="product.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div><input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity" min="1" placeholder="Qty" class="w-full border-gray-300 rounded-md shadow-sm" required></div>
                            <div class="flex gap-2"><input type="number" step="0.01" :name="`items[${index}][unit_price]`" x-model="item.unit_price" placeholder="Unit price" class="w-full border-gray-300 rounded-md shadow-sm"><button type="button" @click="removeItem(index)" class="text-red-600 text-sm">Remove</button></div>
                        </div>
                    </template>
                    <button type="button" @click="addItem()" class="text-sm link-primary mb-6" :disabled="!supplierId">+ Add line item</button>

                    <div class="flex gap-3"><x-primary-button>Save Purchase Order</x-primary-button><a href="{{ route('admin.purchase-orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-md text-sm">Cancel</a></div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function poForm() {
            return {
                supplierId: @json((string) old('supplier_id', $purchaseOrder->supplier_id ?? '')),
                paymentOption: @json(old('payment_option', isset($purchaseOrder) ? $purchaseOrder->payment_option->value : 'one_off')),
                products: @json($productOptions),
                items: @json($initialItems),
                get productsForSupplier() {
                    if (!this.supplierId) {
                        return [];
                    }
                    return this.products.filter(product => product.supplier_id === this.supplierId);
                },
                get needsCreditTimeline() {
                    return ['full_credit', 'partial_credit'].includes(this.paymentOption);
                },
                get needsUpfront() {
                    return ['partial', 'partial_credit'].includes(this.paymentOption);
                },
                addItem() {
                    this.items.push({ formulation_id: '', quantity: 1, unit_price: '' });
                },
                removeItem(i) {
                    if (this.items.length > 1) {
                        this.items.splice(i, 1);
                    }
                },
                init() {
                    this.$watch('supplierId', () => {
                        this.items.forEach(item => {
                            const product = this.products.find(p => p.id === item.formulation_id);
                            if (product && product.supplier_id !== this.supplierId) {
                                item.formulation_id = '';
                                item.unit_price = '';
                            }
                        });
                    });
                },
            }
        }
    </script>
</x-admin-layout>

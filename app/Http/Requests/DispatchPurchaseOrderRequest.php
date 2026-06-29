<?php

namespace App\Http\Requests;

use App\Models\PurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DispatchPurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dispatched_at' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var PurchaseOrder|null $purchaseOrder */
            $purchaseOrder = $this->route('purchase_order');

            if (! $purchaseOrder || ! $this->filled('dispatched_at')) {
                return;
            }

            if ($this->date('dispatched_at')->lt($purchaseOrder->order_date)) {
                $validator->errors()->add(
                    'dispatched_at',
                    'Dispatch date cannot be before the order date.',
                );
            }
        });
    }
}

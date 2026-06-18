<?php

namespace App\Http\Requests;

use App\Enums\PaymentOption;
use App\Models\Formulation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'payment_option' => ['required', 'in:one_off,partial,full_credit,partial_credit'],
            'credit_repayment_timeline_id' => ['nullable', 'exists:credit_repayment_timelines,id'],
            'upfront_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.formulation_id' => ['required', 'exists:formulations,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ];

        if ($this->isMethod('POST')) {
            $rules['status'] = ['required', 'in:draft,submitted'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $supplierId = (int) $this->input('supplier_id');
            $paymentOption = PaymentOption::tryFrom($this->input('payment_option', ''));

            if ($paymentOption?->requiresCreditTimeline() && ! $this->filled('credit_repayment_timeline_id')) {
                $validator->errors()->add('credit_repayment_timeline_id', 'A credit repayment timeline is required for credit payment options.');
            }

            if ($paymentOption?->requiresUpfrontAmount() && ! $this->filled('upfront_amount')) {
                $validator->errors()->add('upfront_amount', 'An upfront amount is required for this payment option.');
            }

            $estimatedTotal = 0;

            foreach ($this->input('items', []) as $index => $item) {
                if (empty($item['formulation_id'])) {
                    continue;
                }

                $formulation = Formulation::query()->find($item['formulation_id']);

                if ($formulation && (int) $formulation->supplier_id !== $supplierId) {
                    $validator->errors()->add(
                        "items.{$index}.formulation_id",
                        'The selected product does not belong to the chosen supplier.'
                    );
                }

                if ($formulation) {
                    $quantity = (int) ($item['quantity'] ?? 0);
                    $unitPrice = $item['unit_price'] ?? $formulation->price_per_unit;
                    $estimatedTotal += $quantity * (float) $unitPrice;
                }
            }

            $estimatedTotal += (float) $this->input('tax_amount', 0);

            if ($paymentOption?->requiresUpfrontAmount() && $this->filled('upfront_amount')) {
                $upfront = (float) $this->input('upfront_amount');

                if ($upfront <= 0) {
                    $validator->errors()->add('upfront_amount', 'Upfront amount must be greater than zero.');
                }

                if ($estimatedTotal > 0 && $upfront >= $estimatedTotal) {
                    $validator->errors()->add('upfront_amount', 'Upfront amount must be less than the order total.');
                }
            }
        });
    }
}

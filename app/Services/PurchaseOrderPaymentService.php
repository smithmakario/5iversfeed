<?php

namespace App\Services;

use App\Enums\PaymentOption;
use App\Enums\PaymentStatus;
use App\Models\PurchaseOrder;

class PurchaseOrderPaymentService
{
    public function syncPaymentFields(PurchaseOrder $order): void
    {
        $order->refresh();

        $updates = [
            'payment_status' => $this->determinePaymentStatus($order)->value,
        ];

        if ($order->dispatched_at && $order->creditRepaymentTimeline) {
            $updates['payment_due_date'] = $order->dispatched_at
                ->copy()
                ->addDays($order->creditRepaymentTimeline->days)
                ->toDateString();
        }

        $order->forceFill($updates)->save();
    }

    public function recordPayment(PurchaseOrder $order, float $amount): void
    {
        $order->forceFill([
            'amount_paid' => min($order->amount_paid + $amount, (float) $order->total),
        ])->save();

        $this->syncPaymentFields($order);
    }

    public function determinePaymentStatus(PurchaseOrder $order): PaymentStatus
    {
        $total = (float) $order->total;
        $paid = (float) $order->amount_paid;
        $option = $order->payment_option;

        if ($paid >= $total && $total > 0) {
            return PaymentStatus::Paid;
        }

        if ($order->payment_due_date?->isPast() && $paid < $total) {
            return PaymentStatus::Overdue;
        }

        return match ($option) {
            PaymentOption::FullCredit => $paid > 0
                ? PaymentStatus::PartiallyPaid
                : PaymentStatus::OnCredit,
            PaymentOption::PartialCredit => $paid >= (float) $order->upfront_amount && $paid < $total
                ? PaymentStatus::OnCredit
                : ($paid > 0 ? PaymentStatus::PartiallyPaid : PaymentStatus::Unpaid),
            PaymentOption::Partial => $paid > 0
                ? PaymentStatus::PartiallyPaid
                : PaymentStatus::Unpaid,
            PaymentOption::OneOff => $paid > 0
                ? PaymentStatus::PartiallyPaid
                : PaymentStatus::Unpaid,
        };
    }

    public function creditAmount(PurchaseOrder $order): float
    {
        return match ($order->payment_option) {
            PaymentOption::FullCredit => (float) $order->total,
            PaymentOption::PartialCredit => max(0, (float) $order->total - (float) $order->upfront_amount),
            default => 0,
        };
    }

    public function amountOutstanding(PurchaseOrder $order): float
    {
        return max(0, (float) $order->total - (float) $order->amount_paid);
    }
}

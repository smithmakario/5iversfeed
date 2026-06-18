<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class PaymentDashboardService
{
    public function __construct(
        private PurchaseOrderPaymentService $paymentService,
    ) {}

    /**
     * @param  Builder<PurchaseOrder>|HasMany<PurchaseOrder, mixed>  $query
     * @return array{
     *     due_count: int,
     *     due_amount: float,
     *     overdue_count: int,
     *     overdue_amount: float,
     *     due_soon_count: int,
     *     total_outstanding: float,
     * }
     */
    public function summary(Builder|HasMany $query): array
    {
        $orders = $this->payableOrders($query)->get();

        $dueCount = 0;
        $dueAmount = 0.0;
        $overdueCount = 0;
        $overdueAmount = 0.0;
        $dueSoonCount = 0;
        $totalOutstanding = 0.0;

        foreach ($orders as $order) {
            $outstanding = $this->paymentService->amountOutstanding($order);

            if ($outstanding <= 0) {
                continue;
            }

            $totalOutstanding += $outstanding;
            $daysToDue = $this->daysToDue($order);

            if ($daysToDue !== null && $daysToDue < 0) {
                $overdueCount++;
                $overdueAmount += $outstanding;
            } elseif ($daysToDue !== null && $daysToDue >= 0) {
                $dueCount++;
                $dueAmount += $outstanding;

                if ($daysToDue <= 7) {
                    $dueSoonCount++;
                }
            } elseif ($order->payment_status === PaymentStatus::Overdue) {
                $overdueCount++;
                $overdueAmount += $outstanding;
            } else {
                $dueCount++;
                $dueAmount += $outstanding;
            }
        }

        return [
            'due_count' => $dueCount,
            'due_amount' => $dueAmount,
            'overdue_count' => $overdueCount,
            'overdue_amount' => $overdueAmount,
            'due_soon_count' => $dueSoonCount,
            'total_outstanding' => $totalOutstanding,
        ];
    }

    /**
     * @param  Builder<PurchaseOrder>|HasMany<PurchaseOrder, mixed>  $query
     * @return Collection<int, array{
     *     purchase_order: PurchaseOrder,
     *     amount_due: float,
     *     days_to_due: ?int,
     *     is_overdue: bool,
     * }>
     */
    public function dueOrders(Builder|HasMany $query, int $limit = 10): Collection
    {
        return $this->payableOrders($query)
            ->with(['supplier', 'creditRepaymentTimeline'])
            ->orderByRaw('payment_due_date IS NULL')
            ->orderBy('payment_due_date')
            ->get()
            ->map(fn (PurchaseOrder $order) => $this->enrichOrder($order))
            ->filter(fn (array $row) => $row['amount_due'] > 0)
            ->sortBy(fn (array $row) => $row['days_to_due'] ?? PHP_INT_MAX)
            ->take($limit)
            ->values();
    }

    public function daysToDue(PurchaseOrder $order): ?int
    {
        if (! $order->payment_due_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($order->payment_due_date->startOfDay(), false);
    }

    public function isOverdue(PurchaseOrder $order): bool
    {
        if ($this->paymentService->amountOutstanding($order) <= 0) {
            return false;
        }

        $days = $this->daysToDue($order);

        if ($days !== null) {
            return $days < 0;
        }

        return $order->payment_status === PaymentStatus::Overdue;
    }

    /**
     * @return array{
     *     purchase_order: PurchaseOrder,
     *     amount_due: float,
     *     days_to_due: ?int,
     *     is_overdue: bool,
     * }
     */
    public function enrichOrder(PurchaseOrder $order): array
    {
        $daysToDue = $this->daysToDue($order);

        return [
            'purchase_order' => $order,
            'amount_due' => $this->paymentService->amountOutstanding($order),
            'days_to_due' => $daysToDue,
            'is_overdue' => $this->isOverdue($order),
        ];
    }

    /** @param Builder<PurchaseOrder>|HasMany<PurchaseOrder, mixed> $query */
    private function payableOrders(Builder|HasMany $query): Builder
    {
        $builder = $query instanceof Relation ? $query->getQuery() : $query;

        return (clone $builder)->whereNotIn('status', [
            PurchaseOrderStatus::Draft->value,
            PurchaseOrderStatus::Cancelled->value,
            PurchaseOrderStatus::Rejected->value,
        ]);
    }
}

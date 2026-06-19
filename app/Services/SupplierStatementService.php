<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SupplierStatementService
{
    public function __construct(
        private PurchaseOrderPaymentService $paymentService,
    ) {}

    /**
     * @return array{
     *     supplier: Supplier,
     *     from_date: string,
     *     to_date: string,
     *     generated_at: Carbon,
     *     lines: Collection<int, array{
     *         purchase_order: PurchaseOrder,
     *         total: float,
     *         amount_paid: float,
     *         outstanding: float,
     *         payment_due_date: ?Carbon,
     *         last_payment_date: ?Carbon,
     *         timeliness: array{label: string, color: string, detail: string},
     *     }>,
     *     summary: array{
     *         total_invoiced: float,
     *         total_paid: float,
     *         total_outstanding: float,
     *         on_time_count: int,
     *         late_count: int,
     *         overdue_count: int,
     *         pending_count: int,
     *         not_applicable_count: int,
     *     },
     * }
     */
    public function generate(Supplier $supplier, string $fromDate, string $toDate): array
    {
        $orders = PurchaseOrder::query()
            ->with([
                'creditRepaymentTimeline',
                'activities' => fn ($query) => $query
                    ->where('type', 'payment_recorded')
                    ->orderBy('created_at'),
            ])
            ->where('supplier_id', $supplier->id)
            ->whereNotIn('status', [
                PurchaseOrderStatus::Draft->value,
                PurchaseOrderStatus::Cancelled->value,
                PurchaseOrderStatus::Rejected->value,
            ])
            ->whereBetween('order_date', [$fromDate, $toDate])
            ->orderBy('order_date')
            ->orderBy('po_number')
            ->get();

        $lines = $orders->map(fn (PurchaseOrder $order) => $this->buildLine($order));

        return [
            'supplier' => $supplier,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'generated_at' => now(),
            'lines' => $lines,
            'summary' => $this->buildSummary($lines),
        ];
    }

    /**
     * @return array{
     *     purchase_order: PurchaseOrder,
     *     total: float,
     *     amount_paid: float,
     *     outstanding: float,
     *     payment_due_date: ?Carbon,
     *     last_payment_date: ?Carbon,
     *     timeliness: array{label: string, color: string, detail: string},
     * }
     */
    private function buildLine(PurchaseOrder $order): array
    {
        $total = (float) $order->total;
        $amountPaid = (float) $order->amount_paid;
        $outstanding = $this->paymentService->amountOutstanding($order);
        $lastPaymentDate = $order->activities->last()?->created_at;

        return [
            'purchase_order' => $order,
            'total' => $total,
            'amount_paid' => $amountPaid,
            'outstanding' => $outstanding,
            'payment_due_date' => $order->payment_due_date,
            'last_payment_date' => $lastPaymentDate,
            'timeliness' => $this->determineTimeliness($order, $outstanding, $lastPaymentDate),
        ];
    }

    /**
     * @param  Collection<int, array{
     *     total: float,
     *     amount_paid: float,
     *     outstanding: float,
     *     timeliness: array{label: string, color: string, detail: string},
     * }>  $lines
     * @return array{
     *     total_invoiced: float,
     *     total_paid: float,
     *     total_outstanding: float,
     *     on_time_count: int,
     *     late_count: int,
     *     overdue_count: int,
     *     pending_count: int,
     *     not_applicable_count: int,
     * }
     */
    private function buildSummary(Collection $lines): array
    {
        $summary = [
            'total_invoiced' => 0.0,
            'total_paid' => 0.0,
            'total_outstanding' => 0.0,
            'on_time_count' => 0,
            'late_count' => 0,
            'overdue_count' => 0,
            'pending_count' => 0,
            'not_applicable_count' => 0,
        ];

        foreach ($lines as $line) {
            $summary['total_invoiced'] += $line['total'];
            $summary['total_paid'] += $line['amount_paid'];
            $summary['total_outstanding'] += $line['outstanding'];

            match ($line['timeliness']['label']) {
                'On time' => $summary['on_time_count']++,
                'Late' => $summary['late_count']++,
                'Overdue' => $summary['overdue_count']++,
                'Pending' => $summary['pending_count']++,
                default => $summary['not_applicable_count']++,
            };
        }

        return $summary;
    }

    /**
     * @return array{label: string, color: string, detail: string}
     */
    private function determineTimeliness(
        PurchaseOrder $order,
        float $outstanding,
        ?Carbon $lastPaymentDate,
    ): array {
        $dueDate = $order->payment_due_date;

        if (! $dueDate) {
            if ($outstanding <= 0) {
                return [
                    'label' => 'Paid',
                    'color' => 'green',
                    'detail' => 'No credit due date — settled',
                ];
            }

            return [
                'label' => 'N/A',
                'color' => 'gray',
                'detail' => 'No payment due date',
            ];
        }

        if ($outstanding <= 0 && $lastPaymentDate) {
            $paidOnTime = $lastPaymentDate->startOfDay()->lte($dueDate->copy()->startOfDay());

            return $paidOnTime
                ? [
                    'label' => 'On time',
                    'color' => 'green',
                    'detail' => 'Paid on '.$lastPaymentDate->format('M d, Y').' (due '.$dueDate->format('M d, Y').')',
                ]
                : [
                    'label' => 'Late',
                    'color' => 'red',
                    'detail' => 'Paid on '.$lastPaymentDate->format('M d, Y').' (due '.$dueDate->format('M d, Y').')',
                ];
        }

        if ($outstanding > 0 && $dueDate->copy()->startOfDay()->isPast()) {
            return [
                'label' => 'Overdue',
                'color' => 'red',
                'detail' => 'Outstanding past due date of '.$dueDate->format('M d, Y'),
            ];
        }

        if ($outstanding > 0) {
            return [
                'label' => 'Pending',
                'color' => 'yellow',
                'detail' => 'Payment due '.$dueDate->format('M d, Y'),
            ];
        }

        return [
            'label' => 'N/A',
            'color' => 'gray',
            'detail' => 'No payment recorded',
        ];
    }
}

<?php

namespace App\Http\Controllers\Supplier;

use App\Enums\PaymentOption;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderPaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function __construct(
        private PurchaseOrderPaymentService $paymentService,
    ) {}

    public function index(Request $request): View
    {
        $supplier = $request->user()->supplier;

        $query = $supplier->purchaseOrders()
            ->with('creditRepaymentTimeline')
            ->whereNotIn('status', ['draft', 'cancelled', 'rejected'])
            ->latest();

        if ($paymentOption = $request->string('payment_option')->toString()) {
            $query->where('payment_option', $paymentOption);
        }

        if ($paymentStatus = $request->string('payment_status')->toString()) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($request->boolean('credit_only')) {
            $query->whereIn('payment_option', [
                PaymentOption::FullCredit->value,
                PaymentOption::PartialCredit->value,
            ]);
        }

        if ($request->boolean('overdue_only')) {
            $query->where('payment_status', PaymentStatus::Overdue->value);
        }

        $orders = $query->paginate(15)->withQueryString();

        $baseQuery = $supplier->purchaseOrders()
            ->whereNotIn('status', ['draft', 'cancelled', 'rejected']);

        $allOrders = (clone $baseQuery)->get();

        $totalOutstanding = $allOrders->sum(fn (PurchaseOrder $order) => $this->paymentService->amountOutstanding($order));
        $overdueCount = (clone $baseQuery)->where('payment_status', PaymentStatus::Overdue->value)->count();
        $onCreditCount = (clone $baseQuery)->where('payment_status', PaymentStatus::OnCredit->value)->count();
        $dueSoonCount = (clone $baseQuery)
            ->whereNotNull('payment_due_date')
            ->where('payment_due_date', '<=', now()->addDays(7))
            ->where('payment_due_date', '>=', now()->toDateString())
            ->whereIn('payment_status', [
                PaymentStatus::Unpaid->value,
                PaymentStatus::PartiallyPaid->value,
                PaymentStatus::OnCredit->value,
            ])
            ->count();

        return view('supplier.finance.index', [
            'orders' => $orders,
            'paymentOptions' => PaymentOption::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
            'summary' => [
                'total_outstanding' => $totalOutstanding,
                'overdue_count' => $overdueCount,
                'on_credit_count' => $onCreditCount,
                'due_soon_count' => $dueSoonCount,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentOption;
use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrderRequest;
use App\Http\Requests\RecordPaymentRequest;
use App\Models\CreditRepaymentTimeline;
use App\Models\Formulation;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseOrderActivityService;
use App\Services\PurchaseOrderPaymentService;
use App\Services\PurchaseOrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private PurchaseOrderStatusService $statusService,
        private PurchaseOrderPaymentService $paymentService,
        private PurchaseOrderActivityService $activityService,
    ) {}

    public function index(\Illuminate\Http\Request $request): View
    {
        $query = PurchaseOrder::query()
            ->with(['supplier', 'creator'])
            ->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return view('admin.purchase-orders.index', [
            'purchaseOrders' => $query->paginate(15)->withQueryString(),
            'statuses' => PurchaseOrderStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.purchase-orders.create', [
            'suppliers' => Supplier::query()->where('status', SupplierStatus::Approved)->orderBy('company_name')->get(),
            'formulations' => Formulation::query()
                ->with(['feedType', 'brand'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'supplier_id', 'sku', 'name', 'price_per_unit']),
            'creditTimelines' => CreditRepaymentTimeline::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('days')
                ->get(),
            'paymentOptions' => PaymentOption::cases(),
        ]);
    }

    public function store(PurchaseOrderRequest $request): RedirectResponse
    {
        $order = DB::transaction(function () use ($request) {
            $order = PurchaseOrder::query()->create([
                ...$this->paymentAttributes($request),
                'supplier_id' => $request->validated('supplier_id'),
                'created_by' => $request->user()->id,
                'status' => PurchaseOrderStatus::from($request->validated('status')),
                'order_date' => $request->validated('order_date'),
                'expected_delivery_date' => $request->validated('expected_delivery_date'),
                'tax_amount' => $request->validated('tax_amount', 0),
                'notes' => $request->validated('notes'),
            ]);

            $this->syncItems($order, $request->validated('items', []));
            $this->paymentService->syncPaymentFields($order);

            return $order;
        });

        $this->statusService->notifyForNewOrder($order, $request->user());

        return redirect()
            ->route('admin.purchase-orders.show', $order)
            ->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'creator', 'items.formulation', 'creditRepaymentTimeline', 'activities.user']);

        return view('admin.purchase-orders.show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder): View|RedirectResponse
    {
        if (! $purchaseOrder->status->isEditableByAdmin()) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'This purchase order cannot be edited in its current status.');
        }

        $purchaseOrder->load('items');

        return view('admin.purchase-orders.edit', [
            'purchaseOrder' => $purchaseOrder,
            'suppliers' => Supplier::query()->where('status', SupplierStatus::Approved)->orderBy('company_name')->get(),
            'formulations' => Formulation::query()
                ->with(['feedType', 'brand'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'supplier_id', 'sku', 'name', 'price_per_unit']),
            'creditTimelines' => CreditRepaymentTimeline::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('days')
                ->get(),
            'paymentOptions' => PaymentOption::cases(),
        ]);
    }

    public function update(PurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if (! $purchaseOrder->status->isEditableByAdmin()) {
            return back()->with('error', 'This purchase order cannot be edited in its current status.');
        }

        DB::transaction(function () use ($request, $purchaseOrder): void {
            $purchaseOrder->update([
                ...$this->paymentAttributes($request),
                'supplier_id' => $request->validated('supplier_id'),
                'order_date' => $request->validated('order_date'),
                'expected_delivery_date' => $request->validated('expected_delivery_date'),
                'tax_amount' => $request->validated('tax_amount', 0),
                'notes' => $request->validated('notes'),
            ]);

            $purchaseOrder->items()->delete();
            $this->syncItems($purchaseOrder, $request->validated('items', []));
            $this->paymentService->syncPaymentFields($purchaseOrder);
        });

        $this->activityService->log(
            $purchaseOrder,
            'order_updated',
            'Purchase order details were updated.',
            $request->user(),
        );

        $this->statusService->notifySupplierOfUpdate($purchaseOrder, $request->user());

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated successfully.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status === PurchaseOrderStatus::Received) {
            return back()->with('error', 'Received purchase orders cannot be deleted.');
        }

        $purchaseOrder->delete();

        return redirect()
            ->route('admin.purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }

    public function issue(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        return $this->performTransition($purchaseOrder, PurchaseOrderStatus::Submitted, 'Purchase order issued to supplier.');
    }

    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        return $this->performTransition($purchaseOrder, PurchaseOrderStatus::Cancelled, 'Purchase order cancelled.');
    }

    public function receive(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        return $this->performTransition($purchaseOrder, PurchaseOrderStatus::Received, 'Receipt acknowledged.');
    }

    public function recordPayment(RecordPaymentRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $outstanding = $this->paymentService->amountOutstanding($purchaseOrder);

        if ($outstanding <= 0) {
            return back()->with('error', 'This purchase order is already fully paid.');
        }

        $amount = min((float) $request->validated('amount'), $outstanding);

        $this->paymentService->recordPayment($purchaseOrder, $amount);

        $this->activityService->log(
            $purchaseOrder,
            'payment_recorded',
            'Payment of ₦'.number_format($amount, 2).' recorded.',
            $request->user(),
            ['amount' => $amount],
        );

        return back()->with('success', 'Payment of ₦'.number_format($amount, 2).' recorded successfully.');
    }

    private function performTransition(PurchaseOrder $purchaseOrder, PurchaseOrderStatus $status, string $message): RedirectResponse
    {
        try {
            $this->statusService->transition(
                $purchaseOrder,
                $status,
                auth()->user(),
                'admin',
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }

    private function paymentAttributes(PurchaseOrderRequest $request): array
    {
        $option = PaymentOption::from($request->validated('payment_option'));

        return [
            'payment_option' => $option->value,
            'credit_repayment_timeline_id' => $option->requiresCreditTimeline()
                ? $request->validated('credit_repayment_timeline_id')
                : null,
            'upfront_amount' => $option->requiresUpfrontAmount()
                ? $request->validated('upfront_amount', 0)
                : 0,
        ];
    }

    private function syncItems(PurchaseOrder $order, array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['formulation_id']) || empty($item['quantity'])) {
                continue;
            }

            $formulation = Formulation::query()->find($item['formulation_id']);
            if (! $formulation || (int) $formulation->supplier_id !== (int) $order->supplier_id) {
                continue;
            }

            $quantity = (int) $item['quantity'];
            $unitPrice = $item['unit_price'] ?? $formulation->price_per_unit;
            $subtotal = $quantity * $unitPrice;

            $order->items()->create([
                'formulation_id' => $formulation->id,
                'product_name' => $formulation->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);
        }

        $order->recalculateTotals();
    }
}

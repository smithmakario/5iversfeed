<?php

namespace App\Http\Controllers\Supplier;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DispatchPurchaseOrderRequest;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderActivityService;
use App\Services\PurchaseOrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private PurchaseOrderStatusService $statusService,
        private PurchaseOrderActivityService $activityService,
    ) {}

    public function index(Request $request): View
    {
        $supplier = $request->user()->supplier;

        $query = $supplier->purchaseOrders()
            ->with('creditRepaymentTimeline')
            ->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return view('supplier.purchase-orders.index', [
            'purchaseOrders' => $query->paginate(15)->withQueryString(),
            'statuses' => PurchaseOrderStatus::cases(),
        ]);
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder): View
    {
        $this->authorizeOrder($request, $purchaseOrder);

        $purchaseOrder->load(['items.formulation', 'creator', 'creditRepaymentTimeline', 'activities.user']);

        return view('supplier.purchase-orders.show', [
            'purchaseOrder' => $purchaseOrder,
            'amountOutstanding' => $purchaseOrder->amountOutstanding(),
            'creditAmount' => $purchaseOrder->creditAmount(),
        ]);
    }

    public function confirm(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorizeOrder($request, $purchaseOrder);

        return $this->performTransition(
            $purchaseOrder,
            PurchaseOrderStatus::Confirmed,
            $request->user(),
            'Purchase order confirmed.',
        );
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorizeOrder($request, $purchaseOrder);

        $request->validate(['rejection_reason' => ['required', 'string', 'max:2000']]);

        $purchaseOrder->update(['rejection_reason' => $request->input('rejection_reason')]);

        return $this->performTransition(
            $purchaseOrder,
            PurchaseOrderStatus::Rejected,
            $request->user(),
            'Purchase order rejected.',
        );
    }

    public function dispatch(DispatchPurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorizeOrder($request, $purchaseOrder);

        $purchaseOrder->update([
            'dispatched_at' => $request->date('dispatched_at')->startOfDay(),
        ]);

        try {
            $this->statusService->transition(
                $purchaseOrder,
                PurchaseOrderStatus::Dispatched,
                $request->user(),
                'supplier',
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Purchase order marked as dispatched. Credit period has started if applicable.');
    }

    public function addNote(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorizeOrder($request, $purchaseOrder);

        $request->validate(['supplier_notes' => ['nullable', 'string', 'max:2000']]);

        $purchaseOrder->update(['supplier_notes' => $request->input('supplier_notes')]);

        $this->activityService->log(
            $purchaseOrder,
            'note_added',
            'Supplier updated notes on the purchase order.',
            $request->user(),
        );

        return back()->with('success', 'Notes saved.');
    }

    private function performTransition(
        PurchaseOrder $purchaseOrder,
        PurchaseOrderStatus $status,
        $user,
        string $message,
    ): RedirectResponse {
        try {
            $this->statusService->transition(
                $purchaseOrder,
                $status,
                $user,
                'supplier',
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $message);
    }

    private function authorizeOrder(Request $request, PurchaseOrder $purchaseOrder): void
    {
        $supplierId = $request->user()->supplier?->id;

        if ((int) $purchaseOrder->supplier_id !== (int) $supplierId) {
            abort(403, 'You do not have access to this purchase order.');
        }
    }
}

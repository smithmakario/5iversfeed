<?php

namespace App\Http\Controllers\Supplier;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\PaymentDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private PaymentDashboardService $paymentDashboard,
    ) {}

    public function __invoke(Request $request): View
    {
        $supplier = $request->user()->supplier;
        $ordersQuery = $supplier->purchaseOrders();

        return view('supplier.dashboard', [
            'supplier' => $supplier,
            'stats' => [
                'total_orders' => $supplier->purchaseOrders()->count(),
                'pending_orders' => $supplier->purchaseOrders()
                    ->whereIn('status', [PurchaseOrderStatus::Submitted, PurchaseOrderStatus::Confirmed])
                    ->count(),
                'received_orders' => $supplier->purchaseOrders()
                    ->where('status', PurchaseOrderStatus::Received)
                    ->count(),
                'total_value' => $supplier->purchaseOrders()
                    ->where('status', PurchaseOrderStatus::Received)
                    ->sum('total'),
            ],
            'paymentSummary' => $this->paymentDashboard->summary($ordersQuery),
            'paymentDueOrders' => $this->paymentDashboard->dueOrders($ordersQuery, 15),
            'recentOrders' => $supplier->purchaseOrders()
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}

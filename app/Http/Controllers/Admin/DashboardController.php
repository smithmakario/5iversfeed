<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\FeedType;
use App\Models\Formulation;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PaymentDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private PaymentDashboardService $paymentDashboard,
    ) {}

    public function __invoke(): View
    {
        $ordersQuery = PurchaseOrder::query();

        return view('admin.dashboard', [
            'stats' => [
                'feed_types' => FeedType::query()->count(),
                'brands' => Brand::query()->count(),
                'products' => Formulation::query()->count(),
                'suppliers' => Supplier::query()->where('status', SupplierStatus::Approved)->count(),
                'pending_suppliers' => Supplier::query()->where('status', SupplierStatus::Pending)->count(),
                'purchase_orders' => PurchaseOrder::query()->count(),
                'open_orders' => PurchaseOrder::query()
                    ->whereIn('status', [
                        PurchaseOrderStatus::Submitted,
                        PurchaseOrderStatus::Confirmed,
                    ])
                    ->count(),
                'total_spend' => PurchaseOrder::query()
                    ->where('status', PurchaseOrderStatus::Received)
                    ->sum('total'),
            ],
            'paymentSummary' => $this->paymentDashboard->summary($ordersQuery),
            'paymentDueOrders' => $this->paymentDashboard->dueOrders($ordersQuery, 15),
            'recentOrders' => PurchaseOrder::query()
                ->with(['supplier', 'creator'])
                ->latest()
                ->limit(5)
                ->get(),
            'pendingSuppliers' => Supplier::query()
                ->where('status', SupplierStatus::Pending)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}

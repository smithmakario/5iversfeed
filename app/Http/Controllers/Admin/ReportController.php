<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Http\Controllers\Controller;
use App\Models\Formulation;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $ordersByStatus = PurchaseOrder::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $spendByMonth = PurchaseOrder::query()
            ->where('status', PurchaseOrderStatus::Received)
            ->select(
                DB::raw("DATE_FORMAT(received_at, '%Y-%m') as month"),
                DB::raw('SUM(total) as total_spend'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();

        $topSuppliers = Supplier::query()
            ->where('status', SupplierStatus::Approved)
            ->withSum(['purchaseOrders as total_spend' => function ($query) {
                $query->where('status', PurchaseOrderStatus::Received);
            }], 'total')
            ->withCount('purchaseOrders')
            ->orderByDesc('total_spend')
            ->limit(10)
            ->get();

        $topFormulations = Formulation::query()
            ->with(['feedType', 'brand'])
            ->withSum('purchaseOrderItems as units_ordered', 'quantity')
            ->orderByDesc('units_ordered')
            ->limit(10)
            ->get();

        return view('admin.reports.index', [
            'ordersByStatus' => $ordersByStatus,
            'spendByMonth' => $spendByMonth,
            'topSuppliers' => $topSuppliers,
            'topFormulations' => $topFormulations,
            'totalSpend' => PurchaseOrder::query()
                ->where('status', PurchaseOrderStatus::Received)
                ->sum('total'),
            'averageOrderValue' => PurchaseOrder::query()
                ->where('status', PurchaseOrderStatus::Received)
                ->avg('total'),
        ]);
    }
}

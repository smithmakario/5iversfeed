<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierStatementRequest;
use App\Models\Formulation;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\SupplierStatementService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private SupplierStatementService $statementService,
    ) {}

    public function index(SupplierStatementRequest $request): View
    {
        $ordersByStatus = PurchaseOrder::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', received_at)"
            : "DATE_FORMAT(received_at, '%Y-%m')";

        $spendByMonth = PurchaseOrder::query()
            ->where('status', PurchaseOrderStatus::Received)
            ->select(
                DB::raw("{$monthExpression} as month"),
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

        $statement = null;

        if ($request->filled(['supplier_id', 'from_date', 'to_date'])) {
            $supplier = Supplier::query()->findOrFail($request->validated('supplier_id'));

            $statement = $this->statementService->generate(
                $supplier,
                $request->validated('from_date'),
                $request->validated('to_date'),
            );
        }

        return view('admin.reports.index', [
            'ordersByStatus' => $ordersByStatus,
            'spendByMonth' => $spendByMonth,
            'topSuppliers' => $topSuppliers,
            'topFormulations' => $topFormulations,
            'suppliers' => Supplier::query()
                ->where('status', SupplierStatus::Approved)
                ->orderBy('company_name')
                ->get(['id', 'company_name']),
            'statement' => $statement,
            'statementFilters' => $request->only(['supplier_id', 'from_date', 'to_date']),
            'totalSpend' => PurchaseOrder::query()
                ->where('status', PurchaseOrderStatus::Received)
                ->sum('total'),
            'averageOrderValue' => PurchaseOrder::query()
                ->where('status', PurchaseOrderStatus::Received)
                ->avg('total'),
        ]);
    }
}

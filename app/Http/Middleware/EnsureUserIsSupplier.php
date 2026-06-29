<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\PurchaseOrder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSupplier
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->role === UserRole::Supplier && ! $user->supplier?->isApproved()) {
            return redirect()->route('supplier.pending');
        }

        $purchaseOrder = $request->route('purchase_order');

        if ($user?->role === UserRole::Admin && $purchaseOrder instanceof PurchaseOrder) {
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder);
        }

        if ($user?->role !== UserRole::Supplier) {
            abort(403, 'Supplier access required.');
        }

        return $next($request);
    }
}

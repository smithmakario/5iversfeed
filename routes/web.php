<?php

use App\Http\Controllers\Admin\CreditRepaymentTimelineController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FeedTypeController;
use App\Http\Controllers\Admin\PurchaseOrderController as AdminPurchaseOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\SupplierProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Supplier\DashboardController as SupplierDashboardController;
use App\Http\Controllers\Supplier\FinanceController as SupplierFinanceController;
use App\Http\Controllers\Supplier\PurchaseOrderController as SupplierPurchaseOrderController;
use App\Http\Controllers\SupplierOnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/supplier/apply', [SupplierOnboardingController::class, 'create'])->name('supplier.apply');
    Route::post('/supplier/apply', [SupplierOnboardingController::class, 'store'])->name('supplier.apply.store');
});

Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->dashboardRoute());
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/supplier/pending', function () {
        $user = auth()->user();

        if (! $user->isSupplier()) {
            abort(403, 'Supplier access required.');
        }

        return view('supplier.pending', ['supplier' => $user->supplier]);
    })->name('supplier.pending');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::resource('feed-types', FeedTypeController::class)->except(['show']);
        Route::resource('brands', BrandController::class)->except(['show']);
        Route::redirect('formulations', '/admin/suppliers')->name('formulations.index');
        Route::redirect('formulations/{any}', '/admin/suppliers')->where('any', '.*');
        Route::resource('suppliers', SupplierController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
        Route::resource('suppliers.products', SupplierProductController::class)
            ->parameters(['products' => 'formulation'])
            ->scoped();
        Route::post('suppliers/{supplier}/approve', [SupplierController::class, 'approve'])->name('suppliers.approve');
        Route::post('suppliers/{supplier}/reject', [SupplierController::class, 'reject'])->name('suppliers.reject');
        Route::post('suppliers/{supplier}/suspend', [SupplierController::class, 'suspend'])->name('suppliers.suspend');
        Route::resource('purchase-orders', AdminPurchaseOrderController::class);
        Route::post('purchase-orders/{purchase_order}/issue', [AdminPurchaseOrderController::class, 'issue'])->name('purchase-orders.issue');
        Route::post('purchase-orders/{purchase_order}/cancel', [AdminPurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::post('purchase-orders/{purchase_order}/receive', [AdminPurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('purchase-orders/{purchase_order}/payments', [AdminPurchaseOrderController::class, 'recordPayment'])->name('purchase-orders.record-payment');
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::resource('credit-timelines', CreditRepaymentTimelineController::class)
                ->parameters(['credit-timelines' => 'creditTimeline']);
        });
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::resource('users', UserController::class)->only(['index', 'show']);
        Route::put('users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password.update');
        Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    });

Route::middleware(['auth', 'verified', 'supplier'])
    ->prefix('supplier')
    ->name('supplier.')
    ->group(function () {
        Route::get('/dashboard', SupplierDashboardController::class)->name('dashboard');
        Route::get('finance', [SupplierFinanceController::class, 'index'])->name('finance.index');
        Route::get('purchase-orders', [SupplierPurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('purchase-orders/{purchase_order}', [SupplierPurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::post('purchase-orders/{purchase_order}/confirm', [SupplierPurchaseOrderController::class, 'confirm'])->name('purchase-orders.confirm');
        Route::post('purchase-orders/{purchase_order}/reject', [SupplierPurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
        Route::post('purchase-orders/{purchase_order}/dispatch', [SupplierPurchaseOrderController::class, 'dispatch'])->name('purchase-orders.dispatch');
        Route::post('purchase-orders/{purchase_order}/notes', [SupplierPurchaseOrderController::class, 'addNote'])->name('purchase-orders.notes');
    });

require __DIR__.'/auth.php';

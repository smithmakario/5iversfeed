<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SupplierStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\SupplierNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::query()->with('user')->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return view('admin.suppliers.index', [
            'suppliers' => $query->paginate(15)->withQueryString(),
            'statuses' => SupplierStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.suppliers.create', [
            'statuses' => SupplierStatus::cases(),
        ]);
    }

    public function store(
        StoreSupplierRequest $request,
        SupplierNotificationService $notificationService,
    ): RedirectResponse {
        $status = SupplierStatus::from($request->validated('status'));

        $supplier = DB::transaction(function () use ($request, $status): Supplier {
            $user = User::query()->create([
                'name' => $request->validated('contact_name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'role' => UserRole::Supplier,
                'email_verified_at' => now(),
            ]);

            $supplierData = collect($request->safe()->only([
                'company_name',
                'contact_name',
                'email',
                'phone',
                'address',
                'city',
                'state',
                'country',
                'tax_id',
                'registration_number',
                'admin_notes',
            ]))->merge([
                'user_id' => $user->id,
                'status' => $status,
            ]);

            if ($status === SupplierStatus::Approved) {
                $supplierData['approved_at'] = now();
                $supplierData['approved_by'] = $request->user()->id;
            }

            return Supplier::query()->create($supplierData->all());
        });

        if ($status === SupplierStatus::Approved) {
            $notificationService->notifyApproved($supplier);
        }

        return redirect()
            ->route('admin.suppliers.show', $supplier)
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['user', 'approvedBy', 'products.feedType', 'products.brand', 'purchaseOrders']);

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()
            ->route('admin.suppliers.show', $supplier)
            ->with('success', 'Supplier updated successfully.');
    }

    public function approve(
        Request $request,
        Supplier $supplier,
        SupplierNotificationService $notificationService,
    ): RedirectResponse {
        $supplier->update([
            'status' => SupplierStatus::Approved,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        $notificationService->notifyApproved($supplier->fresh());

        return back()->with('success', 'Supplier approved successfully.');
    }

    public function reject(
        Request $request,
        Supplier $supplier,
        SupplierNotificationService $notificationService,
    ): RedirectResponse {
        $request->validate(['admin_notes' => ['nullable', 'string', 'max:1000']]);

        $supplier->update([
            'status' => SupplierStatus::Rejected,
            'admin_notes' => $request->input('admin_notes'),
        ]);

        $notificationService->notifyRejected($supplier->fresh());

        return back()->with('success', 'Supplier application rejected.');
    }

    public function suspend(Supplier $supplier): RedirectResponse
    {
        $supplier->update(['status' => SupplierStatus::Suspended]);

        return back()->with('success', 'Supplier suspended.');
    }
}

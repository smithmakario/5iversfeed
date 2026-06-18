<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierProductRequest;
use App\Models\Brand;
use App\Models\FeedType;
use App\Models\Formulation;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierProductController extends Controller
{
    public function index(Supplier $supplier): View
    {
        return view('admin.suppliers.products.index', [
            'supplier' => $supplier,
            'products' => $supplier->products()
                ->with(['feedType', 'brand'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(15),
        ]);
    }

    public function create(Supplier $supplier): View
    {
        return view('admin.suppliers.products.create', [
            'supplier' => $supplier,
            ...$this->formOptions(),
        ]);
    }

    public function store(SupplierProductRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->products()->create($request->validated());

        return redirect()
            ->route('admin.suppliers.products.index', $supplier)
            ->with('success', 'Product created successfully.');
    }

    public function show(Supplier $supplier, Formulation $formulation): View
    {
        $formulation->load(['feedType', 'brand']);

        return view('admin.suppliers.products.show', [
            'supplier' => $supplier,
            'product' => $formulation,
        ]);
    }

    public function edit(Supplier $supplier, Formulation $formulation): View
    {
        return view('admin.suppliers.products.edit', [
            'supplier' => $supplier,
            'product' => $formulation,
            ...$this->formOptions(),
        ]);
    }

    public function update(SupplierProductRequest $request, Supplier $supplier, Formulation $formulation): RedirectResponse
    {
        $formulation->update($request->validated());

        return redirect()
            ->route('admin.suppliers.products.index', $supplier)
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Supplier $supplier, Formulation $formulation): RedirectResponse
    {
        $formulation->delete();

        return redirect()
            ->route('admin.suppliers.products.index', $supplier)
            ->with('success', 'Product deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'feedTypes' => FeedType::query()->where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }
}

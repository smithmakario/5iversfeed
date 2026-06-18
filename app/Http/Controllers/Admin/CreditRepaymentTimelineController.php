<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreditRepaymentTimelineRequest;
use App\Models\CreditRepaymentTimeline;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CreditRepaymentTimelineController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.credit-timelines.index', [
            'timelines' => CreditRepaymentTimeline::query()
                ->orderBy('sort_order')
                ->orderBy('days')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.settings.credit-timelines.create');
    }

    public function store(CreditRepaymentTimelineRequest $request): RedirectResponse
    {
        CreditRepaymentTimeline::query()->create($request->validated());

        return redirect()
            ->route('admin.settings.credit-timelines.index')
            ->with('success', 'Credit repayment timeline created successfully.');
    }

    public function edit(CreditRepaymentTimeline $creditTimeline): View
    {
        return view('admin.settings.credit-timelines.edit', [
            'timeline' => $creditTimeline,
        ]);
    }

    public function update(CreditRepaymentTimelineRequest $request, CreditRepaymentTimeline $creditTimeline): RedirectResponse
    {
        $creditTimeline->update($request->validated());

        return redirect()
            ->route('admin.settings.credit-timelines.index')
            ->with('success', 'Credit repayment timeline updated successfully.');
    }

    public function destroy(CreditRepaymentTimeline $creditTimeline): RedirectResponse
    {
        if ($creditTimeline->purchaseOrders()->exists()) {
            return back()->with('error', 'This timeline is in use by purchase orders and cannot be deleted.');
        }

        $creditTimeline->delete();

        return redirect()
            ->route('admin.settings.credit-timelines.index')
            ->with('success', 'Credit repayment timeline deleted successfully.');
    }
}

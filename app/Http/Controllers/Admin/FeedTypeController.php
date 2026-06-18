<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeedTypeRequest;
use App\Models\FeedType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedTypeController extends Controller
{
    public function index(): View
    {
        return view('admin.feed-types.index', [
            'feedTypes' => FeedType::query()->orderBy('sort_order')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.feed-types.create');
    }

    public function store(FeedTypeRequest $request): RedirectResponse
    {
        FeedType::query()->create($request->validated());

        return redirect()
            ->route('admin.feed-types.index')
            ->with('success', 'Feed type created successfully.');
    }

    public function edit(FeedType $feedType): View
    {
        return view('admin.feed-types.edit', compact('feedType'));
    }

    public function update(FeedTypeRequest $request, FeedType $feedType): RedirectResponse
    {
        $feedType->update($request->validated());

        return redirect()
            ->route('admin.feed-types.index')
            ->with('success', 'Feed type updated successfully.');
    }

    public function destroy(FeedType $feedType): RedirectResponse
    {
        $feedType->delete();

        return redirect()
            ->route('admin.feed-types.index')
            ->with('success', 'Feed type deleted successfully.');
    }
}

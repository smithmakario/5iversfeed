<?php

namespace App\Http\Controllers;

use App\Enums\SupplierStatus;
use App\Enums\UserRole;
use App\Http\Requests\SupplierOnboardingRequest;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SupplierOnboardingController extends Controller
{
    public function create(): View
    {
        return view('supplier-onboarding.create');
    }

    public function store(SupplierOnboardingRequest $request): RedirectResponse
    {
        $avatarPath = $request->file('company_logo')->store('avatars', 'public');

        DB::transaction(function () use ($request, $avatarPath): void {
            $user = User::query()->create([
                'name' => $request->validated('contact_name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'role' => UserRole::Supplier,
                'avatar' => $avatarPath,
            ]);

            Supplier::query()->create([
                'user_id' => $user->id,
                'company_name' => $request->validated('company_name'),
                'contact_name' => $request->validated('contact_name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'address' => $request->validated('address'),
                'city' => $request->validated('city'),
                'state' => $request->validated('state'),
                'country' => $request->validated('country'),
                'tax_id' => $request->validated('tax_id'),
                'registration_number' => $request->validated('registration_number'),
                'status' => SupplierStatus::Pending,
            ]);
        });

        return redirect()
            ->route('login')
            ->with('status', 'Your supplier application has been submitted. You will be notified once approved.');
    }
}

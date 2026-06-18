<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetUserPasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->with('supplier')->latest();

        if ($role = $request->string('role')->toString()) {
            $query->where('role', $role);
        }

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('admin.users.index', [
            'users' => $query->paginate(15)->withQueryString(),
            'roles' => UserRole::cases(),
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function show(User $user): View
    {
        $user->load('supplier');

        return view('admin.users.show', compact('user'));
    }

    public function updatePassword(ResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        $user->update([
            'password' => $request->validated('password'),
        ]);

        return back()->with('success', "Password reset for {$user->name}.");
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        if ($user->isAdmin() && User::query()->where('role', UserRole::Admin)->where('status', UserStatus::Active)->count() <= 1) {
            return back()->with('error', 'At least one active admin account is required.');
        }

        $user->update(['status' => UserStatus::Suspended]);

        return back()->with('success', "{$user->name} has been suspended.");
    }

    public function activate(User $user): RedirectResponse
    {
        $user->update(['status' => UserStatus::Active]);

        return back()->with('success', "{$user->name} has been activated.");
    }
}

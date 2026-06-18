<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSupplier
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->role !== UserRole::Supplier || ! $user->supplier?->isApproved()) {
            abort(403, 'Approved supplier access required.');
        }

        return $next($request);
    }
}

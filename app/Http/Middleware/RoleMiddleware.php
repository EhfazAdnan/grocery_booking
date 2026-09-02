<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            return redirect()->guest(route('login'));
        }

        $allowedRoles = array_map('strtolower', $roles);
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if (! in_array($userRole, $allowedRoles, true)) {
            if ($request->expectsJson()) {
                throw new AccessDeniedHttpException('Forbidden. You do not have permission to access this resource.');
            }

            // Full-page (web) request: send the user to their own dashboard.
            return redirect($userRole === Role::ADMIN->value ? '/admin/products' : '/customer/products');
        }

        return $next($request);
    }
}

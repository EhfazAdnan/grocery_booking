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
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $allowedRoles = array_map('strtolower', $roles);
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if (! in_array($userRole, $allowedRoles, true)) {
            throw new AccessDeniedHttpException('Forbidden. You do not have permission to access this resource.');
        }

        return $next($request);
    }
}

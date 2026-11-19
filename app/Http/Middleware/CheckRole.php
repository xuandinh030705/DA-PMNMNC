<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Roles may be passed as "admin,organizer" in one string or multiple parameters
        $allowedRoles = [];
        foreach ($roles as $roleParam) {
            foreach (explode(',', $roleParam) as $role) {
                $allowedRoles[] = trim($role);
            }
        }

        $userRole = $request->user()->role?->value;

        if ($userRole === null || ! in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Bạn không có quyền truy cập khu vực này.');
        }

        return $next($request);
    }
}

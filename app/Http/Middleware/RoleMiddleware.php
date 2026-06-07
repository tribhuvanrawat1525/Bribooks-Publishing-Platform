<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next,string $role): Response
    {
        $user = auth()->user();

        if (!$user) {
            abort(401);
        }

        if ($user->role !== $role) {

            return response()->json([
                'code' => 403,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return $next($request);
    }
}

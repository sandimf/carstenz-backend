<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleAuthApiMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->role !== $role) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}

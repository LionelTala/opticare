<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Non authentifié'
            ], 401);
        }

        if (!$user->isSuperAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Accès refusé. Vous devez être administrateur.'
            ], 403);
        }

        return $next($request);
    }
}
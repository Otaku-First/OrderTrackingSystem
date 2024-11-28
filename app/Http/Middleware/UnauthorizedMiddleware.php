<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnauthorizedMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Неавторизований доступ. Будь ласка, увійдіть у систему.',
            ], 401);
        }

        return $next($request);
    }
}

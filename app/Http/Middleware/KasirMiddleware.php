<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KasirMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isKasir()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Kasir access required.'], 403);
            }

            if ($request->user()?->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect('/');
        }

        return $next($request);
    }
}

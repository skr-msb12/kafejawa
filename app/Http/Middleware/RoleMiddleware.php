<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! in_array($request->user()->role, $roles, true)) {
            if ($request->user()->role === 'kasir') {
                return redirect()->route('pos.index')->with('error', 'Akses ditolak. Halaman tersebut khusus untuk Administrator.');
            }

            abort(403, 'Akses tidak diizinkan.');
        }

        return $next($request);
    }
}

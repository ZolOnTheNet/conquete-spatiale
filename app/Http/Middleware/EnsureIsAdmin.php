<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acces refuse. Droits administrateur requis.',
                ], 403);
            }
            return redirect()->route('dashboard')
                ->with('error', 'Acces refuse. Droits administrateur requis.');
        }

        return $next($request);
    }
}

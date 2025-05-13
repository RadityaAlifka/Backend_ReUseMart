<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$levels)
    {
        $user = auth()->user();
        
        // Menggunakan kolom 'level' daripada 'role'
        if (!$user || !in_array($user->level, $levels)) {
            return response()->json(['message' => 'Unauthorized access level'], 403);
        }
        
        return $next($request);
    }
}

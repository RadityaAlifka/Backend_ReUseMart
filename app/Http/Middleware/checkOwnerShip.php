<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->route('id'); 
        $authId = auth()->id();

        if ($userId != $authId) {
            return response()->json(['message' => 'Forbidden: not your resource'], 403);
        }

        return $next($request);
    }
}

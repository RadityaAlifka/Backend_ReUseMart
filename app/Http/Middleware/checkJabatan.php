<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkJabatan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$jabatans
     */
    public function handle(Request $request, Closure $next, ...$jabatans): Response
    {
        $user = auth()->user();

        // Pastikan user memiliki role 'pegawai'
        if ($user->level !== 'pegawai') {
            return response()->json(['message' => 'Unauthorized, not a staff member'], 403);
        }

        // Pastikan user memiliki relasi pegawai
        if (!$user->pegawai) {
            return response()->json(['message' => 'Unauthorized, no pegawai data found'], 403);
        }

        // Pastikan jabatan user sesuai dengan parameter
        $jabatanUser = $user->pegawai->jabatan->nama_jabatan ?? null;
        if (!in_array($jabatanUser, $jabatans)) {
            return response()->json(['message' => 'Unauthorized jabatan'], 403);
        }

        return $next($request);
    }
}
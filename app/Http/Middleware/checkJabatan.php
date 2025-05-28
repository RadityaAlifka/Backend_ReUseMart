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

        \Log::info('LEVEL: ' . $user->level);
        \Log::info('JABATAN: ' . ($user->pegawai->jabatan->nama_jabatan ?? 'null'));
        \Log::info('PARAM: ' . json_encode($jabatans));

        // Jika level user bukan 'pegawai', lewati pengecekan jabatan
        if ($user->level !== 'pegawai') {
            return $next($request);
        }

        // Jika user 'pegawai', pastikan dia punya relasi pegawai
        if (!$user->pegawai) {
            return response()->json(['message' => 'Unauthorized, no pegawai data found'], 403);
        }

        // Cek apakah jabatan user termasuk dalam parameter jabatan
        $jabatanUser = $user->pegawai->jabatan->nama_jabatan ?? null;
        if (!in_array($jabatanUser, $jabatans)) {
            return response()->json(['message' => 'Unauthorized jabatan'], 403);
        }

        return $next($request);
    }
}

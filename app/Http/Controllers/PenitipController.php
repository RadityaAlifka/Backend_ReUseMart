<?php

namespace App\Http\Controllers;

use App\Models\Penitip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PenitipController extends Controller
{
    public function index()
    {
        $penitips = Penitip::all();
        return response()->json($penitips);
    }

   
   public function registerPenitip(Request $request)
    {
    $validated = $request->validate([
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6|confirmed',
        'nama' => 'required',
        'no_telp' => 'required',
        'nik' => 'required',
    ]);

    $user = User::create([
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'level' => 'penitip',
    ]);

    Penitip::create([
        'user_id' => $user->id,
        'nama_penitip' => $validated['nama'],
        'email' => $user->email,
        'password' => $user->password, 
        'no_telp' => $validated['no_telp'],
        'nik' => $validated['nik'],
        'saldo' => 0,
        'poin' => 0,
        'akumulasi_rating' => 0,
    ]);

    return response()->json([
        'message' => 'Penitip registered',
        'token' => $user->createToken('auth_token')->plainTextToken
    ]);
}


    public function show($id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response()->json(['message' => 'Penitip not found'], 404);
        }

        return response()->json($penitip);
    }

    public function update(Request $request, $id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response()->json(['message' => 'Penitip not found'], 404);
        }

        $validatedData = $request->validate([
            'nama_penitip' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:penitips,email,' . $id . ',id_penitip',
            'password' => 'sometimes|required|string|min:8',
            'no_telp' => 'sometimes|required|string|max:15',
            'nik' => 'sometimes|required|string|max:16|unique:penitips,nik,' . $id . ',id_penitip',
            'saldo' => 'sometimes|required|numeric|min:0',
            'poin' => 'sometimes|required|integer|min:0',
            'akumulasi_rating' => 'sometimes|required|integer|min:0',
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $penitip->update($validatedData);

        return response()->json([
            'message' => 'Penitip updated successfully',
            'data' => $penitip
        ]);
    }

    public function destroy($id)
    {
        $penitip = Penitip::find($id);

        if (!$penitip) {
            return response()->json(['message' => 'Penitip not found'], 404);
        }

        $penitip->delete();

        return response()->json(['message' => 'Penitip deleted successfully']);
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\Pembeli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PembeliController extends Controller
{
    // Get all pembeli
    public function index()
    {
        $pembelis = Pembeli::all();
        return response()->json($pembelis);
    }

    // Store a new pembeli
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_pembeli' => 'required|string|max:255',
            'email' => 'required|email|unique:pembelis,email',
            'no_telp' => 'required|string|max:15',
            'password' => 'required|string|min:8',
            'poin' => 'required|integer|min:0',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $pembeli = Pembeli::create($validatedData);

        return response()->json([
            'message' => 'Pembeli created successfully',
            'data' => $pembeli
        ], 201);
    }

    // Show a specific pembeli
    public function show($id)
    {
        $pembeli = Pembeli::find($id);

        if (!$pembeli) {
            return response()->json(['message' => 'Pembeli not found'], 404);
        }

        return response()->json($pembeli);
    }

    // Update a specific pembeli
    public function update(Request $request, $id)
    {
        $pembeli = Pembeli::find($id);

        if (!$pembeli) {
            return response()->json(['message' => 'Pembeli not found'], 404);
        }

        $validatedData = $request->validate([
            'nama_pembeli' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:pembelis,email,' . $id . ',id_pembeli',
            'no_telp' => 'sometimes|required|string|max:15',
            'password' => 'sometimes|required|string|min:8',
            'poin' => 'sometimes|required|integer|min:0',
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $pembeli->update($validatedData);

        return response()->json([
            'message' => 'Pembeli updated successfully',
            'data' => $pembeli
        ]);
    }

    // Delete a specific pembeli
    public function destroy($id)
    {
        $pembeli = Pembeli::find($id);

        if (!$pembeli) {
            return response()->json(['message' => 'Pembeli not found'], 404);
        }

        $pembeli->delete();

        return response()->json(['message' => 'Pembeli deleted successfully']);
    }

    // Login for pembeli
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $pembeli = Pembeli::where('email', $validatedData['email'])->first();

        if (!$pembeli || !Hash::check($validatedData['password'], $pembeli->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate a token (you can use Sanctum or Passport for token-based authentication)
        $token = $pembeli->createToken('pembeli-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'pembeli' => $pembeli
        ]);
    }

    // Logout for pembeli
    public function logout(Request $request)
    {
        // Revoke all tokens for the authenticated pembeli
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}

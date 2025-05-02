<?php

namespace App\Http\Controllers;

use App\Models\Organisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OrganisasiController extends Controller
{
    // Get all organisasi
    public function index()
    {
        $organisasis = Organisasi::all();
        return response()->json($organisasis);
    }

    // Store a new organisasi
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_organisasi' => 'required|string|max:255',
            'alamat' => 'required|string',
            'email' => 'required|email|unique:organisasis,email',
            'no_telp' => 'required|string|max:15',
            'password' => 'required|string|min:8',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $organisasi = Organisasi::create($validatedData);

        return response()->json([
            'message' => 'Organisasi created successfully',
            'data' => $organisasi
        ], 201);
    }

    // Show a specific organisasi
    public function show($id)
    {
        $organisasi = Organisasi::find($id);

        if (!$organisasi) {
            return response()->json(['message' => 'Organisasi not found'], 404);
        }

        return response()->json($organisasi);
    }

    // Update a specific organisasi
    public function update(Request $request, $id)
    {
        $organisasi = Organisasi::find($id);

        if (!$organisasi) {
            return response()->json(['message' => 'Organisasi not found'], 404);
        }

        $validatedData = $request->validate([
            'nama_organisasi' => 'sometimes|required|string|max:255',
            'alamat' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:organisasis,email,' . $id . ',id_organisasi',
            'no_telp' => 'sometimes|required|string|max:15',
            'password' => 'sometimes|required|string|min:8',
        ]);

        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $organisasi->update($validatedData);

        return response()->json([
            'message' => 'Organisasi updated successfully',
            'data' => $organisasi
        ]);
    }

    // Delete a specific organisasi
    public function destroy($id)
    {
        $organisasi = Organisasi::find($id);

        if (!$organisasi) {
            return response()->json(['message' => 'Organisasi not found'], 404);
        }

        $organisasi->delete();

        return response()->json(['message' => 'Organisasi deleted successfully']);
    }

    // Login for organisasi
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $organisasi = Organisasi::where('email', $validatedData['email'])->first();

        if (!$organisasi || !Hash::check($validatedData['password'], $organisasi->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate a token (you can use Sanctum or Passport for token-based authentication)
        $token = $organisasi->createToken('organisasi-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'organisasi' => $organisasi
        ]);
    }

    // Logout for organisasi
    public function logout(Request $request)
    {
        // Revoke all tokens for the authenticated organisasi
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}

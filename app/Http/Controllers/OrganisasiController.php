<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Organisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class OrganisasiController 
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
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'nama_organisasi' => 'required|string|max:255',
            'alamat' => 'required|string',
            'email' => 'required|email|unique:users',
            'no_telp' => 'required|string|max:15',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Buat akun pengguna
        $user = User::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'level' => 'organisasi',
        ]);

        // Buat data organisasi terkait
        $organisasi = Organisasi::create([
            'user_id' => $user->id,
            'nama_organisasi' => $validatedData['nama_organisasi'],
            'alamat' => $validatedData['alamat'],
            'email' => $validatedData['email'],
            'password' => $user->password,
            'no_telp' => $validatedData['no_telp'],
        ]);

        return response()->json([
            'message' => 'Organisasi registered successfully',
            'user' => $user,
            'organisasi' => $organisasi,
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
        $pegawai = Pegawai::find($id);
    
        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }
    
        $validatedData = $request->validate([
            'id_jabatan' => 'sometimes|required|exists:jabatans,id_jabatan',
            'nama_pegawai' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:pegawais,email,' . $id . ',id_pegawai',
            'no_telp' => 'sometimes|required|string|max:15',
            'password' => 'sometimes|required|string|min:8',
            'komisi' => 'sometimes|required|numeric|min:0',
        ]);
    
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }
    
        $pegawai->update($validatedData);
    
        // Update juga pada tabel users jika ada
        $user = $pegawai->user;
        if ($user) {
            $user->name = $pegawai->nama_pegawai;
            $user->email = $pegawai->email;
            if (isset($request->password)) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
        }
    
        return response()->json([
            'message' => 'Pegawai updated successfully',
            'data' => $pegawai->load('jabatan')
        ]);
    }
    
        public function destroy($id)
    {
        $pegawai = Pegawai::find($id);
    
        if (!$pegawai) {
            return response()->json(['message' => 'Pegawai not found'], 404);
        }
    
        // Hapus user yang terkait terlebih dahulu
        $pegawai->user()->delete();
    
        // Lalu hapus pegawainya
        $pegawai->delete();
    
        return response()->json(['message' => 'Pegawai dan user terkait berhasil dihapus']);
    }
        public function getPegawaiLogin()
        {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
    
            $pegawai = Pegawai::with('jabatan')->where('user_id', $user->id)->first();
    
            if (!$pegawai) {
                return response()->json(['message' => 'Pegawai not found'], 404);
            }
            return response()->json($pegawai);
        }
    }

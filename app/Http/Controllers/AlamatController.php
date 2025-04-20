<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use App\Models\Pembeli;
use Illuminate\Http\Request;

class AlamatController extends Controller
{
    public function index()
    {
        $alamat = Alamat::with('pembeli')->get();
        return response()->json([
            'message' => 'Alamat retrieved successfully',
            'data' => $alamat
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_pembeli' => 'required|integer|exists:pembelis,id_pembeli',
            'kabupaten' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
            'detail_alamat' => 'required|string|max:500',
            'kode_pos' => 'required|integer',
            'label_alamat' => 'required|string|max:255',
        ], [
    'id_pembeli.exists' => 'The selected Pembeli does not exist.',
        ]);

        $alamat = Alamat::create($validatedData);

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan',
            'data' => $alamat
        ], 201);
    }

    public function show($id)
    {
        $alamat = Alamat::with('pembeli')->find($id);

        if (!$alamat) {
            return response()->json(['message' => 'Alamat not found'], 404);
        }

        return response()->json([
            'message' => 'Alamat retrieved successfully',
            'data' => $alamat
        ]);
    }

    public function edit($id)
    {
        
    }

    public function update(Request $request, $id)
    {
        $alamat = Alamat::find($id);

        if (!$alamat) {
            return response()->json(['message' => 'Alamat not found'], 404);
        }

        $validatedData = $request->validate([
            'kabupaten' => 'sometimes|required|string|max:255',
            'kecamatan' => 'sometimes|required|string|max:255',
            'kelurahan' => 'sometimes|required|string|max:255',
            'detail_alamat' => 'sometimes|required|string|max:500',
            'kode_pos' => 'sometimes|required|integer',
            'label_alamat' => 'sometimes|required|string|max:255',
        ]);

        $alamat->update($validatedData);

        return response()->json([
            'message' => 'Alamat updated successfully',
            'data' => $alamat
        ]);
    }

    public function destroy($id)
    {
        $alamat = Alamat::find($id);

        if (!$alamat) {
            return response()->json(['message' => 'Alamat not found'], 404);
        }

        $alamat->delete();

        return response()->json(['message' => 'Alamat deleted successfully']);
    }
}

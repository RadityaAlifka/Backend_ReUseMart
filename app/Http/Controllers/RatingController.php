<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController 
{
    // Get all ratings
    public function index()
    {
        $ratings = Rating::with(['barang', 'pembeli'])->get();
        return response()->json($ratings);
    }

    // Store a new rating
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_pembeli' => 'required|exists:pembelis,id_pembeli',
            'id_barang' => 'required|exists:barangs,id_barang',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $rating = Rating::create($validatedData);

        return response()->json([
            'message' => 'Rating created successfully',
            'data' => $rating->load(['barang', 'pembeli'])
        ], 201);
    }

    // Show a specific rating
    public function show($id)
    {
        $rating = Rating::with(['barang', 'pembeli'])->find($id);

        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], 404);
        }

        return response()->json($rating);
    }

    // Update a specific rating
    public function update(Request $request, $id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], 404);
        }

        $validatedData = $request->validate([
            'id_pembeli' => 'sometimes|required|exists:pembelis,id_pembeli',
            'id_barang' => 'sometimes|required|exists:barangs,id_barang',
            'rating' => 'sometimes|required|integer|min:1|max:5',
        ]);

        $rating->update($validatedData);

        return response()->json([
            'message' => 'Rating updated successfully',
            'data' => $rating->load(['barang', 'pembeli'])
        ]);
    }

    // Delete a specific rating
    public function destroy($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], 404);
        }

        $rating->delete();

        return response()->json(['message' => 'Rating deleted successfully']);
    }
    public function showByBarang($id_barang)
    {
        $ratings = Rating::with(['barang', 'pembeli'])
                        ->where('id_barang', $id_barang)
                        ->get();

        if ($ratings->isEmpty()) {
            return response()->json(['message' => 'Tidak ada rating untuk barang ini'], 404);
        }

        return response()->json($ratings);
    }
    
}

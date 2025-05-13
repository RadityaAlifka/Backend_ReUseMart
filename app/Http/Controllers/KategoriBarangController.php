<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KategoriBarangController extends Controller
{
    public function index(){
        $kategoriBarang = KategoriBarang::all();

        return response()->json([
            'message' => 'Kategori Barang retrieved successfully',
            'data' => $kategoriBarang
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KategoriBarangController 
{
    public function index(){
        $kategoriBarang = KategoriBarang::all();

        return response()->json([
            'message' => 'Kategori Barang retrieved successfully',
            'data' => $kategoriBarang
        ]);
    }
}

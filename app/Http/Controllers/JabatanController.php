<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jabatan;
class JabatanController 
{
    public function index(){
        // Fetch all Jabatan records
        $jabatan = Jabatan::all();

        return response()->json([
            'message' => 'Jabatan retrieved successfully',
            'data' => $jabatan
        ]);
    }
}

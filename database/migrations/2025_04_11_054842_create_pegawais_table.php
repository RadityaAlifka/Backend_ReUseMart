<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pegawais', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            
            $table->string('id_pegawai')->primary();
            $table->string('id_jabatan');
            $table->string('nama_pegawai');
            $table->string('email')->unique();
            $table->string('no_telp');
            $table->string('password');
            $table->float('komisi');
            $table->foreign('id_jabatan')->references('id_jabatan')->on('jabatans')->onDelete('cascade');
        });

        
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};

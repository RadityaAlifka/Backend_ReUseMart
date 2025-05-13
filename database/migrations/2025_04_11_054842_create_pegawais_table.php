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
            $table->id('id_pegawai');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_jabatan')->references('id_jabatan')->on('jabatans')->onDelete('cascade');
            $table->string('nama_pegawai');
            $table->string('email')->unique();
            $table->string('no_telp');
            $table->string('password');
            $table->float('komisi');
            
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

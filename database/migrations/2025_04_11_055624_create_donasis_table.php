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
        Schema::create('donasis', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('id_donasi');
            $table->foreignId('id_organisasi')->references('id_organisasi')->on('organisasis')->onDelete('cascade');
            $table->date('tanggal_donasi');
            $table->string('nama_penerima');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donasis');
    }
};

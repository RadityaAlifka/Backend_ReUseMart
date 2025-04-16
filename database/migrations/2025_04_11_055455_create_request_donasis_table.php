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
        Schema::create('request_donasis', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('id_request');
            $table->foreignId('id_organisasi')->references('id_organisasi')->on('organisasis')->onDelete('cascade');
            $table->foreignId('id_pegawai')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
            $table->date('tanggal_request');
            $table->string('detail_request');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_donasis');
    }
};

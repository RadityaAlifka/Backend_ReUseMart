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
        Schema::create('barangs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('id_barang'); 
            $table->foreignId('id_kategori')->references('id_kategori')->on('kategori_barangs')->onDelete('cascade');
            $table->foreignId('id_penitipan')->references('id_penitipan')->on('penitipans')->onDelete('cascade');
            $table->foreignId('id_donasi')->references('id_donasi')->on('donasis')->onDelete('cascade');
            $table->string('nama_barang');
            $table->string('deskripsi_barang');
            $table->string('garansi');
            $table->date('tanggal_garansi');
            $table->float('harga');
            $table->string('status_barang');
            $table->float('berat');
            $table->date('tanggal_keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};

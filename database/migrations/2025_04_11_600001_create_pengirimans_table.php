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
        Schema::create('pengirimans', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('id_pengiriman');
            $table->foreignId('id_transaksi')->references('id_transaksi')->on('transaksis')->onDelete('cascade');
            $table->foreignId('id_pegawai')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
            $table->date('tanggal_pengiriman');
            $table->string('status_pengiriman');
            $table->float('ongkir');
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengirimans');
    }
};

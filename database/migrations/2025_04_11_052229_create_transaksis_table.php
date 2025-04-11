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
        Schema::create('transaksis', function (Blueprint $table) {
            $table->string('id_transaksi')->primary();
            $table->integer('id_pengiriman');
            $table->integer('id_pengambilan');
            $table->string('id_pembeli');
            $table->string('id_penjual');
            $table->date('tgl_pesan');
            $table->date('tgl_lunas');
            $table->float('diskon_poin');
            $table->string('bukti_pembayaran');
            $table->string('status_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};

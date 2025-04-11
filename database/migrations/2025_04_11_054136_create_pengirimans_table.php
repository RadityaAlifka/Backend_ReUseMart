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
            $table->integer('id_pengiriman')->primary();
            $table->string('id_transaksi');
            $table->string('id_pegawai');
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

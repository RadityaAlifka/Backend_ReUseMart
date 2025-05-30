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
        Schema::create('pengambilans', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('id_pengambilan');
           $table->foreignId('id_transaksi')->references('id_transaksi')->on('transaksis')->onDelete('cascade')->nullable();
            $table->foreignId('id_penitip')->references('id_penitip')->on('penitips')->onDelete('cascade');
            $table->foreignId('id_pembeli')->references('id_pembeli')->on('pembelis')->onDelete('cascade')->nullable();
            $table->date('tanggal_pengambilan');
            $table->date('batas_pengambilan');
            $table->string('status_pengambilan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengambilans');
    }
};

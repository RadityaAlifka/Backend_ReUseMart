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
            $table->integer('id_pengambilan');
            $table->string('id_transaksi');
            $table->string('id_penitip');
            $table->string('id_pembeli');
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

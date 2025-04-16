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
        Schema::create('penitipans', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('id_penitipan');
            $table->foreignId('id_penitip')->references('id_penitip')->on('penitips')->onDelete('cascade');
            $table->date('tanggal_penitipan');
            $table->date('batas_penitipan');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penitipans');
    }
};

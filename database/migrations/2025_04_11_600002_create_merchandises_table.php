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
        Schema::create('merchandises', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('id_merchandise');
            $table->foreignId('id_pembeli')->references('id_pembeli')->on('pembelis')->onDelete('cascade');
            $table->foreignId('id_pegawai')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
            $table->string('nama_merchandise');
            $table->integer('stock_merchandise');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchandises');
    }
};

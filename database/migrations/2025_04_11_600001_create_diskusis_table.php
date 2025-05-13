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
        Schema::create('diskusis', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->foreignId('id_pembeli')->references('id_pembeli')->on('pembelis')->onDelete('cascade');
            $table->foreignId('id_pegawai')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
            $table->string('detail_diskusi');
            $table->foreignId('id_barang')->references('id_barang')->on('barangs')->onDelete('cascade');
            $table->string('reply');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diskusis');
    }
};

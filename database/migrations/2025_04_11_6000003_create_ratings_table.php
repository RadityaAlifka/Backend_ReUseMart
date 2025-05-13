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
        Schema::create('ratings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('id_rating');
            $table->foreignId('id_pembeli')->references('id_pembeli')->on('pembelis')->onDelete('cascade');
            $table->foreignId('id_barang')->references('id_barang')->on('barangs')->onDelete('cascade');
            $table->integer('rating');
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};

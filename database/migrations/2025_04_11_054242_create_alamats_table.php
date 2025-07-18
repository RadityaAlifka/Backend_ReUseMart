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
        Schema::create('alamats', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('id_alamat');
            $table->foreignId('id_pembeli')->references('id_pembeli')->on('pembelis')->onDelete('cascade');
            $table->string('kabupaten');
            $table->string('kecamatan');
            $table->string('kelurahan');
            $table->string('detail_alamat');
            $table->integer('kode_pos');
            $table->string('label_alamat');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamats');
    }
};

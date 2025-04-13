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
            $table->string('id_alamat')->primary();
            $table->string('id_pembeli');
            $table->string('kabupaten');
            $table->string('kecamatan');
            $table->string('kelurahan');
            $table->string('detail_alamat');
            $table->integer('kode_pos');
            $table->string('label_alamat');
            $table->foreign('id_pembeli')->references('id_pembeli')->on('pembelis')->onDelete('cascade');
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

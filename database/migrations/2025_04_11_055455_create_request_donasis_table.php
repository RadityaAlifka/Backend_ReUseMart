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
        Schema::create('request_donasis', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id_request')->primary();
            $table->string('id_organisasi');
            $table->string('id_pegawai');
            $table->date('tanggal_request');
            $table->string('detail_request');
            $table->foreign('id_organisasi')->references('id_organisasi')->on('organisasis')->onDelete('cascade');
            $table->foreign('id_pegawai')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_donasis');
    }
};

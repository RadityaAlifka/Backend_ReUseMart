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
        Schema::create('penitips', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('id_penitip')->primary();
            $table->string('nama_penitip');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('no_telp');
            $table->string('nik');
            $table->decimal('saldo');
            $table->integer('poin');
            $table->integer('akumulasi_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penitips');
    }
};

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
            $table->integer('id_request');
            $table->string('id_organisasi');
            $table->string('id_pegawai');
            $table->date('tanggal_request');
            $table->string('detail_request');

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

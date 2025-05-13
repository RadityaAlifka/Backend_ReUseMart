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
        Schema::create('pembelis', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('id_pembeli');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama_pembeli');
            $table->string('email')->unique();
            $table->string('no_telp');
            $table->string('password');
            $table->integer('poin');
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelis');
    }
};

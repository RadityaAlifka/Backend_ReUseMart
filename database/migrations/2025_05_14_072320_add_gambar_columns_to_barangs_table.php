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
        Schema::table('barangs', function (Blueprint $table) {
            $table->string('gambar1')->nullable()->after('tanggal_keluar'); // Menambahkan kolom gambar1
            $table->string('gambar2')->nullable()->after('gambar1'); // Menambahkan kolom gambar2
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropColumn(['gambar1', 'gambar2']); // Menghapus kolom gambar1 dan gambar2
        });
    }
};
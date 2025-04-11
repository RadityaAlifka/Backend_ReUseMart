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
        Schema::create('barangs', function (Blueprint $table) {
            $table->string("id_barang")->primary(); 
            $table->string("id_kategori");
            $table->string("id_detailTransaksi");
            $table->integer("id_penitipan");
            $table->integer("id_donasi");
            $table->string("nama_barang");
            $table->string("deskripsi_barang");
            $table->string("garansi");
            $table->date("tanggal_garansi");
            $table->float("harga");
            $table->string("status_barang");
            $table->float("berat");
            $table->date("tanggal_keluar");


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};

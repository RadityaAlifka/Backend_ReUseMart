<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TambahKolomPerpanjangan extends Migration
{
    public function up()
    {
        Schema::table('penitipans', function (Blueprint $table) {
            $table->boolean('perpanjangan')->default(false); // Default: belum diperpanjang
        });
    }

    public function down()
    {
        Schema::table('penitipans', function (Blueprint $table) {
            $table->dropColumn('perpanjangan');
        });
    }
}
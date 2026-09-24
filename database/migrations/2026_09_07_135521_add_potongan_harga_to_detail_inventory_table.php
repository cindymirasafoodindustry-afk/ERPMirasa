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
        Schema::table('detail_inventory', function (Blueprint $table) {
            // Menambahkan kolom potongan_harga default bernilai 0
            $table->integer('potongan_harga')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_inventory', function (Blueprint $table) {
            $table->dropColumn('potongan_harga');
        });
    }
};

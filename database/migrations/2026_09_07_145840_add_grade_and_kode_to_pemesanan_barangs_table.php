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
        Schema::table('pemesanan_barangs', function (Blueprint $table) {
            $table->string('kode_pesanan', 50)->nullable()->after('id');
            $table->string('grade', 20)->default('Non Grade')->nullable()->after('barang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemesanan_barangs', function (Blueprint $table) {
            $table->dropColumn(['kode_pesanan', 'grade']);
        });
    }
};

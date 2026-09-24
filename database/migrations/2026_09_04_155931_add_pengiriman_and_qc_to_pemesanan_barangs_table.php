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
            $table->date('tanggal_kirim')->nullable()->after('jatuh_tempo');
            $table->decimal('jumlah_diterima', 15, 2)->default(0)->after('tanggal_kirim');
            $table->decimal('jumlah_reject', 15, 2)->default(0)->after('jumlah_diterima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pemesanan_barangs', function (Blueprint $table) {
            //
        });
    }
};

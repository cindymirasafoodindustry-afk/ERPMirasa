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
            $table->decimal('sudah_dibayar', 15, 2)->default(0)->after('total_pembayaran');
            $table->decimal('sisa_hutang', 15, 2)->default(0)->after('sudah_dibayar');
            $table->date('jatuh_tempo')->nullable()->after('sisa_hutang');
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

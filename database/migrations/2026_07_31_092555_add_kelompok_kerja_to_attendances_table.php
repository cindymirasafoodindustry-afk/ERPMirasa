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
        Schema::table('attendances', function (Blueprint $table) {
        // Menambahkan kolom kelompok kerja harian setelah kolom employee_id
        $table->string('kelompok_kerja_harian')->nullable()->after('employee_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
        $table->dropColumn('kelompok_kerja_harian');
        });
    }
};

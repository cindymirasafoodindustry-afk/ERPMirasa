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
        Schema::table('payroll_details', function (Blueprint $table) {
            // KUNCI SAKTI: Gabungkan Employee ID dan Bulan Tahun agar menjadi indeks unik acuan Excel Upsert!
            $table->unique(['employee_id', 'bulan_tahun']);
        });
    }

    public function down(): void
    {
        Schema::table('payroll_details', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'bulan_tahun']);
        });
    }

};

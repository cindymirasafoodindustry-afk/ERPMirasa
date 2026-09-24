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
        // Menambahkan kolom nomor_file_bca dengan nilai awal bawaan (default) 36
        $table->integer('nomor_file_bca')->default(36)->nullable();
    });
}

public function down(): void
{
    Schema::table('payroll_details', function (Blueprint $table) {
        $table->dropColumn('nomor_file_bca');
    });
}
};

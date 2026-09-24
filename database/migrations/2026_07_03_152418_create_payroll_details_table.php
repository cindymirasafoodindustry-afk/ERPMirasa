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
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            
            // 1. HUBUNGKAN KE TABEL EMPLOYEES SECARA AMAN (FOREIGN KEY KARYAWAN)
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            
            // PENYARING PERIODE GAJI (Contoh: "2026-07" untuk periode Juli 2026)
            $table->string('bulan_tahun', 7); 

            // KOMPONEN PENDAPATAN GAJI PABRIKAN
            $table->decimal('gaji_perhari', 15, 2)->default(0);          // Input Gaji Perhari
            $table->decimal('honor_lembur_1', 15, 2)->default(0);        // Honor Lembur I (Jam ke-8)
            $table->decimal('honor_lembur_2', 15, 2)->default(0);        // Honor Lembur II (Jam ke-9 dst)
            $table->decimal('tunjangan_masa_kerja', 15, 2)->default(0);  // Tunjangan Masa Kerja
            $table->decimal('tunjangan_jabatan', 15, 2)->default(0);     // Tunjangan Jabatan
            $table->decimal('insentif', 15, 2)->default(0);              // Insentif Kerajinan

            // KOMPONEN POTONGAN GAJI PABRIKAN
            $table->decimal('potongan_bpjs_kes', 15, 2)->default(0);     // Potongan BPJS Kesehatan
            $table->decimal('potongan_bpjs_tk', 15, 2)->default(0);      // Potongan BPJS Ketenagakerjaan
            $table->decimal('potongan_jam_kerja', 15, 2)->default(0);    // Denda Pulang Cepat (< 7 Jam)
            $table->decimal('potongan_lainnya', 15, 2)->default(0);      // Potongan Lainnya Kasbon/Sanksi

            // HASIL KALKULASI BERSIH FINAL
            $table->decimal('total_gaji_bersih', 15, 2)->default(0);     // Gaji Harian Netto Akhir

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};

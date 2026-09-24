<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('id_karyawan')->unique(); // 1. ID Karyawan
            $table->string('nama_karyawan');        // 2. Nama Karyawan
            
            // 3, 4, 5. Pilihan kelompok, shift, dan status dikunci dengan enum
            $table->enum('kelompok', ['langsung', 'tidak langsung', 'LANGSUNG', 'TIDAK LANGSUNG']);
            $table->enum('shift', ['A', 'B', 'non shift']);
            $table->enum('status_karyawan', ['tetap', 'tidak tetap']);
            
            $table->date('tanggal_masuk_kerja');    // 6. Tanggal Masuk Kerja
            $table->timestamps();
        });
    }
};

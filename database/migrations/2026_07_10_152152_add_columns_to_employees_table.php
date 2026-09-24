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
        Schema::table('employees', function (Blueprint $table) {
            // Menyuntikkan 4 laci data baru secara aman ke dalam tabel employees yang lama
            $table->string('bagian')->nullable()->after('kelompok'); // UMUM, PACKING, POT.RENDEM, SORTIR AC, BATCH FRYER
            $table->string('no_hp')->nullable()->after('bagian');
            $table->string('no_rekening')->nullable()->after('no_hp');
            $table->string('nama_bank')->nullable()->after('no_rekening');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            //
        });
    }
};

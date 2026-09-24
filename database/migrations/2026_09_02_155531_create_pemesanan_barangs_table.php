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
        Schema::create('pemesanan_barangs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_pesan');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('jenis_barang_id');
            $table->unsignedBigInteger('barang_id');
            $table->integer('jumlah');
            $table->string('satuan');
            $table->bigInteger('harga_barang');
            $table->bigInteger('diskon')->default(0);
            $table->integer('pajak_ppn'); // Menyimpan nilai angka 11 atau 0
            $table->bigInteger('total_pembayaran');
            $table->string('status')->default('Pending'); // Status awal otomatis Pending
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_barangs');
    }
};

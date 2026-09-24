<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan_batches', function (Blueprint $table) {
            $table->id();
            // Menghubungkan cicilan ini ke ID PO utamanya
            $table->foreignId('pemesanan_barang_id')->constrained('pemesanan_barangs')->onDelete('cascade');

            $table->string('nama_batch', 50); // Contoh: "Batch 1", "Batch 2"
            $table->date('tanggal_datang');
            $table->integer('jumlah_dikirim')->default(0);
            $table->integer('jumlah_diterima_qc')->default(0);
            $table->integer('jumlah_reject')->default(0);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan_batches');
    }
};

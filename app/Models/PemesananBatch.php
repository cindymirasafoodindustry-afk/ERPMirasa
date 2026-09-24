<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemesananBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'pemesanan_barang_id',
        'nama_batch',
        'tanggal_datang',
        'jumlah_dikirim',
        'jumlah_diterima_qc',
        'jumlah_reject',
        'catatan'
    ];

    // Relasi balik ke PO Utama
    public function pemesananBarang()
    {
        return $this->belongsTo(PemesananBarang::class, 'pemesanan_barang_id');
    }
}

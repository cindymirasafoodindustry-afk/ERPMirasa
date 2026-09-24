<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemesananBarang extends Model
{
    // Mengizinkan kolom-kolom ini diisi saat admin menyimpan data dari form
    protected $fillable = [
        'kode_pesanan',    
        'tanggal_pesan',
        'supplier_id',
        'jenis_barang_id',
        'barang_id',
        'grade',
        'jumlah',
        'satuan',
        'harga_barang',
        'diskon',
        'potongan_harga',
        'pajak_ppn',
        'total_pembayaran',
        'status',
        'sudah_dibayar',
        'sisa_hutang',
        'jatuh_tempo',
        'tanggal_kirim',
        'jumlah_diterima',
        'jumlah_reject'
    ];

    // Relasi untuk menarik data Nama/Kode barang di halaman tabel utama nanti
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    // Relasi untuk menarik data supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    // Relasi untuk menarik data jenis barang
    public function jenisBarang()
    {
        return $this->belongsTo(JenisBarang::class, 'jenis_barang_id');
    }

    public function batches()
    {
        return $this->hasMany(PemesananBatch::class, 'pemesanan_barang_id');
    }
}

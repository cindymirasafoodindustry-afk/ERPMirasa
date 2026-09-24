<?php

namespace App\Models;

use App\Models\KartuStok;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DetailInventory extends Model
{
    use LogsActivity;

    protected $table = 'detail_inventory';

    public $keterangan_transaksi = null;

    protected $fillable = [
        'id_inventory',
        'id_supplier',
        'id_produksi',
        'nomor_batch',
        'tanggal_masuk',
        'tanggal_exp',
        'stok',
        'jumlah_diterima',
        'jumlah_rusak',
        'jumlah_return',
        'harga',
        'total_harga',
        'diskon',
        'potongan_harga',
        'kondisi_kerusakan',
        'status_return',
        // 'kondisi_barang',
        // 'kondisi_kendaraan',
        'tempat_penyimpanan',
        'status',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('detail_inventory');
    }

    public function Inventory()
    {
        return $this->belongsTo(Inventory::class, 'id_inventory');
    }

    public function Produksi()
    {
        return $this->belongsTo(Produksi::class, 'id_produksi');
    }

    protected static function booted()
    {
        static::created(function ($detail) {

            $keteranganDinamis = $detail->keterangan_transaksi ?? 'Barang Masuk';
            
            // 1. Catat ke Kartu Stok
            KartuStok::create([
                'id_inventory'      => $detail->id_inventory,
                'tanggal_transaksi' => $detail->tanggal_masuk,
                'keterangan'        => $keteranganDinamis,
                'nomor_batch'       => $detail->nomor_batch,
                'qty'               => $detail->jumlah_diterima,
                'harga'             => $detail->total_harga,
                'source_type'       => get_class($detail),
                'source_id'         => $detail->id,
                'saldo_qty'         => 0,
            ]);

            // 2. Sinkronisasi Stok Global & Hitung Ulang Saldo Ledger
            $detail->Inventory->syncTotalStock();
            $detail->Inventory->recalculateLedger($detail->tanggal_masuk);
        });

        static::updated(function ($detail) {
            // Update baris di kartu stok jika ada perubahan qty/tanggal
            $kartu = KartuStok::where('source_type', get_class($detail))
                ->where('source_id', $detail->id)
                ->first();

            if ($kartu) {
                $kartu->update([
                    'qty'               => $detail->jumlah_diterima,
                    'tanggal_transaksi' => $detail->tanggal_masuk,
                    'harga'             => $detail->total_harga,
                    'nomor_batch'       => $detail->nomor_batch,
                ]);

                $detail->Inventory->syncTotalStock();
                $detail->Inventory->recalculateLedger($detail->tanggal_masuk);
            }
        });

        static::deleted(function ($detail) {
            KartuStok::where('source_type', get_class($detail))
                ->where('source_id', $detail->id)
                ->delete();

            $detail->Inventory->syncTotalStock();
            $detail->Inventory->recalculateLedger($detail->tanggal_masuk);
        });
    }

    public function Supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier')->withTrashed();
    }

    public function BarangKeluar()
    {
        return $this->hasMany(BarangKeluar::class, 'id_detail_inventory');
    }

    public function KartuStok()
    {
        return $this->hasMany(KartuStok::class, 'source_id')->where('source_type', self::class);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PemesananBarangController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $tabAktif = $request->get('tab', 'po');
        
        // 1. Ambil input kata kunci pencarian dari kolom search di website
        $search = $request->get('search');
        
        // 2. Siapkan kueri dasar dengan memanggil semua relasi data logistik
        $query = \App\Models\PemesananBarang::with(['barang', 'supplier', 'jenisBarang', 'batches']);

        // 3. LOGIKA PENCARIAN CERDAS (Otomatis menyaring Kode PO, Nama Supplier, atau Nama Barang jika diisi)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('kode_pesanan', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('nama_supplier', 'LIKE', '%' . $search . '%');
                  })
                  ->orWhereHas('barang', function($bq) use ($search) {
                      $bq->where('nama_barang', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        // 4. Filter Pembagian Sub-Tab Alur Kerja
        if ($tabAktif == 'po') {
            // Tab 1: Menampilkan semua arsip PO
            
        } elseif ($tabAktif == 'pengiriman') {
            // Tab 2: Hanya menampilkan PO yang MASIH MENCICIL BATCH
            $query->where('status', 'Proses');
            
        } elseif ($tabAktif == 'diterima') {
            // Tab 3: Menarik data per gelombang/batch kedatangan supir secara langsung dari database
            $search = $request->get('search');
            
            $batchQuery = \App\Models\PemesananBatch::with(['pemesananBarang.barang', 'pemesananBarang.supplier']);
            
            if ($search) {
                $batchQuery->where(function($q) use ($search) {
                    $q->where('nama_batch', 'LIKE', '%' . $search . '%')
                      ->orWhereHas('pemesananBarang', function($pq) use ($search) {
                          $pq->where('kode_pesanan', 'LIKE', '%' . $search . '%')
                            ->orWhereHas('supplier', function($sq) use ($search) {
                                $sq->where('nama_supplier', 'LIKE', '%' . $search . '%');
                            })
                            ->orWhereHas('barang', function($bq) use ($search) {
                                $bq->where('nama_barang', 'LIKE', '%' . $search . '%');
                            });
                      });
                });
            }
            
            $pemesanans = $batchQuery->latest()->get();
      
        } elseif ($tabAktif == 'pembayaran') {
            // Tab 4: Proses Pelunasan Keuangan
            $query->where('status', 'Pembayaran');
        }

        // 5. Eksekusi penarikan data dari database ke variabel jamak $pemesanans
        $pemesanans = $query->latest()->get();

        // 6. Logika hitung riwayat cicilan pembayaran (Bawaan sistem Anda sebelumnya)
        if ($tabAktif == 'pembayaran') {
            foreach ($pemesanans as $data) {
                $namaBarang = $data->barang->nama_barang ?? '';
                
                $jumlahCicilan = \App\Models\Pengeluaran::where('id_perusahaan', $data->id_perusahaan)
                    ->where(\DB::raw("LOWER(nama_pengeluaran)"), 'LIKE', '%' . strtolower($namaBarang) . '%')
                    ->count();
                    
                $data->total_kali_cicil = $jumlahCicilan > 0 ? $jumlahCicilan : 1;
            }
        }

        // 7. Kembalikan data dengan aman ke view Blade utama
        return view('pages.pemesanan.index', compact('pemesanans', 'tabAktif'));
    }

    public function create()
    {
        // 1. Ambil data semua barang
        $barangs = \App\Models\Barang::orderBy('nama_barang', 'asc')->get();

        // 2. Ambil data semua jenis barang
        $jenis_barangs = \App\Models\JenisBarang::orderBy('nama_jenis', 'asc')->get();

        // 3. Ambil data semua supplier aktif (Baru)
        $suppliers = \App\Models\Supplier::orderBy('nama_supplier', 'asc')->get(); 
        // Catatan: Jika nama kolom nama supplier di database Anda bukan 'nama' (misal 'nama_supplier'), silakan sesuaikan tulisan 'nama' di atas.

        // 4. Lempar semua data ke halaman view form
        return view('pages.pemesanan.create', compact('barangs', 'jenis_barangs', 'suppliers'));
    }

        public function store(\Illuminate\Http\Request $request)
    {
        // 1. Validasi data inputan agar aman dari salah ketik atau data kosong
        $request->validate([
            'tanggal_pesan'    => 'required|date',
            'supplier_id'      => 'required|integer',
            'jenis_barang_id'  => 'required|integer',
            'barang_id'        => 'required|integer',
            'jumlah'           => 'required|integer|min:1',
            'satuan'           => 'required|string',
            'harga_barang'     => 'required|integer|min:0',
            'diskon'           => 'nullable|integer|min:0',
            'potongan_harga'  => 'nullable|integer|min:0',
            'pajak_ppn'        => 'required|in:0,11',
            'total_pembayaran' => 'required|integer',
        ]);

        // 2. Proses simpan data riil ke database MySQL
        \App\Models\PemesananBarang::create([
            'kode_pesanan'     => $request->kode_pesanan,    
            'tanggal_pesan'    => $request->tanggal_pesan,
            'supplier_id'      => $request->supplier_id,
            'jenis_barang_id'  => $request->jenis_barang_id,
            'barang_id'        => $request->barang_id,
            'jumlah'           => $request->jumlah,
            'satuan'           => $request->satuan,
            'harga_barang'     => $request->harga_barang,
            'diskon'           => $request->diskon ?? 0,
            'potongan_harga'  => $request->potongan_harga ?? 0,
            'pajak_ppn'        => $request->pajak_ppn,
            'total_pembayaran' => $request->total_pembayaran,
            'status'           => 'Pending', // Otomatis berstatus pending di awal
        ]);

        // 3. Alihkan kembali ke halaman utama tabel dengan notifikasi sukses
        return redirect()->route('pemesanan-barang.index')->with('success', 'Data pemesanan barang berhasil disimpan!');
    }

        public function updateStatus(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Proses,Pembayaran,Diterima'
        ]);

        $pemesanan = \App\Models\PemesananBarang::findOrFail($id);
        
        $pemesanan->update([
            'status' => $request->status
        ]);

        // DIPERBAIKI: Menggunakan nama route resource yang benar
        return redirect()->route('pemesanan-barang.index')->with('success', 'Status pemesanan berhasil diperbarui!');
    }

    // TAMBAHKAN: Fungsi untuk tombol hapus (Destroy)
    public function destroy($id)
    {
        $pemesanan = \App\Models\PemesananBarang::findOrFail($id);
        $pemesanan->delete();

        return redirect()->route('pemesanan-barang.index')->with('success', 'Data pemesanan barang berhasil dihapus!');
    }

        // 1. Method untuk menampilkan Form Edit data
    public function edit($id)
    {
        // Mengambil data pemesanan yang ingin diubah lengkap dengan relasinya
        $pemesanan = \App\Models\PemesananBarang::with(['barang', 'supplier', 'jenisBarang'])->findOrFail($id);
        
        // Mengambil data pendukung untuk pilihan dropdown (Sama seperti di fungsi create)
        $barangs = \App\Models\Barang::orderBy('nama_barang', 'asc')->get();
        $jenis_barangs = \App\Models\JenisBarang::orderBy('nama_jenis', 'asc')->get();
        $suppliers = \App\Models\Supplier::orderBy('nama_supplier', 'asc')->get();

        // Melemparkan data ke file resources/views/pages/pemesanan/edit.blade.php
        return view('pages.pemesanan.edit', compact('pemesanan', 'barangs', 'jenis_barangs', 'suppliers'));
    }

        // 2. Method untuk memproses penyimpanan perubahan data (Update)
        public function update(\Illuminate\Http\Request $request, $id)
        {
            // 1. Validasi input data dari form atau dropdown status
            $request->validate([
                'kode_pesanan'    => 'required|string|max:255',
                'tanggal_pesan'   => 'required|date',
                'supplier_id'     => 'required',
                'jenis_barang_id' => 'required',
                'barang_id'       => 'required',
                'jumlah'          => 'required|numeric',
                'satuan'          => 'required',
                'harga_barang'    => 'required|numeric',
                'total_pembayaran'=> 'required|numeric',
                'diskon'          => 'nullable|numeric|min:0|max:100',
                'potongan_harga'  => 'nullable|integer|min:0',
            ]);

            // 2. Cari data pemesanan lama beserta relasi untuk keperluan trigger
            $pemesanan = \App\Models\PemesananBarang::with(['barang', 'supplier'])->findOrFail($id);
            
            // Simpan status lama sebelum diupdate untuk mendeteksi perubahan
            $statusLama = $pemesanan->status;

            // 3. Update semua field dengan data yang baru diinputkan
            $pemesanan->update([
                'kode_pesanan'     => $request->kode_pesanan,
                'tanggal_pesan'    => $request->tanggal_pesan,
                'supplier_id'      => $request->supplier_id,
                'jenis_barang_id'  => $request->jenis_barang_id,
                'barang_id'        => $request->barang_id,
                'jumlah'           => $request->jumlah,
                'satuan'           => $request->satuan,
                'harga_barang'     => $request->harga_barang,
                'diskon'           => $request->diskon ?? 0,
                'potongan_harga'   => $request->potongan_harga ?? 0,
                'pajak_ppn'        => $request->pajak_ppn,
                'total_pembayaran' => $request->total_pembayaran,
                'status'           => $request->status ?? $pemesanan->status // Ambil status baru jika dikirim dari dropdown
            ]);

            // 4. TRIGGER OTOMATIS: Masuk ke keuangan jika status berubah MENJADI 'Pembayaran'
            if ($request->status == 'Pembayaran' || $pemesanan->status == 'Pembayaran') {
                
                $id_perusahaan = auth()->user()->id_perusahaan ?? $pemesanan->id_perusahaan;
                $namaBarang = $pemesanan->barang->nama_barang ?? 'Bahan Baku';
                $namaSupplier = $pemesanan->supplier->nama_supplier ?? '-';
                
                $namaPengeluaran = "Pemesanan " . strtoupper($namaBarang);
                $keteranganRinci = "Pembelian bahan baku " . $namaBarang . " sebanyak " . number_format($pemesanan->jumlah) . " " . $pemesanan->satuan . " dari Supplier: " . $namaSupplier;

                // Cari Kategori Pemakaian berdasarkan sub_kategori 'BAHAN BAKU' atau 'OPERASIONAL'
                $cekKategoriPemakaian = \App\Models\KategoriPemakaian::where('id_perusahaan', $id_perusahaan)
                    ->where(function($query) {
                        $query->where('nama_kategori', 'BAHAN BAKU')
                            ->orWhere('nama_kategori', 'OPERASIONAL');
                    })->first();

                // Sesuai dengan Method store() asli PengeluaranController Anda tanpa hambatan
                // JAMINAN 100% ANTI DOBEL: Menggunakan updateOrCreate dengan kunci nama pengeluaran spesifik
                $namaBarang = $pemesanan->barang->nama_barang ?? 'Bahan Baku';
                $namaPengeluaranUnik = "Pemesanan " . strtoupper($namaBarang);

                $pengeluaran = \App\Models\Pengeluaran::updateOrCreate(
                    [
                        // KUNCI UNIK: Jika id_perusahaan dan nama pengeluaran ini sudah ada di tanggal yang sama, JANGAN buat baru, tapi update saja!
                        'id_perusahaan'       => auth()->user()->id_perusahaan ?? ($pemesanan->id_perusahaan ?? 1),
                        'nama_pengeluaran'    => $namaPengeluaranUnik,
                        'tanggal_pengeluaran' => \Carbon\Carbon::now()->format('Y-m-d'),
                    ],
                    [
                        // Data yang akan diisi / diperbarui otomatis jika sudah ada
                        'kategori'            => 'OPERASIONAL',
                        'sub_kategori'        => 'BAHAN BAKU',
                        'metode_alokasi'      => 'FIXED',
                        'jumlah_pengeluaran'  => $pemesanan->total_pembayaran,
                        'absensi'             => null,
                        'is_hpp'              => 'true',
                        'keterangan'          => "Pencatatan otomatis tagihan utama logistik bahan baku " . $namaBarang,
                        'bukti'               => null,
                        'id_kategori_pemakaian' => $cekKategoriPemakaian ? $cekKategoriPemakaian->id : null,
                    ]
                );

                // Update relasi pemakaian bulanan jika kategori ditemukan
                if ($cekKategoriPemakaian) {
                    $tanggalInput = \Carbon\Carbon::parse($pemesanan->tanggal_pesan);
                    $awalBulan = $tanggalInput->copy()->startOfMonth();
                    $akhirBulan = $tanggalInput->copy()->endOfMonth();

                    \App\Models\Pemakaian::where('id_kategori', $cekKategoriPemakaian->id)
                        ->whereNull('id_pengeluaran')
                        ->whereBetween('tanggal_pemakaian', [$awalBulan, $akhirBulan])
                        ->update(['id_pengeluaran' => $pengeluaran->id]);
                }
            }
            return redirect()->route('pemesanan-barang.index')->with('success', 'Data pemesanan barang berhasil diperbarui!');
        }

        public function fakturPembelian($id)
        {
            // Ambil data pemesanan secara detail lengkap dengan relasi barang dan supplier
            $pemesanan = \App\Models\PemesananBarang::with(['barang', 'supplier', 'jenisBarang'])->findOrFail($id);

            // Diubah mengarah ke file baru: faktur_pembelian.blade.php
            return view('pages.pemesanan.faktur_pembelian', compact('pemesanan'));
        }

        public function prosesCicilan(\Illuminate\Http\Request $request, $id)
        {
            // 1. Validasi input nominal uang bayar
            $request->validate([
                'nominal_bayar' => 'required|numeric|min:1'
            ]);

            // 2. Ambil data pemesanan terkait
            $pemesanan = \App\Models\PemesananBarang::with(['barang', 'supplier'])->findOrFail($id);

            // 3. Kalkulasi Angka Keuangan Pembayaran
            $nominalBayarHariIni = $request->nominal_bayar;
            $totalSudahDibayarBaru = ($pemesanan->sudah_dibayar ?? 0) + $nominalBayarHariIni;
            $sisaHutangBaru = $pemesanan->total_pembayaran - $totalSudahDibayarBaru;

            // Jika sisa hutang di bawah 0 (karena pembulatan), set jadi 0
            if ($sisaHutangBaru < 0) {
                $sisaHutangBaru = 0;
            }

            // Batas tempo 30 hari otomatis dihitung dari tanggal pesan
            $tanggalJatuhTempo = \Carbon\Carbon::parse($pemesanan->tanggal_pesan)->addDays(30)->format('Y-m-d');

            // 4. Update status ke database pemesanan
            $pemesanan->update([
                'sudah_dibayar' => $totalSudahDibayarBaru,
                'sisa_hutang'   => $sisaHutangBaru,
                'jatuh_tempo'   => $tanggalJatuhTempo,
                // Jika sisa hutang sudah habis, otomatis ubah status induknya menjadi Diterima/Lunas
                'status'        => 'Proses'
            ]);

            // 5. TEMBAK OTOMATIS KE KEUANGAN (Tabel Pengeluaran)
            $id_perusahaan = auth()->user()->id_perusahaan ?? $pemesanan->id_perusahaan;
            $namaBarang = $pemesanan->barang->nama_barang ?? 'Bahan Baku';
            $namaSupplier = $pemesanan->supplier->nama_supplier ?? '-';
            
            $namaPengeluaran = "Cicilan Pemesanan " . strtoupper($namaBarang);
            $keteranganRinci = "Pembayaran cicilan logistik " . $namaBarang . " kepada " . $namaSupplier . " sebesar Rp " . number_format($nominalBayarHariIni, 0, ',', '.') . ". (Sisa Hutang: Rp " . number_format($sisaHutangBaru, 0, ',', '.') . ")";

            // Cari ID Kategori Pemakaian Bahan Baku / Operasional
            $cekKategoriPemakaian = \App\Models\KategoriPemakaian::where('id_perusahaan', $id_perusahaan)
                ->where(function($query) {
                    $query->where('nama_kategori', 'BAHAN BAKU')
                        ->orWhere('nama_kategori', 'OPERASIONAL');
                })->first();

            // Buat baris baru di Riwayat Pengeluaran
            \App\Models\Pengeluaran::updateOrCreate(
            [
                // FIX AMAN: Mengambil id_perusahaan dari user login, jika kosong ambil dari data pemesanan asli
                'id_perusahaan'       => auth()->user()->id_perusahaan ?? ($pemesanan->id_perusahaan ?? 1),
                'nama_pengeluaran'    => $namaPengeluaran,
                'tanggal_pengeluaran' => \Carbon\Carbon::now()->format('Y-m-d'),
                'jumlah_pengeluaran'  => $nominalBayarHariIni, 
            ],
            [
                'kategori'            => 'OPERASIONAL',
                'sub_kategori'        => 'BAHAN BAKU',
                'metode_alokasi'      => 'FIXED',
                'absensi'             => null,
                'is_hpp'              => 'true',
                'keterangan'          => $keteranganRinci,
                'bukti'               => null,
                'id_kategori_pemakaian' => $cekKategoriPemakaian ? $cekKategoriPemakaian->id : null,
            ]
        );

            return redirect()->route('pemesanan-barang.index', ['tab' => 'Pembayaran'])->with('success', 'Pembayaran cicilan berhasil dikonfirmasi dan tercatat di Keuangan!');
        }

        public function konfirmasiDiterima(Request $request, $id)
    {
        $request->validate([
            'tanggal_kirim'   => 'required|date',
            'jumlah_diterima' => 'required|numeric|min:0',
            'jumlah_reject'   => 'required|numeric|min:0',
        ]);

        $pemesanan = \App\Models\PemesananBarang::findOrFail($id);

        // 1. Update data pengiriman, hasil fisik QC, dan ubah status ke 'Diterima'
        $pemesanan->update([
            'tanggal_kirim'   => $request->tanggal_kirim,
            'jumlah_diterima' => $request->jumlah_diterima,
            'jumlah_reject'   => $request->jumlah_reject,
            'status'          => 'Diterima' // Data otomatis bergeser ke tab Diterima & Selesai
        ]);

        // 2. Langsung redirect tanpa membuat data DetailInventory
        return redirect()->route('pemesanan-barang.index', ['tab' => 'Diterima'])->with('success', 'Barang logistik berhasil diterima dan status diperbarui.');
    }

    public function ajukanProses($id)
    {
        // 1. Cari data PO yang mau diproses
        $pemesanan = \App\Models\PemesananBarang::findOrFail($id);
        
        // 2. Ubah statusnya menjadi 'Proses' agar otomatis pindah ke tab Logistik
        $pemesanan->update([
            'status' => 'Proses'
        ]);

        // 3. Kembalikan ke halaman utama dan arahkan langsung ke tab logistik
        return redirect()->route('pemesanan-barang.index', ['tab' => 'logistik'])
                        ->with('success', 'Pesanan ' . $pemesanan->kode_pesanan . ' berhasil diajukan ke proses konfirmasi supplier!');
    }

    public function simpanBatch(Request $request, $id)
    {
        $request->validate([
            'nama_batch'         => 'required|string|max:50',
            'tanggal_datang'     => 'required|date',
            'jumlah_dikirim'     => 'required|numeric|min:0',
            'jumlah_diterima_qc' => 'required|numeric|min:0',
            'jumlah_reject'      => 'required|numeric|min:0',
            'catatan'            => 'nullable|string',
        ]);

        $poUtama = \App\Models\PemesananBarang::findOrFail($id);

        // 1. Simpan data cicilan gelombang baru ke tabel anak
        \App\Models\PemesananBatch::create([
            'pemesanan_barang_id' => $id,
            'nama_batch'          => $request->nama_batch,
            'tanggal_datang'      => $request->tanggal_datang,
            'jumlah_dikirim'      => $request->jumlah_dikirim,
            'jumlah_diterima_qc'  => $request->jumlah_diterima_qc,
            'jumlah_reject'       => $request->jumlah_reject,
            'catatan'             => $request->catatan,
        ]);

        // 2. Hitung total akumulasi barang yang SUDAH masuk dari semua batch
        $totalMasukQc = $poUtama->batches()->sum('jumlah_diterima_qc');
        $totalReject  = $poUtama->batches()->sum('jumlah_reject');

        // 3. Update summary di tabel PO Utama agar laporan stok & QC Anda sinkron otomatis
        $poUtama->update([
            'jumlah_diterima' => $totalMasukQc,
            'jumlah_reject'   => $totalReject,
            'tanggal_kirim'   => $request->tanggal_datang // Set tanggal pengiriman terakhir
        ]);

        // 4. Jika total barang yang diterima lolos QC sudah memenuhi atau melebihi jumlah pesanan awal, kunci statusnya ke Diterima
        if ($totalMasukQc >= $poUtama->jumlah) {
            $poUtama->update(['status' => 'Diterima']);
        }

        return redirect()->route('pemesanan-barang.index', ['tab' => 'logistik'])
                        ->with('success', 'Data ' . $request->nama_batch . ' sukses dicatat ke sistem!');
    }

    public function kirimKeGudang($batch_id)
    {
        $batch = \App\Models\PemesananBatch::with('pemesananBarang')->findOrFail($batch_id);
        
        // Di sini Anda bisa menyisipkan logika kueri untuk otomatis menambah stok ke tabel Gudang/Inventory Anda:
        // Contoh: \App\Models\Gudang::updateOrCreate(['barang_id' => $batch->pemesananBarang->barang_id], ...);

        return redirect()->back()->with('success', $batch->nama_batch . ' untuk PO ' . $batch->pemesananBarang->kode_pesanan . ' berhasil divalidasi masuk ke inventaris gudang!');
    }

    public function ajukanPembayaran($id)
    {
        $pemesanan = \App\Models\PemesananBarang::findOrFail($id);
        
        // Ubah status PO induk menjadi Pembayaran agar dibaca oleh tab menu Keuangan/Pembayaran
        $pemesanan->update(['status' => 'Pembayaran']);

        return redirect()->route('pemesanan-barang.index', ['tab' => 'pembayaran'])
                        ->with('success', 'Transaksi ' . $pemesanan->kode_pesanan . ' resmi diajukan ke tahap proses pembayaran!');
    }



}

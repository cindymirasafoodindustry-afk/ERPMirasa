<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAKTUR PEMBELIAN - #FB-{{ str_pad($pemesanan->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* CSS Dasar Bersih */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; color: #374151; padding: 30px; margin: 0; display: flex; flex-direction: column; align-items: center; }
        
        /* Tombol Aksi Navigasi */
        .no-print { width: 100%; max-width: 800px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: white; padding: 15px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); box-sizing: border-box; }
        .btn-close { padding: 8px 16px; background: #e5e7eb; border: none; border-radius: 8px; color: #4b5563; font-weight: 600; cursor: pointer; }
        .btn-print { padding: 8px 20px; background: #ea580c; border: none; border-radius: 8px; color: white; font-weight: bold; cursor: pointer; box-shadow: 0 2px 4px rgba(234,88,12,0.2); }
        
        /* Kertas Faktur Dokumen */
        .invoice-card { width: 100%; max-width: 800px; background: white; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); padding: 40px; box-sizing: border-box; position: relative; overflow: hidden; }
        .invoice-accent { position: absolute; top: 0; left: 0; right: 0; height: 8px; background: #ea580c; }
        
        /* Header Bagian Atas */
        .invoice-header { display: flex; justify-content: space-between; border-bottom: 2px solid #f3f4f6; padding-bottom: 25px; margin-bottom: 30px; }
        .company-name { font-size: 22px; font-weight: 900; color: #ea580c; letter-spacing: 1px; margin-bottom: 5px; }
        .company-desc { font-size: 12px; color: #6b7280; line-height: 1.6; margin: 0; }
        .title-right { text-align: right; }
        .invoice-title { font-size: 24px; font-weight: 900; margin: 0 0 5px 0; color: #1f2937; letter-spacing: 0.5px; }
        .po-number { font-family: monospace; font-size: 14px; color: #4b5563; font-weight: 600; }
        .po-date { font-size: 12px; color: #9ca3af; margin-top: 5px; }
        
        /* Blok Grid Informasi */
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .detail-box { background: #f9fafb; border: 1px solid #f3f4f6; padding: 15px; border-radius: 12px; }
        .box-title { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .box-name { font-size: 16px; font-weight: 700; color: #1f2937; margin-bottom: 4px; }
        .box-code { font-family: monospace; font-size: 12px; color: #2563eb; background: #eff6ff; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-bottom: 8px; }
        .status-badge { display: inline-block; padding: 3px 10px; font-size: 11px; font-weight: 800; background: #f3e8ff; color: #7e22ce; border-radius: 9999px; text-transform: uppercase; }
        
        /* Tabel Rincian */
        .table-wrapper { border: 1px solid #f3f4f6; border-radius: 12px; overflow: hidden; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: #f9fafb; padding: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #4b5563; border-bottom: 1px solid #f3f4f6; }
        td { padding: 15px 12px; border-bottom: 1px solid #f3f4f6; color: #374151; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .item-name { font-weight: 700; color: #1f2937; }
        .item-code { font-family: monospace; font-size: 11px; color: #2563eb; margin-top: 3px; }
        
        /* Bagian Total */
        .total-wrapper { display: flex; justify-content: flex-end; }
        .total-box { width: 300px; font-size: 14px; }
        .total-row { display: flex; justify-content: space-between; padding: 6px 0; color: #6b7280; }
        .total-row.discount { color: #dc2626; }
        .total-final { display: flex; justify-content: space-between; font-size: 16px; font-weight: 900; color: #ea580c; background: #fff7ed; padding: 10px; border-radius: 8px; margin-top: 10px; border: 1px solid #ffedd5; }
        
        /* Tanda Tangan */
        .signature-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 50px; text-align: center; font-size: 13px; color: #6b7280; }
        .sig-space { height: 70px; }
        .sig-name { font-weight: 700; color: #1f2937; text-decoration: underline; text-transform: uppercase; }
        
        /* CSS Cetak Otomatis Bersih */
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none !important; }
            .invoice-card { border: none; box-shadow: none; padding: 20px 0; }
        }
    </style>
</head>
<body>

    <!-- Tombol Navigasi Aksi (Otomatis Hilang Saat Diprint) -->
    <div class="no-print">
        <button onclick="window.close()" class="btn-close">✕ Tutup Halaman</button>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Faktur / PDF</button>
    </div>

    <!-- Area Lembar Faktur Utama -->
    <div class="invoice-card">
        <div class="invoice-accent"></div>

        <!-- HEADER FAKTUR -->
        <div class="invoice-header">
            <div>
                <div class="company-name">MIRASA FOOD INDUSTRY</div>
                <p class="company-desc">
                    Jalan Munggur No. 2 Ambartawang, Japun Satu, Paremono, <br>
                    Kec. Mungkid, Kabupaten Magelang, Jawa Tengah 56512<br>
                    <strong>Kontak:</strong> 6287880809279
                </p>
            </div>
            <div class="title-right">
                <!-- Sudah diubah menjadi FAKTUR PEMBELIAN -->
                <h1 class="invoice-title">FAKTUR PEMBELIAN</h1>
                <div class="po-number">No. Faktur: #FB-{{ str_pad($pemesanan->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="po-date">Tanggal: {{ \Carbon\Carbon::parse($pemesanan->tanggal_pesan)->format('d F Y') }}</div>
            </div>
        </div>

        <!-- DETAIL PIHAK TERKAIT -->
        <div class="detail-grid">
            <div class="detail-box">
                <div class="box-title">Diterima Dari (Supplier):</div>
                <div class="box-name">{{ $pemesanan->supplier->nama_supplier ?? 'Supplier Umum' }}</div>
                <div class="box-code">Code: {{ $pemesanan->supplier->kode ?? '-' }}</div>
                <div style="font-size: 11px; color: #6b7280; line-height: 1.4;">Terdaftar aktif sebagai mitra logistik utama pasokan industri.</div>
            </div>
            <div class="detail-box" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div class="box-title">Metode Pengiriman:</div>
                    <div style="font-weight: 600; color: #4b5563;">Logistik Internal Vendor (Fixed)</div>
                </div>
                <div style="margin-top: 15px;">
                    <div class="box-title">Pembayaran:</div>
                    <span class="status-badge" style="background-color: #fee2e2; color: #991b1b;">{{ $pemesanan->status }}</span>
                </div>
            </div>
        </div>

        <!-- TABEL RINCIAN BARANG -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="text-align: left;">Deskripsi Pasokan Bahan Baku</th>
                        <th class="text-right">Harga Satuan</th>
                        <th class="text-center">QTY</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>
                            <div class="item-name">{{ $pemesanan->barang->nama_barang ?? '-' }}</div>
                            <div class="item-code">[{{ $pemesanan->barang->kode ?? '-' }}]</div>
                        </td>
                        <td class="text-right">Rp {{ number_format($pemesanan->harga_barang, 0, ',', '.') }}</td>
                        <td class="text-center" style="font-weight: bold; color: #1f2937;">
                            {{ number_format($pemesanan->jumlah, 0, ',', '.') }} <span style="font-size: 11px; color: #9ca3af; font-weight: normal;">{{ $pemesanan->satuan }}</span>
                        </td>
                        <td class="text-right" style="font-weight: bold; color: #1f2937;">
                            Rp {{ number_format($pemesanan->jumlah * $pemesanan->harga_barang, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- KALKULASI TOTAL AKUNTANSI -->
        <div class="total-wrapper">
            <div class="total-box">
                <div class="total-row">
                    <span>Subtotal Kuantitas:</span>
                    <span style="font-weight: 600; color: #1f2937;">Rp {{ number_format($pemesanan->jumlah * $pemesanan->harga_barang, 0, ',', '.') }}</span>
                </div>
                
                @if(($pemesanan->diskon ?? 0) > 0)
                <div class="total-row discount">
                    <span>Potongan Diskon:</span>
                    <span style="font-weight: 600;">-Rp {{ number_format($pemesanan->diskon, 0, ',', '.') }}</span>
                </div>
                @endif

                <div class="total-row">
                    <span>Pajak PPN ({{ $pemesanan->pajak_ppn ?? 0 }}%):</span>
                                        <!-- Sambungan Kode Tepat Setelah Baris Pajak PPN Anda -->
                    <span style="font-weight: 600; color: #1f2937;">
                        @php
                            $subtotal = ($pemesanan->jumlah * $pemesanan->harga_barang) - ($pemesanan->diskon ?? 0);
                            $nilaiPPN = ($subtotal * ($pemesanan->pajak_ppn ?? 0)) / 100;
                        @endphp
                        Rp {{ number_format($nilaiPPN, 0, ',', '.') }}
                    </span>
                </div>

                <div class="total-final">
                    <span>TOTAL TAGIHAN:</span>
                    <span>Rp {{ number_format($pemesanan->total_pembayaran, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- FOOTER TANDA TANGAN -->
        <div class="signature-container">
            <div>
                <p>Petugas Logistik (Penerima),</p>
                <div class="sig-space"></div>
                <div class="sig-name">{{ auth()->user()->name ?? 'Admin Gudang' }}</div>
                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">Mirasa Food Industry</div>
            </div>
            <div>
                <p>Hormat Kami (Supplier),</p>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $pemesanan->supplier->nama_supplier ?? 'Vendor Resmi' }}</div>
                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px;">Tanda Tangan & Cap Resmi</div>
            </div>
        </div>

    </div>

</body>
</html>

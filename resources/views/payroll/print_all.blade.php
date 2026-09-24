<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Massal Slip Gaji - Periode {{ $bulanTahun }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; color: #1e293b; background-color: #f8fafc; padding: 40px 20px; margin: 0; font-size: 12px; line-height: 1.5; }
        .no-print-box { max-w: 650px; margin: 0 auto 20px auto; background: #fff; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .btn { padding: 8px 16px; font-weight: bold; border-radius: 8px; font-size: 11px; text-transform: uppercase; cursor: pointer; border: none; }
        .btn-close { background: #f1f5f9; color: #64748b; }
        .btn-print { background: #2563eb; color: #fff; }
        
        /* KUNCI SAKTI POTONG KERTAS PRINTER PER KARYAWAN */
        .slip-page { max-w: 650px; margin: 0 auto 40px auto; background: #ffffff; padding: 40px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); box-sizing: border-box; page-break-after: always; }
        
        .header-table { width: 100%; border-collapse: collapse; border-b: 2px solid #0f172a; margin-bottom: 20px; }
        .meta-table { width: 100%; border-collapse: collapse; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9; margin-bottom: 25px; }
        .meta-table td { padding: 8px 12px; vertical-align: top; font-size: 11px; }
        .financial-title { background: #0f172a; color: #ffffff; padding: 6px 12px; font-weight: 900; font-size: 10px; border-radius: 6px 6px 0 0; }
        .financial-box { border: 1px solid #e2e8f0; border-radius: 8px; width: 48%; float: left; box-sizing: border-box; background: #fff; }
        .financial-container { width: 100%; margin-bottom: 25px; }
        .financial-container::after { content: ""; clear: both; display: table; }
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table td { padding: 7px 12px; font-size: 11px; color: #475569; }
        .item-table tr:not(:last-child) td { border-bottom: 1px solid #f1f5f9; }
        .total-row td { font-weight: bold; border-top: 1px dashed #cbd5e1 !important; padding-top: 10px !important; }
        .thp-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 16px; margin-top: 20px; display: table; width: 100%; box-sizing: border-box; }
        .thp-left { display: table-cell; width: 50%; vertical-align: middle; }
        .thp-right { display: table-cell; width: 50%; text-align: right; vertical-align: middle; font-style: italic; color: #64748b; font-size: 10px; font-weight: 600; padding-left: 15px; }
        .signature-container { width: 100%; margin-top: 40px; text-align: center; }
        .signature-box { width: 50%; float: left; font-weight: bold; color: #334155; }
        .signature-container::after { content: ""; clear: both; display: table; }

        @media print {
            body { background: #fff; padding: 0; margin: 0; color: #000; }
            .no-print-box { display: none !important; }
            .slip-page { border: 1px solid #cbd5e1; box-shadow: none; padding: 30px; border-radius: 0; margin: 0 auto; page-break-after: always; }
            .financial-title { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .thp-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; background: #eff6ff !important; }
        }
    </style>
</head>
<body>

    <div class="no-print-box">
        <span style="font-weight: bold; color: #64748b;">Pratinjau Cetak Massal Seluruh Karyawan ({{ count($allSlipsData) }} Slip)</span>
        <div>
            <button onclick="window.close()" class="btn btn-close">Tutup</button>
            <button onclick="window.print()" class="btn btn-print">Cetak Massal / Simpan 1 PDF</button>
        </div>
    </div>

    <!-- LOOPING PERULANGAN SLIP GAJI UNTUK TIAP KARYAWAN -->
    @foreach($allSlipsData as $data)
    <div class="slip-page">
        <table class="header-table">
            <tr>
                <td style="padding-bottom: 15px;">
                    <h1 style="margin: 0; font-size: 18px; font-weight: 900; color: #0f172a;">PT MIRASA FOOD INDUSTRY</h1>
                    <p style="margin: 3px 0 0 0; font-size: 10px; color: #94a3b8; font-weight: 600;">Kawasan Industri & Produksi Pangan Utama</p>
                </td>
                <td style="text-align: right; padding-bottom: 15px; vertical-align: top;">
                    <h2 style="margin: 0; font-size: 12px; font-weight: 900; color: #475569;">SLIP GAJI KARYAWAN</h2>
                    <p style="margin: 4px 0 0 0; font-size: 11px; font-weight: bold; color: #2563eb;">Periode: {{ \Carbon\Carbon::parse($bulanTahun . '-01')->translatedFormat('F Y') }}</p>
                </td>
            </tr>
        </table>

        <table class="meta-table" style="width: 100%;">
            <tr>
                <!-- SISI KIRI: PROFIL & INFORMASI STATUS ADMINISTRASI (CETAK MASSAL) -->
                <td style="width: 50%; vertical-align: top;">
                    <div style="margin-bottom: 4px; color: #64748b; font-weight: 600;">ID Karyawan: <span style="color: #0f172a; font-weight: bold;">{{ $data['employee']->id_karyawan }}</span></div>
                    <div style="margin-bottom: 4px; color: #64748b; font-weight: 600;">Nama Karyawan: <span style="color: #0f172a; font-weight: bold;">{{ $data['employee']->nama_karyawan }}</span></div>
                    <div style="margin-bottom: 8px; color: #64748b; font-weight: 600;">Kelompok Kerja: <span style="color: #0f172a; text-transform: uppercase;">{{ $data['kelompokKerja'] ?? '-' }}</span></div>
                    <div>Status Pembayaran: <span style="background: #dcfce7; color: #166534; font-size: 9px; font-weight: 900; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">SUCCESS PAID</span></div>
                </td>

                <!-- SISI KANAN: KEHADIRAN & RINCIAN AKUMULASI JAM KERJA (CETAK MASSAL) -->
                <td style="width: 50%; text-align: right; vertical-align: top;">
                    <div style="margin-bottom: 4px; color: #64748b; font-weight: 600;">Tanggal Cetak: <span style="color: #334155; font-weight: bold;">{{ date('d F Y') }}</span></div>
                    <div style="margin-bottom: 8px; color: #64748b; font-weight: 600;">Hari Kerja Hadir: <span style="color: #0f172a; font-weight: bold;">{{ $data['jumlahHariKerjaMurni'] }} Hari</span></div>
                    
                    <div style="margin-bottom: 4px; font-size: 11px; font-weight: bold; color: #047857;">Jam Lembur I (8 Jam): <span>{{ $data['totalJamLembur1'] ?? 0 }} Jam</span></div>
                    <div style="margin-bottom: 4px; font-size: 11px; font-weight: bold; color: #047857;">Jam Lembur II (>8 Jam): <span>{{ $data['totalJamLembur2'] ?? 0 }} Jam</span></div>
                </td>
            </tr>
        </table>

        <div class="financial-container">
            <div class="financial-box">
                <div class="financial-title">A. KOMPONEN PENDAPATAN (+)</div>
                <table class="item-table">
                    <tr><td>Gaji Pokok Perhari:</td><td style="text-align: right; font-weight: bold;">Rp {{ number_format($data['gajiPokokTotal'], 0, ',', '.') }}</td></tr>
                    <tr><td>Honor Lembur I:</td><td style="text-align: right; font-weight: bold;">Rp {{ number_format($data['honorLembur1Total'], 0, ',', '.') }}</td></tr>
                    <tr><td>Honor Lembur II:</td><td style="text-align: right; font-weight: bold;">Rp {{ number_format($data['honorLembur2Total'], 0, ',', '.') }}</td></tr>
                    <tr><td>Tunjangan Masa Kerja:</td><td style="text-align: right; font-weight: bold;">Rp {{ number_format($data['tunjanganMasaKerja'], 0, ',', '.') }}</td></tr>
                    <tr><td>Tunjangan Jabatan:</td><td style="text-align: right; font-weight: bold;">Rp {{ number_format($data['tunjanganJabatan'], 0, ',', '.') }}</td></tr>
                    <tr><td>Insentif Kerajinan:</td><td style="text-align: right; font-weight: bold;">Rp {{ number_format($data['insentifKerajinan'], 0, ',', '.') }}</td></tr>
                    <tr class="total-row" style="color: #2563eb;"><td>TOTAL PENDAPATAN:</td><td style="text-align: right;">Rp {{ number_format($data['totalPendapatan'], 0, ',', '.') }}</td></tr>
                </table>
            </div>

            <div class="financial-box" style="float: right;">
                <div class="financial-title" style="background: #991b1b;">B. KOMPONEN POTONGAN DENDA (-)</div>
                    <table class="item-table">
                        <tr><td>Potongan BPJS Kes:</td><td style="text-align: right; font-weight: bold;">Rp {{ number_format($data['bpjsKes'], 0, ',', '.') }}</td></tr>
                        <tr><td>Potongan BPJS TK:</td><td style="text-align: right; font-weight: bold;">Rp {{ number_format($data['bpjsTk'], 0, ',', '.') }}</td></tr>
                        <tr><td>Denda Kekurangan Jam:</td><td style="text-align: right; font-weight: bold;">Rp {{ number_format($data['potonganJamKerja'], 0, ',', '.') }}</td></tr>
                        <tr><td>Potongan Lainnya Kasbon:</td><td style="text-align: right; font-weight: bold;">Rp {{ number_format($data['potonganLainnya'], 0, ',', '.') }}</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                        <tr class="total-row" style="color: #991b1b;"><td>TOTAL POTONGAN:</td><td style="text-align: right;">Rp {{ number_format($data['totalPotongan'], 0, ',', '.') }}</td></tr>
                    </table>
                </div>
            </div>

            <!-- Boks Kesimpulan Gaji Bersih Massal -->
            <div class="thp-box">
            <div class="thp-left">
                <span style="font-size: 10px; font-weight: 900; color: #2563eb; display: block;">TAKE HOME PAY (GAJI BERSIH AKHIR)</span>
                <span style="font-size: 20px; font-weight: 900; color: #1e3a8a; display: block; margin-top: 4px;">Rp {{ number_format($data['totalGajiBersih'], 0, ',', '.') }}</span>
            </div>
            <div class="thp-right">
                Terbilang: <span style="color: #0f172a; font-weight: bold;">"{{ ucwords(terbilangRupiahMassal($data['totalGajiBersih'])) }} Rupiah"</span>
            </div>
        </div>

        <div class="signature-container">
            <div class="signature-box">
                <p style="color: #94a3b8; margin: 0 0 50px 0;">Penerima Upah Gaji,</p>
                <!-- SISA KODINGAN SAMBUNGAN TEPAT DI BAWAH BLOK BIRU KAMU -->
                <p style="margin: 0; color: #0f172a; font-weight: bold; border-bottom: 1px solid #cbd5e1; width: 140px; margin: 0 auto; padding-bottom: 4px;">{{ $data['employee']->nama_karyawan }}</p>
            </div>
            <div class="signature-box" style="float: right;">
                <p style="color: #94a3b8; margin: 0 0 50px 0;">Super Admin Keuangan,</p>
                <p style="margin: 0; color: #0f172a; font-weight: bold; border-bottom: 1px solid #cbd5e1; width: 140px; margin: 0 auto; padding-bottom: 4px;">KEUANGAN</p>
            </div>
        </div>
    </div> <!-- Penutup .slip-page per karyawan -->
    @endforeach

    <!-- REKAYASA FUNGSI AUTO PRINT SAKRAL SAAT HALAMAN SELESAI DI-LOAD -->
    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 500);
        }
    </script>
</body>
</html>
<?php
// FUNGSI SAKTI PEMBANTU: TERBILANG MASSAL KHUSUS FILE PRINT ALL
function terbilangRupiahMassal($angka) {
    $angka = abs($angka);
    $baca = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    $terbilang = "";
    if ($angka < 12) { $terbilang = " " . $baca[$angka]; }
    elseif ($angka < 20) { $terbilang = terbilangRupiahMassal($angka - 10) . " belas"; }
    elseif ($angka < 100) { $terbilang = terbilangRupiahMassal($angka / 10) . " puluh" . terbilangRupiahMassal($angka % 10); }
    elseif ($angka < 200) { $terbilang = " seratus" . terbilangRupiahMassal($angka - 100); }
    elseif ($angka < 1000) { $terbilang = terbilangRupiahMassal($angka / 100) . " ratus" . terbilangRupiahMassal($angka % 100); }
    elseif ($angka < 2000) { $terbilang = " seribu" . terbilangRupiahMassal($angka - 1000); }
    elseif ($angka < 1000000) { $terbilang = terbilangRupiahMassal($angka / 1000) . " ribu" . terbilangRupiahMassal($angka % 1000); }
    elseif ($angka < 1000000000) { $terbilang = terbilangRupiahMassal($angka / 1000000) . " juta" . terbilangRupiahMassal($angka % 1000000); }
    return $terbilang;
}
?>
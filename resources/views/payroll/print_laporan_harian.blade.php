<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Analisis Buku Besar Payroll Harian — Per Periode</title>
    <style>
        /* ========================================================================= */
        /* KUNCI SAKTI: PENGATURAN KERTAS PORTRAIT A4 INDONESIA ANTI-PUDAR & ANTI-MELAR */
        /* ========================================================================= */
        @page { 
            size: A4 portrait; 
            margin: 15mm 10mm 15mm 10mm; 
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            font-size: 10px; 
            color: #1e293b; 
            background-color: #cbd5e1; /* Warna latar luar kertas abu-abu agar kontras Portrait menonjol di browser */
            line-height: 1.4; 
            margin: 0;
            padding: 40px 0;
        }

        /* Paksa Web View membentuk lembaran kertas Portrait A4 ramping tegak di tengah monitor */
        .page-container {
            background-color: #ffffff;
            width: 210mm; /* Ukuran lebar mutlak kertas Portrait A4 */
            min-height: 297mm; 
            margin: 0 auto; /* Memposisikan boks Portrait tepat di tengah layar browser */
            padding: 20mm 15mm;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }
        
        .no-print-box { text-align: center; margin-bottom: 25px; }
        .btn-print { background-color: #059669; color: #fff; font-weight: bold; font-size: 12px; padding: 10px 24px; border-radius: 6px; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn-print:hover { background-color: #047857; }
        
        /* 🖨️ ATURAN SAKRAL SAAT DIPRINT: Paksa warna latar belakang tetap keluar tebal */
        @media print { 
            .no-print-box { display: none; } 
            body { background-color: #fff; padding: 0; }
            .page-container { width: 100%; min-height: auto; margin: 0; padding: 0; box-shadow: none; border-radius: 0; }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
        
        /* Header Kamar Atas */
        .header-container { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
        .header-left h2 { margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; text-transform: uppercase; }
        .header-left p { margin: 4px 0 0 0; font-size: 9.5px; color: #4b5563; font-weight: 600; }
        .status-finalized { font-size: 9.5px; color: #0f172a; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; text-align: right; }
        
        /* Metadata Laporan Baris Atas */
        .meta-container { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 9px; color: #4b5563; font-weight: 700; }
        .meta-box { background-color: #f9fafb; padding: 8px 12px; border-radius: 6px; border: 1px solid #e5e7eb; width: 46%; }
        .meta-box span { display: block; color: #9ca3af; font-size: 8px; margin-bottom: 2px; font-weight: 800; text-transform: uppercase; }
        
        /* BOKS KARTU STATISTIK SEJAJAR 3 KOTAK KOMPAK UNTUK PORTRAIT A4 */
        .stats-grid { display: flex; justify-content: space-between; margin-bottom: 25px; gap: 10px; width: 100%; }
        .stat-card { flex: 1; padding: 10px 12px; border-radius: 6px; border-left: 4px solid #3b82f6; display: flex; flex-direction: column; align-items: flex-start; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .stat-card.blue { background-color: #eff6ff !important; border-left-color: #3b82f6 !important; }
        .stat-card.orange { background-color: #fff7ed !important; border-left-color: #f97316 !important; }
        .stat-card.gray { background-color: #f9fafb !important; border-left-color: #4b5563 !important; }
        .stat-label { font-size: 8px; font-weight: 800; text-transform: uppercase; }
        .stat-card.blue .stat-label { color: #1e40af; }
        .stat-card.orange .stat-label { color: #9a3412; }
        .stat-card.gray .stat-label { color: #1f2937; }
        .stat-value { font-size: 12px; font-weight: 800; color: #111827; }
        
        /* Sub-Judul Rincian */
        .section-title { font-size: 9.5px; font-weight: 800; color: #111827; margin-bottom: 12px; letter-spacing: 0.5px; text-transform: uppercase; border-left: 4px solid #0f172a; padding-left: 8px; }
        
        /* TABEL PORTRAIT HARIAN PABRIK SINKRONISASI TOTAL */
        table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 6px; font-size: 8.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        th { background-color: #0f172a !important; color: #ffffff !important; font-weight: 800; text-transform: uppercase; font-size: 8px; letter-spacing: 0.5px; text-align: center; }
        
        tr:nth-child(even) td { background-color: #f8fafc; }
        .text-right { text-align: right; font-variant-numeric: tabular-nums; font-weight: bold; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .text-green { color: #16a34a !important; }
        
        .total-row td { background-color: #f1f5f9 !important; font-weight: 800; color: #0f172a; border-top: 2px solid #0f172a; font-size: 9px; }
    </style>
</head>
<body>

    @php
        $grandTotalHadir = 0;
        $grandTotalLangsung = 0;
        $grandTotalTidakLangsung = 0;
        $grandTotalGaji = 0;
        
        foreach($dailyReports as $data) {
            $grandTotalHadir += $data['total_hadir'];
            $grandTotalLangsung += $data['gaji_langsung'];
            $grandTotalTidakLangsung += $data['gaji_tidak_langsung'];
            $grandTotalGaji += $data['total_gaji'];
        }
    @endphp

    <div class="no-print-box">
        <button onclick="window.print()" class="btn-print">Klik Cetak Dokumen / Simpan PDF</button>
    </div>

    <!-- BUNGKUS KERTAS PORTRAIT A4 MUTLAK (WEB VIEW & PRINT VIEW) -->
    <div class="page-container">

        <!-- Header Dokumen Mewah -->
        <div class="header-container">
            <div class="header-left">
                <h2>PT MIRASA FOOD INDUSTRY</h2>
                <p>Laporan Analisis Biaya Gaji Operasional Harian Karyawan</p>
            </div>
            <div class="header-right">
                <div class="status-finalized">STATUS DOKUMEN<br>FINALIZED</div>
            </div>
        </div>

        <!-- Informasi Periode Cetak -->
        <div class="meta-container">
            <div class="meta-box">
                <span>PERIODE LAPORAN HARIAN</span>
                <strong>{{ \Carbon\Carbon::parse($bulanPeriode)->translatedFormat('F Y') }}</strong>
            </div>
            <div class="meta-box" style="text-align: right;">
                <span>DIBUAT PADA</span>
                <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB</strong>
            </div>
        </div>

        <!-- BOKS KARTU REKAPITULASI INDIKATOR ATAS SEJAJAR RAMPING -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-label">Total Pengeluaran Keseluruhan</div>
                <div class="stat-value">Rp {{ number_format($grandTotalGaji, 0, ',', '.') }}</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">Total Kelompok Langsung (HPP)</div>
                <div class="stat-value">Rp {{ number_format($grandTotalLangsung, 0, ',', '.') }}</div>
            </div>
            <div class="stat-card gray">
                <div class="stat-label">Total Kelompok Tdk Langsung (Overhead)</div>
                <div class="stat-value">Rp {{ number_format($grandTotalTidakLangsung, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="section-title">I. Rincian Akumulasi Pengeluaran Biaya Gaji Harian Pabrik</div>

        <!-- DISTRIBUSI DISTRIBUSI KOLOM PORTRAIT SEMPURNA JAYA ANTI-PATAH -->
        <table>
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 70px;">Tanggal Laporan</th>
                    <th style="width: 90px;">Total Hadir Karyawan</th>
                    <th style="width: 125px;">Biaya Kelompok Langsung (HPP)</th>
                    <th style="width: 135px;">Biaya Kelompok Tidak Langsung</th>
                    <th style="width: 115px;">Total Gaji Pengeluaran</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @forelse($dailyReports as $data)
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td class="text-center font-bold">{{ \Carbon\Carbon::parse($data['tanggal'])->format('d-m-Y') }}</td>
                        <td class="text-center">{{ $data['total_hadir'] }} Karyawan/Hari</td>
                        <td class="text-right">Rp {{ number_format($data['gaji_langsung'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($data['gaji_tidak_langsung'], 0, ',', '.') }}</td>
                        <td class="text-right text-green">Rp {{ number_format($data['total_gaji'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="color: #6b7280; padding: 20px;">Tidak ada data laporan harian pada bulan ini.</td>
                    </tr>
                @endforelse
                
                @if(count($dailyReports) > 0)
                    <tr class="total-row">
                        <td colspan="2" class="text-center">GRAND TOTAL AKUMULASI</td>
                        <td class="text-center">{{ $grandTotalHadir }} Orang/Hari</td>
                        <td class="text-right">Rp {{ number_format($grandTotalLangsung, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($grandTotalTidakLangsung, 0, ',', '.') }}</td>
                        <td class="text-right" style="color: #16a34a;">Rp {{ number_format($grandTotalGaji, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

    </div>

</body>
</html>
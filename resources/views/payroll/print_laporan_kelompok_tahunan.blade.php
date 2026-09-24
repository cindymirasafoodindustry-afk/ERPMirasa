<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Analisis Buku Besar Payroll Kelompok Kerja Tahunan</title>
    <style>
        @page { size: A4 portrait; margin: 15mm 10mm 15mm 10mm; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 10px; color: #1e293b; background-color: #cbd5e1; margin: 0; padding: 40px 0; }
        .page-container { background-color: #ffffff; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 20mm 15mm; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); box-sizing: border-box; }
        .no-print-box { text-align: center; margin-bottom: 25px; }
        .btn-print { background-color: #0284c7; color: #fff; font-weight: bold; font-size: 12px; padding: 10px 24px; border-radius: 6px; border: none; cursor: pointer; }
        .btn-print:hover { background-color: #0369a1; }
        
        @media print {
            .no-print-box { display: none !important; }
            body { background-color: #fff; padding: 0; }
            .page-container { width: 100%; min-height: auto; margin: 0; padding: 0; box-shadow: none; border-radius: 0; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }

        .header-container { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
        .header-left h2 { margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; text-transform: uppercase; }
        .header-left p { margin: 4px 0 0 0; font-size: 9.5px; color: #4b5563; font-weight: 600; }
        .status-finalized { font-size: 9.5px; color: #0f172a; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; text-align: right; }

        .meta-container { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 9px; color: #4b5563; font-weight: 700; }
        .meta-box { background-color: #f9fafb; padding: 8px 12px; border-radius: 6px; border: 1px solid #e5e7eb; width: 46%; }
        .meta-box span { display: block; color: #9ca3af; font-size: 8px; text-transform: uppercase; margin-bottom: 2px; }

        .stats-grid { display: flex; gap: 18px; width: 100%; margin-bottom: 25px; }
        .stat-card { flex: 1; padding: 10px 12px; border-radius: 6px; border-left: 4px solid #0284c7; display: flex; flex-direction: column; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .stat-card.blue { background-color: #f0f9ff; border-left-color: #0284c7; }
        .stat-card.orange { background-color: #fff7ed; border-left-color: #f97316; }
        .stat-card.gray { background-color: #f9fafb; border-left-color: #4b5563; }
        .stat-label { font-size: 8px; font-weight: 800; text-transform: uppercase; color: #4b5563; }
        .stat-value { font-size: 12px; font-weight: 800; color: #111827; }

        .section-title { font-size: 9.5px; font-weight: 800; color: #111827; margin-bottom: 12px; text-transform: uppercase; border-left: 4px solid #0284c7; padding-left: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 6px; font-size: 8.5px; word-wrap: break-word; white-space: normal; }
        th { background-color: #0f172a; color: #ffffff; font-weight: 800; text-transform: uppercase; font-size: 8px; text-align: center; }
        tr:nth-child(even) td { background-color: #f8fafc; }
        .text-right { text-align: right; font-variant-numeric: tabular-nums; font-weight: bold; }
        .text-center { text-align: center; }
        .text-blue { color: #0284c7; }
        .total-row td { background-color: #f1f5f9; font-weight: 800; border-top: 2px solid #0f172a; font-size: 9px; }
    </style>
</head>
<body>

    <div class="no-print-box">
        <button onclick="window.print()" class="btn-print">Klik Cetak Jurnal Tahunan / Simpan PDF</button>
    </div>

    <div class="page-container">
        <!-- Header -->
        <div class="header-container">
            <div class="header-left">
                <h2>MIRASA FOOD INDUSTRY</h2>
                <p>PT MIRASA FOOD INDUSTRY - Laporan Analisis Buku Besar Akumulasi Biaya Kelompok Kerja per Bulan</p>
            </div>
            <div class="status-finalized">STATUS DOKUMEN<br>FINALIZED</div>
        </div>

        <!-- Meta -->
        <div class="meta-container">
            <div class="meta-box">
                <span>Tahun Anggaran Analisis</span>
                <strong>TAHUN PERIODE {{ $tahunPeriode }}</strong>
            </div>
            <div class="meta-box" style="text-align: right;">
                <span>Dibuat Tanggal</span>
                <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB</strong>
            </div>
        </div>

        @php
            $grandTotalHadir = collect($yearlyReports)->sum('total_hadir');
            $grandTotalLangsung = collect($yearlyReports)->sum('gaji_langsung');
            $grandTotalTidakLangsung = collect($yearlyReports)->sum('gaji_tidak_langsung');
            $grandTotalGaji = collect($yearlyReports)->sum('total_gaji');
        @endphp

        <!-- Indikator -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-label">Total Pengeluaran Buku Besar Tahunan</div>
                <div class="stat-value text-blue">Rp {{ number_format($grandTotalGaji, 0, ',', '.') }}</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">Total Biaya Kelompok Langsung (HPP)</div>
                <div class="stat-value">Rp {{ number_format($grandTotalLangsung, 0, ',', '.') }}</div>
            </div>
            <div class="stat-card gray">
                <div class="stat-label">Total Biaya Kelompok Tdk Langsung (Overhead)</div>
                <div class="stat-value">Rp {{ number_format($grandTotalTidakLangsung, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="section-title">Ringkasan Grafik Rekapitulasi Anggaran Anggota Lini Produksi Per Bulan</div>

        <!-- Tabel Jurnal -->
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 90px;">Bulan Anggaran</th>
                    <th style="width: 90px;">Total Hadir Orang-Hari</th>
                    <th style="width: 120px;">Total Kelompok Langsung (HPP)</th>
                    <th style="width: 120px;">Total Kelompok Tdk Langsung</th>
                    <th style="width: 110px;">Total Alokasi Biaya</th>
                </tr>
            </thead>
            <tbody>
                @forelse($yearlyReports as $index => $data)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center" style="font-weight: bold;">{{ \Carbon\Carbon::parse($data['bulan'].'-01')->translatedFormat('F Y') }}</td>
                        <td class="text-center">{{ $data['total_hadir'] }} Absensi/Bulan</td>
                        <td class="text-right">Rp {{ number_format($data['gaji_langsung'], 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($data['gaji_tidak_langsung'], 0, ',', '.') }}</td>
                        <td class="text-right text-blue">Rp {{ number_format($data['total_gaji'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="color: #6b7280; padding: 20px;">Belum ada catatan transaksi jurnal pada tahun anggaran ini.</td>
                    </tr>
                @endforelse

                @if(count($yearlyReports) > 0)
                    <tr class="total-row">
                        <td colspan="2" class="text-center">TOTAL AKUMULASI TAHUNAN</td>
                        <td class="text-center">{{ $grandTotalHadir }} Orang-Hari</td>
                        <td class="text-right">Rp {{ number_format($grandTotalLangsung, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($grandTotalTidakLangsung, 0, ',', '.') }}</td>
                        <td class="text-right" style="color: #0284c7;">Rp {{ number_format($grandTotalGaji, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

</body>
</html>

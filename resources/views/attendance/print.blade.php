<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan_Rekap_Absensi_Karyawan_PT_MIRASA</title>
    <style>
        /* GAYA DESAIN UTAMA (PENGGANTI TAILWIND AGAR TIDAK POLOS) */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f3f4f6;
            padding: 30px;
            margin: 0;
            color: #1f2937;
        }
        .no-print-container {
            max-width: 900px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: flex-end;
        }
        .btn-print {
            background-color: #059669;
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-print:hover { background-color: #047857; }
        
        /* KERTAS A4 */
        .kertas-a4 {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* KOP SURAT DOKUMEN */
        .header-kop {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #111827;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-kop h1 { margin: 0; font-size: 26px; font-weight: 900; tracking-tight: -0.05em; }
        .header-kop p { margin: 5px 0 0 0; font-size: 14px; font-weight: bold; color: #6b7280; }
        .status-title { font-size: 11px; font-weight: bold; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; }
        .status-val { margin: 2px 0 0 0; font-size: 15px; font-weight: 900; color: #059669; text-transform: uppercase; }

        /* PERIODE BOX */
        .box-periode {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 30px;
        }
        .box-title { font-size: 10px; font-weight: bold; color: #9ca3af; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .box-val { margin: 0; font-weight: bold; color: #374151; }

        /* GRID TIGA KOTAK GAJI (PERSIS SEPERTI GAMBAR CONTOH) */
        .grid-gaji {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 35px;
        }
        .card-gaji { padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .gaji-total { background-color: #e0e7ff; border-color: #c7d2fe; }
        .gaji-langsung { background-color: #ffedd5; border-color: #fed7aa; }
        .gaji-tidak { background-color: #f3f4f6; border-color: #e5e7eb; }
        .lbl-gaji { font-size: 10px; font-weight: bold; text-transform: uppercase; display: block; margin-bottom: 5px; }
        .lbl-gaji.t { color: #4f46e5; } .lbl-gaji.l { color: #ea580c; } .lbl-gaji.tl { color: #4b5563; }
        .val-gaji { margin: 0; font-size: 18px; font-weight: 900; }
        .val-gaji.t { color: #3730a3; } .val-gaji.l { color: #9a3412; } .val-gaji.tl { color: #1f2937; }

        /* TABEL RESMI */
        .sub-judul { font-size: 13px; font-weight: 900; text-transform: uppercase; border-left: 4px solid #041078; padding-left: 8px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; margin-bottom: 40px; }
        th { background-color: #041078; color: white; padding: 10px; font-weight: bold; text-transform: uppercase; font-size: 11px; border: 1px solid #041078; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* GAYA WARNA REVISI LEMBUR */
        .txt-lembur { font-weight: bold; color: #dc2626; }
        .txt-normal { color: #9ca3af; }

        /* TANDA TANGAN */
        .wrapper-ttd { display: grid; grid-template-columns: 1fr 1fr; font-size: 12px; text-align: center; margin-top: 50px; }
        .space-ttd { margin-top: 70px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }

        /* LOGIKA AUTOMATIS SAAT TOMBOL PRINT DIKLIK */
        @media print {
            body { background-color: white; padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .kertas-a4 { border: none !important; box-shadow: none !important; padding: 0 !important; max-width: 100% !important; }
            th { background-color: #111827 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .card-gaji { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .box-periode { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <!-- TOMBOL ATAS (HILANG OTOMATIS SAAT PROSES PRINTING) -->
    <div class="no-print-container no-print">
        <button onclick="window.print()" class="btn-print">
            Klik Cetak Dokumen / Simpan PDF
        </button>
    </div>

    <!-- STRUKTUR KERTAS LAPORAN SEUAI STANDAR A4 -->
    <div class="kertas-a4">
        
        <!-- HEADER KOP REKAP UTAMA PABRIKAN -->
        <div class="header-kop">
            <div>
                <h1 style="color: #960505; margin: 0; font-size: 26px; font-weight: 900; tracking-tight: -0.05em;">PT MIRASA FOOD INDUSTRY</h1>
                <p>Laporan Rekapitulasi Absensi & Kehadiran Massal Karyawan</p>
            </div>
            <div>
                <span class="status-title">Status Dokumen</span>
                <p class="status-val">Finalized</p>
            </div>
        </div>

        <!-- INFO PERIODE FILTRASI (SUDAH AMAN BEBAS EROR UNDEFINED VARIABLE REQUEST) -->
        <div class="box-periode">
            <div>
                <span class="box-title">Periode Saringan Laporan</span>
                <p class="box-val">
                    @if(request('tanggal_mulai') && request('tanggal_selesai'))
                        {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->translatedFormat('d F Y') }}
                    @elseif(request('tanggal_mulai'))
                        Mulai {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->translatedFormat('d F Y') }}
                    @elseif(request('tanggal_selesai'))
                        Sampai {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->translatedFormat('d F Y') }}
                    @else
                        Semua Periode Tanggal (Keseluruhan)
                    @endif
                </p>
            </div>
            <div style="text-align: right;">
                <span class="box-title">Dibuat Pada</span>
                <p class="box-val" style="font-weight: normal; color: #4b5563;">{{ date('d/m/Y H:i') }} WIB</p>
            </div>
        </div>

        <!-- 3 KOTAK INFO GAJI DISEMBUNYIKAN SEMENTARA AGAR TIDAK TAMPIL ANGKA RP 0 PADA LAPORAN ABSENSI -->

        <!-- TABEL RINCIAN DATA ABSENSI KARYAWAN -->
        <div class="sub-judul">I. RINCIAN REKAPITULASI DATA KEHADIRAN KARYAWAN</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 12%;">ID Karyawan</th>
                    <th style="width: 20%;">Nama Karyawan</th>
                    <th style="width: 13%;">Kelompok</th>
                    <th style="width: 8%; text-align: center;">Shift</th>
                    <th style="width: 8%; text-align: center;">Masuk</th>
                    <th style="width: 8%; text-align: center;">Pulang</th>
                    <th style="width: 10%; text-align: center;">Jam Kerja</th>
                    <th style="width: 10%; text-align: center;">Lembur</th>
                    <th style="width: 14%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $index => $item)
                    @php
                        $jamKerjaNet = 0;
                        $jamLembur = 0;

                        if ($item->jam_masuk && $item->jam_pulang) {
                            $masuk = \Carbon\Carbon::parse($item->jam_masuk);
                            $pulang = \Carbon\Carbon::parse($item->jam_pulang);
                            $totalJamKotor = $masuk->diffInHours($pulang);
                            
                            // Potong 1 jam istirahat harian pabrik
                            $jamKerjaNet = $totalJamKotor > 1 ? $totalJamKotor - 1 : $totalJamKotor;

                            // Hitung lembur jika jam kerja bersih di atas 7 jam harian
                            if ($jamKerjaNet > 7) {
                                $jamLembur = $jamKerjaNet - 7;
                            }
                        }
                    @endphp
                    <tr>
                        <td class="text-center" style="color: #6b7280;">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                        <td style="font-weight: bold; color: #4b5563; font-family: monospace;">{{ $item->employee->id_karyawan ?? '-' }}</td>
                        <td style="font-weight: bold; color: #111827;">{{ $item->employee->nama_karyawan ?? '-' }}</td>
                        <td style="text-transform: uppercase; font-size: 11px; color: #047857;">{{ $item->kelompok_kerja_harian ?? $item->employee->kelompok }}</td>
                        <td class="text-center" style="font-weight: bold; color: #4b5563;">{{ $item->employee->shift ?? '-' }}</td>
                        <td class="text-center">{{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '-' }}</td>
                        <td class="text-center">{{ $item->jam_pulang ? \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') : '-' }}</td>
                        <td class="text-center" style="font-weight: bold; color: #111827;">{{ $jamKerjaNet }} Jam</td>
                        <td class="text-center">
                            @if($jamLembur > 0)
                                <span class="txt-lembur">+{{ $jamLembur }} Jam</span>
                            @else
                                <span class="txt-normal">Tidak Ada</span>
                            @endif
                        </td>
                        <td style="color: #4b5563; font-style: italic;">{{ $item->keterangan }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center" style="padding: 30px; color: #9ca3af; font-style: italic;">
                            Tidak ditemukan rincian data catatan absensi pada kriteria saringan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- AREA PENANDATANGANAN RESMI DOKUMEN -->
        <div class="wrapper-ttd">
            <div></div>
            <div>
                <p style="margin: 0;">Mengetahui,<br><span style="font-weight: bold; color: #374151;">Kabag. HRD & Personalia PT Mirasa Food Industry</span></p>
                <p class="space-ttd">( ............................................ )</p>
            </div>
        </div>

        <!-- AREA CATATAN KAKI DOKUMEN INTERNAL -->
        <div style="margin-top: 60px; text-align: center; font-size: 10px; color: #9ca3af; font-style: italic; border-top: 1px dashed #e5e7eb; padding-top: 12px; letter-spacing: 0.5px;">
            *Dokumen rekapitulasi kehadiran ini diterbitkan secara otomatis oleh sistem data komputer internal PT Mirasa Food Industry dan bersifat rahasia.
        </div>

    </div>
</body>
</html>
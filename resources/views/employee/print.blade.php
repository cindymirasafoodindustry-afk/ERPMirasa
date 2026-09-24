<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan_Data_Master_Karyawan</title>
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

        /* GRID TIGA KOTAK HITUNGAN KARYAWAN (MATING STYLE DENGAN LAPORAN GAJI) */
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

        /* TABEL RESMI BERGARIS KOTAK TEGAS */
        .sub-judul { font-size: 13px; font-weight: 900; text-transform: uppercase; border-left: 4px solid #111827; padding-left: 8px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; margin-bottom: 40px; }
        th { background-color: #111827; color: white; padding: 10px; font-weight: bold; text-transform: uppercase; font-size: 11px; border: 1px solid #111827; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; vertical-align: middle; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* TANDA TANGAN */
        .wrapper-ttd { display: flex; justify-content: flex-end; font-size: 12px; margin-top: 50px; padding-right: 20px; }
        .box-ttd-kanan { text-align: center; width: 220px; }
        .space-ttd { margin-top: 70px; font-weight: bold; border-top: 1px solid #4b5563; padding-top: 4px; text-transform: uppercase; }

        /* LOGIKA OTOMATIS SAAT TOMBOL PRINT DIKLIK */
        @media print {
            body { background-color: white; padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .kertas-a4 { border: none !important; box-shadow: none !important; padding: 0 !important; max-width: 100% !important; }
            th { background-color: #111827 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .card-gaji { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .box-periode { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        .wrapper-ttd { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            font-size: 12px; 
            text-align: center; 
            margin-top: 50px; 
        }
        .space-ttd { 
            margin-top: 70px; 
            font-weight: bold; 
            text-decoration: underline; 
            text-transform: uppercase; 
        }
    </style>
    <style>
            .container-footer-nota {
                margin-top: 60px;
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                width: 100%;
            }
            .disclaimer-kiri {
                width: 50%;
                text-align: left;
                font-size: 11px;
            }
            .disclaimer-kiri .line-dashed {
                border-top: 1px dashed #d1d5db;
                margin-bottom: 8px;
                width: 100%;
            }
            .disclaimer-kiri p {
                margin: 0;
                color: #6b7280;
                font-style: italic;
            }
            .ttd-kanan {
                width: 250px;
                text-align: center;
                font-size: 12px;
            }
            .ttd-kanan .title-mengetahui {
                margin: 0 0 4px 0;
                color: #374151;
            }
            .ttd-kanan .title-jabatan {
                margin: 0;
                font-weight: bold;
                color: #111827;
            }
            .ttd-kanan .space-blank {
                height: 80px;
            }
            .ttd-kanan .line-nama {
                font-weight: bold;
                color: #111827;
                margin: 0;
                letter-spacing: 0.5px;
            }
        </style>
</head>
<body onload="window.print()">

    <!-- TOMBOL ATAS (HILANG SAAT PRINTING) -->
    <div class="no-print-container no-print">
        <button onclick="window.print()" class="btn-print">
            Klik Cetak Dokumen / Simpan PDF
        </button>
    </div>

    <!-- STRUKTUR KERTAS LAPORAN -->
    <div class="kertas-a4">
        
        <!-- HEADER KOP -->
        <div class="header-kop">
            <div>
                <h1>PT MIRASA FOOD INDUSTRY</h1>
                <p>Laporan Data Master Kepegawaian Karyawan</p>
            </div>
            <div>
                <span class="status-title">Status Dokumen</span>
                <p class="status-val">Finalized</p>
            </div>
        </div>

        <!-- INFO PERIODE -->
        <div class="box-periode">
            <div>
                <span class="box-title">Periode Laporan</span>
                <p class="box-val">
                    {{ request('filter_tanggal') ? \Carbon\Carbon::parse(request('filter_tanggal'))->translatedFormat('d F Y') : \Carbon\Carbon::today()->translatedFormat('d F Y') }}
                </p>
            </div>
            <div style="text-align: right;">
                <span class="box-title">Dibuat Pada</span>
                <p class="box-val" style="font-weight: normal; color: #4b5563;">{{ date('d/m/Y H:i') }} WIB</p>
            </div>
        </div>

        <!-- TIGA KOTAK INFO HITUNGAN KARYAWAN (PERSIS SEPERTI MAP PRODUKSI) -->
        <div class="grid-gaji">
            <div class="card-gaji gaji-total">
                <span class="lbl-gaji t">Total Karyawan Keseluruhan</span>
                <p class="val-gaji t">{{ $employees->count() }} Orang</p>
            </div>
            <div class="card-gaji gaji-langsung">
                <span class="lbl-gaji l">Total Kelompok Langsung</span>
                <p class="val-gaji l">{{ $employees->where('kelompok', 'langsung')->count() }} Orang</p>
            </div>
            <div class="card-gaji gaji-tidak">
                <span class="lbl-gaji tl">Total Kelompok Tidak Langsung</span>
                <p class="val-gaji tl">{{ $employees->where('kelompok', 'tidak langsung')->count() }} Orang</p>
            </div>
        </div>

        <!-- TABEL RINCIAN DATA MASTER KARYAWAN -->
        <div class="sub-judul">I. RINCIAN REKAPITULASI DATA MASTER KARYAWAN</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 3%; text-align: center;">No</th>
                    <th style="width: 10%;">ID Karyawan</th>
                    <th style="width: 10%;">Nama Karyawan</th>
                    <th style="width: 10%;">Kelompok</th>
                    <th style="width: 8%; text-align: center;">Shift</th>
                    <th style="width: 8%; text-align: center;">Status</th>
                    <th style="width: 8%; text-align: center;">Bagian</th>
                    <th style="width: 10%; text-align: center;">Nomor HP</th>
                    <th style="width: 12%; text-align: center;">Bank & Rekening</th>
                    <th style="width: 8%; text-align: center;">Tanggal Masuk</th>
                    <th style="width: 13%; text-align: center;">Masa Kerja</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $index => $emp)
                    @php
                                        // KALKULATOR CERDAS - OTOMATIS BERUBAH SETIAP GANTI HARI (REAL-TIME)
                                            $masaKerja = '-';
                                        if (!empty($emp->tanggal_masuk_kerja)) {
                                            $masuk = \Carbon\Carbon::parse($emp->tanggal_masuk_kerja);
                                            $sekarang = \Carbon\Carbon::now();
                            
                                            $tahun = $masuk->diffInYears($sekarang);
                                            $bulan = $masuk->diffInMonths($sekarang) % 12;
                            
                                        // Hitung sisa hari secara presisi setelah dipotong tahun dan bulan
                                            $masukSisaHari = $masuk->copy()->addYears($tahun)->addMonths($bulan);
                                            $hari = (int) floor($masukSisaHari->diffInDays($sekarang));

                                        // Susun teks tampilan agar rapi lurus simetris
                                            $teksMasaKerja = [];
                            if ($tahun > 0) { $teksMasaKerja[] = (int)$tahun . " Thn"; }
                            if ($bulan > 0) { $teksMasaKerja[] = (int)$bulan . " Bln"; }
                            if ($hari > 0 || empty($teksMasaKerja)) { $teksMasaKerja[] = (int)$hari . " Hari"; }
                            
                                            $masaKerja = implode(' ', $teksMasaKerja);
                                            }
                    @endphp
                    <tr>
                        <td class="text-center" style="color: #9ca3af; font-family: monospace;">{{ $index + 1 }}</td>
                        <td style="font-family: monospace; font-weight: bold; color: #4b5563;">{{ $emp->id_karyawan }}</td>
                        <td style="font-weight: bold; color: #111827;">{{ $emp->nama_karyawan }}</td>
                        <td style="text-transform: uppercase; font-weight: bold; color: #10b981;">{{ $emp->kelompok }}</td>
                        <td class="text-center" style="text-transform: uppercase;">
                            {{ $emp->shift == 'non shift' ? 'non shift' : 'SHIFT ' . $emp->shift }}
                        </td>
                        <td class="text-center" style="text-transform: uppercase; font-weight: bold; color: #4b5563;">
                            {{ $emp->status_karyawan }}
                        </td>
                        <td class="text-center" style="text-transform: uppercase; font-weight: bold; color: #4b5563;">
                            {{ $emp->bagian ?? '-' }}
                        </td>
                        <td class="text-center" style="font-weight: bold; color: #4b5563;">
                            {{ $emp->no_hp ?? '-' }}
                        </td>
                        <td class="text-center" style="font-size: 11px; color: #1f2937;">
                            <span style="font-weight: 900; text-transform: uppercase;">{{ $emp->nama_bank ?? '-' }}</span>
                            <br>
                            <span style="font-family: monospace; color: #6b7280;">{{ $emp->no_rekening ?? '-' }}</span>
                        </td>
                        <td class="text-center" style="font-weight: bold; color: #4b5563;">
                            {{ (!empty($emp->tanggal_masuk_kerja) ? \Carbon\Carbon::parse($emp->tanggal_masuk_kerja)->format('d/m/Y') : '-') }}
                        </td>
                        <td class="text-center" style="font-weight: bold; color: #111827;">
                            {{ $masaKerja }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center" style="padding: 30px; color: #9ca3af; font-style: italic;">
                            Tidak ada data master karyawan yang sesuai dengan saringan filter saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <!-- TAG DI ATAS ADALAH PENUTUP TABEL DATA KARYAWAN EMPLOYEE -->

        <!-- ================= FORCE INLINE TABLE UNTUK TTD POJOK KANAN (ANTI-GARIS BOCOR) ================= -->
        <table style="width: 100% !important; border: none !important; margin-top: 60px !important; background: transparent !important; border-collapse: collapse !important;">
            <tr style="background: transparent !important; border: none !important;">
                <td style="width: 55% !important; border: none !important; background: transparent !important; padding: 0 !important;"></td>
                <td style="width: 45% !important; border: none !important; background: transparent !important; text-align: center !important; font-size: 12px !important; color: #1f2937 !important; padding: 0 !important; line-height: 1.6 !important;">
                    <p style="margin: 0 !important; padding: 0 !important;">Mengetahui,<br><span style="font-weight: bold; color: #374151;">Kabag. HRD & Personalia PT Mirasa Food Industry</span></p>
                    <p style="margin-top: 75px !important; margin-bottom: 0 !important; font-weight: bold !important; color: #1f2937 !important; letter-spacing: 0.5px !important;">( ............................................ )</p>
                </td>
            </tr>
        </table>

        <!-- AREA CATATAN KAKI DOKUMEN INTERNAL -->
        <div style="margin-top: 50px !important; text-align: center !important; font-size: 10px !important; color: #9ca3af !important; font-style: italic !important; border-top: 1px dashed #e5e7eb !important; padding-top: 12px !important; letter-spacing: 0.5px !important;">
            *Dokumen rekapitulasi kepegawaian ini diterbitkan secara otomatis oleh sistem data komputer internal PT Mirasa Food Industry dan bersifat rahasia.
        </div>

    </div>
</body>
</html>

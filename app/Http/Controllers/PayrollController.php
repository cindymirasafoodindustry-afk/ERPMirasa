<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollDetail;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Imports\PayrollImport;
use Maatwebsite\Excel\Facades\Excel;


class PayrollController extends Controller
{
    // 1. TAMPILAN UTAMA LIST PAYROLL KARYAWAN BERDASARKAN BULAN PERIODE
    public function index(Request $request)
    {
        // 1. Ambil filter periode bulan aktif (default: bulan ini jika kosong)
        $bulanPeriode = $request->input('bulan_periode', date('Y-m'));
        
        // 2. Tangkap inputan kata kunci pencarian dari form depan
        $cariKaryawan = $request->input('cari_karyawan');

        // 3. JURUS DETEKTIF DATABASE: Cari karyawan dengan filter nama atau ID secara fleksibel
        $employeesQuery = \App\Models\Employee::query();
        
        if (!empty($cariKaryawan)) {
            $employeesQuery->where(function($q) use ($cariKaryawan) {
                $q->where('nama_karyawan', 'ILIKE', '%' . $cariKaryawan . '%')
                  ->orWhere('id_karyawan', 'ILIKE', '%' . $cariKaryawan . '%');
            });
        }

        // Jalankan query tarik data karyawan
        $employees = $employeesQuery->get();
        
        // TAKTIK DEWA KILAT VERSION: SEDOT MASSAL SEBULAN PENUH DALAM 1 KALI QUERY TUNGGAL
        // =========================================================================

        $allAttendances = \App\Models\Attendance::where('tanggal', 'LIKE', "%{$bulanPeriode}%")
        ->get()
        ->groupBy('employee_id'); // Mengelompokkan data absensi per kepala di RAM

        $allPayrollDetails = \App\Models\PayrollDetail::where('bulan_tahun', $bulanPeriode)
        ->get()
        ->keyBy('employee_id'); // Mengunci data payroll berdasarkan id karyawan di RAM

        // Siapkan penampung data payroll hasil perhitungan otomatis real-time
        $payrollData = [];

        foreach ($employees as $emp) {
            $emp->masa_kerja = '0';
            if (!empty($emp->tanggal_masuk_kerja)) {
                // Gunakan format Carbon yang mutlak untuk selisih tanggal harian
                $masuk = \Carbon\Carbon::parse($emp->tanggal_masuk_kerja);
                $sekarang = \Carbon\Carbon::now();

                // diff() akan otomatis menghasilkan objek selisih tahun dan bulan yang bulat murni!
                $selisih = $masuk->diff($sekarang);
                $tahun = $selisih->y;
                $bulan = $selisih->m;

                $teksMasaKerja = [];
                if ($tahun > 0) { $teksMasaKerja[] = $tahun . " Thn"; }
                if ($bulan > 0) { $teksMasaKerja[] = $bulan . " Bln"; }
                
                $emp->masa_kerja = !empty($teksMasaKerja) ? implode(' ', $teksMasaKerja) : '0';
            }
            // Ambil semua riwayat absensi karyawan ini pada bulan tersebut yang berstatus masuk
            $attendances = $allAttendances->get($emp->id) ?? collect();

            $totalHariHadir = $attendances->count();
            $totalJamLembur1 = 0;
            $totalJamLembur2 = 0;
            $totalJamPotongan = 0;
            $jumlahHariKerjaMurni = 0;

            // DETEKTIF ABSENSI SINKRON TOTAL: Menghitung jam lembur & potongan bulanan secara adil dan dinamis
                foreach ($attendances as $att) {
                    if ($att->jam_masuk && $att->jam_pulang) {
                        $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                        $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                        $tanggalAbsen = \Carbon\Carbon::parse($att->tanggal); // Ambil objek tanggal absensi riil harian
                        
                        $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                        // RUMUS SAKRAL HARI MINGGU: Kebal potongan istirahat, TIDAK menambah hari kerja, jam lembur II masuk penuh!
                        if ($tanggalAbsen->isSunday()) {
                            $durasiKerja = $durasiKotor; 
                            $totalJamLembur2 += $durasiKerja; // Semua jam masuk kuantitas Lembur II bulanan massal
                        } else {
                            // Logika Hari Biasa Regular (Menambah kuantitas jumlah hari kerja murni perusahaan)
                            $jumlahHariKerjaMurni++; // Hari biasa resmi menambah hari kerja

                            $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                            if ($durasiKerja < 7) {
                                $totalJamPotongan += (7 - $durasiKerja);
                            } else {
                                $kelebihanJam = $durasiKerja - 7;
                                
                                if ($durasiKerja > 8) {
                                    $totalJamLembur1 += 1;                  
                                    $totalJamLembur2 += ($durasiKerja - 8);   
                                } else {
                                    $totalJamLembur1 += $kelebihanJam;      
                                    $totalJamLembur2 += 0;
                                }
                            }
                        }
                    }
                }


            // Ambil data inputan rupiah komponen (jika admin sudah pernah menyimpannya di tabel database)
            $existingPayroll = $allPayrollDetails->get($emp->id);

            $gajiPerhari = $existingPayroll->gaji_perhari ?? 0;
            $rateLembur1 = $existingPayroll->honor_lembur_1 ?? 0;
            $rateLembur2 = $existingPayroll->honor_lembur_2 ?? 0;
            $masukKerjaDate = \Carbon\Carbon::parse($emp->tanggal_masuk_kerja);
            $sekarangDate   = \Carbon\Carbon::now();
            $masaKerjaTahun = $masukKerjaDate->diffInYears($sekarangDate);

            $tunjanganMasa = 0;
            if ($masaKerjaTahun > 20) {
                $tunjanganMasa = 2550;
            } elseif ($masaKerjaTahun >= 15 && $masaKerjaTahun <= 20) {
                $tunjanganMasa = 2200;
            } elseif ($masaKerjaTahun >= 10 && $masaKerjaTahun < 15) {
                $tunjanganMasa = 1700;
            } elseif ($masaKerjaTahun >= 5 && $masaKerjaTahun < 10) {
                $tunjanganMasa = 1000;
            } else {
                // Fallback jika tidak masuk kriteria, ambil dari database detail
                $tunjanganMasa = (float)str_replace('.', '', $existingPayroll->tunjangan_masa ?? 0);
            }

            // // KALKULATOR HARIAN: Mengubah nominal tunjangan bulanan menjadi tarif harian dikalikan hari masuk riil!
            // RUMUS PROPORSIONAL 7 JAM SEJATI: Membagi tarif tunjangan harian per 7 jam kerja wajib secara dinamis murni!
            $totalTunjanganMasaKotor = $tunjanganMasa * $jumlahHariKerjaMurni; 
            
            if (isset($totalJamPotongan) && $totalJamPotongan > 0 && $totalTunjanganMasaKotor > 0) {
                // 1. Hitung tarif tunjangan masa kerja per jam secara dinamis murni
                $tunjanganPerJam = $tunjanganMasa / 7;
                
                // 2. Hitung total potongan tunjangan berdasarkan durasi jam kerja yang kurang dari 7 jam
                $totalPotonganTunjanganMasa = $tunjanganPerJam * $totalJamPotongan;
                
                // 3. Kurangi total kotor dengan potongan jam kerja secara adil perorangan
                $tunjanganMasa = $totalTunjanganMasaKotor - $totalPotonganTunjanganMasa;
            } else {
                // Jika karyawan rajin masuk penuh tanpa potongan jam kerja, tunjangan masa kerja utuh penuh!
                $tunjanganMasa = $totalTunjanganMasaKotor;
            }

            $tunjanganJab = $existingPayroll->tunjangan_jabatan ?? 0;
            $insentif     = $existingPayroll->insentif ?? 0;

            $bpjskes      = $existingPayroll->potongan_bpjs_kes ?? 0;
            $bpjstk       = $existingPayroll->potongan_bpjs_tk ?? 0;
            $potonganJam  = $existingPayroll->potongan_jam_kerja ?? 0;
            $potonganLain = $existingPayroll->potongan_lainnya ?? 0;

                //1. RUMUS MATEMATIKA ESSENSIAL PAYROLL (AKUMULASI BULANAN DESIMAL PT MIRASA FOOD INDUSTRY)
                // KUNCI KEMENANGAN TOTAL: Mengalikan nominal pokok dengan Hari Kerja Murni (21 Hari), dan lembur dengan kuantitas pecahan desimal riil!
                $subtotalGajiPokok  = $gajiPerhari * $jumlahHariKerjaMurni; 
                $subtotalLembur1    = $rateLembur1 * $totalJamLembur1;
                $subtotalLembur2    = $rateLembur2 * $totalJamLembur2; // Lembur II dikalikan utuh dengan kuantitas pecahan desimal (seperti 21.5 Jam)
                $subtotalDendaJam   = $potonganJam * $totalJamPotongan;

                // 2. Jumlahkan Total Pendapatan Kotor dan Potongan Bersih Secara Akurat
                $totalPendapatan = $subtotalGajiPokok + $subtotalLembur1 + $subtotalLembur2 + $tunjanganMasa + $tunjanganJab + $insentif;
                $totalPotongan   = $bpjskes + $bpjstk + $subtotalDendaJam + $potonganLain;
                
                //3. Hasil Bersih Gaji Harian Per Personil Akhir
                $totalGajiBersih = $totalPendapatan - $totalPotongan;

            $payrollData[] = [
            'employee'           => $emp,
            'total_hari_hadir'   => $jumlahHariKerjaMurni,
            'total_jam_lembur_1' => round($totalJamLembur1, 1),
            'total_jam_lembur_2' => round($totalJamLembur2, 1),
            'total_jam_potongan' => round($totalJamPotongan, 1),
            'payroll_details'    => $existingPayroll,
            'total_pendapatan'   => $totalPendapatan, 
            'subtotal_gaji_pokok'=> $subtotalGajiPokok,
            'subtotal_lembur_1'  => $subtotalLembur1,
            'subtotal_lembur_2'  => $subtotalLembur2,
            'subtotal_denda_jam' => $subtotalDendaJam,
            'total_gaji_bersih'  => $totalGajiBersih,
            ];
        }

        return view('payroll.index', compact('payrollData', 'bulanPeriode'));
    }

    // 2. AKSI UNTUK MENYIMPAN / UPDATE NOMINAL RUPIAH INPUTAN PAYROLL DARI FORM UTAMA
    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'employee_id'        => 'required',
            'bulan_tahun'        => 'required|string|size:7',
            'gaji_perhari'       => 'nullable',
            'potongan_jam_kerja' => 'nullable',
        ]);

        // Ambil semua data inputan dari boks popup modal front-end
        $data = $request->all();

        // --- PROSES STRIP FORMAT TITIK RUPIAH PADA SEMUA INPUT DATA ---
        foreach ($data as $key => $value) {
            if (is_string($value) && $key !== 'bulan_tahun' && $key !== 'employee_id') {
                // Bersihkan sisa titik jika ada
                $cleanValue = str_replace('.', '', $value);
                $data[$key] = (float) $cleanValue;
            }
        }

        // PASANG PASUKAN PENGUNCI DATA: Pastikan seluruh komponen baru dan lama ikut tersimpan murni ke database!
        $data['gaji_perhari']         = isset($data['gaji_perhari']) ? (float)$data['gaji_perhari'] : 0;
        $data['potongan_jam_kerja']   = isset($data['potongan_jam_kerja']) ? (float)$data['potongan_jam_kerja'] : 0;
        $data['honor_lembur_1']       = isset($data['honor_lembur_1']) ? (float)$data['honor_lembur_1'] : 0;
        $data['honor_lembur_2']       = isset($data['honor_lembur_2']) ? (float)$data['honor_lembur_2'] : 0;
        $data['tunjangan_masa_kerja'] = isset($data['tunjangan_masa_kerja']) ? (float)$data['tunjangan_masa_kerja'] : 0;
        
        // SUNTIKAN KOMPONEN BARU PT MIRASA FOOD INDUSTRY (SINKRONISASI 1000% FORM MODAL WEB)
        $data['tunjangan_jabatan']    = isset($data['tunjangan_jabatan']) ? (float)$data['tunjangan_jabatan'] : 0;
        $data['insentif']             = isset($data['insentif']) ? (float)$data['insentif'] : 0;
        $data['potongan_bpjs_kes']    = isset($data['potongan_bpjs_kes']) ? (float)$data['potongan_bpjs_kes'] : 0;
        $data['potongan_bpjs_tk']     = isset($data['potongan_bpjs_tk']) ? (float)$data['potongan_bpjs_tk'] : 0;
        $data['potongan_lainnya']     = isset($data['potongan_lainnya']) ? (float)$data['potongan_lainnya'] : 0;

        // 1. Potong LANGSUNG teks "UMUM-33" atau "MRSA-027" untuk mengambil angka murninya (33)
        $inputEmployeeId = $request->employee_id;
        $realEmployeeId = null;

        if (is_string($inputEmployeeId) && str_contains($inputEmployeeId, '-')) {
            $parts = explode('-', trim($inputEmployeeId));
            $realEmployeeId = (int) end($parts);
        } else {
            $realEmployeeId = (int) $inputEmployeeId;
        }

        // 2. Pengaman darurat jika data karyawan tidak valid / tidak ditemukan
        if (!$realEmployeeId) {
            return redirect()->back()->with('error', 'Kombinasi ID Karyawan murni tidak ditemukan!');
        }

        // Paksa isi array $data menggunakan ID angka murni agar kebal dari teks string pengganggu
        $data['employee_id'] = $realEmployeeId;

        // 3. Jalankan perintah simpan sakral dengan database menggunakan relasi model PayrollDetail yang lurus!
        \App\Models\PayrollDetail::updateOrCreate(
            [
                'employee_id' => $realEmployeeId,
                'bulan_tahun' => $request->bulan_tahun
            ],
            $data
        );

        return redirect()->back()->with('success', 'Nominal Komponen Payroll Karyawan Berhasil Disimpan!');
    }

        // 3. APT CERDAS: MENYUNTIKKAN DATA ABSENSI HARIAN KE LACI HITAM (SUDAH POTONG 1 JAM ISTIRAHAT)
    public function getDailyAttendance(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $bulanPeriode = $request->input('bulan_periode');

        $attendances = Attendance::where('employee_id', $employeeId)
            ->where('tanggal', 'LIKE', $bulanPeriode . '%')
            ->orderBy('tanggal', 'asc')
            ->get();

        $rows = [];

        foreach ($attendances as $att) {
            $durasiKerja = 0;
            $lembur1 = 0;
            $lembur2 = 0;
            $potongan = 0;

            if ($att->jam_masuk && $att->jam_pulang) {
                $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                $tanggalAbsen = \Carbon\Carbon::parse($att->tanggal); 
                $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                // INTEGRASI SAKRAL HARI MINGGU: Kebal potongan istirahat, JAM KERJA UTAMA WAJIB 0, seluruhnya meluncur ke LEMBUR II!
                if ($tanggalAbsen->isSunday()) {
                    $durasiKerja = 0;            // 🚀 KUNCI KEMENANGAN: Setel 0 jam kerja pokok agar di laci depan otomatis tercetak kosong / strip!
                    $lembur2     = $durasiKotor; // Suku jam kotor hari Minggu seutuhnya dialirkan masuk kuantitas Lembur II murni!
                    $lembur1     = 0;
                    $potongan    = 0;            // Hari Minggu bebas merdeka dari denda potongan jam kerja reguler
                } else {
                    
                    // Logika Hari Biasa Regular (Tetap Membawa Potongan 1 Jam Istirahat Jika di Atas 7 Jam)
                    $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                    if ($durasiKerja < 7) {
                        $potongan = 7 - $durasiKerja;
                    } else {
                        // Perbaikan logika pembagi desimal jam kerja regular harian secara adil
                        $kelebihanJam = $durasiKerja - 7;
                        
                        if ($durasiKerja > 8) {
                            $lembur1 = 1;
                            $lembur2 = $durasiKerja - 8;
                        } else {
                            $lembur1 = $kelebihanJam;
                            $lembur2 = 0;
                        }
                    }
                }
            }

            $rows[] = [
                'tanggal'      => Carbon::parse($att->tanggal)->format('d/m/Y'),
                'jam_masuk'    => $att->jam_masuk ?? '-',
                'jam_pulang'   => $att->jam_pulang ?? '-',
                'durasi_kerja' => round($durasiKerja, 1) . ' Jam',
                'lembur_1'     => $lembur1 > 0 ? round($lembur1, 1) . ' Jam' : '-',
                'lembur_2'     => $lembur2 > 0 ? round($lembur2, 1) . ' Jam' : '-',
                'potongan'     => $potongan > 0 ? round($potongan, 1) . ' Jam' : '-',
            ];
        }

        return response()->json($rows);
    }

        // 4. ANALISIS BIAYA GAJI OPERASIONAL HARIAN (REAL-TIME SMART)
            public function detailPayrollHarian(Request $request)
    {
        // 1. Kunci pencarian ketat HANYA pada bulan yang dipilih di dropdown depan (Default bulan ini)
        $bulanPeriode = $request->get('bulan_period') ?? $request->get('bulan_periode') ?? date('Y-m');
        if (empty($bulanPeriode)) {
            $bulanPeriode = date('Y-m');
        }

        // 2. TAKTIK DEWA KILAT: Ambil data absensi massal sebulan penuh hanya dalam 1 KALI QUERY TUNGGAL
        $allAttendances = \App\Models\Attendance::with(['employee'])
            ->where('tanggal', 'LIKE', "{$bulanPeriode}%")
            ->orderBy('tanggal', 'desc')
            ->get();

        $dailyReports = [];

        if ($allAttendances->isNotEmpty()) {
            // 3. AMBIL MASTER PAYROLL BULANAN SEBAGAI KOLEKSI LOKAL (Kunci Utama Anti-Lemot Setengah Jam!)
            $allPayrolls = \App\Models\PayrollDetail::where('bulan_tahun', $bulanPeriode)
                ->get()
                ->keyBy('employee_id'); // Mengunci data per kepala agar tersedot instan di RAM

            // Ambil daftar tanggal unik khusus bulan ini saja dari memori lokal
            $allDates = $allAttendances->pluck('tanggal')->unique();

            foreach ($allDates as $date) {
                $attendances = $allAttendances->where('tanggal', $date);
                $totalKaryawanMasuk = $attendances->count();
                
                $gajiKelompokLangsung = 0;
                $gajiKelompokTidakLangsung = 0;
                $totalGajiKeseluruhan = 0;

                foreach ($attendances as $att) {
                    $emp = $att->employee;
                    if (!$emp) continue;

                    // SEDOT INSTAN: Mengambil data payroll langsung dari memori koleksi lokal (0,0001 detik murni!)
                    $payroll = $allPayrolls->get($emp->id);
                    if (!$payroll) continue;

                    // SUNTIKAN GLOBAL: Deklarasikan di luar kamar "if" absensi agar kebal dari warning Undefined!
                    $gajiHariIni = $payroll->gaji_perhari;

                    // Logika matematika jam kerja efektif kodingan premium kita kemarin sore
                    if ($att->jam_masuk && $att->jam_pulang) {
                        $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                        $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                        $tanggalAbsen = \Carbon\Carbon::parse($att->tanggal); // Ambil tanggal absensi riil harian
                        
                        $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                        // RUMUS UANG HARI MINGGU REAL: Kebal dari potongan 1 jam istirahat!
                        if ($tanggalAbsen->isSunday()) {
                            // Hari Minggu: Gaji pokok harian adalah 0 (TIDAK terhitung 1 hari kerja)
                            $gajiHariIni = 0; 
                            
                            // Durasi kerja hari minggu murni tanpa potongan istirahat
                            $durasiKerja = $durasiKotor; 
                            
                            // Semua jam kerja di hari Minggu dihitung masuk ke komponen tarif Lembur 2
                            $gajiHariIni = $durasiKerja * $payroll->honor_lembur_2;

                        } else {
                            // Logika Hari Biasa Regular 
                            // 1. GAJI POKOK PER HARI (Nilai dasar sebelum ditambah lembur / dikurangi potongan)
                            $gajiHariIni = $payroll->gaji_perhari; 
                            
                            $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                            // 2. PERHITUNGAN POTONGAN JAM KERJA
                            if ($durasiKerja < 7) {
                                $jamPotongan = 7 - $durasiKerja;
                                // Mengurangi langsung dari gaji pokok hari ini
                                $gajiHariIni -= ($jamPotongan * $payroll->potongan_jam_kerja);
                            } else {
                                // 3. PERHITUNGAN LEMBUR HARI BIASA (Hanya jika kerja >= 7 jam)
                                $kelebihanJam = $durasiKerja - 7;

                                if ($durasiKerja > 8) {
                                    $lembur1Kuantitas = 1;
                                    $lembur2Kuantitas = $durasiKerja - 8;
                                } else {
                                    $lembur1Kuantitas = $kelebihanJam;
                                    $lembur2Kuantitas = 0;
                                }

                                // Menambahkan hasil lembur ke gaji hari ini
                                $gajiHariIni += ($lembur1Kuantitas * $payroll->honor_lembur_1);
                                $gajiHariIni += ($lembur2Kuantitas * $payroll->honor_lembur_2);
                            }
                        }                
                            // Di bawah sini langsung menyambung lurus ke perhitungan tunjangan harian tanpa ada kurung gantung lagi!
                            $gajiHariIni += (($payroll->tunjangan_masa_kerja + $payroll->tunjangan_jabatan + ($payroll->insentif ?? 0)) / 25);
                            $gajiHariIni -= (($payroll->potongan_bpjs_kes + $payroll->potongan_bpjs_tk) / 25);
                            $gajiHariIni = round($gajiHariIni);
                    }

                    if (strtolower($emp->kelompok) == 'langsung') {
                        $gajiKelompokLangsung += $gajiHariIni;
                    } else {
                        $gajiKelompokTidakLangsung += $gajiHariIni;
                    }
                    $totalGajiKeseluruhan += $gajiHariIni;
                }

                $dailyReports[] = [
                    'tanggal' => $date,
                    'total_hadir' => $totalKaryawanMasuk,
                    'gaji_langsung' => $gajiKelompokLangsung,
                    'gaji_tidak_langsung' => $gajiKelompokTidakLangsung,
                    'total_gaji' => $totalGajiKeseluruhan
                ];
            }
        }

        return view('payroll.detail_harian', compact('dailyReports', 'bulanPeriode'));
    }
    
        // 5. AKSI UNTUK MEMPROSES UNGGAL MASSAL NOMINAL GAJI VIA EXCEL (SMART UPSERT SUCI)
    public function importExcel(\Illuminate\Http\Request $request)
    {
       $request->validate([
            'file_excel'   => 'required|mimes:xlsx,xls,csv',
            'bulan_periode' => 'required|string|size:7'
        ]);

        try {
            // 🚀 KUNCI KEMENANGAN IMPORT: Mengalirkan parameter bulan_periode aktif langsung masuk ke konstruktor file PayrollImport!
            $bulanAktif = $request->input('bulan_periode');
            
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\PayrollImport($bulanAktif), 
                $request->file('file_excel')
            );

            return redirect()->back()->with('success', 'Data Komponen Rupiah Payroll Massal Sukses Diimport!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel, pastikan format kolom sesuai: ' . $e->getMessage());
        }
    }

    // 6. AKSI MEMBUAT DAN MENGUNDUH TEMPLATE EXCEL PAYROLL SECARA INSTAN
    public function downloadTemplatePayroll()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_komponen_payroll.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // 🚀 KEMBAR IDENTIK FORMAT BARU: Hanya mencetak 7 nama kolom utama, bersih dari lembur dan denda jam manual!
            fputcsv($file, [
                'ID Karyawan', 
                'Gaji Pokok Perhari', 
                'Tunjangan Jabatan', 
                'Insentif Kerajinan', 
                'Potongan BPJS Kesehatan', 
                'Potongan BPJS Ketenagakerjaan', 
                'Potongan Lainnya'
            ]);

            // Baris contoh baris dummy data masukan murni angka polosan (Layout baru 100% klop)
            fputcsv($file, ['MRSA-001', '97300', '500000', '300000', '65195', '52156', '0']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    // 3. AKSI GENERATE & PRINT OUT SLIP GAJI KARYAWAN INDIVIDUAL (REVISI SAKRAL)
    public function printSlip(\Illuminate\Http\Request $request)
    {
        $employeeId = $request->get('employee_id');
        $bulanTahun = $request->get('bulan_periode') ?? date('Y-m');

        $employee = \App\Models\Employee::where('id', $employeeId)->orWhere('id_karyawan', $employeeId)->first();
        if (!$employee) {
            return redirect()->back()->with('error', 'Data Karyawan tidak ditemukan!');
        }

        $payroll = \App\Models\PayrollDetail::where('employee_id', $employee->id)
            ->where('bulan_tahun', $bulanTahun)
            ->first();

        if (!$payroll) {
            return redirect()->back()->with('error', 'Data Payroll periode ini belum dibuat/disimpan!');
        }

        // Hitung kuantitas Hari Kerja Murni Khusus Non-Minggu untuk dicetak di pojok kanan slip
        $attendances = \App\Models\Attendance::where('employee_id', $employee->id)
            ->where('tanggal', 'LIKE', $bulanTahun . '-%')
            ->get();
            
        // 🚀 DETEKTIF ABSENSI SINKRON STRUK PERORANGAN: Menghitung total jam lembur pecahan desimal secara real-time
        $jumlahHariKerjaMurni = 0;
        $totalJamLembur1 = 0;
        $totalJamLembur2 = 0;
        $totalJamPotongan = 0;

        foreach ($attendances as $att) {
            if ($att->jam_masuk && $att->jam_pulang) {
                $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                $tanggalAbsen = \Carbon\Carbon::parse($att->tanggal);
                
                $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                if ($tanggalAbsen->isSunday()) {
                    $durasiKerja = $durasiKotor;
                    $totalJamLembur2 += $durasiKerja;
                } else {
                    $jumlahHariKerjaMurni++; // 🚀 KUNCI: Menaikkan kuantitas hari kerja murni non-Minggu (21 Hari)

                    $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                    if ($durasiKerja < 7) {
                        $totalJamPotongan += (7 - $durasiKerja);
                    } else {
                        $kelebihanJam = $durasiKerja - 7;
                        if ($durasiKerja > 8) {
                            $totalJamLembur1 += 1;
                            $totalJamLembur2 += ($durasiKerja - 8);
                        } else {
                            $totalJamLembur1 += $kelebihanJam;
                            $totalJamLembur2 += 0;
                        }
                    }
                }
            }
        }
        
        // 🚀 KALKULATOR AKUNTANSI STRUK: Mengalikan tarif dasar database dengan kuantitas hari hadir murni and pecahan desimal jam lembur!
        $gajiPokokTotal     = (float)($payroll->gaji_perhari ?? 0) * $jumlahHariKerjaMurni;
        $honorLembur1Total  = (float)($payroll->honor_lembur_1 ?? 0) * $totalJamLembur1;
        $honorLembur2Total  = (float)($payroll->honor_lembur_2 ?? 0) * $totalJamLembur2; // Pecahan desimal ,5 jam terhitung murni!
        // Hitung nilai dasar tunjangan masa berdasarkan tahun masuk kerja karyawan secara dinamis
        $tunjanganMasaDasar = 0;
                            if (!empty($employee->tanggal_masuk_kerja)) {
                                $masukKerjaDate = \Carbon\Carbon::parse($employee->tanggal_masuk_kerja);
                                $sekarangDate   = \Carbon\Carbon::now();
                                $masaKerjaTahun = $masukKerjaDate->diffInYears($sekarangDate);

                                if ($masaKerjaTahun > 20) { 
                                    $tunjanganMasaDasar = 2550; 
                                } elseif ($masaKerjaTahun >= 15 && $masaKerjaTahun <= 20) { 
                                    $tunjanganMasaDasar = 2200; 
                                } elseif ($masaKerjaTahun >= 10 && $masaKerjaTahun < 15) { 
                                    $tunjanganMasaDasar = 1700; 
                                } elseif ($masaKerjaTahun >= 5 && $masaKerjaTahun < 10) { 
                                    $tunjanganMasaDasar = 1000; 
                                } else { 
                                    $tunjanganMasaDasar = (float)str_replace('.', '', $payroll->tunjangan_masa ?? 0); 
                                }
                            }

        // Kalikan dengan jumlah hari kerja murni agar nilainya sama dengan halaman index
        $totalTunjanganMasaKotor = $tunjanganMasaDasar * $jumlahHariKerjaMurni;

        // Terapkan potongan proproporsional jam jika ada kekurangan jam kerja
        if (isset($totalJamPotongan) && $totalJamPotongan > 0 && $totalTunjanganMasaKotor > 0) {
            $tunjanganPerJam = $tunjanganMasaDasar / 7;
            $totalPotonganTunjanganMasa = $tunjanganPerJam * $totalJamPotongan;
            $tunjanganMasaKerja = $totalTunjanganMasaKotor - $totalPotonganTunjanganMasa;
        } else {
            $tunjanganMasaKerja = $totalTunjanganMasaKotor;
        }
        
        $tunjanganJabatan  = (float)($payroll->tunjangan_jabatan ?? 0);
        $insentifKerajinan = (float)($payroll->insentif ?? 0);
        $bpjsKes            = (float)($payroll->potongan_bpjs_kes ?? 0);
        $bpjsTk             = (float)($payroll->potongan_bpjs_tk ?? 0);
        $potonganJamKerja   = (float)($payroll->potongan_jam_kerja ?? 0) * $totalJamPotongan;
        $potonganLainnya    = (float)($payroll->potongan_lainnya ?? 0);

        // Akumulasi Akhir Total Bersih Berstandar Akuntansi PT MIRASA FOOD INDUSTRY
        $totalPendapatan  = $gajiPokokTotal + $honorLembur1Total + $honorLembur2Total + $tunjanganMasaKerja + $tunjanganJabatan + $insentifKerajinan;
        $totalPotongan    = $bpjsKes + $bpjsTk + $potonganJamKerja + $potonganLainnya;
        $totalGajiBersih  = round($totalPendapatan - $totalPotongan);

        $kelompokKerja = isset($att) ? strtoupper($att->kelompok_kerja_harian) : '-';

        return view('payroll.print', compact(
            'employee', 'payroll', 'bulanTahun', 'jumlahHariKerjaMurni',
            'gajiPokokTotal', 'honorLembur1Total', 'honorLembur2Total', 'tunjanganMasaKerja', 'tunjanganJabatan', 'insentifKerajinan',
            'bpjsKes', 'bpjsTk', 'potonganJamKerja', 'potonganLainnya', 'totalPendapatan', 'totalPotongan', 'totalGajiBersih', 'totalJamLembur1', 'totalJamLembur2', 'totalJamPotongan', 
            'kelompokKerja'
        ));
    }
        // 4. AKSI GENERATE & PRINT OUT MASSAL SELURUH SLIP GAJI KARYAWAN PER BULAN
    public function printAllSlips(\Illuminate\Http\Request $request)
    {
        $bulanTahun = $request->get('bulan_periode') ?? date('Y-m');
        
        $payrolls = \App\Models\PayrollDetail::with('employee')
            ->where('bulan_tahun', $bulanTahun)
            ->get();

        if ($payrolls->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data payroll yang bisa dicetak pada periode ini!');
        }

        $allSlipsData = [];
        $allAttendances = \App\Models\Attendance::where('tanggal', 'LIKE', $bulanTahun . '-%')->get();
        foreach ($payrolls as $payroll) {
            $employee = $payroll->employee;
            if (!$employee) continue;

            // 🚀 DETEKTIF ABSENSI SINKRON MASSAL: Menyaring hari masuk kerja murni khusus non-Minggu & pecahan desimal jam lembur massal
            $attendances = $allAttendances->where('employee_id', $employee->id);
            $jumlahHariKerjaMurni = 0;
            $totalJamLembur1 = 0;
            $totalJamLembur2 = 0;
            $totalJamPotongan = 0;

            foreach ($attendances as $att) {
                if ($att->jam_masuk && $att->jam_pulang) {
                    $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                    $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                    $tanggalAbsen = \Carbon\Carbon::parse($att->tanggal);
                    
                    $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                    if ($tanggalAbsen->isSunday()) {
                        $durasiKerja = $durasiKotor;
                        $totalJamLembur2 += $durasiKerja;
                    } else {
                        $jumlahHariKerjaMurni++; // Menambah kuantitas hari masuk kerja regular harian

                        $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                        if ($durasiKerja < 7) {
                            $totalJamPotongan += (7 - $durasiKerja);
                        } else {
                            $kelebihanJam = $durasiKerja - 7;
                            if ($durasiKerja > 8) {
                                $totalJamLembur1 += 1;
                                $totalJamLembur2 += ($durasiKerja - 8);
                            } else {
                                $totalJamLembur1 += $kelebihanJam;
                                $totalJamLembur2 += 0;
                            }
                        }
                    }
                }
            }

            // 🚀 KALKULATOR AKUNTANSI MASSAL: Mengalikan tarif dasar database dengan variabel kehadiran murni satu pabrik
            $gajiPokokTotal     = (float)($payroll->gaji_perhari ?? 0) * $jumlahHariKerjaMurni;
            $honorLembur1Total  = (float)($payroll->honor_lembur_1 ?? 0) * $totalJamLembur1;
            $honorLembur2Total  = (float)($payroll->honor_lembur_2 ?? 0) * $totalJamLembur2;
            $tunjanganMasaDasar = 0;
                                if (!empty($employee->tanggal_masuk_kerja)) {
                                    $masukKerjaDate = \Carbon\Carbon::parse($employee->tanggal_masuk_kerja);
                                    $sekarangDate   = \Carbon\Carbon::now();
                                    $masaKerjaTahun = $masukKerjaDate->diffInYears($sekarangDate);

                                    if ($masaKerjaTahun > 20) { 
                                        $tunjanganMasaDasar = 2550; 
                                    } elseif ($masaKerjaTahun >= 15 && $masaKerjaTahun <= 20) { 
                                        $tunjanganMasaDasar = 2200; 
                                    } elseif ($masaKerjaTahun >= 10 && $masaKerjaTahun < 15) { 
                                        $tunjanganMasaDasar = 1700; 
                                    } elseif ($masaKerjaTahun >= 5 && $masaKerjaTahun < 10) { 
                                        $tunjanganMasaDasar = 1000; 
                                    } else { 
                                        $tunjanganMasaDasar = (float)str_replace('.', '', $payroll->tunjangan_masa ?? 0); 
                                    }
                                }
            $totalTunjanganMasaKotor = $tunjanganMasaDasar * $jumlahHariKerjaMurni;
                                    if (isset($totalJamPotongan) && $totalJamPotongan > 0 && $totalTunjanganMasaKotor > 0) {
                                        $tunjanganPerJam = $tunjanganMasaDasar / 7;
                                        $tunjanganMasaKerja = $totalTunjanganMasaKotor - ($tunjanganPerJam * $totalJamPotongan);
                                    } else {
                                        $tunjanganMasaKerja = $totalTunjanganMasaKotor;
                                    }
            $tunjanganJabatan   = (float)($payroll->tunjangan_jabatan ?? 0);
            $insentifKerajinan  = (float)($payroll->insentif ?? 0);
            
            $bpjsKes            = (float)($payroll->potongan_bpjs_kes ?? 0);
            $bpjsTk             = (float)($payroll->potongan_bpjs_tk ?? 0);
            $potonganJamKerja   = (float)($payroll->potongan_jam_kerja ?? 0) * $totalJamPotongan;
            $potonganLainnya    = (float)($payroll->potongan_lainnya ?? 0);

            // Akumulasi Akhir Total Bersih Berstandar Akuntansi PT MIRASA FOOD INDUSTRY
            $totalPendapatan = $gajiPokokTotal + $honorLembur1Total + $honorLembur2Total + $tunjanganMasaKerja + $tunjanganJabatan + $insentifKerajinan;
            $totalPotongan   = $bpjsKes + $bpjsTk + $potonganJamKerja + $potonganLainnya;
            $totalGajiBersih = $totalPendapatan - $totalPotongan;

            $allSlipsData[] = [
                'employee'             => $employee,
                'payroll'              => $payroll,
                'jumlahHariKerjaMurni' => $jumlahHariKerjaMurni,
                'gajiPokokTotal'       => $gajiPokokTotal,
                'honorLembur1Total'    => $honorLembur1Total,
                'honorLembur2Total'    => $honorLembur2Total,
                'tunjanganMasaKerja'   => $tunjanganMasaKerja,
                'tunjanganJabatan'     => $tunjanganJabatan,
                'insentifKerajinan'    => $insentifKerajinan,
                'bpjsKes'              => $bpjsKes,
                'bpjsTk'               => $bpjsTk,
                'potonganJamKerja'     => $potonganJamKerja,
                'potonganLainnya'      => $potonganLainnya,
                'totalPendapatan'      => $totalPendapatan,
                'totalPotongan'        => $totalPotongan,
                'totalGajiBersih'      => $totalGajiBersih,
                'totalJamLembur1'      => $totalJamLembur1,
                'totalJamLembur2'      => $totalJamLembur2,
                'totalJamPotongan'     => $totalJamPotongan,
                'kelompokKerja'        => isset($attendances) && $attendances->first() ? strtoupper($attendances->first()->kelompok_kerja_harian) : '-',
            ];
        }

        return view('payroll.print_all', compact('allSlipsData', 'bulanTahun'));
    }

    // 5. PRINT OUT LAPORAN HARIAN BIAYA PAYROLL PABRIK BULANAN
    public function printHarian(\Illuminate\Http\Request $request)
    {
        // 1. Ambil parameter bulan dengan aman tanpa spasi liar
        $bulanPeriode = trim($request->input('bulan_period', $request->input('bulan_periode')));
        if (empty($bulanPeriode)) {
            $bulanPeriode = date('Y-m');
        }

        // 2. KUNCI BULAN SECARA KETAT (Menghilangkan spasi liar pada string LIKE)
        $allAttendances = \App\Models\Attendance::with(['employee'])
            ->where('tanggal', 'LIKE', $bulanPeriode . '%')
            ->orderBy('tanggal', 'desc')
            ->get();

        // 3. Ambil daftar tanggal unik yang ada pada bulan tersebut
        $allDates = $allAttendances->pluck('tanggal')->unique()->toArray();
        sort($allDates); // Mengurutkan tanggal dari yang terkecil (1, 2, 3...)

        // 4. Ambil master data detail gaji berdasarkan periode bulan tahun yang sama
        $allPayrollDetails = \App\Models\PayrollDetail::where('bulan_tahun', $bulanPeriode)
            ->get()
            ->keyBy('employee_id');

        $dailyReports = [];

        // 5. Mulai perulangan data per tanggal secara tunggal (TIDAK DOUBLE)
        foreach ($allDates as $date) {
            $attendances = $allAttendances->where('tanggal', $date);
            $totalKaryawanMasuk = $attendances->count();

            // Tetap menggunakan variabel asli Anda, di-reset setiap tanggal baru
            $gajiKelompokLangsung = 0;
            $gajiKelompokTidakLangsung = 0;
            $totalGajiKeseluruhan = 0;

            $tanggalAbsen = \Carbon\Carbon::parse($date);

            foreach ($attendances as $att) {
                $emp = $att->employee;
                if (!$emp) continue;

                $payroll = $allPayrollDetails->get($emp->id);
                if (!$payroll) continue;

                $gajiHariIni = 0;

                if ($att->jam_masuk && $att->jam_pulang) {
                    $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                    $pulang = \Carbon\Carbon::parse($att->jam_pulang);

                    $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                    // Logika Hari Minggu (Sesuai kesepakatan: murni lembur 2, tanpa gaji pokok harian)
                    if ($tanggalAbsen->isSunday()) {
                        $durasiKerja = $durasiKotor;
                        $gajiHariIni = $durasiKerja * (float)($payroll->honor_lembur_2 ?? 0);
                    } else {
                        // Logika Hari Biasa
                        $gajiHariIni = (float)($payroll->gaji_perhari ?? 0);
                        $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                        // Perhitungan Potongan Jam Kerja (Kurang dari 7 jam)
                        if ($durasiKerja < 7) {
                            $jamPotongan = 7 - $durasiKerja;
                            $gajiHariIni -= ($jamPotongan * (float)($payroll->potongan_jam_kerja ?? 0));
                        } else {
                            // Perhitungan Lembur Hari Biasa
                            if ($durasiKerja > 8) {
                                $lembur1Kuantitas = 1;
                                $lembur2Kuantitas = $durasiKerja - 8;
                            } else {
                                $lembur1Kuantitas = $durasiKerja - 7;
                                $lembur2Kuantitas = 0;
                            }

                            $gajiHariIni += ($lembur1Kuantitas * (float)($payroll->honor_lembur_1 ?? 0));
                            $gajiHariIni += ($lembur2Kuantitas * (float)($payroll->honor_lembur_2 ?? 0));
                        }
                    }

                    // Tambahkan proporsional tunjangan & iuran (Asumsi dibagi rata 25 hari kerja)
                    $tunjanganTotal = ((float)($payroll->tunjangan_masa_kerja ?? 0) +
                    (float)($payroll->tunjangan_jabatan ?? 0) +
                    (float)($payroll->insentif ?? 0)) / 25;

                    $potonganTotal = ((float)($payroll->potongan_bpjs_kes ?? 0) +
                    (float)($payroll->potongan_bpjs_tk ?? 0)) / 25;

                    $gajiHariIni += $tunjanganTotal;
                    $gajiHariIni -= $potonganTotal;
                    $gajiHariIni = round($gajiHariIni);
                }

                // Kelompokkan HPP Langsung vs Overhead (Sesuai kode asli Anda)
                if (strtolower($emp->kelompok) == 'langsung') {
                    $gajiKelompokLangsung += $gajiHariIni;
                } else {
                    $gajiKelompokTidakLangsung += $gajiHariIni;
                }

                $totalGajiKeseluruhan += $gajiHariIni;
            }

            $dailyReports[] = [
                'tanggal'            => $date,
                'total_hadir'        => $totalKaryawanMasuk,
                'gaji_langsung'      => $gajiKelompokLangsung,
                'gaji_tidak_langsung'=> $gajiKelompokTidakLangsung,
                'total_gaji'         => $totalGajiKeseluruhan,
            ];
        }

        return view('payroll.print_laporan_harian', compact('dailyReports', 'bulanPeriode'));
    }

    
    public function MultiPayroll(Request $request)
    {
        // 1. DETEKTIF PERIODE DINAMIS MULTIPAYROLL: Tangkap parameter form aktif yang sedang dibuka admin di browser web!
        $bulanTahun = $request->get('bulan_periode') ?? date('Y-m');

        // SARI DATA PERIODE SAKRAL: Murni menyedot data payroll berdasarkan bulan, kebal dari eror column does not exist!
        $payrollData = \App\Models\PayrollDetail::with('employee')
            ->where('bulan_tahun', $bulanTahun)
            ->get();

        if ($payrollData->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data payroll periode bulan ini untuk diMulti Payroll!');
        }

        // Ambil seluruh data absensi yang murni terikat pada periode bulan laporan aktif yang sedang dibuka
        $allAttendances = \App\Models\Attendance::where('tanggal', 'LIKE', $bulanTahun . '-%')->get();

        // 3. Set Header Download File CSV Asli Berstandar Enkripsi Perbankan Multi KlikBCA
        $filename = "Multi_Payroll_Transfer_" . $bulanTahun . ".csv";

        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $output = fopen("php://output", "w");

        // Tulis Headers Kolom: 100% SINKRON KEMBAR IDENTIK DENGAN KOLOM NO FISIK KLIKBCA
        fputcsv($output, [
            'No',                      // Kolom A (Fisik Nomor Urut)
            'Transaction ID',          // Kolom B: Transaction ID
            'Transfer Type',           // Kolom C: Transfer Type
            'Beneficiary ID',          // Kolom D: Beneficiary ID
            'Credited Account',        // Kolom E: Credited Account
            'Receiver Name',           // Kolom F: Receiver Name
            'Amount',                  // Kolom G: Amount
            'NIP',                     // Kolom H: NIP
            'Remark',                  // Kolom I: Remark
            'Beneficiary email address',
            'Receiver Swift Code',
            'Receiver Cust Type',
            'Receiver Cust Residence'
        ]);

        $datePrefix = date('Ymd');
        $counter = 1;

        // 4. Olah data perulangan karyawan secara otomatis menggeser kolom ke kanan
        foreach ($payrollData as $row) {
            $emp = $row->employee;
            if (!$emp) continue;

            // Saring data absensi khusus karyawan ini dari koleksi memori lokal periode aktif
            $attendances = $allAttendances->where('employee_id', $emp->id)->sortBy('tanggal');
            
            // DETEKTIF ABSENSI SINKRON MASSAL: Menyaring hari masuk kerja murni khusus non-Minggu & pecahan desimal jam lembur massal
            $jumlahHariMasuk = 0;
            $totaljamLembur1 = 0;
            $totaljamLembur2 = 0;
            $totaljamPotongan = 0;

            foreach ($attendances as $att) {
                if ($att->jam_masuk && $att->jam_pulang) {
                    $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                    $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                    $tanggalAbsen = \Carbon\Carbon::parse($att->tanggal);
                    
                    $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                    if ($tanggalAbsen->isSunday()) {
                        $durasiKerja = $durasiKotor;
                        $totaljamLembur2 += $durasiKerja; // Suku jam masuk hari Minggu mengalir penuh ke kuantitas Lembur II murni
                    } else {
                        $jumlahHariMasuk++; // 🚀 KUNCI: Huruf j kecil sesuai inisialisasi awal!

                        $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                        if ($durasiKerja < 7) {
                            $totaljamPotongan += (7 - $durasiKerja);
                        } else {
                            // 🚀 LOGIKA DESIMAL HARIAN ADIL: Membagi pecahan desimal jam lembur harian secara otomatis
                            $kelebihanJam = $durasiKerja - 7;
                            if ($durasiKerja > 8) {
                                $totaljamLembur1 += 1;
                                $totaljamLembur2 += ($durasiKerja - 8);
                            } else {
                                $totaljamLembur1 += $kelebihanJam;
                                $totaljamLembur2 += 0;
                            }
                        }
                    }
                }
            }
            

            // DIRECT MAPPING BERSTANDAR AKUNTANSI MULTIPAYROLL (Mencopot str_replace pembengkak nilai ghaib!)
            $gajiPerhari       = (float)($row->gaji_perhari ?? 0);
            $rateLembur1       = (float)($row->honor_lembur_1 ?? 0);
            $rateLembur2       = (float)($row->honor_lembur_2 ?? 0);
            
            // PERKALIAN AKUNTANSI DIRECT MAPPING: Menyinkronkan pemanggilan nama variabel secara presisi (j kecil!)
            $subtotalGajiPokok  = $gajiPerhari * $jumlahHariMasuk;
            $subtotalLembur1    = $rateLembur1 * $totaljamLembur1;
            $subtotalLembur2    = $rateLembur2 * $totaljamLembur2;
            $potonganJamKerja  = (float)($row->potongan_jam_kerja ?? 0) * $totaljamPotongan;

            // Penentuan Tunjangan Masa Kerja Berdasarkan Aturan Umur Kerja Proyekmu
            $tunjanganMasa = 0;
            $masukKerjaDate = \Carbon\Carbon::parse($emp->tanggal_masuk_kerja);
            $sekarangDate = \Carbon\Carbon::now();
            $masaKerjaTahunan = $masukKerjaDate->diffInYears($sekarangDate);

            if ($masaKerjaTahunan > 20) {
                $tunjanganMasa = 2550;
            } elseif ($masaKerjaTahunan >= 15 && $masaKerjaTahunan <= 20) {
                $tunjanganMasa = 2200;
            } elseif ($masaKerjaTahunan >= 10 && $masaKerjaTahunan < 15) {
                $tunjanganMasa = 1700;
            } elseif ($masaKerjaTahunan >= 5 && $masaKerjaTahunan < 10) {
                $tunjanganMasa = 1000;
            } else {
                $tunjanganMasa = (float)($row->tunjangan_masa_kerja ?? 0);
            }

            // // KALKULATOR BARU: Mengubah nominal tunjangan bulanan menjadi tarif harian dikalikan hari masuk riil!
            // RUMUS SAKRAL SINKRON MULTI PAYROLL: Menghitung pecahan desimal tunjangan masa kerja per jam secara dinamis mengikuti denda KlikBCA!
            $totalTunjanganMasaKotor = $tunjanganMasa * $jumlahHariMasuk;
            
            if (isset($potonganJamKerja) && $potonganJamKerja > 0 && $totalTunjanganMasaKotor > 0) {
                // Ambil total kuantitas jam denda dengan membagi total nominal denda dibagi tarif standar (13900)
                $kuantitasJamPotongan = $potonganJamKerja / 13900;
                $tunjanganPerJam = $tunjanganMasa / 7;
                $tunjanganMasa = $totalTunjanganMasaKotor - ($tunjanganPerJam * $kuantitasJamPotongan);
            } else {
                $tunjanganMasa = $totalTunjanganMasaKotor;
            }

            // Menyedot data komponen baru bersih dari database row tanpa manipulasi string pembengkak
            $tunjanganJabatanReal = (float)($row->tunjangan_jabatan ?? 0);
            $insentifReal         = (float)($row->insentif ?? 0);
            $bpjsKesReal          = (float)($row->potongan_bpjs_kes ?? 0);
            $bpjsTkReal           = (float)($row->potongan_bpjs_tk ?? 0);
            $potonganLainnyaReal  = (float)($row->potongan_lainnya ?? 0);

            // // Kalkulasi Akhir Total Bersih Akuntansi Korporat PT MIRASA FOOD INDUSTRY
            $totalPendapatan = $subtotalGajiPokok + $subtotalLembur1 + $subtotalLembur2 + $tunjanganMasa + $tunjanganJabatanReal + $insentifReal;
            $totalPotongan   = $bpjsKesReal + $bpjsTkReal + $potonganJamKerja + $potonganLainnyaReal;
            $gajiBersihRaw   = $totalPendapatan - $totalPotongan;

            // KUNCI FORMAT ACCOUNTS BCA: Pastikan ketiga variabel ini terpanggil resmi untuk mencetak baris CSV bank!
            $transactionId   = $datePrefix . "10SIA" . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $noRekening      = preg_replace('/[^0-9]/', '', $emp->no_rekening ?? ($emp->nomor_rekening ?? ''));
            $noRekeningRaw   = "\t" . $noRekening;
            
            $namaMentah      = $emp->nama ?? ($emp->nama_karyawan ?? 'KARYAWAN');
            $namaPenerima    = strtoupper(trim($namaMentah));
            $nominalGaji     = number_format($gajiBersihRaw, 2, '.', '');
            $beritaTransfer  = strtoupper(\Carbon\Carbon::parse($bulanTahun . '-01')->translatedFormat('F Y'));

            // Tulis baris datanya lurus berjejer menggunakan variabel yang sudah terpanggil lunas dan menyala terang!
            fputcsv($output, [
                $counter,          // Kolom A: No
                $transactionId,    // Kolom B: Transaction ID
                'BCA',             // Kolom C: Transfer Type
                '',                // Kolom D: Beneficiary ID
                preg_replace('/[^0-9]/', '', $emp->no_rekening ?? ''),    // Kolom E: Credited Account
                strtoupper($emp->nama_karyawan ?? ''),     // Kolom F: Receiver Name
                number_format($gajiBersihRaw, 2, '.', ','),      // Kolom G: Amount (Mengunci desimal sen .00 perbankan KlikBCA!)
                '',                // Kolom H: NIP
                $beritaTransfer    // Kolom I: Remark
            ]);

            $counter++;
        }

        fclose($output);
        exit;
    }
    public function printTahunan(Request $request)
    {
        // // 1. Ambil parameter tahun dari filter dashboard depan secara aman
        $tahun = $request->get('tahun_period') ?? ($request->get('tahun_periode') ?? date('Y'));

        // // 2. TANGKAP DATA ABSENSI MASAL: Tarik absensi karyawan dalam 1 tahun query tunggal
        $allAttendances = \App\Models\Attendance::with(['employee'])
            ->where('tanggal', 'LIKE', $tahun . '-%')
            ->orderBy('tanggal', 'asc')
            ->get();

        if ($allAttendances->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data absensi pada tahun ' . $tahun);
        }

        // Ambil master daftar seluruh data payroll bulanan yang aktif di tahun tersebut
        $allPayrollDetails = \App\Models\PayrollDetail::where('bulan_tahun', 'LIKE', $tahun . '-%')->get();

        // Ambil daftar tanggal unik sepanjang tahun yang ada transaksi hadirnya
        $allDates = $allAttendances->pluck('tanggal')->unique()->toArray();
        sort($allDates);

        $dailyReports = [];

        // // 3. Mulai perulangan buku besar harian sepanjang tahun secara terstruktur (Kloning 100% detailPayrollHarian)
        foreach ($allDates as $date) {
            $attendances = $allAttendances->where('tanggal', $date);
            $totalKaryawanMasuk = $attendances->count();

            $currentBulanTahun = substr($date, 0, 7);
            $tanggalAbsen = \Carbon\Carbon::parse($date);

            $gajiKelompokLangsung = 0;
            $gajiKelompokTidakLangsung = 0;

            foreach ($attendances as $att) {
                $emp = $att->employee;
                if (!$emp) continue;

                $payroll = $allPayrollDetails->where('employee_id', $emp->id)
                    ->where('bulan_tahun', $currentBulanTahun)
                    ->first();

                if (!$payroll) continue;

                // 🚀 DEKLARASI VARIABEL AWAL HARIAN (SINKRON 100% GAMBAR 5 BARIS 357)
                $gajiHariIni = $payroll->gaji_perhari;

                if ($att->jam_masuk && $att->jam_pulang) {
                    $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                    $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                    
                    $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                    // 🚀 RUMUS UANG HARI MINGGU REAL (SINKRON 100% GAMBAR 5 BARIS 368)
                    if ($tanggalAbsen->isSunday()) {
                        $gajiHariIni = 0;
                        $durasiKerja = $durasiKotor;
                        $gajiHariIni = $durasiKerja * $payroll->honor_lembur_2;
                    } else {
                        // 🚀 LOGIKA HARI BIASA REGULAR (SINKRON 100% GAMBAR 5 BARIS 379)
                        $gajiHariIni = $payroll->gaji_perhari;
                        $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                        // Perhitungan Potongan Jam Kerja (SINKRON 100% GAMBAR 5 BARIS 385)
                        if ($durasiKerja < 7) {
                            $jamPotongan = 7 - $durasiKerja;
                            $gajiHariIni -= ($jamPotongan * $payroll->potongan_jam_kerja);
                        } else {
                            // Perhitungan Lembur Jam Kerja (SINKRON 100% GAMBAR 5 BARIS 391)
                            $kelebihanJam = $durasiKerja - 7;
                            if ($durasiKerja > 8) {
                                $lembur1Kuantitas = 1;
                                $lembur2Kuantitas = $durasiKerja - 8;
                            } else {
                                $lembur1Kuantitas = $kelebihanJam;
                                $lembur2Kuantitas = 0;
                            }

                            $gajiHariIni += ($lembur1Kuantitas * $payroll->honor_lembur_1);
                            $gajiHariIni += ($lembur2Kuantitas * $payroll->honor_lembur_2);
                        }
                    }
                }

                // 🚀 SUNTIKAN PROPORSI TUNJANGAN & POTONGAN HARIAN (SINKRON 100% GAMBAR 2 BARIS 407)
                $gajiHariIni += (($payroll->tunjangan_masa_kerja + $payroll->tunjangan_jabatan + ($payroll->insentif ?? 0)) / 25);
                $gajiHariIni -= (($payroll->potongan_bpjs_kes + $payroll->potongan_bpjs_tk + $payroll->potongan_lainnya) / 25);
                $gajiHariIni  = round($gajiHariIni);

                // Akumulasikan ke kelompok kerja masing-masing harian (SINKRON 100% GAMBAR 2 BARIS 413)
                if (strtolower($emp->kelompok) == 'langsung') {
                    $gajiKelompokLangsung += $gajiHariIni;
                } else {
                    $gajiKelompokTidakLangsung += $gajiHariIni;
                }
            }

            $totalGajiKeseluruhan = $gajiKelompokLangsung + $gajiKelompokTidakLangsung;

            $dailyReports[$date] = [
                'tanggal'            => $date,
                'total_hadir'        => $totalKaryawanMasuk, // Mengunci angka murni numerik kebal eror non-numeric!
                'gaji_langsung'      => $gajiKelompokLangsung,
                'gaji_tidak_langsung'=> $gajiKelompokTidakLangsung,
                'total_gaji'         => $totalGajiKeseluruhan,
            ];
        }

        // Urutkan tanggal dari yang terkecil agar runtut rapi dari Januari - Desember
        ksort($dailyReports);

        return view('payroll.print_laporan_tahunan', compact('dailyReports', 'tahun'));

    }
    
        public function exportDetailExcel(Request $request)
    {
        // 1. DETEKSI PERIODE DINAMIS: Menyerap parameter bulan_tahun aktif yang sedang dibuka admin di browser web
        $bulanTahun = $request->get('bulan_tahun') ?? date('Y-m');

        // 2. CARI DATA PERIODE: Hanya menyedot data payroll_details yang murni terdaftar di bulan yang sedang dibuka saja!
        $payrollData = \App\Models\PayrollDetail::with('employee')
            ->where('bulan_tahun', $bulanTahun)
            ->get();

        if ($payrollData->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data payroll periode ini untuk di-export!');
        }

        // Ambil data absensi yang murni terikat pada periode bulan laporan aktif yang sedang dibuka
        $allAttendances = \App\Models\Attendance::where('tanggal', 'LIKE', $bulanTahun . '-%')->get();

        // 3. Set Header File Excel CSV Amankan Kembali Formatnya
        $filename = "Detail_Laporan_Payroll_" . $bulanTahun . ".csv";
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $output = fopen("php://output", "w");

        // Tulis Headers Kolom Laporan Akuntansi Pabrik PT MIRASA FOOD INDUSTRY (Layout murni angka & formula)
        fputcsv($output, [
            'No', 'ID Karyawan', 'Nama Karyawan', 'Jumlah Hari Masuk', 'Jumlah Hari Kerja Murni',
            'Total Lembur I (Jam)', 'Total Lembur II (Jam)', 'Potongan Jam Kerja (Jam)',
            'Gaji Pokok Perhari', 'Honor Lembur I', 'Honor Lembur II', 'Tunjangan Masa Kerja', 'Tunjangan Jabatan', 'Insentif Kerajinan', 'Total Pendapatan (Formula)',
            'Potongan BPJS Kesehatan', 'Potongan BPJS Ketenagakerjaan', 'Potongan Jam Kerja (Formula)', 'Potongan Lainnya Kasbon', 'Total Potongan (Formula)', 'Total Gaji Bersih (Formula)'
        ]);

        $counter = 1;
        $grandTotalGajiBersih = 0;

        // 4. Olah data di memori lokal array agar loading super kilat 0.1 detik murni
        foreach ($payrollData as $row) {
            $emp = $row->employee;
            if (!$emp) continue;

            // Saring data absensi khusus karyawan ini dari koleksi memori lokal
            $attendances = $allAttendances->where('employee_id', $emp->id)->sortBy('tanggal');

            // HITUNG HARI KERJA MURNI: Hari Minggu libur resmi
            $jumlahHariMasuk = 0;
            $jumlahHariKerjaMurni = 0;
            $totalLembur1 = 0;
            $totalLembur2 = 0;
            $totalKekuranganJam = 0;

            foreach ($attendances as $att) {
                if ($att->jam_masuk && $att->jam_pulang) {
                    $jumlahHariMasuk++;
                    $masuk        = \Carbon\Carbon::parse($att->jam_masuk);
                    $pulang       = \Carbon\Carbon::parse($att->jam_pulang);
                    $tanggalAbsen = \Carbon\Carbon::parse($att->tanggal);

                    $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                    if ($tanggalAbsen->isSunday()) {
                        $durasiKerja = $durasiKotor;
                        $totalLembur2 += $durasiKerja;
                    } else {
                        $jumlahHariKerjaMurni++;

                        $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                        if ($durasiKerja < 7) {
                            $totalKekuranganJam += (7 - $durasiKerja);
                        } else {
                            $kelebihanJam = $durasiKerja - 7;
                            if ($durasiKerja > 8) {
                                $totalLembur1 += 1;
                                $totalLembur2 += ($durasiKerja - 8);
                            } else {
                                $totalLembur1 += $kelebihanJam;
                            }
                        }
                    }
                }
            }

            // AMBIL NILAI NOMINAL RUPIAH ASLI DATABASE
            $gajiPerHari = (float)($row->gaji_perhari ?? 0);
            $rateLembur1 = (float)($row->honor_lembur_1 ?? 0);
            $rateLembur2 = (float)($row->honor_lembur_2 ?? 0);

            // Penentuan Tunjangan Masa Kerja Berdasarkan Aturan Umur Kerja
            $tunjanganMasaDasar = 0;
            if (!empty($emp->tanggal_masuk_kerja)) {
                $masukKerjaDate = \Carbon\Carbon::parse($emp->tanggal_masuk_kerja);
                $sekarangDate   = \Carbon\Carbon::now();
                $masaKerjaTahun = $masukKerjaDate->diffInYears($sekarangDate);

                if ($masaKerjaTahun > 20) {
                    $tunjanganMasaDasar = 2550;
                } elseif ($masaKerjaTahun >= 15 && $masaKerjaTahun <= 20) {
                    $tunjanganMasaDasar = 2200;
                } elseif ($masaKerjaTahun >= 10 && $masaKerjaTahun < 15) {
                    $tunjanganMasaDasar = 1700;
                } elseif ($masaKerjaTahun >= 5 && $masaKerjaTahun < 10) {
                    $tunjanganMasaDasar = 1000;
                } else {
                    $tunjanganMasaDasar = (float)str_replace('.', '', $row->tunjangan_masa ?? 0);
                }
            }

            $totalTunjanganMasaKotor = $tunjanganMasaDasar * $jumlahHariKerjaMurni;

            if (isset($totalKekuranganJam) && $totalKekuranganJam > 0 && $totalTunjanganMasaKotor > 0) {
                $tunjanganPerJam = $tunjanganMasaDasar / 7;
                $totalPotonganTunjanganMasa = $tunjanganPerJam * $totalKekuranganJam;
                $tunjanganMasaKerja = round($totalTunjanganMasaKotor - $totalPotonganTunjanganMasa);
            } else {
                $tunjanganMasaKerja = round($totalTunjanganMasaKotor);
            }

            // Ambil data nominal murni komponen lainnya
            $tunjanganJab = (float)($row->tunjangan_jabatan ?? 0);
            $insentif     = (float)($row->insentif ?? 0);
            $bpjsKes      = (float)($row->potongan_bpjs_kes ?? 0);
            $bpjsTk       = (float)($row->potongan_bpjs_tk ?? 0);
            $potonganLain = (float)($row->potongan_lainnya ?? 0);

            // Perhitungan Gaji Bersih internal PHP untuk Grand Total
            $subtotalGajiPokok = $gajiPerHari * $jumlahHariKerjaMurni;
            $subtotalLembur1   = $rateLembur1 * $totalLembur1;
            $subtotalLembur2   = $rateLembur2 * $totalLembur2;
            $subtotalDendaJam  = (float)($row->potongan_jam_kerja ?? 0) * $totalKekuranganJam;

            $totalPendapatanPHP = $subtotalGajiPokok + $subtotalLembur1 + $subtotalLembur2 + $tunjanganMasaKerja + $tunjanganJab + $insentif;
            $totalPotonganPHP   = $bpjsKes + $bpjsTk + $subtotalDendaJam + $potonganLain;
            $gajiBersihPHP      = round($totalPendapatanPHP - $totalPotonganPHP);

            $grandTotalGajiBersih += $gajiBersihPHP;

            // Koordinat Baris Excel (Header = Baris 1, Data pertama = Baris 2)
            $rowNum = $counter + 1;

            // Tulis baris rincian data karyawan (Murni angka tanpa teks statis "Rp" / "Jam")
            fputcsv($output, [
                $counter,
                $emp->id_karyawan,
                strtoupper($emp->nama_karyawan ?? ''),
                $jumlahHariMasuk,          // Kolom D: Jumlah Hari Masuk Riil
                $jumlahHariKerjaMurni,     // Kolom E: Jumlah Hari Kerja Murni
                $totalLembur1,             // Kolom F: Total Jam Lembur I
                round($totalLembur2, 1),   // Kolom G: Total Jam Lembur II
                round($totalKekuranganJam, 1), // Kolom H: Potongan Jam Kerja
                $gajiPerHari,              // Kolom I: Tarif Gaji Perhari
                $rateLembur1,              // Kolom J: Tarif Lembur 1
                $rateLembur2,              // Kolom K: Tarif Lembur 2
                $tunjanganMasaKerja,       // Kolom L: Tunjangan Masa Kerja
                $tunjanganJab,             // Kolom M: Tunjangan Jabatan
                $insentif,                 // Kolom N: Insentif Kerajinan
                
                // --- FORMULA RUMUS MATEMATIKA EXCEL ---
                "= (E{$rowNum} * I{$rowNum}) + (F{$rowNum} * J{$rowNum}) + (G{$rowNum} * K{$rowNum}) + L{$rowNum} + M{$rowNum} + N{$rowNum}", // Kolom O (Total Pendapatan)

                $bpjsKes,                  // Kolom P: BPJS Kesehatan
                $bpjsTk,                   // Kolom Q: BPJS TK
                (float)($row->potongan_jam_kerja ?? 0) * $totalKekuranganJam, // Kolom R: Potongan Jam Kerja
                $potonganLain,             // Kolom S: Potongan Lainnya Kasbon
                
                "= P{$rowNum} + Q{$rowNum} + R{$rowNum} + S{$rowNum}", // Kolom T (Total Potongan)
                "= O{$rowNum} - T{$rowNum}" // Kolom U (Total Gaji Bersih)
            ]);

            $counter++;
        }

        // Hitung batas baris awal dan akhir data untuk rumus SUM di Excel
        // Data dimulai dari baris ke-2 (U2), dan baris akhir adalah koordinat baris sebelum Grand Total ($counter)
        $barisAkhirData = $counter; 

        // BARIS AKUMULASI GRAND TOTAL DI BAGIAN PALING BAWAH TABEL
        fputcsv($output, [
            'GRAND TOTAL', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', "=SUM(U2:U{$barisAkhirData})"
        ]);

        fclose($output);
        exit;
    }

    public function exportBcaCsv(Request $request)
    {
        // 🚀 1. KUNCI PERIODE SEJATI KLIKBCA: Memaksa backend menangkap parameter bulan_periode aktif dari browser web!
        $bulanPeriode = $request->input('bulan_periode') ?? ($request->input('bulan_tahun') ?? date('Y-m'));
        $tanggalTransfer = date('Ymd');
        $bulanLaporanText = strtoupper(date('F Y', strtotime($bulanPeriode)));

        $corporateId = 'IBSMIRASAF';
        $sourceAccount = '1049173332';

        $outlines = [];
        $counter = 1;

        // // 2. KUNCI UTAMA: Loop berdasarkan PayrollDetail bulan aktif (Sama persis dengan web)
        $payrollData = \App\Models\PayrollDetail::where('bulan_tahun', $bulanPeriode)->get();

        foreach ($payrollData as $row) {
            // Ambil data employee asli berdasarkan relasi employee_id di baris payroll ini
            $emp = \App\Models\Employee::find($row->employee_id);
            if (!$emp) continue;

            // Ambil data absensi bulanan dari employee terkait periode berjalan
            $attendances = \App\Models\Attendance::where('employee_id', $emp->id)
                ->where('tanggal', 'LIKE', $bulanPeriode . '-%')
                ->get();

            // 🚀 INTEGRASI KUANTITAS ABSENSI: Menyeleraskan nama variabel secara lurus agar terbaca lunas di rumus bawah!
            $jumlahHariMasuk = 0;
            $jumlahHariKerjaMurni = 0;
            $totalJamLembur1 = 0;
            $totalJamLembur2 = 0;
            $totalJamPotongan = 0;

            foreach ($attendances as $att) {
                if ($att->jam_masuk && $att->jam_pulang) {
                    $jumlahHariMasuk++;
                    $masuk        = \Carbon\Carbon::parse($att->jam_masuk);
                    $pulang       = \Carbon\Carbon::parse($att->jam_pulang);
                    $tanggalAbsen = \Carbon\Carbon::parse($att->tanggal);

                    $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                    if ($tanggalAbsen->isSunday()) {
                        $durasiKerja = $durasiKotor;
                        $totalJamLembur2 += $durasiKerja;
                    } else {
                        $jumlahHariKerjaMurni++;

                        $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                        if ($durasiKerja < 7) {
                            $totalJamPotongan += (7 - $durasiKerja);
                        } else {
                            $kelebihanJam = $durasiKerja - 7;
                            if ($durasiKerja > 8) {
                                $totalJamLembur1 += 1;
                                $totalJamLembur2 += ($durasiKerja - 8);
                            } else {
                                $totalJamLembur1 += $kelebihanJam;
                            }
                        }
                    }
                }
            }

            // AMBIL TARIF NOMINAL RUPIAH ASLI DATABASE DETAIL PAYROLL
            $gajiPerHari     = (float)($row->gaji_perhari ?? 0);
            $rateJamLembur1  = (float)($row->honor_lembur_1 ?? 0);
            $rateJamLembur2  = (float)($row->honor_lembur_2 ?? 0);
            $potonganJamKerja = (float)($row->potongan_jam_kerja ?? 0);

            // PERKALIAN AKUNTANSI DIRECT MAPPING
            $subtotalGajiPokok = $gajiPerHari * $jumlahHariKerjaMurni;
            $subtotalLembur1   = $rateJamLembur1 * $totalJamLembur1;
            $subtotalLembur2   = $rateJamLembur2 * $totalJamLembur2;
            $subtotalDendaJam  = $potonganJamKerja * $totalJamPotongan;

            // Penentuan Tunjangan Masa Kerja Berdasarkan Aturan Umur Kerja Proyekmu
            $tunjanganMasa = 0;
            if (!empty($emp->tanggal_masuk_kerja)) {
                $masukKerjaDate = \Carbon\Carbon::parse($emp->tanggal_masuk_kerja);
                $sekarangDate   = \Carbon\Carbon::now();
                $masaKerjaTahun = $masukKerjaDate->diffInYears($sekarangDate);

                if ($masaKerjaTahun > 20) {
                    $tunjanganMasa = 2550;
                } elseif ($masaKerjaTahun >= 15 && $masaKerjaTahun <= 20) {
                    $tunjanganMasa = 2200;
                } elseif ($masaKerjaTahun >= 10 && $masaKerjaTahun < 15) {
                    $tunjanganMasa = 1700;
                } elseif ($masaKerjaTahun >= 5 && $masaKerjaTahun < 10) {
                    $tunjanganMasa = 1000;
                } else {
                    $tunjanganMasa = (float)str_replace('.', '', $row->tunjangan_masa ?? 0);
                }
            }

            // KALKULATOR BARU: Mengubah nominal tunjangan bulanan menjadi tarif harian dikalikan hari masuk riil!
            $totalTunjanganMasaKotor = $tunjanganMasa * $jumlahHariKerjaMurni;
        
            if (isset($totalJamPotongan) && $totalJamPotongan > 0 && $totalTunjanganMasaKotor > 0) {
                $tunjanganPerJam = $tunjanganMasa / 7;
                $tunjanganMasaKerja = $totalTunjanganMasaKotor - ($tunjanganPerJam * $totalJamPotongan);
            } else {
                $tunjanganMasaKerja = $totalTunjanganMasaKotor;
            }

            // Menyedot data bersih dari boks row update terbaru modal depan kamu, sayang!
            $tunjanganJab = (float)($row->tunjangan_jabatan ?? 0);
            $insentif     = (float)($row->insentif ?? 0);
            $bpjsKes      = (float)($row->potongan_bpjs_kes ?? 0);
            $bpjsTk       = (float)($row->potongan_bpjs_tk ?? 0);
            $potonganLain = (float)($row->potongan_lainnya ?? 0);

            // 3. RUMUS MATEMATIKA ESSENSIAL PAYROLL (Kalkulasi akumulasi bulanan PT MIRASA FOOD INDUSTRY)
            $totalPendapatan = $subtotalGajiPokok + $subtotalLembur1 + $subtotalLembur2 + $tunjanganMasaKerja + $tunjanganJab + $insentif;
            $totalPotongan   = $bpjsKes + $bpjsTk + $subtotalDendaJam + $potonganLain;
            $gajiBersihPersonil = round($totalPendapatan - $totalPotongan);

            // 🚀 TAMENG PENYARING MEMORI SAKRAL BCA: Jika karyawan gajinya masih 0 atau minus, otomatis DEPAK/LEWATI dari daftar transfer bank!
            if ($gajiBersihPersonil <= 0) {
                continue;
            }

            // FORMAT BERSIH UNTUK KLIKBCA (OTOMATIS .00 TANPA MERUSAK NILAI)
            $gajiFormatBca = number_format($gajiBersihPersonil, 2, '.', '');

            // Buat nomor referensi transaksi unik perorangan (MEMPERBAIKI ERROR BARIS 1159)
            $idRefPad = str_pad($counter, 2, '0', STR_PAD_LEFT);
            $noReferensiBca = "0000" . date('Ymd') . "AC" . $idRefPad;

            // 1. PENCARIAN BERLAPIS: Ambil nomor rekening dari kolom yang tersedia di tabel employee atau payroll
            $norekMentah = $emp->no_rekening;
            
            // 2. Bersihkan semua karakter titik, koma, spasi, atau minus agar murni angka saja
            $rekeningKaryawan = str_replace(['.', ',', '-', ' '], '', $norekMentah);
            
            // 3. Ambil Nama Karyawan format huruf besar semua
            $namaMentahed = $emp->nama_karyawan ?? $row->nama_karyawan ?? $emp->nama_lengkap ?? 'KARYAWAN';
            $namaKaryawanUpper = strtoupper(trim($namaMentahed));

            // Format Teks Baris KlikBCA Bisnis
            $detailline = "1|{$noReferensiBca}|BCA||{$rekeningKaryawan}|{$namaKaryawanUpper}|{$gajiFormatBca}||{$bulanLaporanText}||||";
            
            $outlines[] = $detailline;
            $counter++;
        }

        // 1. Ambil baris payroll bulan aktif ini
        $lastPayroll = \App\Models\PayrollDetail::where('bulan_tahun', $bulanPeriode)->first();
        
        // Ambil urutan angka bulan saat ini (Contoh: "2026-07" akan diambil angka bulannya saja yaitu 7)
        $angkaBulanAktif = (int) date('m', strtotime($bulanPeriode)); 

        // RUMUS PAYROLL BULANAN: Misal nilai awal dasar perusahaan Anda dimulai dari angka 30
        // Jika bulan Juli (7), maka nomor file otomatis dikunci di angka: 30 + 7 = 37.
        // Berapapun kali Anda download di bulan Juli, nilainya akan selalu tetap 37!
        // Ketika masuk bulan Agustus (8), otomatis tanpa diubah kodingannya akan naik sendiri menjadi 38.
        $noFileBaru = 30 + $angkaBulanAktif; 

        // Update nilai terbaru ke database sebagai catatan arsip pelaporan (Opsional)
        if ($lastPayroll) {
            $lastPayroll->nomor_file_bca = $noFileBaru;
            $lastPayroll->save();
        }

        // 2. Format angka menjadi 8 digit dengan tambahan nol di depan (Hasil: 00000037)
        $noFilePad = str_pad($noFileBaru, 8, '0', STR_PAD_LEFT);

        // 3. Susun susunan baris HEADER UTAMA (Baris 0)
        $totalRecordLolos = count($outlines);
        $totalKaryawanPad = str_pad($totalRecordLolos, 8, '0', STR_PAD_LEFT); 
        
        // // 3. Susun susunan baris HEADER UTAMA (Baris 0) - DINAMIS FORMAT 5 DIGIT KEMBAR CONTOH BANK!
        $totalRecordLolos = count($outlines); // Menghitung riil jumlah karyawan yang gajinya > 0
        $totalKaryawanPad = str_pad($totalRecordLolos, 5, '0', STR_PAD_LEFT); // Mengubah angka 4 menjadi '00004', atau 290 menjadi '00290' secara otomatis!

        // Suntikkan variabel $totalKaryawanPad untuk mendepak angka hardcode 00054 lama!
        $headerline = "0|PY|{$corporateId}|01040276||{$tanggalTransfer}||{$sourceAccount}|{$totalKaryawanPad}|AFKIR|{$bulanLaporanText}";

        // Sisipkan baris header utama ke posisi paling atas teks dokumen
        array_unshift($outlines, $headerline);

        // Gabungkan semua baris teks dengan line-break Windows Txt (\r\n)
        $csvContent = implode("\r\n", $outlines);

        $filename = date('Ymd') . '-PAYROLL-BCA-' . $corporateId . '.txt';

        return response($csvContent)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    public function gajiKelompokHarian(Request $request)
    {
        // 1. Ambil Parameter Filter Input dari Form Web
    $tipeFilter = $request->get('tipe_filter', 'bulanan'); 
    $bulanFilter = $request->get('bulan_period', date('Y-m')); 
    $tahunFilter = $request->get('tahun_period', date('Y'));    

    // 2. Tentukan Rentang Query Absensi Berdasarkan Pilihan Jenis Analisis
    $query = \App\Models\Attendance::with(['employee']);
    
    if ($tipeFilter == 'tahunan') {
        $query->whereYear('tanggal', $tahunFilter);
    } else {
        $query->where('tanggal', 'LIKE', "{$bulanFilter}%");
    }
    
    $allAttendances = $query->orderBy('tanggal', 'asc')->get();
    $rekapHarian = [];

    if ($allAttendances->isNotEmpty()) {
        // Ambil data detail payroll bulanan koleksi lokal
        $allPayrolls = \App\Models\PayrollDetail::where('bulan_tahun', 'LIKE', ($tipeFilter == 'tahunan' ? "{$tahunFilter}-%" : "{$bulanFilter}%"))
            ->get()
            ->keyBy('employee_id');

        foreach ($allAttendances as $att) {
            $date = $att->tanggal;
            $emp  = $att->employee;
            if (!$emp) continue;

            $payroll = $allPayrolls->get($emp->id);
            if (!$payroll) continue;

            // KUNCI INTI: Jika tahunan kelompokkan berdasarkan Bulan (YYYY-MM), jika bulanan berdasarkan Tanggal Penuh (YYYY-MM-DD)
            $groupKey = $tipeFilter == 'tahunan' ? substr($date, 0, 7) : $date;

            // Inisialisasi struktur wadah array jika belum ada
            if (!isset($rekapHarian[$groupKey])) {
                $rekapHarian[$groupKey] = [
                    'tanggal'              => $groupKey,
                    'IFM_orang'            => 0, 'IFM_gaji' => 0,
                    'MANUAL_orang'         => 0, 'MANUAL_gaji' => 0,
                    'PACKING_orang'        => 0, 'PACKING_gaji' => 0,
                    'TIDAK_LANGSUNG_orang' => 0, 'TIDAK_LANGSUNG_gaji' => 0,
                    'TOTAL_hari_orang'     => 0, 'TOTAL_hari_gaji' => 0,
                ];
            }

            // --- REPLIKA RUMUS MATEMATIKA SINKRON HARIAN TANPA BPJS ---
            $gajiHariIni = (float)($payroll->gaji_perhari ?? 0);
            $durasiKotor = 0; $kuantitasLembur1 = 0; $kuantitasLembur2 = 0; $potonganJam = 0;

            if ($att->jam_masuk && $att->jam_pulang) {
                $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                $tanggalAbsen = \Carbon\Carbon::parse($date);
                $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                if ($tanggalAbsen->isSunday()) {
                    $gajiHariIni = 0;
                    $kuantitasLembur2 = $durasiKotor;
                } else {
                    if ($durasiKotor > 7) {
                        $durasiKerja = $durasiKotor - 1;
                        if ($durasiKerja < 7) { $durasiKerja = 7; }
                        $kelebihanJam = $durasiKerja - 7;
                        if ($durasiKerja > 8) {
                            $kuantitasLembur1 = 1; $kuantitasLembur2 = $durasiKerja - 8;
                        } else {
                            $kuantitasLembur1 = $kelebihanJam; $kuantitasLembur2 = 0;
                        }
                    } else {
                        $durasiKerja = $durasiKotor;
                        if ($durasiKerja < 7) { $potonganJam = 7 - $durasiKerja; }
                    }
                }
            }

            $rateJamLembur1 = (float)($payroll->honor_lembur_1 ?? 0);
            $rateJamLembur2 = (float)($payroll->honor_lembur_2 ?? 0);
            $tarifDendaJam  = (float)($payroll->potongan_jam_kerja ?? 0);

            $honorLembur1Harian = $kuantitasLembur1 * $rateJamLembur1;
            $honorLembur2Harian = $kuantitasLembur2 * $rateJamLembur2;
            $potonganJamHarian   = $potonganJam * $tarifDendaJam;

            $tunjanganMasaHarian    = (float)($payroll->tunjangan_masa_kerja ?? 0);
            $tunjanganJabatanHarian = (float)($payroll->tunjangan_jabatan ?? 0) / 25;
            $insentifKerjanHarian   = (float)($payroll->insentif ?? 0) / 25;

            $totalPendapatanHarian = $gajiHariIni + $honorLembur1Harian + $honorLembur2Harian + $tunjanganMasaHarian + $tunjanganJabatanHarian + $insentifKerjanHarian;
            $gajiHarianKaryawan = round($totalPendapatanHarian - $potonganJamHarian);

            $kelompok = strtoupper($att->kelompok_kerja_harian ?? 'TIDAK LANGSUNG');

            if (str_contains($kelompok, 'IFM')) {
                $rekapHarian[$groupKey]['IFM_orang']++;
                $rekapHarian[$groupKey]['IFM_gaji'] += $gajiHarianKaryawan;
            } elseif (str_contains($kelompok, 'MANUAL')) {
                $rekapHarian[$groupKey]['MANUAL_orang']++;
                $rekapHarian[$groupKey]['MANUAL_gaji'] += $gajiHarianKaryawan;
            } elseif (str_contains($kelompok, 'PACKING')) {
                $rekapHarian[$groupKey]['PACKING_orang']++;
                $rekapHarian[$groupKey]['PACKING_gaji'] += $gajiHarianKaryawan;
            } else {
                $rekapHarian[$groupKey]['TIDAK_LANGSUNG_orang']++;
                $rekapHarian[$groupKey]['TIDAK_LANGSUNG_gaji'] += $gajiHarianKaryawan;
            }

            if (str_contains($kelompok, 'IFM') || str_contains($kelompok, 'MANUAL') || str_contains($kelompok, 'PACKING')) {
                $rekapHarian[$groupKey]['TOTAL_hari_orang']++;
                $rekapHarian[$groupKey]['TOTAL_hari_gaji'] += $gajiHarianKaryawan;
            }
        }
    }

    krsort($rekapHarian);
    return view('payroll.kelompok_harian', compact('rekapHarian', 'bulanFilter', 'tahunFilter', 'tipeFilter'));
    }

    public function printKelompokHarian(Request $request)
    {
        // 1. Tangkap parameter periode bulan aktif dari filter halaman sebelumnya
        $bulanPeriode = trim($request->input('bulan_period', $request->get('bulan_period')));
        if (empty($bulanPeriode)) {
            $bulanPeriode = date('Y-m');
        }

        // // 2. Tarik riwayat absensi bulanan massal
        $allAttendances = \App\Models\Attendance::with(['employee'])
            ->where('tanggal', 'LIKE', "{$bulanPeriode}%")
            ->orderBy('tanggal', 'asc')
            ->get();

        $dailyReports = [];

        if ($allAttendances->isNotEmpty()) {
            $allPayrolls = \App\Models\PayrollDetail::where('bulan_tahun', $bulanPeriode)
                ->get()
                ->keyBy('employee_id');

            $allDates = $allAttendances->pluck('tanggal')->unique()->toArray();
            sort($allDates);

            foreach ($allDates as $date) {
                $attendances = $allAttendances->where('tanggal', $date);
                $totalKaryawanMasuk = $attendances->count();

                $gajiKelompokLangsung = 0;
                $gajiKelompokTidakLangsung = 0;

                foreach ($attendances as $att) {
                    $emp = $att->employee;
                    if (!$emp) continue;

                    $payroll = $allPayrolls->get($emp->id);
                    if (!$payroll) continue;

                    // Awal upah harian dasar
                    $gajiHariIni = (float)($payroll->gaji_perhari ?? 0);
                    $durasiKotor = 0;
                    $kuantitasLembur1 = 0;
                    $kuantitasLembur2 = 0;
                    $potonganJam = 0;

                    if ($att->jam_masuk && $att->jam_pulang) {
                        $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                        $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                        $tanggalAbsen = \Carbon\Carbon::parse($date);
                        $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                        if ($tanggalAbsen->isSunday()) {
                            $gajiHariIni = 0; // Hari Minggu hangus, ganti lembur penuh
                            $kuantitasLembur2 = $durasiKotor;
                        } else {
                            if ($durasiKotor > 7) {
                                $durasiKerja = $durasiKotor - 1; // Potong 1 jam istirahat
                                if ($durasiKerja < 7) {
                                    $durasiKerja = 7;
                                }
                                $kelebihanJam = $durasiKerja - 7;

                                if ($durasiKerja > 8) {
                                    $kuantitasLembur1 = 1;
                                    $kuantitasLembur2 = $durasiKerja - 8;
                                } else {
                                    $kuantitasLembur1 = $kelebihanJam;
                                    $kuantitasLembur2 = 0;
                                }
                            } else {
                                $durasiKerja = $durasiKotor;
                                if ($durasiKerja < 7) {
                                    $potonganJam = 7 - $durasiKerja;
                                }
                            }
                        }
                    }

                    // Ambil komponen dari database payroll
                    $rateJamLembur1 = (float)($payroll->honor_lembur_1 ?? 0);
                    $rateJamLembur2 = (float)($payroll->honor_lembur_2 ?? 0);
                    $tarifDendaJam  = (float)($payroll->potongan_jam_kerja ?? 0);

                    $honorLembur1Harian = $kuantitasLembur1 * $rateJamLembur1;
                    $honorLembur2Harian = $kuantitasLembur2 * $rateJamLembur2;
                    $potonganJamHarian   = $potonganJam * $tarifDendaJam;

                    $tunjanganMasaHarian    = (float)($payroll->tunjangan_masa_kerja ?? 0);
                    $tunjanganJabatanHarian = (float)($payroll->tunjangan_jabatan ?? 0) / 25;
                    $insentifKerjanHarian   = (float)($payroll->insentif ?? 0) / 25;

                    // Perhitungan matematika sinkron (Tanpa potongan BPJS)
                    $totalPendapatanHarian = $gajiHariIni + $honorLembur1Harian + $honorLembur2Harian + $tunjanganMasaHarian + $tunjanganJabatanHarian + $insentifKerjanHarian;
                    $totalPotonganHarian   = $potonganJamHarian;

                    $gajiHarianKaryawan = round($totalPendapatanHarian - $totalPotonganHarian);
                    $kelompok = strtoupper($att->kelompok_kerja_harian ?? 'TIDAK LANGSUNG');

                    // Filter kantong kelompok
                    if (str_contains($kelompok, 'IFM') || str_contains($kelompok, 'MANUAL') || str_contains($kelompok, 'PACKING')) {
                        $gajiKelompokLangsung += $gajiHarianKaryawan;
                    } else {
                        $gajiKelompokTidakLangsung += $gajiHarianKaryawan;
                    }
                }

                $dailyReports[] = [
                    'tanggal'             => $date,
                    'total_hadir'         => $totalKaryawanMasuk,
                    'gaji_langsung'       => $gajiKelompokLangsung,
                    'gaji_total_langsung' => $gajiKelompokLangsung,
                    'gaji_tidak_langsung' => $gajiKelompokTidakLangsung,
                    'total_gaji'          => $gajiKelompokLangsung + $gajiKelompokTidakLangsung,
                ];
            }
        }

        return view('payroll.print_laporan_kelompok_harian', compact('dailyReports', 'bulanPeriode'));
    }

    public function printKelompokTahunan(Request $request)
    {
        // 1. Ambil parameter tahun dari filter pencarian web
        $tahunPeriode = $request->get('tahun_period', date('Y'));

        // 2. Tarik data absensi massal sepanjang tahun dalam 1 kali query cepat
        $allAttendances = \App\Models\Attendance::with(['employee'])
            ->whereYear('tanggal', $tahunPeriode)
            ->orderBy('tanggal', 'asc')
            ->get();

        $yearlyReports = [];

        if ($allAttendances->isNotEmpty()) {
            // Ambil master payroll data setahun penuh sebagai koleksi RAM lokal agar loading cepat
            $allPayrolls = \App\Models\PayrollDetail::where('bulan_tahun', 'LIKE', "{$tahunPeriode}-%")
                ->get()
                ->keyBy('employee_id');

            // Kelompokkan daftar bulan unik yang memiliki riwayat transaksi (Format: YYYY-MM)
            $allMonths = $allAttendances->map(function($att) {
                return substr($att->tanggal, 0, 7);
            })->unique()->toArray();
            sort($allMonths);

            foreach ($allMonths as $bulanKey) {
                // Ambil absensi khusus bulan berjalan ini
                $attendancesBulan = $allAttendances->filter(function($att) use ($bulanKey) {
                    return substr($att->tanggal, 0, 7) == $bulanKey;
                });

                $totalKaryawanMasuk = $attendancesBulan->count();
                $gajiKelompokLangsung = 0;
                $gajiKelompokTidakLangsung = 0;

                foreach ($attendancesBulan as $att) {
                    $emp = $att->employee;
                    if (!$emp) continue;

                    $payroll = $allPayrolls->get($emp->id);
                    if (!$payroll) continue;

                    // --- REPLIKA MATEMATIKA SINKRON HARIAN TANPA BPJS ---
                    $gajiHariIni = (float)($payroll->gaji_perhari ?? 0);
                    $durasiKotor = 0;
                    $kuantitasLembur1 = 0;
                    $kuantitasLembur2 = 0;
                    $potonganJam = 0;

                    if ($att->jam_masuk && $att->jam_pulang) {
                        $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                        $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                        $tanggalAbsen = \Carbon\Carbon::parse($att->tanggal);
                        $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                        if ($tanggalAbsen->isSunday()) {
                            $gajiHariIni = 0;
                            $kuantitasLembur2 = $durasiKotor;
                        } else {
                            if ($durasiKotor > 7) {
                                $durasiKerja = $durasiKotor - 1;
                                if ($durasiKerja < 7) {
                                    $durasiKerja = 7;
                                }
                                $kelebihanJam = $durasiKerja - 7;
                                if ($durasiKerja > 8) {
                                    $kuantitasLembur1 = 1;
                                    $kuantitasLembur2 = $durasiKerja - 8;
                                } else {
                                    $kuantitasLembur1 = $kelebihanJam;
                                    $kuantitasLembur2 = 0;
                                }
                            } else {
                                $durasiKerja = $durasiKotor;
                                if ($durasiKerja < 7) {
                                    $potonganJam = 7 - $durasiKerja;
                                }
                            }
                        }
                    }

                    $rateJamLembur1 = (float)($payroll->honor_lembur_1 ?? 0);
                    $rateJamLembur2 = (float)($payroll->honor_lembur_2 ?? 0);
                    $tarifDendaJam  = (float)($payroll->potongan_jam_kerja ?? 0);

                    $honorLembur1Harian = $kuantitasLembur1 * $rateJamLembur1;
                    $honorLembur2Harian = $kuantitasLembur2 * $rateJamLembur2;
                    $potonganJamHarian   = $potonganJam * $tarifDendaJam;

                    $tunjanganMasaHarian    = (float)($payroll->tunjangan_masa_kerja ?? 0);
                    $tunjanganJabatanHarian = (float)($payroll->tunjangan_jabatan ?? 0) / 25;
                    $insentifKerjanHarian   = (float)($payroll->insentif ?? 0) / 25;

                    $totalPendapatanHarian = $gajiHariIni + $honorLembur1Harian + $honorLembur2Harian + $tunjanganMasaHarian + $tunjanganJabatanHarian + $insentifKerjanHarian;
                    $gajiHarianKaryawan = round($totalPendapatanHarian - $potonganJamHarian);
                    
                    $kelompok = strtoupper($att->kelompok_kerja_harian ?? 'TIDAK LANGSUNG');

                    if (str_contains($kelompok, 'IFM') || str_contains($kelompok, 'MANUAL') || str_contains($kelompok, 'PACKING')) {
                        $gajiKelompokLangsung += $gajiHarianKaryawan;
                    } else {
                        $gajiKelompokTidakLangsung += $gajiHarianKaryawan;
                    }
                }

                // Simpan data akumulasi bulanan ke dalam array laporan tahunan
                $yearlyReports[] = [
                    'bulan'               => $bulanKey,
                    'total_hadir'         => $totalKaryawanMasuk,
                    'gaji_langsung'       => $gajiKelompokLangsung,
                    'gaji_tidak_langsung' => $gajiKelompokTidakLangsung,
                    'total_gaji'          => $gajiKelompokLangsung + $gajiKelompokTidakLangsung,
                ];
            }
        }

        return view('payroll.print_laporan_kelompok_tahunan', compact('yearlyReports', 'tahunPeriode'));
    }

    public function exportExcelKelompokHarian(Request $request)
    {
        // 1. Ambil Parameter Filter Input Sama Persis dengan Web Utama
        $tipeFilter = request('tipe_filter', 'bulanan');
        $bulanFilter = request('bulan_period', date('Y-m')); 
        $tahunFilter = request('tahun_period', date('Y'));

        // 2. Tarik Data Absensi Massal dengan Filter Rentang Tanggal
        $query = \App\Models\Attendance::with(['employee']);
        
        if ($tipeFilter == 'tahunan') {
            $query->whereYear('tanggal', $tahunFilter);
            
            $namaPeriodeFile = "Tahun_" . $tahunFilter;
            $teksPeriodeTampil = "Tahun : " . $tahunFilter;
        } else {
            // Batasi tanggal dari tanggal 1 sampai akhir bulan Juli (2026-07-01 s/d 2026-07-31)
            $startDate = $bulanFilter . "-01";
            $endDate = date("Y-m-t", strtotime($startDate)); 
            $query->whereBetween('tanggal', [$startDate, $endDate]);
            
            // Perbaikan Array Eksplode Bulan (Menghasilkan format 07_2026)
            $partBulan = explode('-', $bulanFilter);
            $formatBulanBaru = (count($partBulan) == 2) ? $partBulan[1] . "_" . $partBulan[0] : $bulanFilter;
            
            $namaPeriodeFile = "Bulan_" . $formatBulanBaru;
            $teksPeriodeTampil = "Bulan : " . $formatBulanBaru;
        }

        $allAttendances = $query->orderBy('tanggal', 'asc')->get();
        $rekapHarian = [];

        if ($allAttendances->isNotEmpty()) {
            if ($tipeFilter == 'tahunan') {
                $allPayrolls = \App\Models\PayrollDetail::where('bulan_tahun', 'LIKE', $tahunFilter . '-%')->get()->keyBy('employee_id');
            } else {
                $allPayrolls = \App\Models\PayrollDetail::where('bulan_tahun', $bulanFilter)->get()->keyBy('employee_id');
            }

            foreach ($allAttendances as $att) {
                $date = $att->tanggal; // Format: '2026-06-29' atau '2026-07-03'
                
                // VALIDASI KETAT: Jika filter bulanan, pastikan tanggal absensi mengandung string '2026-07'
                if ($tipeFilter == 'bulanan' && !str_starts_with($date, $bulanFilter)) {
                    continue; // Lewati dan buang jika data berasal dari bulan Juni atau bulan lainnya!
                }

                $emp = $att->employee;
                if (!$emp) continue;

                $payroll = $allPayrolls->get($emp->id);
                if (!$payroll) continue;

                // Set Group Key Berdasarkan Tanggal Operasional secara Presisi
                $groupKey = $date;

                if (!isset($rekapHarian[$groupKey])) {
                    $rekapHarian[$groupKey] = [
                        'tanggal'              => $groupKey,
                        'IFM_orang'            => 0, 'IFM_gaji' => 0,
                        'MANUAL_orang'         => 0, 'MANUAL_gaji' => 0,
                        'PACKING_orang'        => 0, 'PACKING_gaji' => 0,
                        'TIDAK_LANGSUNG_orang' => 0, 'TIDAK_LANGSUNG_gaji' => 0,
                        'TOTAL_hari_orang'     => 0, 'TOTAL_hari_gaji' => 0,
                    ];
                }

                // --- REPLIKA RUMUS MATEMATIKA SINKRON HARIAN TANPA BPJS ---
                $gajiHariIni = (float)($payroll->gaji_perhari ?? 0);
                $durasiKotor = 0; $kuantitasLembur1 = 0; $kuantitasLembur2 = 0; $potonganJam = 0;

                if ($att->jam_masuk && $att->jam_pulang) {
                    $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                    $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                    $tanggalAbsen = \Carbon\Carbon::parse($date);
                    $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                    if ($tanggalAbsen->isSunday()) {
                        $gajiHariIni = 0;
                        $kuantitasLembur2 = $durasiKotor;
                    } else {
                        if ($durasiKotor > 7) {
                            $durasiKerja = $durasiKotor - 1;
                            if ($durasiKerja < 7) { $durasiKerja = 7; }
                            $kelebihanJam = $durasiKerja - 7;
                            if ($durasiKerja > 8) {
                                $kuantitasLembur1 = 1; $kuantitasLembur2 = $durasiKerja - 8;
                            } else {
                                $kuantitasLembur1 = $kelebihanJam; $kuantitasLembur2 = 0;
                            }
                        } else {
                            $durasiKerja = $durasiKotor;
                            if ($durasiKerja < 7) { $potonganJam = 7 - $durasiKerja; }
                        }
                    }
                }

                $rateJamLembur1 = (float)($payroll->honor_lembur_1 ?? 0);
                $rateJamLembur2 = (float)($payroll->honor_lembur_2 ?? 0);
                $tarifDendaJam  = (float)($payroll->potongan_jam_kerja ?? 0);

                $honorLembur1Harian = $kuantitasLembur1 * $rateJamLembur1;
                $honorLembur2Harian = $kuantitasLembur2 * $rateJamLembur2;
                $potonganJamHarian   = $potonganJam * $tarifDendaJam;

                $tunjanganMasaHarian    = (float)($payroll->tunjangan_masa_kerja ?? 0);
                $tunjanganJabatanHarian = (float)($payroll->tunjangan_jabatan ?? 0) / 25;
                $insentifKerjanHarian   = (float)($payroll->insentif ?? 0) / 25;

                $totalPendapatanHarian = $gajiHariIni + $honorLembur1Harian + $honorLembur2Harian + $tunjanganMasaHarian + $tunjanganJabatanHarian + $insentifKerjanHarian;
                $gajiHarianKaryawan = round($totalPendapatanHarian - $potonganJamHarian);

                $kelompok = strtoupper($att->kelompok_kerja_harian ?? 'TIDAK LANGSUNG');

                if (str_contains($kelompok, 'IFM')) {
                    $rekapHarian[$groupKey]['IFM_gaji'] += $gajiHarianKaryawan;
                } elseif (str_contains($kelompok, 'MANUAL')) {
                    $rekapHarian[$groupKey]['MANUAL_gaji'] += $gajiHarianKaryawan;
                } elseif (str_contains($kelompok, 'PACKING')) {
                    $rekapHarian[$groupKey]['PACKING_gaji'] += $gajiHarianKaryawan;
                } else {
                    $rekapHarian[$groupKey]['TIDAK_LANGSUNG_gaji'] += $gajiHarianKaryawan;
                }

                if (str_contains($kelompok, 'IFM') || str_contains($kelompok, 'MANUAL') || str_contains($kelompok, 'PACKING')) {
                    $rekapHarian[$groupKey]['TOTAL_hari_gaji'] += $gajiHarianKaryawan;
                }
            }
        }

        // Urutkan Tanggal Terbalik (Mengikuti setelan krsort web terbaru Anda)
        krsort($rekapHarian);

        // Set Nama File Unduhan
        $namaPeriode = $tipeFilter == 'tahunan' ? "Tahun_" . $tahunFilter : "Bulan_" . $bulanFilter;
       $filename = "Laporan_Kelompok_Kerja_" . $namaPeriodeFile . ".xls";

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo '<table border="1">';
        echo '<tr><th colspan="6" style="font-size:26px; font-weight:bold; text-align:center; height:45px;">PT MIRASA FOOD INDUSTRY</th></tr>';
        echo '<tr><td colspan="6" style="font-size:20px; font-weight:bold; text-align:center; height:35px;">ANALISIS BIAYA GAJI PER KELOMPOK KERJA (' . strtoupper($tipeFilter) . ')</td></tr>';
        echo '<tr><td colspan="6" style="font-size:14px; text-align:center; color:#444; height:25px;">Periode: ' . $teksPeriodeTampil . '</td></tr>';
        echo '<tr><td colspan="6">&nbsp;</td></tr>';
        
        echo '<tr style="background-color:#1e293b; color:#ffffff; font-weight:bold; text-align:center; height:40px;">' .
             '<th style="width:150px; vertical-align:middle;">Tanggal Operasional</th>' .
             '<th style="width:150px; vertical-align:middle;">LANGSUNG - IFM</th>' .
             '<th style="width:150px; vertical-align:middle;">LANGSUNG - MANUAL</th>' .
             '<th style="width:150px; vertical-align:middle;">LANGSUNG - PACKING</th>' .
             '<th style="width:180px; vertical-align:middle; background-color:#064e3b;">TOTAL KELOMPOK LANGSUNG</th>' .
             '<th style="width:180px; vertical-align:middle;">TIDAK LANGSUNG (OVERHEAD)</th>' .
             '</tr>';

        $gTotalIFM = 0; $gTotalManual = 0; $gTotalPacking = 0; $gTotalLangsung = 0; $gTotalOverhead = 0;

        foreach ($rekapHarian as $hari) {
            // Tampilkan format teks Bulan jika tahunan, atau format tanggal penuh jika bulanan
            $labelTanggal = $tipeFilter == 'tahunan' ? \Carbon\Carbon::parse($hari['tanggal'].'-01')->translatedFormat('F Y') : date('d-m-Y', strtotime($hari['tanggal']));
            
            $gTotalIFM += $hari['IFM_gaji'];
            $gTotalManual += $hari['MANUAL_gaji'];
            $gTotalPacking += $hari['PACKING_gaji'];
            $gTotalLangsung += $hari['TOTAL_hari_gaji'];
            $gTotalOverhead += $hari['TIDAK_LANGSUNG_gaji'];

            echo '<tr>
                    <td style="text-align:center; font-weight:bold; height:22px;">' . $labelTanggal . '</td>
                    <td style="text-align:right; mso-number-format:\#\,\#\#0;">' . $hari['IFM_gaji'] . '</td>
                    <td style="text-align:right; mso-number-format:\#\,\#\#0;">' . $hari['MANUAL_gaji'] . '</td>
                    <td style="text-align:right; mso-number-format:\#\,\#\#0;">' . $hari['PACKING_gaji'] . '</td>
                    <td style="text-align:right; font-weight:bold; background-color:#f0fdf4; mso-number-format:\#\,\#\#0;">' . $hari['TOTAL_hari_gaji'] . '</td>
                    <td style="text-align:right; mso-number-format:\#\,\#\#0;">' . $hari['TIDAK_LANGSUNG_gaji'] . '</td>
                </tr>';
        }

        echo '<tr style="background-color:#0f172a; color:#ffffff; font-weight:bold; height:25px;">
                <td style="text-align:center;">GRAND TOTAL</td>
                <td style="text-align:right; mso-number-format:\#\,\#\#0;">' . $gTotalIFM . '</td>
                <td style="text-align:right; mso-number-format:\#\,\#\#0;">' . $gTotalManual . '</td>
                <td style="text-align:right; mso-number-format:\#\,\#\#0;">' . $gTotalPacking . '</td>
                <td style="text-align:right; color:#facc15; background-color:#064e3b; mso-number-format:\#\,\#\#0;">' . $gTotalLangsung . '</td>
                <td style="text-align:right; mso-number-format:\#\,\#\#0;">' . $gTotalOverhead . '</td>
            </tr>';
        echo '</table>';
        exit;
    }
        
        public function sendEmailMassal(Request $request)
    {
        $bulanFilter = $request->get('bulan_periode', date('Y-m'));
        $tipeFilter = 'bulanan';

        // 1. Tarik data absensi massal
        $query = \App\Models\Attendance::with(['employee'])->where('tanggal', 'LIKE', $bulanFilter . '%');
        $allAttendances = $query->orderBy('tanggal', 'asc')->get();

        if ($allAttendances->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data payroll untuk dikirim pada bulan ini.');
        }

        // Ambil data detail master payroll bulanan yang valid
        $allPayrolls = \App\Models\PayrollDetail::where('bulan_tahun', $bulanFilter)->get()->keyBy('employee_id');
        $allSlipsData = [];

        // Kelompokkan absensi berdasarkan ID Karyawan agar loop berjalan tertib per kepala
        $groupedAttendances = $allAttendances->groupBy('employee_id');

        foreach ($groupedAttendances as $employeeId => $attendances) {
            $firstAtt = $attendances->first();
            $emp = $firstAtt->employee;
            
            // Lewati jika data karyawan atau email tidak valid
            if (!$emp || !$emp->email) continue;

            $payroll = $allPayrolls->get($employeeId);
            if (!$payroll) continue;

            // Inisialisasi hitungan counter detektor absensi per karyawan
            $jumlahHariKerjaMurni = 0;
            $totalJamLembur1 = 0;
            $totalJamLembur2 = 0;
            $totalJamPotongan = 0;

            foreach ($attendances as $att) {
                if ($att->jam_masuk && $att->jam_pulang) {
                    $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                    $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                    $tanggalAbsen = \Carbon\Carbon::parse($att->tanggal);

                    $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                    if ($tanggalAbsen->isSunday()) {
                        // Hari Minggu murni lembur II
                        $totalJamLembur2 += $durasiKotor;
                    } else {
                        // Hari biasa regular kerja murni bertambah
                        $jumlahHariKerjaMurni++;
                        $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                        if ($durasiKerja < 7) {
                            $totalJamPotongan += (7 - $durasiKerja);
                        } else {
                            $kelebihanJam = $durasiKerja - 7;
                            if ($durasiKerja > 8) {
                                $totalJamLembur1 += 1;
                                $totalJamLembur2 += ($durasiKerja - 8);
                            } else {
                                $totalJamLembur1 += $kelebihanJam;
                            }
                        }
                    }
                }
            }

            // =========================================================================
            // OPERATOR KALKULATOR STRUK AKUNTANSI PAYROLL PT MIRASA FOOD INDUSTRY
            // =========================================================================
            $gajiPokokTotal     = (float)($payroll->gaji_perhari ?? 0) * $jumlahHariKerjaMurni;
            $honorLembur1Total  = (float)($payroll->honor_lembur_1 ?? 0) * $totalJamLembur1;
            $honorLembur2Total  = (float)($payroll->honor_lembur_2 ?? 0) * $totalJamLembur2;
            $potonganJamKerja   = (float)($payroll->potongan_jam_kerja ?? 0) * $totalJamPotongan;

            $tunjanganMasaKerja = (float)($payroll->tunjangan_masa_kerja ?? 0) * $jumlahHariKerjaMurni;
            $tunjanganJabatan   = (float)($payroll->tunjangan_jabatan ?? 0);
            $insentifKerajinan  = (float)($payroll->insentif ?? 0);

            $bpjsKes            = (float)($payroll->potongan_bpjs_kes ?? 0);
            $bpjsTk             = (float)($payroll->potongan_bpjs_tk ?? 0);
            $potonganLainnya    = (float)($payroll->potongan_lainnya ?? 0);

            $totalPendapatan    = $gajiPokokTotal + $honorLembur1Total + $honorLembur2Total + $tunjanganMasaKerja + $tunjanganJabatan + $insentifKerajinan;
            $totalPotongan      = $bpjsKes + $bpjsTk + $potonganJamKerja + $potonganLainnya;
            $totalGajiBersih    = $totalPendapatan - $totalPotongan;
            
            // Hitung akumulasi kehadiran murni (asumsi counter sederhana dari records)
            if (!isset($allSlipsData[$emp->id])) {
                $allSlipsData[$emp->id] = [
                    'employee'              => $emp,
                    'payroll'               => $payroll,
                    'bulanTahun'            => \Carbon\Carbon::parse($bulanFilter.'-01')->translatedFormat('F Y'),
                    'jumlahHariKerjaMurni'  => $jumlahHariKerjaMurni,
                    'totalJamLembur1'       => $totalJamLembur1,
                    'totalJamLembur2'       => $totalJamLembur2,
                    'totalJamPotongan'      => $totalJamPotongan,
                    'gajiPokokTotal'        => $gajiPokokTotal,
                    'honorLembur1Total'     => $honorLembur1Total,
                    'honorLembur2Total'     => $honorLembur2Total,
                    'tunjanganMasaKerja'    => $tunjanganMasaKerja,
                    'tunjanganJabatan'      => $tunjanganJabatan,
                    'insentifKerajinan'     => $insentifKerajinan,
                    'bpjsKes'               => $bpjsKes,
                    'bpjsTk'                => $bpjsTk,
                    'potonganJamKerja'      => $potonganJamKerja,
                    'potonganLainnya'       => $potonganLainnya,
                    'totalPendapatan'       => $totalPendapatan,
                    'totalPotongan'         => $totalPotongan,
                    'totalGajiBersih'       => $totalGajiBersih,
                    'kelompokKerja'         => isset($attendances) && $attendances->first() ? strtoupper($attendances->first()->kelompok_kerja_harian) : '-',
                ];
            }
        }

        // 2. RENDERING TUNGGAL MASSAL AMAN DARI DUPLIKASI FUNGSI BLADE
        $dataPassingUntukBlade = [
            'allSlipsData' => $allSlipsData,
            'bulanFilter'  => $bulanFilter,
            'bulanTahun'   => date('F Y', strtotime($bulanFilter . '-01'))
        ];

        // Jalankan render cuma 1 KALI agar fungsi 'terbilangRupiahMassal' tidak tabrakan
        $htmlUtuh = view('payroll.print_all', $dataPassingUntukBlade)->render();

        // Potong string HTML per halaman slip karyawan menggunakan penanda khusus class .slip-page
        $htmlSplitted = explode('class="slip-page"', $htmlUtuh);
        $headerHTML = explode('<body>', $htmlUtuh)[0] . '<body style="background: #fff; padding:0; margin:0;">';

        // 3. LOOP PENGIRIMAN EMAIL INDIVIDU DARI HASIL POTONGAN STRUKTUR HTML
        $index = 0;
        foreach ($allSlipsData as $empId => $dataSlip) {
            $index++;
            
            // Jika potongan HTML valid, rakit kembali menjadi dokumen HTML mini tunggal khusus karyawan tersebut
            if (isset($htmlSplitted[$index])) {
                $kontenHalamanKaryawan = $headerHTML . '<div class="slip-page"' . explode('<!-- Penutup .slip-page per karyawan -->', $htmlSplitted[$index])[0] . '</div></body></html>';
                
                // Konversi potongan HTML tersebut menjadi dokumen PDF resmi
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($kontenHalamanKaryawan)->setPaper('a4', 'portrait')->output();

                // Kirim email personal dengan lampiran nama file yang otomatis aman terbaca di SendPayrollSlipMail baris 42
                \Illuminate\Support\Facades\Mail::to($dataSlip['employee']->email)
                    ->sendNow(new \App\Mail\SendPayrollSlipMail($dataSlip, $pdf));
            }
        }

        return redirect()->back()->with('success', 'Slip gaji berhasil dikirimkan ke seluruh email karyawan dengan nominal yang 100% akurat!');
    }

}
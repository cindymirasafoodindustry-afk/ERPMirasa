<?php

namespace App\Imports;

use App\Models\PayrollDetail;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class PayrollImport implements ToModel, WithHeadingRow, WithUpserts
{
    protected $bulanPeriode;

    public function __construct($bulanPeriode)
    {
        $this->bulanPeriode = $bulanPeriode;
    }

    /**
     * FUNGSI BANTUAN DETEKTIF: Membersihkan tanda titik, Rp, atau spasi liar
     * agar berubah menjadi angka murni desimal yang sangat ramah database Neon PostgreSQL.
     */
    private function bersihkanNominalRupiah(string $value)
    {
        if (empty($value)) return 0;
        
        // Hapus lambang Rp, tanda titik ribuan, spasi ghaib, dan paksa jadi string bersih
        $cleanString = preg_replace('/[^0-9]/', '', $value);
        
        return (float) $cleanString;
    }

    public function model(array $row)
    {
        // 🚀 KUNCI PERIODE SAKRAL: Menyedot nilai bulanPeriode dari konstruktor lembaran controller kamu harian
        $bulanPeriode = $this->bulanPeriode ?? (request('bulan_periode') ?? date('Y-m'));

        // 🚀 KUNCI HEADING ROW: Memanggil data menggunakan teks JUDUL KOLOM EXCEL secara persis (Sesuai template 7 kolom kita!)
        $idKaryawan = isset($row['id_karyawan']) ? trim($row['id_karyawan']) : null;
        
        if (empty($idKaryawan)) {
            return null; // Otomatis melewati baris jika boks ID Karyawannya kosong melosot
        }

        $employee = \App\Models\Employee::where('id_karyawan', $idKaryawan)->first();
        if (!$employee) {
            return null; // Otomatis melewati baris jika ID Karyawan tidak terdaftar di master data pabrik
        }

        // 🚀 TANGKAP NOMINAL STATIS EXCEL: Membaca nilai rupiah bersih polosan numerik tanpa titik/simbol Rp
        $gajiPerhari       = (float)($row['gaji_pokok_perhari'] ?? 0);
        $tunjanganJabatan   = (float)($row['tunjangan_jabatan'] ?? 0);
        $insentifKerajinan  = (float)($row['insentif_kerajinan'] ?? 0);
        $bpjsKes            = (float)($row['potongan_bpjs_kesehatan'] ?? 0);
        $bpjsTk             = (float)($row['potongan_bpjs_ketenagakerjaan'] ?? 0);
        $potonganLainnya    = (float)($row['potongan_lainnya'] ?? 0);

        // 🚀 DETEKTIF ABSENSI AUTOMATION: Menyedot seluruh kuantitas jam kerja riil khusus bulan periode berjalan
        $attendances = \App\Models\Attendance::where('employee_id', $employee->id)
            ->where('tanggal', 'LIKE', $bulanPeriode . '-%')
            ->get();

        $jumlahHariKerjaMurni = 0;
        $totaljamLembur1      = 0;
        $totaljamLembur2      = 0;
        $totaljamPotongan     = 0;

        foreach ($attendances as $att) {
            if ($att->jam_masuk && $att->jam_pulang) {
                $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                $tanggalAbsen = \Carbon\Carbon::parse($att->tanggal);
                
                $durasiKotor = $masuk->diffInMinutes($pulang) / 60;

                if ($tanggalAbsen->isSunday()) {
                    $durasiKerja = $durasiKotor;
                    $totaljamLembur2 += $durasiKerja; // Suku jam masuk hari Minggu mengalir penuh ke upah Lembur II murni
                } else {
                    $jumlahHariKerjaMurni++; // Hari masuk regular bertambah (Kunci 21 Hari)

                    $durasiKerja = ($durasiKotor > 7) ? ($durasiKotor - 1) : $durasiKotor;

                    if ($durasiKerja < 7) {
                        $totaljamPotongan += (7 - $durasiKerja);
                    } else {
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

        // // HITUNG TARIF OTOMATIS BERSTANDAR PT MIRASA FOOD INDUSTRY
        $rateLembur1 = 20850; 
        $rateLembur2 = 27800; 
        $rateDenda   = 13900; 

        $honorLembur1Total = $rateLembur1 * $totaljamLembur1;
        $honorLembur2Total = $rateLembur2 * $totaljamLembur2;
        $potonganJamKerja  = $rateDenda * $totaljamPotongan;

        // // HITUNG TUNJANGAN MASA KERJA OTOMATIS BERDASARKAN TANGGAL MASUK KERJA SEJATI KARYAWAN
        $tunjanganMasaKerja = 0;
        if ($employee->tanggal_masuk_kerja) {
            $masukKerjaDate = \Carbon\Carbon::parse($employee->tanggal_masuk_kerja);
            $sekarangDate = \Carbon\Carbon::now();
            $masaKerjaTahunan = $masukKerjaDate->diffInYears($sekarangDate);

            if ($masaKerjaTahunan > 20) {
                $tunjanganMasaKerja = 2550;
            } elseif ($masaKerjaTahunan >= 15 && $masaKerjaTahunan <= 20) {
                $tunjanganMasaKerja = 2200;
            } elseif ($masaKerjaTahunan >= 10 && $masaKerjaTahunan < 15) {
                $tunjanganMasaKerja = 1700;
            } elseif ($masaKerjaTahunan >= 5 && $masaKerjaTahunan < 10) {
                $tunjanganMasaKerja = 1000;
            } else {
                $tunjanganMasaKerja = 0;
            }
        }

        // // AKUMULASI MATEMATIKA ESSENSIAL PAYROLL SINKRON 100% DENGAN DASHBOARD
        $subtotalGajiPokok = $gajiPerhari * $jumlahHariKerjaMurni;
        $totalPendapatan   = $subtotalGajiPokok + $honorLembur1Total + $honorLembur2Total + $tunjanganMasaKerja + $tunjanganJabatan + $insentifKerajinan;
        $totalPotongan     = $bpjsKes + $bpjsTk + $potonganJamKerja + $potonganLainnya;
        $totalGajiBersih   = $totalPendapatan - $totalPotongan;

        // // KUNCI SIMPAN / PERBARUI KE DALAM TABEL DATABASE PAYROLL_DETAILS
        return \App\Models\PayrollDetail::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'bulan_tahun' => $bulanPeriode,
            ],
            [
                'gaji_perhari'         => $gajiPerhari,
                'honor_lembur_1'       => $honorLembur1Total, 
                'honor_lembur_2'       => $honorLembur2Total, 
                'tunjangan_masa_kerja' => $tunjanganMasaKerja, 
                'tunjangan_jabatan'    => $tunjanganJabatan,
                'insentif'             => $insentifKerajinan,
                'potongan_bpjs_kes'    => $bpjsKes,
                'potongan_bpjs_tk'     => $bpjsTk,
                'potongan_jam_kerja'   => $potonganJamKerja,  
                'potongan_lainnya'     => $potonganLainnya,
                'total_gaji'           => $totalGajiBersih,   
            ]
        );
    }

    // KUNCI COCOK DATA: Cek kombinasi Employee ID dan Bulan agar kebal dari eror data duplikat!
    public function uniqueBy()
    {
        return ['employee_id', 'bulan_tahun'];
    }
}
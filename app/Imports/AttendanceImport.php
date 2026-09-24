<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class AttendanceImport implements ToModel, WithHeadingRow
{
        public function model(array $row)
    {
        // 1. Ambil data primer Employee berdasarkan ID Karyawan unik dari dokumen Excel
        $employee = \App\Models\Employee::where('id_karyawan', trim($row['id_karyawan'] ?? ''))->first();
        
        if (!$employee) {
            return null; // Otomatis melewati baris data jika ID Karyawan tidak terdaftar
        }

        // 2. LOGIKA PENERJEMAH TANGGAL SAKTI (Mendukung format strip bawaan Excel internasional)
        $tanggal = null;
        $rawTanggal = trim($row['tanggal'] ?? '');
        if (!empty($rawTanggal)) {
            try {
                // Mengonversi format tanggal apa pun dari Excel menjadi standar database (YYYY-MM-DD)
                $tanggal = \Carbon\Carbon::parse($rawTanggal)->format('Y-m-d');
            } catch (\Exception $e) {
                $tanggal = \Carbon\Carbon::today()->format('Y-m-d');
            }
        }

        // 3. LOGIKA DETEKTIF SUPER CERDAS: KEBAL SPASI GANDA & FORMAT JAM AM/PM EXCEL
        $jamMasuk = '07:00:00';
        $jamPulang = '16:00:00';

        // Ambil data mentah lalu bersihkan spasi ganda/berlebih di dalam teks jam
        $rawMasuk = preg_replace('/\s+/', ' ', trim($row['jam_masuk'] ?? ''));
        $rawPulang = preg_replace('/\s+/', ' ', trim($row['jam_pulang'] ?? ''));

        // PENERJEMAH JAM MASUK
        try {
            if (!empty($rawMasuk)) {
                $rawMasukUpper = strtoupper($rawMasuk);
                if (str_contains($rawMasukUpper, 'AM') || str_contains($rawMasukUpper, 'PM')) {
                    // Mencoba format standar dengan detik (Contoh: 9:00:00 AM)
                    try {
                        $jamMasuk = \Carbon\Carbon::createFromFormat('g:i:s A', $rawMasukUpper)->format('H:i:s');
                    } catch (\Exception $e) {
                        // Cadangan jika tanpa detik di Excel (Contoh: 9:00 AM)
                        $jamMasuk = \Carbon\Carbon::createFromFormat('g:i A', $rawMasukUpper)->format('H:i:s');
                    }
                } else {
                    $jamMasuk = \Carbon\Carbon::parse($rawMasuk)->format('H:i:s');
                }
            }
        } catch (\Exception $e) {
            $jamMasuk = '07:00:00';
        }

        // PENERJEMAH JAM PULANG
        try {
            if (!empty($rawPulang)) {
                $rawPulangUpper = strtoupper($rawPulang);
                if (str_contains($rawPulangUpper, 'AM') || str_contains($rawPulangUpper, 'PM')) {
                    try {
                        $jamPulang = \Carbon\Carbon::createFromFormat('g:i:s A', $rawPulangUpper)->format('H:i:s');
                    } catch (\Exception $e) {
                        $jamPulang = \Carbon\Carbon::createFromFormat('g:i A', $rawPulangUpper)->format('H:i:s');
                    }
                } else {
                    $jamPulang = \Carbon\Carbon::parse($rawPulang)->format('H:i:s');
                }
            }
        } catch (\Exception $e) {
            $jamPulang = '16:00:00';
        }

        $kelompokHarian = !empty($row['kelompok_kerja_harian']) 
            ? strtoupper($row['kelompok_kerja_harian']) 
            : $employee->kelompok;

        return new Attendance([
            'employee_id' => $employee->id, // Mengunci relasi ID Master Karyawan
            'tanggal'     => $tanggal ?? \Carbon\Carbon::today()->format('Y-m-d'),
            'kelompok_kerja_harian' => $kelompokHarian,
            'jam_masuk'   => $jamMasuk,
            'jam_pulang'  => $jamPulang,
            'status_kehadiran'      => $row['status_kehadiran'] ?? 'Hadir',
            'keterangan'  => $row['keterangan'] ?? 'Hadir',
        ]);
    }
}

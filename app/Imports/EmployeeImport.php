<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class EmployeeImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
                // 1. Penyaring Tanggal Masuk Kerja Pintar (Bisa membaca format standar maupun Indonesia)
        $tanggalMasuk = null;
        if (!empty($row['tanggal_masuk_kerja'])) {
            $rawTanggal = trim($row['tanggal_masuk_kerja']);

            if (is_numeric($rawTanggal)) {
                // Jika berupa format serial angka bawaan Excel
                $tanggalMasuk = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawTanggal))->format('Y-m-d');
            } else {
                try {
                    if (str_contains($rawTanggal, '/')) {
                        // Jika menggunakan format INDONESIA (Contoh: 25/07/2025)
                        $tanggalMasuk = \Carbon\Carbon::createFromFormat('d/m/Y', $rawTanggal)->format('Y-m-d');
                    } else {
                        // Jika menggunakan format standar internasional (Contoh: 2025-07-25)
                        $tanggalMasuk = \Carbon\Carbon::parse($rawTanggal)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    // Jaga-jaga jika formatnya rusak, otomatis diisi tanggal hari ini
                    $tanggalMasuk = \Carbon\Carbon::now()->format('Y-m-d');
                }
            }
        } else {
            $tanggalMasuk = \Carbon\Carbon::now()->format('Y-m-d');
        }
        if (empty($row['id_karyawan']) || empty($row['nama_karyawan'])) {
            return null;
        }     

        // 2. Penyaring Huruf Otomatis Agar Lolos Saringan PostgreSQL Enum
        $kelompokInput = strtoupper(trim($row['kelompok'] ?? ''));
        
        // 🚀 KUNCI KESEMBUHAN KELOMPOK: Memetakan teks Excel secara presisi agar klop 100% dengan isi Enum Database!
        if ($kelompokInput === 'TIDAK LANGSUNG' || $kelompokInput === 'OVERHEAD') {
            $kelompok = 'TIDAK LANGSUNG';
        } else {
            $kelompok = 'LANGSUNG';
        }
        
        // MENGUBAH INPUT MENJADI HURUF KAPITAL UNTUK SHIFT (A / B)
        $shiftOriginal = trim($row['shift'] ?? '');
        $shift = in_array(strtoupper($shiftOriginal), ['A', 'B']) ? strtoupper($shiftOriginal) : 'non shift';
        
        // MEMASTIKAN STATUS SESUAI PILIHAN DATABASE (tetap / tidak tetap)
        $statusRaw = strtolower(trim($row['status_karyawan'] ?? ''));
        $status = in_array($statusRaw, ['tetap', 'tidak tetap']) ? $statusRaw : 'tidak tetap';

        return new Employee([
            'id_karyawan'         => $row['id_karyawan'],
            'nama_karyawan'       => $row['nama_karyawan'],
            'kelompok'            => $kelompok,
            'shift'               => $shift,
            'status_karyawan'     => $status,
            'tanggal_masuk_kerja' => $tanggalMasuk,
            'bagian'              => $row['bagian'] ?? '-',       // Membaca kolom header 'bagian' di Excel
            'no_hp'               => $row['no_hp'] ?? '-',        // Membaca kolom header 'no_hp' di Excel
            'nama_bank'           => $row['nama_bank'] ?? '-',    // Membaca kolom header 'nama_bank' di Excel
            'no_rekening'         => $row['no_rekening'] ?? '-',  // Membaca kolom header 'no_rekening' di Excel
            'email'               => $row['email'] ?? '-',
        ]); // <-- PASTIKAN DI BARIS 61 INI TERTULIS PERFORMA SEPERTI INI, SAYANGKU CINTAKU
    }
}
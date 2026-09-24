<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping
{
    protected $employees;

    // 1. MENERIMA LEMPARAN DATA DARI CONTROLLER (MENGGUNAKAN VARIABEL EMPLOYEES YANG SINKRON)
    public function __construct($employees)
    {
        $this->employees = $employees;
    }

    public function collection()
    {
        return $this->employees;
    }

    // 2. TENTUKAN JUDUL KEPALA KOLOM DI EXCEL HASIL UNDUHAN
    public function headings(): array
    {
        return [
            'ID Karyawan',
            'Nama Lengkap Karyawan',
            'Kelompok Kerja',
            'Shift Kerja',
            'Status Kepegawaian',
            'Tanggal Masuk Kerja',
            'Masa Kerja',
            'Bagian / Divisi',
            'Nomor HP',
            'Email',
            'Nama Bank',
            'Nomor Rekening',
        ];
    }

    // 3. METODE PEMETAAN DATA ISI BARIS PER BARIS SECARA PRESISI
    public function map($emp): array
    {
                // KALKULATOR CERDAS - SINKRONISASI HITUNGAN HARI OTOMATIS KE EXCEL
        $masaKerja = '-';
        if (!empty($emp->tanggal_masuk_kerja)) {
            $masuk = \Carbon\Carbon::parse($emp->tanggal_masuk_kerja);
            $sekarang = \Carbon\Carbon::now();
            
            $tahun = $masuk->diffInYears($sekarang);
            $bulan = $masuk->diffInMonths($sekarang) % 12;
            
            // Hitung sisa hari secara presisi setelah dipotong tahun dan bulan
            $masukSisaHari = $masuk->copy()->addYears($tahun)->addMonths($bulan);
            $hari = (int) floor($masukSisaHari->diffInDays($sekarang));

            // Susun teks tampilan agar rapi di dalam cell spreadsheet
            $teksMasaKerja = [];
                            if ($tahun > 0) { $teksMasaKerja[] = (int)$tahun . " Thn"; }
                            if ($bulan > 0) { $teksMasaKerja[] = (int)$bulan . " Bln"; }
                            if ($hari > 0 || empty($teksMasaKerja)) { $teksMasaKerja[] = (int)$hari . " Hari"; }
            
            $masaKerja = implode(' ', $teksMasaKerja);
        }

        return [
            $emp->id_karyawan,
            $emp->nama_karyawan,
            strtoupper($emp->kelompok),
            strtoupper($emp->shift),
            strtoupper($emp->status_karyawan),
            $emp->tanggal_masuk_kerja ? \Carbon\Carbon::parse($emp->tanggal_masuk_kerja)->format('d-m-Y') : '-',
            $masaKerja,
            $emp->bagian ?? '-',
            $emp->no_hp ?? '-',
            $emp->email ?? '-',
            $emp->nama_bank ?? '-',
            $emp->no_rekening ?? '-',
        ];
    }
}
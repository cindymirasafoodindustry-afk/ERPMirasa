<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $attendances;

    // // 1. JEMBATAN PENANGKAP QUERY DATA DARI CONTROLLER
    public function __construct($attendances)
    {
        $this->attendances = $attendances;
    }

    public function collection()
    {
        return $this->attendances;
    }

    // // 2. TENTUKAN NAMA KEPALA KOLOM DI EXCEL HASIL UNDUHAN (KEMBAR IDENTIK FORMAT PABRIK)
    public function headings(): array
    {
        return [
            'Tanggal',
            'ID Karyawan',
            'Nama Karyawan',
            'Kelompok Kerja (Hari Ini)',
            'Shift',
            'Jam Masuk',
            'Jam Pulang',
            'Keterangan'
        ];
    }

    // // 3. PETAKKAN ISI DATA SESUAI KOLOMNYA MASING-MASING SECARA BERSIH
    public function map($att): array
    {
        $emp = $att->employee;

        // Merapikan string waktu format jam menit (08:00) bersih dari detik ghaib
        $jamMasukClean  = $att->jam_masuk ? date('H:i', strtotime($att->jam_masuk)) : '08:00';
        $jamPulangClean = $att->jam_pulang ? date('H:i', strtotime($att->jam_pulang)) : '18:30';

        // Pemetaan nama kelompok kerja format uppercase tegak lurus
        $statusKelompok = strtoupper(trim($emp->kelompok ?? 'LANGSUNG'));
        $kelompokText = ($statusKelompok === 'LANGSUNG' || $statusKelompok === 'HPP') ? 'LANGSUNG - IFM' : 'TIDAK LANGSUNG';

        return [
            $att->tanggal ? date('d-m-Y', strtotime($att->tanggal)) : '-',
            $emp->id_karyawan ?? '-',
            strtoupper($emp->nama_karyawan ?? '-'),
            $kelompokText,
            strtoupper($att->shift ?? ($emp->shift ?? 'A')),
            $jamMasukClean,
            $jamPulangClean,
            $att->status ?? 'Hadir'
        ];
    }

    // // 4. SUNTIKKAN STYLING SAKRAL (BARIS 1 HIJAU PUPUS SOFT, FONT TIMES NEW ROMAN 11PT & BORDER TIPIS)
    public function styles(Worksheet $sheet)
    {
        // Garis kisi-kisi (Gridlines) bawaan Excel dipaksa menyala aktif
        $sheet->setShowGridlines(true);

        // Styling Kepala Header Baris 1
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'name' => 'Times New Roman', 'size' => 11],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE2EFDA'] // Warna Hijau Pupus Lembut Khas Excel Pabrik!
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFA6A6A6']]
            ]
        ]);

        // Styling Baris Data Karyawan (Mulai dari Baris 2 sampai Baris Akhir Pengenal Data)
        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 2) {
            for ($row = 2; $row <= $highestRow; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(20);
                
                // Set perataan data harian biar sejajar simetris dengan contoh gambar
                $sheet->getStyle('A'.$row.':D'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('E'.$row.':G'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            }

            // Set font massal data harian: Times New Roman 11pt
            $sheet->getStyle('A2:H' . $highestRow)->getFont()->setName('Times New Roman')->setSize(11);
            
            // Beri garis pembatas tegap tipis abu-abu di setiap petak sel data
            $sheet->getStyle('A2:H' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFD9D9D9');
        }
    }
}

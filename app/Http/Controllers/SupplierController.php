<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use App\Imports\SupplierImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class SupplierController extends Controller
{

    /**
     * Menampilkan daftar supplier (Index)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Filter Dropdown Perusahaan
        // Super Admin bisa melihat semua perusahaan, role lain hanya perusahaan sendiri
        if ($user->hasRole('Super Admin')) {
            $perusahaan = Perusahaan::whereNull('deleted_at')->get();
        } else {
            $perusahaan = Perusahaan::where('id', $user->id_perusahaan)
                ->whereNull('deleted_at')
                ->get();
        }

        // 2. Query dasar dengan eager loading relasi perusahaan
        $query = Supplier::with('perusahaan');

        // 3. PROTEKSI DATA: Batasi akses berdasarkan role
        if (!$user->hasRole('Super Admin')) {
            // Jika bukan Super Admin, paksa filter ke perusahaan milik user login
            $query->where('id_perusahaan', $user->id_perusahaan);
        } elseif ($request->filled('id_perusahaan')) {
            // Jika Super Admin memilih filter perusahaan tertentu
            $query->where('id_perusahaan', $request->id_perusahaan);
        }

        // 4. Filter berdasarkan Search (Nama Supplier atau Kode)
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nama_supplier) like ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(kode) like ?', ["%{$search}%"]);
            });
        }

        // 5. Filter berdasarkan Jenis Supplier
        if ($request->filled('jenis_supplier')) {
            $query->where('jenis_supplier', $request->jenis_supplier);
        }

        // 6. Filter Status (Aktif / Tidak Aktif)
        if ($request->filled('status')) {
            if ($request->status == 'aktif') {
                $query->whereNull('deleted_at');
            } elseif ($request->status == 'tidak_aktif') {
                $query->onlyTrashed();
            }
        } else {
            $query->whereNull('deleted_at');
        }

        // 7. Eksekusi Paginate
        $supplier = $query->latest()->paginate(10)->withQueryString();

        return view('pages.supplier.index', compact('supplier', 'perusahaan'));
    }

    /**
     * Pencegahan Error 500 jika route /create diakses manual
     */
    public function create()
    {
        $perusahaan = Perusahaan::whereNull('deleted_at')->get();

        return view('pages.supplier.create', compact('perusahaan'));
    }

    /**
     * Simpan data dari Modal Tambah
     */
    // Bagian Store (Simpan)
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'id_perusahaan'  => 'required|exists:perusahaan,id',
            'nama_supplier'  => 'required|string',
            'jenis_supplier' => 'required|string',
            'kode'           => 'required|string',
        ]);

        // --- AWAL LOGIKA DINAMIS PREFIX KODE ---
        // Bersihkan input kode dari user jika mereka tidak sengaja mengetik SUP atau SKG di form
        $cleanKode = preg_replace('/^(SUP|SKG)[-\s]*/i', '', trim($validated['kode']));

        // Menggunakan stripos (tidak sensitif huruf besar/kecil) untuk mengecek kata 'bahan' atau 'baku'
        if (stripos($validated['jenis_supplier'], 'baku') !== false) {
            $prefix = 'SKG-';
        } else {
            $prefix = 'SUP-';
        }
        // --- AKHIR LOGIKA DINAMIS PREFIX KODE ---

        // 2. Olah Data (Uppercase & Format Kode)
        $data = [
            'id_perusahaan'  => $validated['id_perusahaan'],
            'nama_supplier'  => $validated['nama_supplier'],
            'jenis_supplier' => $validated['jenis_supplier'],
            'kode'           => strtoupper($prefix . $cleanKode),
        ];

        // 3. Simpan ke Database
        Supplier::create($data);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil disimpan.');
    }

    /**
     * Edit data (Jika masih menggunakan halaman terpisah, jika modal edit, gunakan logic yang sama)
     */
    public function edit($id)
    {
        $user = auth()->user();
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $perusahaan = Perusahaan::whereNull('deleted_at')->get();

        if (!$user->hasRole('Super Admin') && $user->id_perusahaan !== $supplier->id_perusahaan) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data ini.');
        }

        return view('pages.supplier.edit', compact('supplier', 'perusahaan'));
    }

    /**
     * Update data
     */
    public function update(Request $request, $id)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'id_perusahaan'  => 'required|exists:perusahaan,id',
            'nama_supplier'  => 'required|string',
            'jenis_supplier' => 'required|string',
            'kode'           => 'required|string',
        ]);

        // 2. Cari Data (Termasuk yang soft deleted agar bisa diupdate)
        $user = auth()->user();
        $supplier = Supplier::withTrashed()->findOrFail($id);

        if (!$user->hasRole('Super Admin') && $user->id_perusahaan !== $supplier->id_perusahaan) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data ini.');
        }

        // --- AWAL LOGIKA DINAMIS PREFIX KODE ---
        // Bersihkan input kode dari user jika mengandung SUP atau SKG lama
        $cleanKode = preg_replace('/^(SUP|SKG)[-\s]*/i', '', trim($validated['kode']));

        // Cek apakah teks mengandung kata 'baku' tanpa memedulikan huruf besar/kecil
        if (stripos($validated['jenis_supplier'], 'baku') !== false) {
            $prefix = 'SKG-';
        } else {
            $prefix = 'SUP-';
        }
        // --- AKHIR LOGIKA DINAMIS PREFIX KODE ---

        // 3. Olah Data (Sama dengan logika Store agar Uppercase)
        $data = [
            'id_perusahaan'  => $validated['id_perusahaan'],
            'nama_supplier'  => $validated['nama_supplier'],
            'jenis_supplier' => $validated['jenis_supplier'],
            'kode'           => strtoupper($prefix . $cleanKode),
        ];

        // 4. Eksekusi Update
        $supplier->update($data);

        return redirect()->route('supplier.index')
            ->with('success', 'Supplier berhasil diperbarui!');
    }

    /**
     * Hapus data berdasarkan index
     */
    public function destroy($id)
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $supplier->delete();

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil dihapus');
    }

    public function activate($id)
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);

        $supplier->deleted_at = null;
        $supplier->save();

        return redirect()->back()->with('success', 'Supplier berhasil diaktifkan kembali.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new class implements WithHeadings, WithEvents, WithStyles, WithTitle {

            public function title(): string
            {
                return 'Template Import Supplier';
            }

            public function headings(): array
            {
                return [
                    'nama_supplier',
                    'kode',
                    'jenis_supplier'
                ];
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    // Style untuk Header (Baris 1)
                    1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4F46E5'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ]
                    ],
                ];
            }

            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $rowCount = 100; // Proteksi untuk 100 baris

                        // 1. Setup Kolom Otomatis
                        foreach (range('A', 'C') as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }

                        // 2. Dropdown untuk Jenis Supplier (Kolom C)
                        $validationJenis = $sheet->getCell('C2')->getDataValidation();
                        $validationJenis->setType(DataValidation::TYPE_LIST)
                            ->setErrorStyle(DataValidation::STYLE_STOP)
                            ->setAllowBlank(false)
                            ->setShowDropDown(true)
                            ->setShowInputMessage(true)
                            ->setPromptTitle('Pilih Jenis')
                            ->setPrompt('Silahkan pilih: Barang atau Bahan Baku')
                            ->setFormula1('"Barang,Bahan Baku"');

                        // 3. Input Message untuk Nama & Kode (A & B)
                        $msgInfo = $sheet->getCell('A2')->getDataValidation();
                        $msgInfo->setShowInputMessage(true)
                            ->setPromptTitle('Aturan Pengisian')
                            ->setPrompt('Wajib diisi, unik, dan disarankan menggunakan format konsisten seperti SUP-CLP, SUP-SMR.');

                        // Terapkan ke baris selanjutnya
                        for ($i = 2; $i <= $rowCount; $i++) {
                            $sheet->getCell("C$i")->setDataValidation(clone $validationJenis);
                            $sheet->getCell("A$i")->setDataValidation(clone $msgInfo);
                            $sheet->getCell("B$i")->setDataValidation(clone $msgInfo);

                            // Beri border tipis agar terlihat rapi seperti form
                            $sheet->getStyle("A$i:C$i")->getBorders()->getAllBorders()
                                ->setBorderStyle(Border::BORDER_THIN);
                        }
                    },
                ];
            }
        }, 'template_supplier.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        try {
            $import = new SupplierImport;
            Excel::import($import, $request->file('file'));

            $berhasil = $import->getRowCount();
            $failures = $import->failures();

            if ($failures->isNotEmpty()) {
                $totalGagal = $failures->count();

                // Grouping error yang sama
                $groupedFailures = $failures->groupBy(function ($failure) {
                    return implode(", ", $failure->errors());
                });

                $details = $groupedFailures->map(function ($group, $errorMessage) {
                    $rows = $group->map(fn($f) => $f->row())->implode(', ');
                    return "<li class='mb-2 text-red-600'>
                            <b class='block'>$errorMessage:</b>
                            <span class='text-gray-500'>Baris: $rows</span>
                        </li>";
                })->implode('');

                return back()->with('error_import', [
                    'title' => "Hasil Import Supplier",
                    'success_count' => $berhasil,
                    'fail_count' => $totalGagal,
                    'html' => "<ul class='text-left text-xs list-none p-0'>$details</ul>"
                ]);
            }

            return back()->with('success', "Berhasil! $berhasil data supplier telah diimpor.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{

    // 2. Aksi Tambah Karyawan Baru (Simpan ke Database)
        public function store(Request $request)
    {
        $request->validate([
            'id_karyawan'         => 'required|string|max:255|unique:employees,id_karyawan',
            'nama_karyawan'       => 'required|string|max:255',
            'tanggal_masuk_kerja' => 'required|date',
            'kelompok'            => 'required',
            'shift'               => 'required',
            'status_karyawan'     => 'required',
            'bagian'              => 'nullable|string',
            'no_hp'               => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:255|unique:employees,email',
            'no_rekening'         => 'nullable|string|max:30',
            'nama_bank'           => 'nullable|string|max:100',
        ]);

        // RUMUS DETEKTIF SAKRAL: SINKRONISASI 1000% SESUAI MIGRATION ENUM DATABASE
        $data = $request->all();

        if (isset($data['kelompok'])) {
            $data['kelompok'] = (str_contains(strtolower($data['kelompok']), 'tidak')) ? 'tidak langsung' : 'langsung';
        }
        if (isset($data['status_karyawan'])) {
            $data['status_karyawan'] = (str_contains(strtolower($data['status_karyawan']), 'tidak')) ? 'tidak tetap' : 'tetap';
        }
        if (isset($data['shift'])) {
            $s = strtolower($data['shift']);
            if ($s == 'a' || $s == 'shift a') {
                $data['shift'] = 'A'; // Hanya huruf kapital tunggal 'A'
            } elseif ($s == 'b' || $s == 'shift b') {
                $data['shift'] = 'B'; // Hanya huruf kapital tunggal 'B'
            } else {
                $data['shift'] = 'non shift'; // Huruf kecil pakai spasi murni
            }
        }

        // Simpan array $data yang sudah resmi suci murni lulus sensor ENUM database
        Employee::create($data);

        if (class_exists(\Spatie\Activitylog\Models\Activity::class) && Auth::check()) {
            \Spatie\Activitylog\Models\Activity::create([
                'log_name'    => 'attendance',
                'description' => 'CREATED',
                'subject_type'=> 'App\Models\Employee',
                'causer_type' => 'App\Models\User',
                'causer_id'   => Auth::id(),
                'properties' => [
                    'nama_karyawan' => $request->nama_karyawan,
                    'email'         => $request->email,
                    'keterangan'    => 'Menambahkan data master karyawan baru.'
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Data karyawan baru berhasil ditambahkan!');
    }

        public function update(Request $request, $id)
    {
        $request->validate([
            'id_karyawan'         => 'required|string|max:255|unique:employees,id_karyawan,' . $id,
            'nama_karyawan'       => 'required|string|max:255',
            'tanggal_masuk_kerja' => 'required|date',
            'kelompok'            => 'required',
            'shift'               => 'required',
            'status_karyawan'     => 'required',
            'bagian'              => 'nullable|string',
            'no_hp'               => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:255|unique:employees,email,' . $id,
            'no_rekening'         => 'nullable|string|max:30',
            'nama_bank'           => 'nullable|string|max:100',
        ]);

        // RUMUS DETEKTIF SAKRAL: SINKRONISASI 1000% SESUAI MIGRATION ENUM DATABASE
        $data = $request->all();

        if (isset($data['kelompok'])) {
            $data['kelompok'] = (str_contains(strtolower($data['kelompok']), 'tidak')) ? 'tidak langsung' : 'langsung';
        }
        if (isset($data['status_karyawan'])) {
            $data['status_karyawan'] = (str_contains(strtolower($data['status_karyawan']), 'tidak')) ? 'tidak tetap' : 'tetap';
        }
        if (isset($data['shift'])) {
            $s = strtolower($data['shift']);
            if ($s == 'a' || $s == 'shift a') {
                $data['shift'] = 'A';
            } elseif ($s == 'b' || $s == 'shift b') {
                $data['shift'] = 'B';
            } else {
                $data['shift'] = 'non shift';
            }
        }

        $employee = Employee::findOrFail($id);
        $employee->update($data);

        return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui!');
    }

    // 4. Aksi Destroy (Hapus Data Karyawan)
        public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        // 1. CATAT OTOMATIS KE LOG AKTIVITAS SPATIE SECARA SANGAT SINKRON SEBELUM DIHAPUS
        if (class_exists(\Spatie\Activitylog\Models\Activity::class) && Auth::check()) {
            \Spatie\Activitylog\Models\Activity::create([
                'log_name'    => 'attendance',
                'description' => 'DELETED',
                'subject_type'=> 'App\Models\Employee',
                'causer_type' => 'App\Models\User',
                'causer_id'   => Auth::id(),
                'properties'  => [
                    'nama_karyawan' => $employee->nama_karyawan,
                    'email'         => $employee->email,
                    'keterangan'    => 'Menghapus data master karyawan.'
                ]
            ]);
        }

        // 2. EKSEKUSI PENGHAPUSAN DATA DARI DATABASE NEON
        $employee->delete();

        return redirect()->back()->with('success', 'Data karyawan berhasil dihapus!');
    }

        // 1. HALAMAN UTAMA MASTER KARYAWAN - LOGIKA FILTERING TERINTEGRASI 100% AKURAT
    public function index(Request $request)
    {
        $query = \App\Models\Employee::query();

        // A. FILTER PENCARIAN NAMA DEPAN / ID KARYAWAN (ANTI-BENTROK)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('id_karyawan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_karyawan', 'LIKE', "%{$search}%");
            });
        }
        
        // B. FILTER KELOMPOK KERJA (KEBAL BENTROK HURUF BESAR KECIL)
        if ($request->filled('kelompok')) {
            $query->whereIn('kelompok', [$request->kelompok, strtolower($request->kelompok), ucfirst($request->kelompok)]);
        }
        
        // C. FILTER SHIFT KERJA (KEBAL BENTROK HURUF BESAR KECIL)
        if ($request->filled('shift')) {
            $query->whereIn('shift', [$request->shift, strtolower($request->shift), ucfirst($request->shift)]);
        }
        
        // D. FILTER STATUS KEPEGAWAIAN (KEBAL BENTROK HURUF BESAR KECIL)
        if ($request->filled('status_karyawan')) {
            $query->whereIn('status_karyawan', [$request->status_karyawan, strtolower($request->status_karyawan), ucfirst($request->status_karyawan)]);
        }
        
        // F. FILTER BARU PREMIUM: SARING BERDASARKAN BAGIAN / DIVISI KERJA PABRIK
        if ($request->filled('bagian')) {
            $query->where('bagian', $request->bagian);
        }
        
        // E. ATURAN URUTAN MASA KERJA (URUT BERDASARKAN TANGGAL MASUK KERJA POSTGRESQL)
        if ($request->urut_masa_kerja == 'id_asc') {
            $query->orderBy('id_karyawan', 'asc');
        } elseif ($request->urut_masa_kerja == 'id_desc') {
            $query->orderBy('id_karyawan', 'desc');
        } elseif ($request->urut_masa_kerja == 'lama_baru') {
            $query->orderBy('tanggal_masuk_kerja', 'asc')->orderBy('id_karyawan', 'asc');
        } else {
            $query->orderBy('tanggal_masuk_kerja', 'desc')->orderBy('id_karyawan', 'asc'); // Default
        }

        // Tarik data final hasil saringan
        $employees = $query->get();

        return view('employee.index', compact('employees'));
    }

        // 5. Aksi Unggah Excel Massal
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\EmployeeImport, $request->file('file_excel'));

        // Catat Otomatis ke Log Aktivitas Spatie
        \Spatie\Activitylog\Models\Activity::create([
            'log_name'     => 'attendance',
            'description'  => 'IMPORTED',
            'subject_type' => 'App\Models\Employee',
            'causer_type'  => 'App\Models\User',
            'causer_id'    => \Illuminate\Support\Facades\Auth::id(),
            'properties'   => ['keterangan' => 'Mengunggah data master karyawan massal via Excel.']
        ]);

        return redirect()->back()->with('success', 'Massal data karyawan sukses di-import!');
    }

        // 6. CETAK LAPORAN KARYAWAN BERDASARKAN FILTER YANG DIPILIH (CLEAN MATCH)
    public function print(Request $request)
    {
        $query = \App\Models\Employee::query();

        // 1. Filter Pencarian Nama / ID
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('id_karyawan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_karyawan', 'LIKE', "%{$search}%");
            });
        }

        // 2. FILTER KELOMPOK KERJA (KEBAL BENTROK HURUF BESAR KECIL)
        if ($request->filled('kelompok')) {
            $query->whereIn('kelompok', [$request->kelompok, strtolower($request->kelompok), ucfirst($request->kelompok)]);
        }

        // 3. FILTER SHIFT KERJA (KEBAL BENTROK HURUF BESAR KECIL)
        if ($request->filled('shift')) {
            $query->whereIn('shift', [$request->shift, strtolower($request->shift), ucfirst($request->shift)]);
        }

        // 4. FILTER STATUS KEPEGAWAIAN (KEBAL BENTROK HURUF BESAR KECIL)
        if ($request->filled('status_karyawan')) {
            $query->whereIn('status_karyawan', [$request->status_karyawan, strtolower($request->status_karyawan), ucfirst($request->status_karyawan)]);
        }

        // 5. FILTER PREMIUM: CETAK LAPORAN BERDASARKAN BAGIAN / DIVISI KERJA PABRIK
        if ($request->filled('bagian')) {
            $query->where('bagian', $request->bagian);
        }


        // 6. Aturan Urutan Masa Kerja
                // LOGIKA BARU: KEBAL ACAK-ACAKAN, MENDUKUNG SORTIR MASA KERJA & NOMOR ID KARYAWAN
        if ($request->urut_masa_kerja == 'id_asc') {
            $query->orderBy('id_karyawan', 'asc'); // Urut ID Terkecil ke Terbesar (MRSA-001 -> MRSA-014)
        } elseif ($request->urut_masa_kerja == 'id_desc') {
            $query->orderBy('id_karyawan', 'desc'); // Urut ID Terbesar ke Terkecil (MRSA-014 -> MRSA-001)
        } elseif ($request->urut_masa_kerja == 'lama_baru') {
            $query->orderBy('tanggal_masuk_kerja', 'asc')->orderBy('id_karyawan', 'asc');
        } else {
            $query->orderBy('tanggal_masuk_kerja', 'desc')->orderBy('id_karyawan', 'asc'); // Default
        }

        $employees = $query->get();

        return view('employee.print', compact('employees'));
    }


        // KODE BARU: AKSI EKSEKUSI UNDUH DATA MASTER EXCEL KARYAWAN (INTEGRASI FILTER)
    public function export(Request $request)
    {
        $query = \App\Models\Employee::query();

        // 1. Filter Pencarian Nama / ID
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('id_karyawan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_karyawan', 'LIKE', "%{$search}%");
            });
        }

        // 2. FILTER KELOMPOK KERJA (KEBAL BENTROK HURUF BESAR KECIL)
        if ($request->filled('kelompok')) {
            $query->whereIn('kelompok', [$request->kelompok, strtolower($request->kelompok), ucfirst($request->kelompok)]);
        }

        // 3. FILTER SHIFT KERJA (KEBAL BENTROK HURUF BESAR KECIL)
        if ($request->filled('shift')) {
            $query->whereIn('shift', [$request->shift, strtolower($request->shift), ucfirst($request->shift)]);
        }

        // 4. FILTER STATUS KEPEGAWAIAN (KEBAL BENTROK HURUF BESAR KECIL)
        if ($request->filled('status_karyawan')) {
            $query->whereIn('status_karyawan', [$request->status_karyawan, strtolower($request->status_karyawan), ucfirst($request->status_karyawan)]);
        }

        // 5. FILTER PREMIUM: EXPORT DATA EXCEL BERDASARKAN BAGIAN / DIVISI KERJA
        if ($request->filled('bagian')) {
            $query->where('bagian', $request->bagian);
        }

        // 6. Aturan Urutan Masa Kerja
            // LOGIKA BARU: KEBAL ACAK-ACAKAN, MENDUKUNG SORTIR MASA KERJA & NOMOR ID KARYAWAN
        if ($request->urut_masa_kerja == 'id_asc') {
            $query->orderBy('id_karyawan', 'asc'); // Urut ID Terkecil ke Terbesar (MRSA-001 -> MRSA-014)
        } elseif ($request->urut_masa_kerja == 'id_desc') {
            $query->orderBy('id_karyawan', 'desc'); // Urut ID Terbesar ke Terkecil (MRSA-014 -> MRSA-001)
        } elseif ($request->urut_masa_kerja == 'lama_baru') {
            $query->orderBy('tanggal_masuk_kerja', 'asc')->orderBy('id_karyawan', 'asc');
        } else {
            $query->orderBy('tanggal_masuk_kerja', 'desc')->orderBy('id_karyawan', 'asc'); // Default
        }


        // Ambil data hasil saringan bersih
        $dataEmployees = $query->get();

        // Download otomatis melempar data ke berkas EmployeeExport
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\EmployeeExport($dataEmployees), 
            'Master_Data_Karyawan_' . date('Ymd_His') . '.xlsx'
        );
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
        // 1. HALAMAN UTAMA - MENAMPILKAN DAFTAR ABSENSI + FITUR FILTER MULTI-TANGGAL
    public function index(Request $request)
    {
        // Membuka query awal dengan merelasikan tabel data karyawan
        $query = Attendance::with('employee');
        // KODE BARU: FILTER PENCARIAN MASIF BERDASARKAN NAMA / ID KARYAWAN (ANTI-BENTROK)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('id_karyawan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_karyawan', 'LIKE', "%{$search}%");
            });
        }

        // A. FILTER RENTANG TANGGAL (Dari Tanggal A sampai Tanggal B)
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai]);
        } elseif ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        } elseif ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        // B. FILTER BERDASARKAN KELOMPOK KERJA (Langsung / Tidak Langsung)
        if ($request->filled('kelompok_kerja_harian')) {
            $query->where('kelompok_kerja_harian', 'LIKE', '%' . $request->kelompok_kerja_harian . '%');
        }

        // C. FILTER BERDASARKAN SHIFT KERJA (A, B, atau Non Shift)
        if ($request->filled('shift')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('shift', $request->shift);
            });
        }

        // Ambil data hasil saringan terbaru
        $attendances = $query->latest('tanggal')->paginate(100);
        
        // Mengambil master karyawan untuk keperluan dropdown form input atas
        $employees = Employee::orderBy('id_karyawan', 'asc')->get();

        return view('attendance.index', compact('attendances', 'employees'));
    }

    // 2. AKSI SIMPAN DATA ABSENSI BARU + KALKULASI OTOMATIS
    public function store(Request $request)
    {
        $request->validate([
            'id_karyawan'           => 'required|exists:employees,id_karyawan',
            'tanggal'               => 'required|date',
            'kelompok_kerja_harian' => 'required', // Validasi input baru
            'jam_masuk'             => 'nullable',
            'jam_pulang'            => 'nullable',
            'keterangan'            => 'required',
        ]);

        // Cari data primer model Employee berdasarkan ID uniknya
        $employee = Employee::where('id_karyawan', $request->id_karyawan)->firstOrFail();

        // Ambil data kiriman input form absensi
        $jamMasuk = $request->jam_masuk;
        $jamPulang = $request->jam_pulang;

        // Eksekusi penyimpanan data ke database PostgreSQL
        Attendance::create([
            'employee_id' => $employee->id, // Mengunci relasi ID Master Karyawan
            'tanggal'     => $request->tanggal,
            'kelompok_kerja_harian' => $request->kelompok_kerja_harian,
            'jam_masuk'   => $jamMasuk,
            'jam_pulang'  => $jamPulang,
            'status_kehadiran' => $request->status_kehadiran ?? 'Hadir',
            'keterangan'  => $request->keterangan,
        ]);

        // Catat Otomatis ke Log Aktivitas Spatie
        Activity::create([
            'log_name'     => 'attendance',
            'description'  => 'CREATED',
            'subject_type' => 'App\Models\Attendance',
            'causer_type'  => 'App\Models\User',
            'causer_id'    => Auth::id(),
            'properties'   => [
                'nama_karyawan' => $employee->nama_karyawan,
                'keterangan'    => 'Mencatat kehadiran absensi baru manual.'
            ]
        ]);

        return redirect()->back()->with('success', 'Data absensi karyawan berhasil dicatat!');
    }

    // 3. AKSI HAPUS DATA ABSENSI
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return redirect()->back()->with('success', 'Data absensi berhasil dihapus!');
    }
        // FUNCTION UPDATE DATA ABSENSI (BARU)
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $request->validate([
            'tanggal'    => 'required|date',
            'jam_masuk'  => 'required',
            'jam_pulang' => 'required',
            'kelompok_kerja_harian' => 'required',
            'keterangan' => 'required',
        ]);

        $attendance->update([
            'tanggal'    => $request->tanggal,
            'jam_masuk'  => $request->jam_masuk,
            'jam_pulang' => $request->jam_pulang,
            'kelompok_kerja_harian' => $request->kelompok_kerja_harian,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Catatan absensi berhasil diperbarui!');
    }

    // KODE BARU: AKSI EKSEKUSI UNGHAH EXCEL MASSAL ABSENSI
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\AttendanceImport, $request->file('file_excel'));
            return redirect()->back()->with('success', 'Seluruh data absensi massal karyawan sukses di-import!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel, periksa kembali format kolom Anda.');
        }
    }
        // KODE BARU: FUNGSI CETAK LAPORAN REKAP ABSENSI TERINTEGRASI FILTER
    public function print(Request $request)
    {
        $query = Attendance::with('employee');
                // KODE BARU: FILTER PENCARIAN MASIF BERDASARKAN NAMA / ID KARYAWAN (ANTI-BENTROK)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('id_karyawan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_karyawan', 'LIKE', "%{$search}%");
            });
        }


        // Samakan logika saringan kertas laporan dengan saringan halaman utama
        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai]);
        }
        if ($request->filled('kelompok_kerja_harian')) {
            $query->where('kelompok_kerja_harian', 'LIKE', '%' . $request->kelompok_kerja_harian . '%');
        }
        if ($request->filled('shift')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('shift', $request->shift);
            });
        }

        $attendances = $query->orderBy('tanggal', 'asc')->get();

        return view('attendance.print', compact('attendances'));
    }
        // KODE BARU: AKSI EKSEKUSI UNDUH DATA EXCEL ABSENSI (MENDUKUNG FILTER)
    public function export(Request $request)
    {
        $query = Attendance::with('employee');

        // Satuan saringan ekspor agar sama persis dengan saringan halaman utama
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('id_karyawan', 'LIKE', "%{$search}%")
                  ->orWhere('nama_karyawan', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal', [$request->tanggal_mulai, $request->tanggal_selesai]);
        }

        if ($request->filled('kelompok_kerja_harian')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('kelompok', 'LIKE', '%' . $request->kelompok_kerja_harian . '%');
            });
        }

        if ($request->filled('shift')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('shift', $request->shift);
            });
        }

        // 🚀 KUNCI KEMENANGAN SORTING: Mengurutkan data Excel secara runtut tegak lurus mulai dari tanggal terkecil!
        $attendances = $query->orderBy('tanggal', 'asc')->get();

        // Panggil file AttendanceExport untuk dikonversi jadi file Excel unduhan
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceExport($attendances),
            'Rekap_Absensi_Karyawan_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function bulkDelete(\Illuminate\Http\Request $request)
    {
        // 1. Ambil data array dari name checkbox HTML depan
        $ids = $request->input('ids_absensi');

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'Silakan centang minimal satu data absensi!');
        }

        try {
            // 2. PERBAIKAN: Jalankan query whereIn secara langsung, lalu panggil method delete() di akhir query
            \App\Models\Attendance::whereIn('id', $ids)->delete();

            return redirect()->back()->with('success', count($ids) . ' Baris Data Rincian Absensi Berhasil Dihapus Massal!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses hapus massal: ' . $e->getMessage());
        }
    }


}

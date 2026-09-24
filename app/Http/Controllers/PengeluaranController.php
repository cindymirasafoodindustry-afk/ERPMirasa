<?php

namespace App\Http\Controllers;

use App\Models\KategoriPemakaian;
use App\Models\Pemakaian;
use App\Models\Pengeluaran;
use App\Models\Perusahaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PengeluaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Filter Kategori Berdasarkan Permission User
        $allKategori = [
            'OPERASIONAL' => 'pengeluaran.operasional',
            'OFFICE' => 'pengeluaran.office',
            'LIMBAH' => 'pengeluaran.limbah',
            'KESEJAHTERAAN' => 'pengeluaran.kesejahteraan',
            'MAINTENANCE' => 'pengeluaran.maintenance',
            'ADMINISTRASI' => 'pengeluaran.administrasi',
        ];

        $kategoriList = [];
        foreach ($allKategori as $katName => $permission) {
            if ($user->can($permission)) {
                $kategoriList[] = $katName;
            }
        }

        // Jika user sama sekali tidak punya akses ke kategori manapun
        if (empty($kategoriList)) {
            abort(403, 'Anda tidak memiliki izin untuk melihat data pengeluaran.');
        }

        // 2. Inisialisasi Filter
        $search = $request->get('search');
        $id_perusahaan = $request->get('id_perusahaan');
        $is_hpp = $request->get('is_hpp');
        $metode_alokasi = $request->get('metode_alokasi');
        $date_range = $request->get('date_range');
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        // 3. Base Query
        $queryAll = Pengeluaran::query();

        // KEAMANAN: Filter Perusahaan
        $queryAll->where(function ($q) use ($id_perusahaan, $user) {
            if ($user->hasRole('Super Admin')) {
                if ($id_perusahaan) {
                    $q->where('id_perusahaan', $id_perusahaan);
                }
            } else {
                $q->where('id_perusahaan', $user->id_perusahaan);
            }
        });

        // Filter Kategori (Hanya ambil data yang user boleh lihat)
        $queryAll->whereIn('kategori', $kategoriList);

        // Filter Pencarian
        if ($search) {
            $searchUpper = strtoupper($search);
            $queryAll->where(function ($q) use ($searchUpper) {
                $q->where('nama_pengeluaran', 'LIKE', "%{$searchUpper}%")
                    ->orWhere('sub_kategori', 'LIKE', "%{$searchUpper}%")
                    ->orWhere('keterangan', 'LIKE', "%{$searchUpper}%")
                    ->orWhere('jumlah_pengeluaran', 'LIKE', "%{$searchUpper}%");
            });
        }

        // Filter HPP
        if ($is_hpp !== null && $is_hpp !== '') {
            $queryAll->where('is_hpp', $is_hpp);
        }

        if ($metode_alokasi) {
            $queryAll->where('metode_alokasi', $metode_alokasi);
        }

        // Filter Tanggal
        if ($date_range) {
            $dates = explode(' to ', $date_range);
            if (count($dates) == 2) {
                $queryAll->whereBetween('tanggal_pengeluaran', [$dates[0], $dates[1]]);
            } else {
                $queryAll->where('tanggal_pengeluaran', $dates[0]);
            }
        } else {
            $queryAll->whereMonth('tanggal_pengeluaran', $month)
                ->whereYear('tanggal_pengeluaran', $year);
        }

        // Eksekusi data untuk perhitungan total (Hanya kategori yang diizinkan)
        $allData = $queryAll->get();
        $totalPengeluaran = $allData->sum('jumlah_pengeluaran');

        // 4. Looping Kategori Berdasarkan Izin
        $perKategori = [];
        foreach ($kategoriList as $kat) {
            $pageName = 'page_' . strtolower(str_replace(' ', '_', $kat));

            $items = (clone $queryAll)->where('kategori', $kat)
                ->latest('tanggal_pengeluaran')
                ->paginate(10, ['*'], $pageName)
                ->withQueryString();

            $totalPerKat = $allData->where('kategori', $kat)->sum('jumlah_pengeluaran');

            $perKategori[$kat] = [
                'items' => $items,
                'total' => $totalPerKat,
                'pageName' => $pageName
            ];
        }

        // Tentukan Tab Aktif (Pastikan tab default adalah kategori yang user boleh lihat)
        $activeTab = $request->get('tab', $kategoriList[0]);

        // Validasi ulang activeTab jika user mencoba memasukkan tab manual via URL
        if (!in_array($activeTab, $kategoriList)) {
            $activeTab = $kategoriList[0];
        }

        $perusahaan = $user->hasRole('Super Admin') ? Perusahaan::all() : collect();

        return view('pages.pengeluaran.index', compact(
            'totalPengeluaran',
            'perKategori',
            'activeTab',
            'month',
            'year',
            'kategoriList',
            'perusahaan'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createOperasional()
    {
        return view('pages.pengeluaran.create-operasional');
    }

    public function createOffice()
    {
        return view('pages.pengeluaran.create-office');
    }

    public function createPengolahanLimbah()
    {
        return view('pages.pengeluaran.create-limbah');
    }

    public function createGajiKaryawan()
    {
        return view('pages.pengeluaran.create-gaji');
    }

    public function createMaintenance()
    {
        return view('pages.pengeluaran.create-maintenance');
    }

    public function createAdministrasi()
    {
        return view('pages.pengeluaran.create-administrasi');
    }
    
    /**
     * Menyimpan pengeluaran baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pengeluaran' => 'required|string|max:255',
            'kategori' => 'required',
            'jumlah_pengeluaran' => 'required|numeric',
            'tanggal_pengeluaran' => 'required|date',
            'absensi' => 'nullable|numeric',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $Kategori = strtoupper($request->kategori);
            $subKategori = strtoupper($request->sub_kategori);
            $id_perusahaan = auth()->user()->id_perusahaan;

            // 1. Handle Upload Bukti
            $path = null;
            if ($request->hasFile('bukti')) {
                $path = $this->compressFile($request->file('bukti'));
            }

            $is_hpp = ($request->is_hpp == '1' || $request->is_hpp == 'on') ? 'true' : 'false';

            // 2. Simpan Data ke Tabel Pengeluaran
            $pengeluaran = Pengeluaran::create([
                'id_perusahaan'      => auth()->user()->id_perusahaan,
                'tanggal_pengeluaran' => $request->tanggal_pengeluaran,
                'nama_pengeluaran'   => $request->nama_pengeluaran,
                'kategori'           => $Kategori,
                'sub_kategori'       => $subKategori,
                'metode_alokasi'     => $request->metode_alokasi ?? 'FIXED',
                'jumlah_pengeluaran' => $request->jumlah_pengeluaran,
                'absensi'            => $request->absensi,
                'is_hpp'             => $is_hpp,
                'keterangan'         => $request->keterangan,
                'bukti'              => $path,
            ]);

            // 3. Logika Khusus Gas: Hubungkan pemakaian harian yang belum terbayar
            if ($Kategori === 'OPERASIONAL') {
                // Cek apakah sub_kategori yang diinput terdaftar di tabel kategori_pemakaian
                $cekKategoriPemakaian = KategoriPemakaian::where('id_perusahaan', $id_perusahaan)
                    ->where('nama_kategori', $subKategori)
                    ->first();

                if ($cekKategoriPemakaian) {
                    // Tentukan awal dan akhir bulan dari tanggal pengeluaran yang diinput
                    $tanggalInput = Carbon::parse($request->tanggal_pengeluaran);
                    $awalBulan = $tanggalInput->copy()->startOfMonth();
                    $akhirBulan = $tanggalInput->copy()->endOfMonth();

                    // Update semua pemakaian (Listrik, Gas, Air, dll) yang:
                    // 1. Milik kategori yang cocok
                    // 2. Belum memiliki relasi pengeluaran (id_pengeluaran IS NULL)
                    // 3. Dalam periode bulan yang sama
                    Pemakaian::where('id_perusahaan', $id_perusahaan)
                        ->where('id_kategori', $cekKategoriPemakaian->id)
                        ->whereNull('id_pengeluaran')
                        ->whereBetween('tanggal_pemakaian', [$awalBulan, $akhirBulan])
                        ->update(['id_pengeluaran' => $pengeluaran->id]);
                }
            }

            DB::commit();
            return redirect()->route('pengeluaran.index', ['tab' => $Kategori])
                ->with('success', 'Pengeluaran ' . $Kategori . ' berhasil dicatat!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = auth()->user();
        $pengeluaran = Pengeluaran::findOrFail($id);
        if (!$user->hasRole('Super Admin') && $user->id_perusahaan !== $pengeluaran->id_perusahaan) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data ini.');
        }

        return view('pages.pengeluaran.edit', compact('pengeluaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pengeluaran' => 'required|string|max:255',
            'kategori' => 'required',
            'jumlah_pengeluaran' => 'required|numeric',
            'tanggal_pengeluaran' => 'required|date',
            'absensi' => 'nullable|numeric',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $pengeluaran = Pengeluaran::findOrFail($id);
            if (!$user->hasRole('Super Admin') && $user->id_perusahaan !== $pengeluaran->id_perusahaan) {
                abort(403, 'Anda tidak memiliki izin untuk mengedit data ini.');
            }
            $id_perusahaan = auth()->user()->id_perusahaan;
            $Kategori = strtoupper($request->kategori);
            $subKategori = strtoupper($request->sub_kategori);

            // 1. Handle Update Bukti (Hapus file lama jika ada upload baru)
            $path = $pengeluaran->bukti;
            if ($request->hasFile('bukti')) {
                // Hapus file lama dari storage
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
                // Upload & compress file baru
                $path = $this->compressFile($request->file('bukti'));
            }

            // 2. Lepaskan kaitan Pemakaian lama (Rollback kaitan sebelum diupdate)
            // Ini penting agar jika tanggal atau sub_kategori berubah, data lama tidak menggantung
            Pemakaian::where('id_pengeluaran', $pengeluaran->id)
                ->update(['id_pengeluaran' => null]);

            $is_hpp = ($request->is_hpp == '1' || $request->is_hpp == 'on') ? 'true' : 'false';

            $absensi = $request->absensi;
            if ($Kategori === 'KESEJAHTERAAN' && $subKategori !== 'GAJI') {
                $absensi = null;
            }

            // 3. Update Data Utama
            $pengeluaran->update([
                'tanggal_pengeluaran' => $request->tanggal_pengeluaran,
                'nama_pengeluaran'    => $request->nama_pengeluaran,
                'kategori'           => $Kategori,
                'sub_kategori'       => $subKategori,
                'metode_alokasi'     => $request->metode_alokasi ?? 'FIXED',
                'jumlah_pengeluaran' => $request->jumlah_pengeluaran,
                'absensi'            => $absensi,
                'is_hpp'             => $is_hpp,
                'keterangan'         => $request->keterangan,
                'bukti'              => $path,
            ]);

            // 4. Logika Khusus Operasional (Gas, Listrik, Air, dll)
            if ($Kategori === 'OPERASIONAL') {
                $cekKategoriPemakaian = KategoriPemakaian::where('id_perusahaan', $id_perusahaan)
                    ->where('nama_kategori', $subKategori)
                    ->first();

                if ($cekKategoriPemakaian) {
                    $tanggalInput = Carbon::parse($request->tanggal_pengeluaran);
                    $awalBulan = $tanggalInput->copy()->startOfMonth();
                    $akhirBulan = $tanggalInput->copy()->endOfMonth();

                    // Hubungkan kembali pemakaian yang sesuai dengan data baru
                    Pemakaian::where('id_perusahaan', $id_perusahaan)
                        ->where('id_kategori', $cekKategoriPemakaian->id)
                        ->whereNull('id_pengeluaran') // Hanya yang belum terbayar
                        ->whereBetween('tanggal_pemakaian', [$awalBulan, $akhirBulan])
                        ->update(['id_pengeluaran' => $pengeluaran->id]);
                }
            }

            DB::commit();
            return redirect()->route('pengeluaran.index', ['tab' => $Kategori])
                ->with('success', 'Data pengeluaran ' . $Kategori . ' berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // 1. Cari data pengeluaran berdasarkan ID
        $pengeluaran = Pengeluaran::findOrFail($id);
        $kategoriAsal = $pengeluaran->kategori;

        try {
            DB::beginTransaction();

            // 2. Hapus file bukti dari storage jika ada
            if ($pengeluaran->bukti && Storage::disk('public')->exists($pengeluaran->bukti)) {
                Storage::disk('public')->delete($pengeluaran->bukti);
            }

            // 3. Logika Khusus Operasional (Gas, Listrik, Air, dll)
            if (strtoupper($pengeluaran->kategori) === 'OPERASIONAL') {
                Pemakaian::where('id_pengeluaran', $pengeluaran->id)
                    ->update(['id_pengeluaran' => null]);
            }

            // 4. Hapus data dari database
            $pengeluaran->delete();

            DB::commit();

            // Redirect kembali ke tab kategori yang sama agar user tidak bingung
            return redirect()->route('pengeluaran.index', ['tab' => $kategoriAsal])
                ->with('success', 'Data pengeluaran berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    private function compressFile($file, $folder = 'bukti_pengeluaran')
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::random(40) . '.' . $extension;
        $destinationPath = storage_path('app/public/' . $folder);

        // Buat folder jika belum ada
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            // --- LOGIKA KOMPRESI GAMBAR ---
            $source = ($extension == 'png')
                ? @imagecreatefrompng($file->getRealPath())
                : @imagecreatefromjpeg($file->getRealPath());

            if (!$source) {
                // Jika gagal load gambar (file korup), simpan apa adanya
                return $file->storeAs($folder, $filename, 'public');
            }

            // Simpan dengan kualitas 60%
            $fullPath = $destinationPath . '/' . $filename;
            if ($extension == 'png') {
                imagepng($source, $fullPath, 6); // Skala 0-9 untuk PNG
            } else {
                imagejpeg($source, $fullPath, 60); // Kualitas 0-100 untuk JPG
            }

            imagedestroy($source);
            return $folder . '/' . $filename;
        } elseif ($extension == 'pdf') {
            return $file->storeAs($folder, $filename, 'public');
        }

        return null;
    }
}
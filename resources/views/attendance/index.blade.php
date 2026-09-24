<x-layout.user.app title="Form Absensi Kayawan">
        <div class="p-6 space-y-8 w-full" x-data="{ openImportAbsen: false }">
        
        <!-- HEADER HALAMAN -->
        <div class="flex items-center justify-between bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Pencatatan Absensi Karyawan</h1>
                    <p class="text-xs text-gray-500 font-medium">Sistem manajemen kehadiran otomatis terintegrasi ID Data Master Karyawan PT MIRASA FOOD INDUSTRY</p>
                </div>
            </div>
        </div>

        <!-- FORM INPUT MANUAL ABSENSI CEREBRO (PENGATURAN LEBAR JAM FIX) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-6">
            <h2 class="text-sm font-black text-gray-800 uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 pb-3">
                <span class="w-2 h-4 bg-blue-600 rounded-sm"></span> Input Kehadiran & Jam Kerja
            </h2>

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('attendance.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Dropdown ID Karyawan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">ID Karyawan</label>
                        <select name="id_karyawan" id="id_karyawan" required class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all" onchange="cariProfilKaryawan(this.value)">
                            <option value="">-- Pilih ID Karyawan --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id_karyawan }}">{{ $emp->id_karyawan }} - {{ $emp->nama_karyawan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input Tanggal -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50 focus:bg-white transition-all">
                    </div>

                    <!-- Input Jam Masuk (Menggunakan p-3 minimum-width agar jam kelihatan utuh) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jam Masuk</label>
                        <input type="time" name="jam_masuk" value="07:00" required class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50 focus:bg-white transition-all min-w-[150px]">
                    </div>

                    <!-- Input Jam Pulang (Menggunakan p-3 minimum-width agar jam kelihatan utuh) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jam Pulang</label>
                        <input type="time" name="jam_pulang" value="16:00" required class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50 focus:bg-white transition-all min-w-[150px]">
                    </div>
                </div>

                <!-- ASISTEN PROFIL OTOMATIS -->
                <!-- ASISTEN PROFIL OTOMATIS & DROPDOWN PENEMPATAN HARIAN (BARIS 63-97) -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 border border-gray-100 rounded-2xl p-4 mb-4 text-xs font-semibold text-gray-600">
                    
                    <!-- 1. Nama Karyawan (Otomatis dari JS) -->
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Nama Karyawan</p>
                        <p id="txt_nama" class="text-sm font-bold text-gray-800 italic">- Belum Dipilih -</p>
                    </div>

                    <!-- 2. Bagian / Divisi (Ditembak Otomatis oleh JS ke id="txt_bagian") -->
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Bagian / Divisi</p>
                        <p id="txt_bagian" class="text-sm font-bold text-gray-800 italic">- Belum Dipilih -</p>
                    </div>
                    <!-- 4. Shift Kerja (Otomatis dari JS) -->
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Shift Kerja</p>
                        <p id="txt_shift" class="text-sm font-bold text-gray-800 italic">- Belum Dipilih -</p>
                    </div>
                    <!-- 3. INPUT DROPDOWN MANUAL BARU (Wajib Dipilih Admin) -->
                    <div>
                        <label class="block text-[10px] text-gray-400 font-bold uppercase mb-1">Kelompok Kerja (Hari Ini)</label>
                        <select name="kelompok_kerja_harian" id="kelompok_kerja_harian" class="w-full text-xs font-bold text-gray-800 bg-transparent border-0 p-0 focus:ring-0 focus:outline-none cursor-pointer" required>
                            <option value="" disabled selected>-- Pilih Kelompok Kerja --</option>
                            <optgroup label="LANGSUNG (PRODUKSI)">
                                <option value="LANGSUNG - IFM">LANGSUNG - IFM</option>
                                <option value="LANGSUNG - MANUAL">LANGSUNG - MANUAL</option>
                                <option value="LANGSUNG - PACKING">LANGSUNG - PACKING</option>
                            </optgroup>
                            <optgroup label="TIDAK LANGSUNG">
                                <option value="TIDAK LANGSUNG">TIDAK LANGSUNG</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Keterangan / Teks Catatan</label>
                    <input type="text" name="keterangan" placeholder="Silahkan input keterangan disini" required class="w-full rounded-xl border-gray-200 text-xs p-3 bg-gray-50 focus:bg-white transition-all font-semibold text-slate-700">
                </div>

                <!-- MERUBAH AREA FOOTER FORM MENJADI SEPASANG TOMBOL BERJEJER -->
                <div class="flex justify-end items-center gap-3 pt-2">
                    <!-- 1. TOMBOL UTAMA IMPORT EXCEL MASSAL VERSI NATIVE -->
                    <button type="button" onclick="bukaModalImport()" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition-all shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6m0 0l-3-3m3 3l3-3M3.75 13.5h16.5M21 3.75v16.5A1.5 1.5 0 0119.5 21H4.5A1.5 1.5 0 013 19.5V3.75A1.5 1.5 0 014.5 2H19.5A1.5 1.5 0 0121 3.75z" />
                        </svg>
                        Import Excel Massal
                    </button>

                    <!-- 2. Tombol Biru Simpan Data Absensi Bawaan Anda -->
                    <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm transition-all shadow-sm flex items-center gap-2">
                        Simpan Data Absensi
                    </button>
                </div>
            </form>
        </div>

                <!-- KOMPONEN BARIS SARING PENCATATAN ABSENSI (MATCH DESIGN ORIGINAL) -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-50 pb-3">
                <h2 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    Saring Pencarian Rekap Absensi Karyawan
                </h2>
            </div>

            <!-- Form Mengirim Atribut Filter GET Kembali ke Controller -->
            <form action="{{ route('attendance.index') }}" method="GET" class="space-y-4">
                
                <!-- BARIS 1: AREA KOTAK INPUT FILTER (DIBAGI 5 KOLOM SUPER LEGA & SEIMBANG) -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- 1. Input Pencarian Nama / ID -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Cari Karyawan</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik ID / Nama..." class="w-full rounded-xl border-gray-200 text-xs p-2.5 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all font-semibold text-slate-700">
                    </div>

                    <!-- 2. Input Tanggal Mulai -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="w-full rounded-xl border-gray-200 text-xs p-2.5 bg-gray-50/50 focus:bg-white transition-all">
                    </div>

                    <!-- 3. Input Tanggal Selesai -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="w-full rounded-xl border-gray-200 text-xs p-2.5 bg-gray-50/50 focus:bg-white transition-all">
                    </div>

                    <!-- 4. Dropdown Shift Kerja -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Shift Kerja</label>
                        <select name="shift" class="w-full rounded-xl border-gray-200 text-xs p-2.5 bg-gray-50/50 text-gray-600 font-medium">
                            <option value="">-- Semua Shift --</option>
                            <option value="A" {{ request('shift') == 'A' ? 'selected' : '' }}>SHIFT A</option>
                            <option value="B" {{ request('shift') == 'B' ? 'selected' : '' }}>SHIFT B</option>
                            <option value="Non Shift" {{ request('shift') == 'Non Shift' ? 'selected' : '' }}>NON SHIFT</option>
                        </select>
                    </div>

                    <!-- 5. Dropdown Kelompok Kerja -->
                    <div class="w-full">
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Kelompok Kerja (Hari Ini)</label>
                        <select name="kelompok_kerja_harian" id="kelompok_kerja_harian" class="w-full rounded-xl border-gray-200 text-xs p-2.5 bg-gray-50/50 text-gray-600 font-medium">
                            <option value="">-- Semua Kelompok --</option>
                            <optgroup label="LANGSUNG (PRODUKSI)">
                                <option value="LANGSUNG - IFM" {{ request('kelompok_kerja_harian') == 'LANGSUNG - IFM' ? 'selected' : '' }}>LANGSUNG - IFM</option>
                                <option value="LANGSUNG - MANUAL" {{ request('kelompok_kerja_harian') == 'LANGSUNG - MANUAL' ? 'selected' : '' }}>LANGSUNG - MANUAL</option>
                                <option value="LANGSUNG - PACKING" {{ request('kelompok_kerja_harian') == 'LANGSUNG - PACKING' ? 'selected' : '' }}>LANGSUNG - PACKING</option>
                            </optgroup>
                            <!-- Opsi Kelompok Kerja Tidak Langsung -->
                            <optgroup label="TIDAK LANGSUNG">
                                <option value="TIDAK LANGSUNG" {{ request('kelompok_kerja_harian') == 'TIDAK LANGSUNG' ? 'selected' : '' }}>TIDAK LANGSUNG</option>
                            </optgroup>
                        </select>
                    </div>
                </div>

                <!-- BARIS 2: AREA TOMBOL AKSI UTAMA BERJAJAR RAPI DI POJOK KANAN BAWAH -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-50 pt-4">
                    
                    <!-- A. Tombol Reset Filter Otomatis Cerdas -->
                    @if(request()->filled('search') || request()->filled('tanggal_mulai') || request()->filled('tanggal_selesai') || request()->filled('kelompok_kerja_harian') || request()->filled('shift'))
                        <a href="{{ route('attendance.index') }}" class="px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-600 font-black rounded-xl text-xs transition-all shadow-sm border border-gray-200 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Reset Filter
                        </a>
                    @endif

                    <!-- B. Tombol Terapkan (Biru) -->
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl text-xs transition-all shadow-sm uppercase tracking-wider flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Terapkan Saringan
                    </button>

                    <!-- C. Tombol Ekspor Excel (Hijau Emerald) -->
                    <a href="{{ route('attendance.export', request()->query()) }}" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs transition-all shadow-sm flex items-center gap-1.5 uppercase tracking-wider">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Ekspor Excel
                    </a>

                    <!-- D. Tombol Laporan (Ungu) -->
                    <a href="{{ route('attendance.print', request()->query()) }}" target="_blank" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl text-xs transition-all shadow-sm flex items-center gap-1.5 uppercase tracking-wider">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.8A8.004 8.004 0 1021 12h-3m-3.06-3.06l-3.06-3.06M14.94 9H9" />
                        </svg>
                        Cetak Laporan
                    </a>
                </div>

            </form>
        </div>

        <!-- DAFTAR REKAP DATA TABEL ABSENSI (PENYEJAJARAN KOLOM FIX) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-black text-gray-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-4 bg-emerald-500 rounded-sm"></span> Tabel Rincian Absensi Karyawan
                </h2>
                <span class="px-3 py-1 text-xs font-black rounded-full bg-blue-50 text-blue-600 border border-blue-100 shadow-sm">
                    Total: {{ $attendances->total() }} Records
                </span>
            </div>
            
            <form action="{{ route('attendance.bulkDelete') }}" method="POST" id="form-bulk-delete">
                @csrf
                @method('DELETE')
            </form>

            <!-- Tombol ditaruh mandiri di bawahnya dan dihubungkan menggunakan atribut form="" -->
            <div class="flex justify-start mb-4 no-print">
                <button type="submit" form="form-bulk-delete" onclick="return confirm('Apakah Kakak yakin ingin menghapus seluruh data absensi yang diceklis secara massal?')" class="inline-flex items-center gap-x-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded shadow-sm transition-colors">
                    Hapus Massal Terpilih
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <!-- SUNTIKAN 1: Kolom Boks Master Ceklis Paling Atas Kepala Tabel -->
                            <th class="p-4 border border-slate-700 text-center w-12 bg-slate-900">
                                <input type="checkbox" id="checkAllMaster" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4">
                            </th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">ID Karyawan</th>
                            <th class="px-6 py-4">Nama Karyawan</th>
                            <th class="px-6 py-4 text-center">Kelompok Kerja (Hari Ini)</th>
                            <th class="px-6 py-4 text-center">Shift</th>
                            <th class="px-6 py-4 text-center">Jam Masuk</th>
                            <th class="px-6 py-4 text-center">Jam Pulang</th>
                            <th class="px-6 py-4 text-center">Jam Kerja (Net)</th>
                            <th class="px-6 py-4 text-center">Jam Lembur</th>
                            <th class="px-6 py-4">Keterangan</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                         @forelse($attendances as $att)
                            @php
                                $jamKerjaNet = 0;
                                $jamLembur = 0;

                                if ($att->jam_masuk && $att->jam_pulang) {
                                    $masuk = \Carbon\Carbon::parse($att->jam_masuk);
                                    $pulang = \Carbon\Carbon::parse($att->jam_pulang);
                                    $totalJamKotor = $masuk->diffInHours($pulang);
                                    
                                    // 1. Potong 1 jam istirahat harian
                                    $jamKerjaNet = $totalJamKotor > 1 ? $totalJamKotor - 1 : $totalJamKotor;

                                    // 2. Hitung lembur jika jam kerja bersih di atas 7 jam
                                    if ($jamKerjaNet > 7) {
                                        $jamLembur = $jamKerjaNet - 7;
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-all">
                                <!-- 🚀 SUNTIKAN 2: Kotak Boks Ceklis Satuan Pengunci ID Absensi di Setiap Baris Nama Karyawan -->
                                <td class="p-4 text-center border-b border-gray-100 bg-gray-50/50">
                                    <input type="checkbox" name="ids_absensi[]" value="{{ $att->id }}" form="form-bulk-delete" class="cb-satuan rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer w-4 h-4">
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-600">
                                    {{ \Carbon\Carbon::parse($att->tanggal)->format('d-m-Y') }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs font-bold text-gray-500">
                                    {{ $att->employee->id_karyawan ?? '-' }}
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-800">
                                    {{ $att->employee->nama_karyawan ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-bold {{ str_contains($att->kelompok_kerja_harian ?? '', 'LANGSUNG') ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800' }}">
                                        {{ $att->kelompok_kerja_harian ?? $att->employee->kelompok }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center uppercase font-semibold text-xs text-gray-600">
                                    {{ $att->employee->shift ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-gray-600">
                                    {{ $att->jam_masuk ? \Carbon\Carbon::parse($att->jam_masuk)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-xs text-gray-600">
                                    {{ $att->jam_pulang ? \Carbon\Carbon::parse($att->jam_pulang)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-slate-800">
                                    {{ $jamKerjaNet }} Jam
                                </td>
                                <td class="px-6 py-4 text-center font-black">
                                    @if($jamLembur > 0)
                                        <span class="px-2.5 py-1 text-xs rounded-xl bg-rose-50 text-rose-700 border border-rose-100 shadow-sm">
                                            +{{ $jamLembur }} Jam
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs font-medium">Tidak Ada</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 font-medium" style="max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-style: italic;" title="{{ $att->keterangan }}">
                                    {{ $att->keterangan }}
                                </td>
                                <td class="px-6 py-4 text-center flex items-center justify-center gap-3">

                                     <!-- TOMBOL PENSIL JINGGA VERSI NATIVE JAVASCRIPT (ANTI-BENTROK) -->
                                    <button type="button" 
                                            onclick="bukaKotakKoreksi('{{ $att->id }}', '{{ $att->employee->nama_karyawan }}', '{{ $att->tanggal }}', '{{ $att->jam_masuk }}', '{{ $att->jam_pulang }}', '{{ $att->keterangan }}', '{{ $att->kelompok_kerja_harian }}')"
                                            class="text-amber-500 hover:text-amber-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </button>

                                    <!-- Tombol Eksekusi Hapus (Merah) -->
                                    <form action="{{ route('attendance.destroy', $att->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data absensi ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-800 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-6 py-10 text-center text-gray-400 italic text-sm">
                                    Belum ada catatan absensi masuk hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-end mt-3">
                    {{ $attendances->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

    </div>

            <!-- ================= JENDELA MODAL POP-UP EDIT VERSI NATIVE (ANTI-BENTROK) ================= -->
    <div id="modalKoreksiAbsensi" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl space-y-6 text-left">
            
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-4 bg-amber-500 rounded-sm"></span> Koreksi Absensi: <span id="mdl_txt_nama" class="text-blue-600"></span>
                </h3>
                <button type="button" onclick="tutupKotakKoreksi()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>

            <form id="formKoreksiAbsensi" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Tanggal Kehadiran</label>
                    <input type="date" name="tanggal" id="mdl_input_tanggal" required class="w-full rounded-xl border-gray-200 text-xs p-3 bg-gray-50">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Jam Masuk</label>
                        <input type="time" name="jam_masuk" id="mdl_input_masuk" required class="w-full rounded-xl border-gray-200 text-xs p-3 bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Jam Pulang</label>
                        <input type="time" name="jam_pulang" id="mdl_input_pulang" required class="w-full rounded-xl border-gray-200 text-xs p-3 bg-gray-50">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-text-xs font-bold text-gray-500 uppercase mb-1.5">Kelompok Kerja (Hari Ini)</label>
                    <select name="kelompok_kerja_harian" id="mdl_kelompok_kerja" class="w-full rounded-xl border border-gray-200 text-xs p-3 bg-gray-50 font-bold" required>
                        <option value="LANGSUNG - IFM">LANGSUNG - IFM</option>
                        <option value="LANGSUNG - MANUAL">LANGSUNG - MANUAL</option>
                        <option value="LANGSUNG - PACKING">LANGSUNG - PACKING</option>
                        <option value="TIDAK LANGSUNG">TIDAK LANGSUNG</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Keterangan Catatan</label>
                    <input type="text" name="keterangan" id="mdl_input_keterangan" required class="w-full rounded-xl border-gray-200 text-xs p-3 bg-gray-50">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" onclick="tutupKotakKoreksi()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl text-xs transition-all">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs transition-all shadow-sm">Simpan Koreksi</button>
                </div>
            </form>
        </div>
    </div>

                <!-- ================= JENDELA POP-UP MODAL IMPORT EXCEL VERSI NATIVE PREMIUM (MATCH DESIGN 100%) ================= -->
    <div id="modalImportAbsensi" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl w-full max-w-xl p-8 shadow-2xl space-y-6 text-left relative max-h-[90vh] overflow-y-auto">
            
            <!-- TOMBOL SILANG KECIL DI POJOK KANAN ATAS -->
            <button type="button" onclick="tutupModalImport()" class="absolute top-6 right-6 text-gray-300 hover:text-gray-500 transition-colors text-2xl font-light">&times;</button>

            <!-- JUDUL UTAMA MODAL -->
            <div class="space-y-1">
                <h3 class="text-lg font-black text-slate-800 uppercase tracking-wide">IMPORT DATA REKAP ABSENSI</h3>
                <p class="text-xs text-gray-400 font-medium">Gunakan file Excel untuk mengungguh banyak data sekaligus.</p>
            </div>

            <form action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- KOTAK BIRU ATURAN PENGISIAN -->
                <div class="bg-blue-50/50 border border-blue-100/50 rounded-2xl p-5 space-y-3">
                    <h4 class="text-xs font-black text-blue-800 uppercase tracking-wider">ATURAN PENGISIAN:</h4>
                    <ul class="text-[11px] text-slate-600 font-semibold space-y-1.5 list-disc pl-4">
                        <li><strong class="text-slate-700">ID Karyawan:</strong> Pastikan kode unik terdaftar di master data (Contoh: MRSA-001).</li>
                        <li><strong class="text-slate-700">Tanggal:</strong> Format kolom di Excel wajib Date dengan standar (<span class="font-mono text-blue-600 font-bold">YYYY-MM-DD</span>).</li>
                        <li><strong class="text-slate-700">Jam Masuk:</strong> Isi dengan format waktu 24 jam (Contoh: <span class="font-mono text-blue-600">07:00</span>).</li>
                        <li><strong class="text-slate-700">Jam Pulang:</strong> Isi dengan format waktu 24 jam (Contoh: <span class="font-mono text-blue-600">16:00</span>).</li>
                        <li><strong class="text-slate-700">Keterangan:</strong> Cukup isi dengan kata teks pendek bebas (Contoh: Hadir).</li>
                    </ul>
                </div>

                <!-- AREA DROP-FILE DENGAN IKON AWAN -->
                <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 bg-gray-50/30 hover:bg-emerald-50/10 rounded-2xl p-8 cursor-pointer transition-all group space-y-4">
                    <input type="file" name="file_excel" required class="hidden" onchange="document.getElementById('nama_file_info').innerText = this.files[0].name; document.getElementById('nama_file_info').classList.add('text-emerald-600')">
                    
                    <!-- BULATAN IKON AWAN -->
                    <div class="p-3.5 bg-white rounded-xl shadow-sm border border-gray-100 group-hover:scale-105 transition-transform text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                        </svg>
                    </div>

                    <div class="text-center space-y-1">
                        <span id="nama_file_info" class="text-xs font-black text-slate-700 block tracking-wide">Pilih file Excel (.xlsx)</span>
                        <span class="text-[10px] text-gray-400 font-bold block">Maksimal ukuran dokumen 2 MB</span>
                    </div>
                </label>

                <!-- ROW TOMBOL FOOTER (TEMPLATE DI KIRI & PROSES DI KANAN) -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                    <!-- Tombol Download Template di Sisi Kiri -->
                    <a href="{{ asset('templates/template_absensi.xlsx') }}" class="px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-600 font-black rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-sm border border-blue-100/50">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Template
                    </a>

                    <!-- Tombol Aksi Simpan di Sisi Kanan -->
                    <div class="flex gap-2">
                        <button type="button" onclick="tutupModalImport()" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl text-xs transition-all">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl text-xs transition-all shadow-md tracking-wider">Unggah & Proses Data</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- ================= SCRIPT PENGGERAK UTAMA ABSENSI PT MIRASA ================= -->
    <script>
        // 1. Fungsi Buka Pintu Modal Edit Manual (Murni JavaScript)
        function bukaKotakKoreksi(id, nama, tanggal, masuk, pulang, keterangan, kelompokHarian) {
            // Set action URL form tujuan update database secara dinamis
            document.getElementById('formKoreksiAbsensi').action = '/attendance/' + id;

            // Suntikkan teks nama dan value data ke dalam form modal
            document.getElementById('mdl_txt_nama').innerText = nama;
            document.getElementById('mdl_input_tanggal').value = tanggal;
            document.getElementById('mdl_input_masuk').value = masuk;
            document.getElementById('mdl_input_pulang').value = pulang;
            document.getElementById('mdl_input_keterangan').value = keterangan;
            
            // MENYUNTIKKAN DATA KELOMPOK KERJA HARIAN KE DROPDOWN MODAL EDIT
            document.getElementById('mdl_kelompok_kerja').value = kelompokHarian;

            // Munculkan kotak modal mengambang ke layar
            document.getElementById('modalKoreksiAbsensi').style.display = 'flex';
        }

        // 2. Fungsi Tutup Jendela Modal
        function tutupKotakKoreksi() {
            document.getElementById('modalKoreksiAbsensi').style.display = 'none';
        }
        
        // FUNGSI NATIVE BARU UNTUK MEMBUKA MODAL IMPORT ABSENSI EXCEL
        function bukaModalImport() {
            document.getElementById('modalImportAbsensi').style.display = 'flex';
        }

        // FUNGSI NATIVE BARU UNTUK MENUTUP MODAL IMPORT ABSENSI EXCEL
        function tutupModalImport() {
            document.getElementById('modalImportAbsensi').style.display = 'none';
        }

        // 3. Fungsi Asisten Pencari ID Karyawan Otomatis yang sudah berjalan sukses
        function cariProfilKaryawan(id_karyawan) {
            if (!id_karyawan) {
                document.getElementById('txt_nama').innerText = '- Belum Dipilih -';
                document.getElementById('txt_bagian').innerText = '- Belum Dipilih -';
                document.getElementById('txt_shift').innerText = '- Belum Dipilih -';
                return;
            }
            fetch('/api/employee/' + id_karyawan)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        document.getElementById('txt_nama').innerText = data.nama_karyawan;
                        document.getElementById('txt_bagian').innerText = data.bagian.toUpperCase();
                        document.getElementById('txt_shift').innerText = 'SHIFT ' + data.shift.toUpperCase();
                    }
                })
                .catch(error => console.error('Gagal memuat API profil:', error));
        }
    </script>
    <!-- SUNTIKAN 3: JAVASCRIPT AUTOMATION (Sekali ketuk klik master atas, otomatis menceklis/melepas seluruh boks bawah!) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAllMaster = document.getElementById('checkAllMaster');
            if (checkAllMaster) {
                checkAllMaster.addEventListener('change', function() {
                    let checkboxes = document.querySelectorAll('.cb-satuan');
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });
            }
        });
    </script>
</x-layout.user.app>

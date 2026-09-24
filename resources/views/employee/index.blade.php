<x-layout.user.app title="Master Data Karyawan">
    <!-- MASTER DRIVER ALPINE GLOBAL: WAJIB DI BARIS NOMOR 2 PALING LUAR HALAMAN -->
    <div class="p-6 space-y-8 w-full" x-data="{ 
        openimport: false, 
        openedit: false, 
        edit_id: '', 
        edit_id_karyawan: '', 
        edit_nama_karyawan: '', 
        edit_kelompok: '', 
        edit_shift: '', 
        edit_status_karyawan: '', 
        edit_tanggal: '',
        edit_bagian: '',
        edit_no_hp: '',
        edit_nama_bank: '',
        edit_no_rekening: '',
        edit_email: '',
    }">

        
                <!-- ====== HEADER HALAMAN ====== -->
        <div class="flex items-center justify-between bg-white rounded-xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5a2.25 2.25 0 002.25 2.25zm.75-12h3.75c.414 0 .75.336.75.75v3.75a.75 0 01-.75.75H5.25a.75 0 01-.75-.75V8.25c0-.414.336-.75.75-.75z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Master Data Karyawan</h1>
                    <p class="text-xs text-gray-500 font-medium">Manajemen profil, status karyawan, dan kalkulator masa kerja otomatis PT MIRASA FOOD INDUSTRY</p>
                </div>
            </div>
        </div>

        <!-- FORM INPUT MANUAL KARYAWAN -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-6">
            <h2 class="text-sm font-black text-gray-800 uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 pb-3">
                <span class="w-2 h-4 bg-blue-600 rounded-sm"></span> Input Karyawan Baru
            </h2>

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('employee.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-4">
                    
                    <!-- Baris 1: ID, Nama, Tanggal Masuk -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">ID KARYAWAN</label>
                        <input type="text" name="id_karyawan" placeholder="Contoh: MRSA-001" class="w-full rounded-xl border-gray-200 text-xs p-2.5 font-bold text-slate-700 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20" required>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">NAMA KARYAWAN</label>
                        <input type="text" name="nama_karyawan" placeholder="Masukkan nama lengkap" class="w-full rounded-xl border-gray-200 text-xs p-2.5 font-bold text-slate-700 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20" required>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">TANGGAL MASUK KERJA</label>
                        <input type="date" name="tanggal_masuk_kerja" class="w-full rounded-xl border-gray-200 text-xs p-2.5 font-bold text-slate-700 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20" required>
                    </div>

                    <!-- Baris 2: Kelompok Kerja, Shift Kerja, Status Karyawan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">KELOMPOK KERJA</label>
                        <select name="kelompok" class="w-full rounded-xl border-gray-200 text-xs p-2.5 font-bold text-slate-700 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20" required>
                            <option value="Langsung">Langsung</option>
                            <option value="Tidak Langsung">Tidak Langsung</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">SHIFT KERJA</label>
                        <select name="shift" class="w-full rounded-xl border-gray-200 text-xs p-2.5 font-bold text-slate-700 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20" required>
                            <option value="A">Shift A</option>
                            <option value="B">Shift B</option>
                            <option value="Non Shift">Non Shift</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">STATUS KARYAWAN</label>
                        <select name="status_karyawan" class="w-full rounded-xl border-gray-200 text-xs p-2.5 font-bold text-slate-700 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20" required>
                            <option value="Tetap">Karyawan Tetap</option>
                            <option value="Tidak Tetap">Karyawan Tidak Tetap</option>
                        </select>
                    </div>

                    <!-- Baris 3: Bagian/Divisi, Nomor HP, Nama Bank -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">BAGIAN / DIVISI</label>
                        <select name="bagian" class="w-full rounded-xl border-gray-200 text-xs p-2.5 font-bold text-slate-700 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20" required>
                            <option value="UMUM">UMUM</option>
                            <option value="PACKING">PACKING</option>
                            <option value="POT.RENDEM">POT.RENDEM</option>
                            <option value="SORTIR AC">SORTIR AC</option>
                            <option value="BATCH FRYER">BATCH FRYER</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">NOMOR HP</label>
                        <input type="text" name="no_hp" placeholder="Contoh: 08123456789" class="w-full rounded-xl border-gray-200 text-xs p-2.5 font-bold text-slate-700 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">NAMA BANK</label>
                        <input type="text" name="nama_bank" placeholder="Contoh: BCA, MANDIRI, BRI, BNI" class="w-full rounded-xl border-gray-200 text-xs p-2.5 font-bold text-slate-700 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20" required>
                    </div>

                    <!-- Baris 4: Nomor Rekening Bank (Mengunci Baris Sendiri Agar Seimbang) dan Email Karyawan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">NOMOR REKENING</label>
                        <input type="text" name="no_rekening" placeholder="Masukkan nomor rekening..." class="w-full rounded-xl border-gray-200 text-xs p-2.5 font-bold text-slate-700 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">EMAIL KARYAWAN</label>
                        <input type="email" name="email" id="email" placeholder="Contoh: karyawan@gmail.com" class="w-full rounded-xl border-gray-200 text-xs p-2.5 font-bold text-slate-700 bg-white">
                    </div>

                </div>

                                <!-- ====== BARIS DUA TOMBOL AKSI SEJAJAR (BAGIAN BAWAH FORM) ====== -->
                <div class="flex justify-end items-center gap-3 pt-4 border-t border-gray-100">
                    
                    <!-- TOMBOL IMPORT GREEN (SEBELAH KIRI) -->
                    <button type="button" @click="openimport = true" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition-all shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                        </svg>
                        Import Excel Massal
                    </button>

                    <!-- TOMBOL SIMPAN BLUE (SEBELAH KANAN) -->
                    <button type="submit" class="px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-sm transition-all shadow-sm flex items-center gap-2">
                        Simpan Data Karyawan
                    </button>
                    
                </div>
            </form>
        </div>

        <!-- ====== BARIS KOTAK SARINGAN FILTER DATA MULTI-DROPDOWN ====== -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 space-y-5">
            <div class="flex items-center justify-between border-b border-gray-50 pb-3">
                <h2 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                    Saring Pencarian Master Karyawan
                </h2>
                <span class="px-2.5 py-0.5 text-[10px] font-black rounded-full bg-blue-50 text-blue-600 border border-blue-100">
                    Total: {{ $employees->count() }} Personil
                </span>
            </div>

            <!-- Form Mengirim Atribut Filter GET -->
            <form action="{{ route('employee.index') }}" method="GET" class="space-y-4">
                
                <!-- BARIS 1: AREA KOTAK INPUT FILTER  -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- 1. Input Pencarian Nama -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Cari Karyawan</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama depan..." class="w-full rounded-xl border-gray-200 text-xs p-2.5 bg-gray-50/50 text-gray-600 font-medium focus:bg-white focus:border-blue-400">
                    </div>

                    <!-- 2. Dropdown Kelompok (REVISI SUCI ANTI-CRASH) -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Kelompok Kerja</label>
                        <select name="kelompok" class="w-full rounded-xl border-gray-200 text-xs p-2.5 bg-gray-50/50 text-gray-600 font-medium focus:bg-white">
                            <option value="">Semua Kelompok</option>
                            <option value="Langsung" {{ request('kelompok') == 'Langsung' ? 'selected' : '' }}>LANGSUNG</option>
                            <option value="Tidak Langsung" {{ request('kelompok') == 'Tidak Langsung' ? 'selected' : '' }}>TIDAK LANGSUNG</option>
                        </select>
                    </div>

                    <!-- 3. Saring Bagian / Divisi (REVISI SUCI ANTI-CRASH) -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Saring Bagian / Divisi</label>
                        <select name="bagian" class="w-full rounded-xl border-gray-200 text-xs p-2.5 bg-gray-50/50 text-gray-600 font-medium focus:bg-white">
                            <option value="">Semua Bagian / Divisi</option>
                            <option value="UMUM" {{ request('bagian') == 'UMUM' ? 'selected' : '' }}>UMUM</option>
                            <option value="PACKING" {{ request('bagian') == 'PACKING' ? 'selected' : '' }}>PACKING</option>
                            <option value="POT.RENDEM" {{ request('bagian') == 'POT.RENDEM' ? 'selected' : '' }}>POT.RENDEM</option>
                            <option value="SORTIR AC" {{ request('bagian') == 'SORTIR AC' ? 'selected' : '' }}>SORTIR AC</option>
                            <option value="BATCH FRYER" {{ request('bagian') == 'BATCH FRYER' ? 'selected' : '' }}>BATCH FRYER</option>
                        </select>
                    </div>

                    <!-- 4. Dropdown Shift -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Shift Kerja</label>
                        <select name="shift" class="w-full rounded-xl border-gray-200 text-xs p-2.5 bg-gray-50/50 text-gray-600 font-medium">
                            <option value="">-- Semua Shift --</option>
                            <option value="A" {{ request('shift') == 'A' ? 'selected' : '' }}>SHIFT A</option>
                            <option value="B" {{ request('shift') == 'B' ? 'selected' : '' }}>SHIFT B</option>
                            <option value="Non Shift" {{ request('shift') == 'Non Shift' ? 'selected' : '' }}>NON SHIFT</option>
                        </select>
                    </div>

                    <!-- 5. UPDATE DROPDOWN: SEKARANG MENDUKUNG SORTIR NOMOR ID KARYAWAN & MASA KERJA -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-1.5 tracking-wider">Urutan & Sortir Data</label>
                        <select name="urut_masa_kerja" class="w-full rounded-xl border-gray-200 text-xs p-2.5 bg-gray-50/50 text-gray-600 font-medium">
                            <option value="">Paling Baru → Lama (Default)</option>
                            <option value="lama_baru" {{ request('urut_masa_kerja') == 'lama_baru' ? 'selected' : '' }}>Paling Lama → Baru</option>
                            <option value="id_asc" {{ request('urut_masa_kerja') == 'id_asc' ? 'selected' : '' }}>ID Karyawan: Kecil → Besar</option>
                            <option value="id_desc" {{ request('urut_masa_kerja') == 'id_desc' ? 'selected' : '' }}>ID Karyawan: Besar → Kecil</option>
                        </select>
                    </div>
                </div>

                <!-- BARIS 2: AREA TOMBOL AKSI UTAMA (DIBAGI RATALURUS DI POJOK KANAN) -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-50 pt-4">
                    
                    <!-- A. Tombol Reset Cerdas (Otomatis Muncul) -->
                    @if(request()->filled('search') || request()->filled('kelompok') || request()->filled('shift') || request()->filled('status_karyawan') || request()->filled('urut_masa_kerja'))
                        <a href="{{ route('employee.index') }}" class="px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-600 font-black rounded-xl text-xs transition-all shadow-sm border border-gray-200 uppercase tracking-wider flex items-center gap-1.5">
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
                    <a href="{{ route('employee.export', request()->query()) }}" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs transition-all shadow-sm flex items-center gap-1.5 uppercase tracking-wider">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Ekspor Excel
                    </a>

                    <!-- D. Tombol Laporan (Ungu) -->
                    <a href="{{ route('employee.print', request()->query()) }}" target="_blank" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl text-xs transition-all shadow-sm flex items-center gap-1.5 uppercase tracking-wider">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.8A8.004 8.004 0 1021 12h-3m-3.06-3.06l-3.06-3.06M14.94 9H9" />
                        </svg>
                        Cetak Laporan
                    </a>
                </div>

            </form>
        </div>
        <!-- ====== SELESAI BARIS KOTAK SARINGAN FILTER ====== -->

        <!-- DAFTAR REKAP DATA KARYAWAN -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-sm font-black text-gray-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-4 bg-emerald-500 rounded-sm"></span> Tabel Data Master Karyawan
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 80px;">ID Karyawan</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 150px;">Nama Karyawan</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 100px;">Kelompok</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 70px;">Shift</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 50px;">Status Karyawan</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 90px;">Bagian/Devisi</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 110px;">Tgl Masuk Kerja</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 90px;">Nomor HP</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 100px;">Email</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 120px;">Bank & No. Rekening</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 180px;">Masa Kerja</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider" style="width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse($employees as $emp)
                            <tr class="hover:bg-gray-50/50 transition-all">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-gray-500">{{ $emp->id_karyawan }}</td>
                                <td class="px-6 py-4 font-bold text-gray-800">{{ $emp->nama_karyawan }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md {{ $emp->kelompok == 'Langsung' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                                        {{ $emp->kelompok }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 uppercase font-semibold text-xs text-gray-600">{{ $emp->shift }}</td>
                                <td class="px-6 py-4 capitalize text-xs font-medium text-gray-600">{{ $emp->status_karyawan }}</td>
                                <td class="px-4 py-3 font-semibold text-blue-600 uppercase">{{ $emp->bagian ?? '-' }}</td>
                                <td class="px-6 py-4 font-medium text-gray-600">{{ \Carbon\Carbon::parse($emp->tanggal_masuk_kerja)->format('d-m-Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs font-bold text-slate-600">{{ $emp->no_hp ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs font-bold text-slate-600">{{ $emp->email ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs font-medium text-slate-600">
                                    <div class="font-black text-slate-900 uppercase text-[10px] tracking-wide">{{ $emp->nama_bank ?? '-' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $emp->no_rekening ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        // KALKULATOR CERDAS - OTOMATIS BERUBAH SETIAP GANTI HARI (REAL-TIME)
                                            $masaKerja = '-';
                                        if (!empty($emp->tanggal_masuk_kerja)) {
                                            $masuk = \Carbon\Carbon::parse($emp->tanggal_masuk_kerja);
                                            $sekarang = \Carbon\Carbon::now();
                            
                                            $tahun = $masuk->diffInYears($sekarang);
                                            $bulan = $masuk->diffInMonths($sekarang) % 12;
                            
                                        // Hitung sisa hari secara presisi setelah dipotong tahun dan bulan
                                            $masukSisaHari = $masuk->copy()->addYears($tahun)->addMonths($bulan);
                                            $hari = (int) floor($masukSisaHari->diffInDays($sekarang));

                                        // Susun teks tampilan agar rapi lurus simetris
                                            $teksMasaKerja = [];
                            if ($tahun > 0) { $teksMasaKerja[] = (int)$tahun . " Thn"; }
                            if ($bulan > 0) { $teksMasaKerja[] = (int)$bulan . " Bln"; }
                            if ($hari > 0 || empty($teksMasaKerja)) { $teksMasaKerja[] = (int)$hari . " Hari"; }
                            
                                            $masaKerja = implode(' ', $teksMasaKerja);
                                            }
                                    @endphp
                                    <span class="px-3 py-1 text-xs font-black rounded-full bg-blue-50 text-blue-700 border border-blue-100 shadow-sm">
                                        {{ $masaKerja }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center flex items-center justify-center gap-3">

                                 <!-- TOMBOL EDIT KUNING VERSI AMAN 1000% GLOBAL -->
                                <button type="button" 
                                    @click="
                                        openedit = true;
                                        $data.edit_id = '{{ $emp->id }}';
                                        $data.edit_id_karyawan = '{{ $emp->id_karyawan }}';
                                        $data.edit_nama_karyawan = '{{ addslashes($emp->nama_karyawan) }}';
                                        $data.edit_tanggal = '{{ $emp->tanggal_masuk_kerja }}';
                                        $data.edit_masa_kerja = '{{ $tahun }}';
                                        $data.edit_kelompok = '{{ $emp->kelompok }}';
                                        $data.edit_shift = '{{ $emp->shift }}';
                                        $data.edit_status_karyawan = '{{ $emp->status_karyawan }}';
                                        $data.edit_bagian = '{{ $emp->bagian ?? "" }}';
                                        $data.edit_no_hp = '{{ $emp->no_hp ?? "" }}';
                                        $data.edit_email = '{{ $emp->email ?? "" }}';
                                        $data.edit_nama_bank = '{{ $emp->nama_bank ?? "" }}';
                                        $data.edit_no_rekening = '{{ $emp->no_rekening ?? "" }}';
                                    " 
                                    class="text-amber-600 hover:text-amber-900 transition-colors">
                                    <span class="text-xs font-bold bg-amber-100 px-2 py-1 rounded-md">Edit</span>
                                </button>

                                <form action="{{ url('employee') }}/{{ $emp->id }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data karyawan ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-900 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                                                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-gray-400 italic text-sm">
                                    Belum ada data master karyawan. Silakan tambahkan melalui form di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    <!-- ================= KOTAK POP-UP MODAL EDIT DATA KARYAWAN ================= -->
    <div x-show="openedit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.away="openedit = false" class="bg-white rounded-2xl w-full max-w-2xl p-6 shadow-xl space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-black text-gray-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-4 bg-amber-500 rounded-sm"></span> Ubah Data Master Karyawan
                </h3>
                <button @click="openedit = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>

            <form :action="'/employee/' + edit_id" method="POST" class="space-y-4 text-left">

                @csrf
                    @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">ID Karyawan</label>
                        <input type="text" name="id_karyawan" x-model="edit_id_karyawan" required class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nama Karyawan</label>
                        <input type="text" name="nama_karyawan" x-model="edit_nama_karyawan" required class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tanggal Masuk Kerja</label>
                        <input type="date" name="tanggal_masuk_kerja" x-model="edit_tanggal" class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50 focus:bg-white" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kelompok Kerja</label>
                        <select name="kelompok" x-model="edit_kelompok" required class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50">
                            <option value="Langsung">Langsung</option>
                            <option value="Tidak Langsung">Tidak Langsung</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Shift Kerja</label>
                        <select name="shift" x-model="edit_shift" required class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50">
                            <option value="A">Shift A</option>
                            <option value="B">Shift B</option>
                            <option value="Non Shift">Non Shift</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Status Karyawan</label>
                        <select name="status_karyawan" x-model="edit_status_karyawan" required class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50">
                            <option value="Tetap">Karyawan Tetap</option>
                            <option value="Tidak Tetap">Karyawan Tidak Tetap</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <!-- 1. Dropdown Edit Bagian / Divisi Kerja Pabrik -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Bagian / Divisi</label>
                        <select name="bagian" x-model="edit_bagian" class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50 focus:bg-white" required>
                            <option value="UMUM">UMUM</option>
                            <option value="PACKING">PACKING</option>
                            <option value="POT.RENDEM">POT.RENDEM</option>
                            <option value="SORTIR AC">SORTIR AC</option>
                            <option value="BATCH FRYER">BATCH FRYER</option>
                        </select>
                    </div>

                    <!-- 2. Input Teks Edit Nomor Handphone Karyawan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nomor HP</label>
                        <input type="text" name="no_hp" x-model="edit_no_hp" placeholder="Contoh: 08123456789" class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50 focus:bg-white" required>
                    </div>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <!-- 3. Input Teks Edit Nama Bank -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nama Bank</label>
                        <input type="text" name="nama_bank" x-model="edit_nama_bank" placeholder="Contoh: BCA, MANDIRI, BRI, BNI" class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50 focus:bg-white" required>
                    </div>

                    <!-- 4. Input Teks Edit Nomor Rekening Bank -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nomor Rekening</label>
                        <input type="text" name="no_rekening" x-model="edit_no_rekening" placeholder="Masukkan nomor rekening..." class="w-full rounded-xl border-gray-200 text-sm p-3 bg-gray-50 focus:bg-white" required>
                    </div>

                    <!-- 5. Input Teks Edit Email Karyawan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Email</label>
                        <input type="email" name="email" x-model="$data.edit_email" placeholder="Contoh: john.doe@example.com" class="w-full rounded-xl border-gray-200 text-sm p-3 bg-white font-bold text-slate-700">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="openedit = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl text-sm transition-all">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-white font-bold rounded-xl text-sm transition-all shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
            <!-- ================= JENDELA POP-UP MODAL IMPORT EXCEL PREMIUM (MATCH DESIGN) ================= -->
    <div x-show="openimport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 animate-fade-in">
        <div @click.away="openimport = false" 
             x-data="{ fileName: '' }"
             class="bg-white rounded-[24px] w-full max-w-xl p-8 shadow-2xl relative space-y-6 border border-gray-100 text-left">
            
            <!-- Tombol Close Silang di Pojok Kanan Atas -->
            <button @click="openimport = false" class="absolute top-6 right-6 text-gray-300 hover:text-gray-500 transition-colors text-2xl font-light">&times;</button>

            <!-- JUDUL UTAMA MODAL -->
            <div class="space-y-1">
                <h3 class="text-lg font-extrabold text-gray-800 uppercase tracking-wide">Import Data Master Karyawan</h3>
                <p class="text-xs text-gray-400 font-medium">Gunakan file Excel untuk mengunggah banyak data sekaligus.</p>
            </div>

            <!-- KOTAK ATURAN PENGISIAN (BLUE BOX MATCH) -->
            <div class="p-5 bg-blue-50/60 rounded-2xl border border-blue-100/50 space-y-2.5">
                <p class="text-xs font-black text-blue-800 uppercase tracking-wider">Aturan Pengisian:</p>
                <ul class="space-y-1.5 text-xs text-blue-900/90 font-medium list-disc pl-4">
                    <li><span class="font-bold">ID Karyawan:</span> Pastikan kode unik tidak kembar (Contoh: MRSA-001).</li>
                    <li><span class="font-bold">Nama Karyawan:</span> Masukkan nama lengkap personil dengan benar.</li>
                    <li><span class="font-bold">Kelompok:</span> Cukup isi kata <span class="italic font-bold">LANGSUNG</span> atau <span class="italic font-bold">TIDAK LANGSUNG</span>.</li>
                    <li><span class="font-bold">Shift:</span> Isi dengan karakter huruf kapital <span class="font-bold">A</span>, <span class="font-bold">B</span>, atau <span class="font-bold">NON SHIFT</span>.</li>
                    <li><span class="font-bold">Status Karyawan:</span> Isi dengan status <span class="font-bold">Tetap</span> atau <span class="font-bold">Tidak Tetap</span>.</li>
                    <li><span class="font-bold">Tanggal Masuk:</span> Format kolom di Excel wajib Date <span class="font-bold">(MM-DD-YYYY)</span>.</li>
                    <li><span class="font-bold">Bagian:</span> Isi dengan pilihan <span class="font-bold">UMUM, PACKING, POT.RENDEM, SORTIR AC, BATCH FRYER</span>.</li>
                    <li><span class="font-bold">Nomor HP:</span> Isi dengan nomor handphone aktif karyawan (Contoh: 0812345678).</li>
                    <li><span class="font-bold">Email:</span> Isi dengan alamat email karyawan (Contoh: john.doe@example.com).</li>
                    <li><span class="font-bold">Nama Bank:</span> Isi dengan nama bank menggunakan huruf kapital (Contoh: BCA, MANDIRI, BRI, BNI).</li>
                    <li><span class="font-bold">Nomor Rekening:</span> Masukkan angka nomor rekening bank karyawan tanpa tanda baca.</li>

                </ul>
            </div>

            <!-- FORM UNTUK UNGGAH BERKAS SPREADSHEET -->
            <form action="{{ route('employee.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- AREA DRAG & DROP PICKER FILE (DASHED BOX MATCH) -->
                <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 bg-gray-50/50 hover:bg-blue-50/20 rounded-2xl p-8 cursor-pointer transition-all duration-200 group">
                    <input type="file" name="file_excel" required class="hidden" @change="fileName = $event.target.files[0].name">
                    
                    <!-- Wadah Ikon Cloud Upload -->
                    <div class="p-3 bg-white rounded-xl shadow-sm text-gray-400 group-hover:text-blue-600 group-hover:scale-105 transition-all mb-4 border border-gray-100">
                        <svg class="w-6 h-6 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                        </svg>
                    </div>

                    <!-- Teks Keterangan Berkas -->
                    <span class="text-xs font-bold text-gray-700 text-center" x-text="fileName ? fileName : 'Pilih file Excel (.xlsx)'"></span>
                    <span class="text-[10px] text-gray-400 mt-1 font-medium" x-show="!fileName">Maksimal ukuran dokumen 2 MB</span>
                </label>

                <!-- GARIS PEMBATAS FOOTER (DIVIDER MATCH) -->
                <div class="border-t border-gray-100 pt-4 flex items-center justify-between gap-4">
                    
                    <!-- Tombol Template (Kiri Bawah) -->
                    <a href="{{ asset('templates/template_data_karyawan.xlsx') }}" download="Template_Import_Karyawan_Mirasa.xlsx" class="px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-black rounded-xl transition-all flex items-center gap-2 border border-blue-100/30">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Template
                    </a>

                    <!-- Tombol Eksekusi Unggah (Kanan Bawah) -->
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-black rounded-xl transition-all shadow-md shadow-blue-100 flex items-center gap-2">
                        Unggah & Proses Data
                    </button>
                    
                </div>
            </form>
        </div>
    </div>
</x-layout.user.app>



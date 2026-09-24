<x-layout.beranda.app title="Sistem Payroll Karyawan">
    <div class="p-6 space-y-6 bg-slate-50/50 min-h-screen font-sans">

        <!-- ================= BAGIAN 1: HEADER HALAMAN & SEPASANG TOMBOL PEMICU AKURAT ================= -->
        <div class="space-y-4 mb-6 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        
            <!-- BARIS 1: KHUSUS TEMPAT JUDUL UTAMA SISTEM (LEGA & BERWIBAWA) -->
            <div class="border-b border-gray-100 pb-3">
                <h1 class="text-xl font-black text-slate-800 tracking-tight">SISTEM MANAJEMEN PAYROLL KARYAWAN</h1>
                <p class="text-xs font-semibold text-slate-400 mt-1">PT MIRASA FOOD INDUSTRY • Penguncian Komponen Gaji Bulanan Administrasi & Pabrik</p>
            </div>

            <!-- BARIS 2: BARISAN SELURUH TOMBOL AKSI & ATRIBUT FUNGSIONAL (BERJEJER LAPANG) -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                
                <!-- SISI KIRI BARIS 2: FORM PENCARIAN GABUNGAN (INPUT SEARCH & BUTTON CLEAR) -->
                <div class="flex items-center gap-2 flex-1 max-w-md">
                    <form action="{{ route('payroll.index') }}" method="GET" class="flex flex-1 items-center gap-3 bg-gray-50 p-2 rounded-xl border border-gray-200">
                        <div class="flex items-center gap-1.5 w-full bg-white rounded-xl border border-gray-200 px-2.5 py-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" name="cari_karyawan" value="{{ request('cari_karyawan') }}" placeholder="Cari ID / Nama Karyawan..." class="w-full border-0 p-0 text-xs font-bold text-slate-700 bg-transparent focus:ring-0">
                        </div>
                    </form>

                    @if(request('cari_karyawan'))
                        <a href="{{ route('payroll.index', ['bulan_periode' => $bulanPeriode]) }}" class="text-[10px] font-black text-red-500 hover:text-red-700 uppercase tracking-wider bg-red-50 px-2 py-1.5 rounded-lg border border-red-100 transition-all">
                            Clear
                        </a>
                    @endif
                </div>

                <!-- SISI KANAN BARIS 2: FILTER PERIODE BULAN & TIGA TOMBOL AKSI UTAMA -->
                <div class="flex flex-wrap items-center gap-3">
                    
                    <!-- BOKS FILTER PERIODE BULAN ASLI -->
                    <form action="{{ route('payroll.index') }}" method="GET" class="flex items-center gap-2 bg-gray-50 p-2 rounded-xl border border-gray-200 m-0">
                        <label class="text-[10px] font-black text-gray-500 uppercase px-2 tracking-wider">Periode:</label>
                        <input type="month" name="bulan_periode" value="{{ $bulanPeriode }}" onchange="this.form.submit()" class="rounded-lg border-gray-200 text-xs p-1.5 font-bold text-slate-700 bg-white shadow-sm focus:ring-2 focus:ring-blue-500/20">
                    </form>

                    <!-- TOMBOL 3: IMPORT EXCEL -->
                    <button type="button" onclick="document.getElementById('modalImportPayroll').classList.remove('hidden')" class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs px-4 py-2.5 rounded-xl shadow-sm space-x-1.5 transition-all transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775A5.25 5.25 0 0117.25 10.5a4.5 4.5 0 01-1.41 8.775H6.75z"/>
                        </svg>
                        <span>Import Excel</span>
                    </button>

                    <!-- TOMBOL 4: CETAK SEMUA SLIP -->
                    <a href="{{ route('payroll.print_all_slips', ['bulan_periode' => $bulanPeriode]) }}" 
                        target="_blank"
                        class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl text-xs uppercase tracking-wider shadow-md flex items-center gap-1.5 cursor-pointer transition-all decoration-none border-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.82l2.9-2.9m0 0l2.9 2.9m-2.9-2.9v6c0 1.22-.98 2.22-2.2 2.22H6.72M19 12a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Cetak Semua Slip
                    </a>

                    <a href="{{ route('payroll.send-email-massal', ['bulan_periode' => $bulanPeriode]) }}"  
                        class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl shadow-sm transition-colors"
                        onclick="return confirm('Apakah Anda yakin ingin memproses dan mengirimkan slip gaji bulan ini ke seluruh email karyawan?')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l8-5.333a2 2 0 012.22 0l8 5.333A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-2.25-1.5M12 14.5l-2.25-1.5" />
                            </svg>
                            KIRIM EMAIL MASSAL
                    </a>

                    <!-- TOMBOL 6: EXPORT DETAIL EXCEL -->
                    <a href="{{ route('payroll.exportDetailExcel', ['bulan_tahun' => request('bulan_period', $bulanPeriode ?? date('Y-m'))]) }}" 
                        class="inline-flex items-center bg-emerald-700 hover:bg-emerald-800 text-white font-black text-xs px-4 py-2.5 rounded-xl shadow-sm space-x-1.5 transition-all transform hover:-translate-y-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6m0 0l-3-3m3 3l3-3M6.75 19.5a4.5 4.5 0 01-1.41-8.775A5.25 5.25 0 0117.25 10.5a4.5 4.5 0 01-1.41 8.775H6.75z"/>
                            </svg>
                        <span>Export Detail Excel</span>
                    </a>

                    <!-- TOMBOL 5: MULTI PAYROLL CORPORATE -->
                    <a href="{{ route('payroll.MultiPayroll', ['bulan_periode' => request()->get('bulan_periode', date('Y-m'))]) }}" class="inline-flex items-center bg-[#0056a3] hover:bg-[#004481] text-white font-black text-xs px-4 py-2.5 rounded-xl shadow-sm space-x-1.5 transition-all transform hover:-translate-y-0.5">
                        <span>📊 Multi Payroll</span>
                    </a>

                    <!-- TOMBOL 6: EXPORT BCA GENERATE -->
                    <a href="{{ route('payroll.exportBca') }}?bulan_periode={{ request('bulan_periode', date('Y-m')) }}" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition-all ml-2">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Generate KlikBCA Bisnis
                    </a>

                </div>
            </div>
        </div>

        <!-- 2. TABEL DAFTAR REKAP GAJI KARYAWAN MASSAL -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-50 flex items-center justify-between bg-white">
            <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5M4.5 19.5h15M5.25 4.5V18.75m13.5-14.25V18.75M2.25 4.5l1.353-.135A2.25 2.25 0 016 6.3c0 .605-.13 1.192-.372 1.72l-.372.812A1.125 1.125 0 006.273 10.5h11.454a1.125 1.125 0 001.017-.668l.371-.812A3.75 3.75 0 0118 6.3a2.25 2.25 0 012.397-1.936l1.353.135" />
                </svg>
                Rincian Perhitungan Gaji Bulanan Pegawai
            </h3>
            <span class="px-2.5 py-0.5 text-[10px] font-black rounded-full bg-blue-50 text-blue-600 border border-blue-100">
                Periode Rekap: {{ \Carbon\Carbon::parse($bulanPeriode.'-01')->format('F Y') }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-100 text-[10px] font-black text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-4 text-center" style="width: 5%;">No</th>
                        <th class="px-6 py-4" style="width: 12%;">ID & Nama Karyawan</th>
                        <th class="px-6 py-4 text-center" style="width: 10%;">Absensi (Hari)</th>
                        <th class="px-6 py-4 text-center" style="width: 15%;">Akumulasi Jam Kerja</th>
                        <th class="px-6 py-4 text-right" style="width: 15%;">Total Pendapatan (+)</th>
                        <th class="px-6 py-4 text-right" style="width: 15%;">Total Potongan (-)</th>
                        <th class="px-6 py-4 text-right" style="width: 15%;">Gaji Bersih Akhir</th>
                        <th class="px-6 py-4 text-center" style="width: 15%;">Atur Komponen</th>
                        <th class="px-6 py-4 text-center" style="width: 10%;">Cetak Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-xs font-semibold text-slate-600">
                    @forelse($payrollData as $index => $data)
                    <tr class="hover:bg-slate-50/50 transition-all">
                        <!-- 1. Nomor Urut -->
                        <td class="px-6 py-4 text-center text-gray-400 font-bold">{{ $index + 1 }}</td>
                        
                        <!-- 2. ID & Nama Lengkap Karyawan -->
                        <td class="px-6 py-4">
                            <div class="font-black text-slate-700">{{ $data['employee']->nama_karyawan }}</div>
                            <div class="text-[10px] font-bold text-gray-400 mt-0.5 tracking-wider">{{ $data['employee']->id_karyawan }}</div>
                        </td>
                        
                        <!-- 3. Total Hari Hadir (Dihitung Otomatis Berbasis Absensi) -->
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-black block w-max mx-auto">
                                {{ $data['total_hari_hadir'] }} Hari
                            </span>
                            <!-- Tombol Klik Audit Pemicu Laci Riwayat Harian -->
                            <button type="button" 
                                    onclick="bukaModalRiwayatHarian('{{ $data['employee']->id }}', '{{ $data['employee']->nama_karyawan }}', '{{ $data['employee']->id_karyawan }}')" 
                                    class="text-[10px] font-black text-blue-600 hover:text-blue-800 uppercase tracking-wider mt-1.5 inline-flex items-center gap-1 cursor-pointer bg-transparent border-none p-0 mx-auto">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.43 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Lihat Absensi
                            </button>
                        </td>
                        
                        <!-- 4. Rincian Akumulasi Jam Kerja Nyata (Lembur I, II, & Potongan) -->
                        <td class="px-6 py-4 text-center space-y-1">
                            <div class="text-[11px] text-emerald-600 font-bold flex items-center justify-between">
                                <span>Lembur I (8 Jam):</span> <span class="font-black">{{ $data['total_jam_lembur_1'] }} Jam</span>
                            </div>
                            <div class="text-[11px] text-teal-600 font-bold flex items-center justify-between">
                                <span>Lembur II (>8 Jam):</span> <span class="font-black">{{ $data['total_jam_lembur_2'] }} Jam</span>
                            </div>
                            <div class="text-[11px] text-rose-600 font-bold flex items-center justify-between">
                                <span>Kurang Jam (<7 Jam):</span> <span class="font-black">{{ $data['total_jam_potongan'] }} Jam</span>
                            </div>
                        </td>
                        
                        <!-- 5. Total Pendapatan Kotor (+) -->
                        <td class="px-6 py-4 text-right text-emerald-700 font-black text-xs">
                            @php
                                $existing = $data['payroll_details'];
                                $totalPendapatan = $data['total_pendapatan'] ?? 0;
                            @endphp
                            Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
                        </td>
                        <!-- 6. Total Potongan Denda & Iuran (-) -->
                        <td class="px-6 py-4 text-right text-rose-700 font-black text-xs">
                            @php
                            $rupiahDendaJam = ($data['total_jam_potongan'] ?? 0) * ($existing->potongan_jam_kerja ?? 0);

                            $totalPotongan = ($existing->potongan_bpjs_kes ?? 0) + ($existing->potongan_bpjs_tk ?? 0) + $rupiahDendaJam + ($existing->potongan_lainnya ?? 0);
                            @endphp
                            Rp {{ number_format($totalPotongan, 0, ',', '.') }}
                        </td>
                        
                        <!-- 7. Gaji Bersih Akhir Per Personil (Hasil Netto) -->
                        <td class="px-6 py-4 text-right">
                            <span class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 border border-blue-100 text-xs font-black shadow-sm">
                                Rp {{ number_format($data['total_gaji_summary'] ?? $data['total_gaji_bersih'], 0, ',', '.') }}
                            </span>
                        </td>
                        
                        <!-- 8. Tombol Pemicu Jendela Laci Input Komponen Rupiah -->
                        <!-- KAMAR 1: KHUSUS TOMBOL INPUT KOMPONEN RUPIAH -->
                        <td class="px-6 py-4 text-center border-r border-gray-50">
                            <button type="button" 
                                    onclick="bukaModalGaji('{{ json_encode($data['employee']) }}', '{{ isset($data['payroll_details']) && $data['payroll_details'] ? json_encode($data['payroll_details']) : 'null' }}', this)" 
                                    class="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white font-black rounded-xl text-[10px] uppercase tracking-wider inline-flex items-center gap-1 shadow-sm cursor-pointer transition-all border-0 whitespace-nowrap"
                                    data-masa-kerja="{{ $data['employee']->masa_kerja ?? '0' }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Atur Komponen
                            </button>
                        </td>

                        <!-- KAMAR 2: KHUSUS TOMBOL AMBIL SLIP GAJI RESMI -->
                        <td class="px-6 py-4 text-center">
                            @if(isset($data['total_gaji_bersih']) && $data['total_gaji_bersih'] > 0)
                                <a href="{{ route('payroll.print_slip', ['employee_id' => $data['employee']->id, 'bulan_periode' => $bulanPeriode]) }}" 
                                   target="_blank"
                                   class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl text-[10px] uppercase tracking-wider inline-flex items-center gap-1 shadow-sm cursor-pointer transition-all decoration-none border-0 whitespace-nowrap">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.82l2.9-2.9m0 0l2.9 2.9m-2.9-2.9v6c0 1.22-.98 2.22-2.2 2.22H6.72M19 12a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Slip Gaji
                                </a>
                            @else
                                <span class="text-[10px] font-bold text-gray-300 italic tracking-wide">Belum Di-input</span>
                            @endif
                        </td>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-400 font-semibold italic">
                            Belum ada data absensi karyawan terdata pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ================= MODAL SLIDEOVER FORM INPUT 11 KOMPONEN PAYROLL ================= -->
    <div id="modalGaji" class="fixed inset-0 z-50 overflow-hidden hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" onclick="tutupModalGaji()"></div>
    
        <div class="absolute inset-y-0 right-0 pl-10 max-w-full flex"
            id="modalContent"
            style="transform: translateX(100%); transition: transform 0.3s ease-in-out;">
            <div class="w-screen max-w-xl bg-white shadow-2xl flex flex-col border-l border-gray-100">
                
                <!-- Header Modal -->
                <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wider">ATUR NOMINAL RUPIAH KOMPONEN PAYROLL</h2>
                        <p id="modalKaryawanTitle" class="text-xs text-blue-400 font-semibold mt-0.5">Nama Karyawan (ID)</p>
                    </div>
                    <button type="button" onclick="tutupModalGaji()" class="text-gray-400 hover:text-white transition-all cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('payroll.store') }}" method="POST" class="flex-1 overflow-y-auto p-6 space-y-5 text-xs font-bold text-slate-700">
                    @csrf
                    <input type="hidden" name="employee_id" id="form_employee_id" data-masa-kerja="{{ $edit_masa_kerja ?? 0 }}">
                    <input type="hidden" name="bulan_tahun" value="{{ $bulanPeriode }}">

                    <div class="space-y-4">
                        <h4 class="text-[10px] font-black text-blue-600 uppercase tracking-widest border-b border-blue-50 pb-1.5 flex items-center gap-1">
                            <span>A. KOMPONEN PENDAPATAN GAJI (+)</span>
                        </h4>
                        
                        <!-- ================== SINKRONISASI ID INPUT UTAMA (WAJIB SAMA DENGAN JAVASCRIPT) ================== -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- 1. Gaji Pokok -->
                            <div>
                                <label class="block text-gray-400 text-[10px] mb-1">1. Gaji Pokok Perhari</label>
                                <div class="flex rounded-xl shadow-xs">
                                    <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-xs font-black">Rp</span>
                                    <input type="text" name="gaji_perhari" id="in_gaji_perhari" class="w-full rounded-r-xl border-gray-200 p-2.5 text-xs font-black input-rupiah" required>
                                </div>
                            </div>
                            <!-- 2. Lembur 1 -->
                            <div>
                                <label class="block text-gray-400 text-[10px] mb-1">2. Honor Lembur I (Jam ke-8)</label>
                                <div class="flex rounded-xl shadow-xs">
                                    <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-xs font-black">Rp</span>
                                    <input type="text" name="honor_lembur_1" id="in_honor_lembur_1" class="w-full rounded-r-xl border-gray-200 p-2.5 text-xs font-black input-rupiah" required>
                                </div>
                            </div>
                            <!-- 3. Lembur 2 -->
                            <div>
                                <label class="block text-gray-400 text-[10px] mb-1">3. Honor Lembur II (&gt;8 Jam)</label>
                                <div class="flex rounded-xl shadow-xs">
                                    <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-xs font-black">Rp</span>
                                    <input type="text" name="honor_lembur_2" id="in_honor_lembur_2" class="w-full rounded-r-xl border-gray-200 p-2.5 text-xs font-black input-rupiah" required>
                                </div>
                            </div>
                            <!-- 4. Tunjangan Masa Kerja -->
                            <div>
                                <label class="block text-gray-400 text-[10px] mb-1">4. Tunjangan Masa Kerja</label>
                                <div class="flex rounded-xl shadow-xs">
                                    <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-xs font-black">Rp</span>
                                    <input type="text" name="tunjangan_masa_kerja" id="in_tunjangan_masa" class="w-full rounded-r-xl border-gray-200 p-2.5 text-xs font-black input-rupiah" required>
                                </div>
                            </div>
                            <!-- 5. Tunjangan Jabatan -->
                            <div>
                                <label class="block text-gray-400 text-[10px] mb-1">5. Tunjangan Jabatan</label>
                                <div class="flex rounded-xl shadow-xs">
                                    <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-xs font-black">Rp</span>
                                    <input type="text" name="tunjangan_jabatan" id="in_tunjangan_jabatan" class="w-full rounded-r-xl border-gray-200 p-2.5 text-xs font-black input-rupiah" required>
                                </div>
                            </div>
                            <!-- 6. Insentif -->
                            <div>
                                <label class="block text-gray-400 text-[10px] mb-1">6. Insentif Kerajinan</label>
                                <div class="flex rounded-xl shadow-xs">
                                    <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-xs font-black">Rp</span>
                                    <input type="text" name="insentif" id="in_insentif" class="w-full rounded-r-xl border-gray-200 p-2.5 text-xs font-black input-rupiah" required>
                                </div>
                            </div>
                        </div>
                    </div>    
                        
                        <!-- BAGIAN POTONGAN GAJI -->
                    <div class="space-y-4 pt-2">
                        <h4 class="text-[10px] font-black text-rose-600 uppercase tracking-widest border-b border-rose-50 pb-1.5 flex items-center gap-1">
                            <span>B. KOMPONEN POTONGAN GAJI (-)</span>
                        </h4>

                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <!-- 7. BPJS Kes -->
                            <div>
                                <label class="block text-gray-400 text-[10px] mb-1">7. Potongan BPJS Kesehatan</label>
                                <div class="flex rounded-xl shadow-xs">
                                    <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-xs font-black">Rp</span>
                                    <input type="text" name="potongan_bpjs_kes" id="in_bpjs_kes" class="w-full rounded-r-xl border-gray-200 p-2.5 text-xs font-black input-rupiah" required>
                                </div>
                            </div>
                            <!-- 8. BPJS TK -->
                            <div>
                                <label class="block text-gray-400 text-[10px] mb-1">8. Potongan BPJS Ketenagakerjaan</label>
                                <div class="flex rounded-xl shadow-xs">
                                    <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-xs font-black">Rp</span>
                                    <input type="text" name="potongan_bpjs_tk" id="in_bpjs_tk" class="w-full rounded-r-xl border-gray-200 p-2.5 text-xs font-black input-rupiah" required>
                                </div>
                            </div>
                            <!-- 9. Potongan Jam Kerja -->
                            <div>
                                <label class="block text-gray-400 text-[10px] mb-1">9. Potongan Jam Kerja (&lt;7 Jam)</label>
                                <div class="flex rounded-xl shadow-xs">
                                    <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-xs font-black">Rp</span>
                                    <input type="text" name="potongan_jam_kerja" id="in_potongan_jam" class="w-full rounded-r-xl border-gray-200 p-2.5 text-xs font-black input-rupiah" required>
                                </div>
                            </div>
                            <!-- 10. Kasbon -->
                            <div>
                                <label class="block text-gray-400 text-[10px] mb-1">10. Potongan Lainnya Kasbon</label>
                                <div class="flex rounded-xl shadow-xs">
                                    <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-100 text-gray-500 text-xs font-black">Rp</span>
                                    <input type="text" name="potongan_lainnya" id="in_potongan_lain" class="w-full rounded-r-xl border-gray-200 p-2.5 text-xs font-black input-rupiah" required>
                                </div>
                            </div>
                        </div>

                            <!-- TOMBOL AKSI BAWAH FORM LACI -->
                            <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                                <button type="button" onclick="tutupModalGaji()" class="px-4 py-2 border border-gray-200 text-gray-500 hover:bg-gray-50 font-black rounded-xl text-xs uppercase cursor-pointer bg-transparent">Batal</button>
                                <button type="submit" onclick="this.form.submit();" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl text-xs uppercase tracking-wider shadow-md cursor-pointer border-0">Simpan Nominal</button>
                            </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================= 4. WADAH MODAL LACI AUDIT ABSENSI HARIAN (ID: modalRiwayat) ================= -->
    <div id="modalRiwayat" class="fixed inset-0 z-50 overflow-hidden hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" onclick="tutupModalRiwayatHarian()">
        </div>
        <div class="absolute inset-y-0 right-0 pl-10 max-w-full flex" id="modalContentRiwayat" style="transform: translateX(100%); transition: transform 0.3s ease-in-out;">
            <div class="w-screen max-w-2xl bg-white shadow-2xl flex flex-col border-l border-gray-100">
                
                <!-- Kepala Laci Absensi -->
                <div class="p-6 bg-slate-800 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wider">RIWAYAT ABSENSI & JAM KERJA HARIAN</h2>
                        <p id="modalRiwayatKaryawanTitle" class="text-xs text-emerald-400 font-semibold mt-0.5">Nama Karyawan (ID)</p>
                    </div>
                    <button type="button" onclick="tutupModalRiwayatHarian()" class="text-gray-400 hover:text-white transition-all cursor-pointer bg-transparent border-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Tabel Kalender Harian -->
                <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <table class="w-full text-left border-collapse text-xs font-semibold text-slate-600">
                            <thead>
                                <tr class="bg-slate-100 border-b border-gray-200 text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                    <th class="px-4 py-3 text-center">Tgl</th>
                                    <th class="px-4 py-3 text-center">Masuk - Pulang</th>
                                    <th class="px-4 py-3 text-center">Durasi</th>
                                    <th class="px-4 py-3 text-right text-emerald-600">Lembur I</th>
                                    <th class="px-4 py-3 text-right text-teal-600">Lembur II</th>
                                    <th class="px-4 py-3 text-right text-rose-600">Potongan</th>
                                </tr>
                            </thead>
                            <tbody id="tabelRiwayatBody" class="divide-y divide-gray-100">
                                <!-- Baris Data Tanggal Disuntik via JavaScript Fetch -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- ================= 5. KOTAK POPUP MODAL UNGGAH FILE EXCEL PAYROLL MASSAL (ID: modalImportPayroll) ================= -->
    <div id="modalImportPayroll" class="fixed inset-0 z-50 overflow-hidden hidden" role="dialog" aria-modal="true">
        <!-- Background Gelap Transparan Belakang -->
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs" onclick="document.getElementById('modalImportPayroll').classList.add('hidden')"></div>
        
        <!-- Kontainer Tengah Modal -->
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 max-w-xl w-full p-6 space-y-5 relative z-10">
                
                <!-- A. Header Modal Premium -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">IMPORT DATA COMPONENT PAYROLL</h3>
                        <p class="text-[10px] text-gray-400 font-semibold mt-0.5">Gunakan file Excel untuk mengunggah nominal rupiah 11 komponen sekaligus.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('modalImportPayroll').classList.add('hidden')" class="text-gray-400 hover:text-slate-600 transition-all cursor-pointer bg-transparent border-0 p-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- B. BOKS ATURAN PENGISIAN PAYROLL BIRU MUDA (SAMA PERSIS CONTOH GAMBAR) -->
                <div class="bg-blue-50/60 rounded-xl p-4 border border-blue-100/70 text-slate-700 text-[11px] leading-relaxed font-semibold space-y-1.5 shadow-xs">
                    <div class="text-[11px] font-black text-blue-700 uppercase tracking-wider">ATURAN PENGISIAN PAYROLL:</div>
                    <ul class="list-disc pl-4 space-y-1 text-slate-600 font-medium">
                        <li><strong class="text-slate-800 font-black">ID Karyawan:</strong> Wajib diisi kode unik terdaftar (Contoh: MRSA-001).</li>
                        <li><strong class="text-slate-800 font-black">Nominal Rupiah:</strong> Diisi angka bulat murni polos tanpa simbol Rp, tanpa titik, dan tanpa koma (Contoh upah perhari: 150000).</li>
                        <li><strong class="text-slate-800 font-black">Komponen Pendapatan:</strong> Meliputi gaji_perhari, honor_lembur_1, honor_lembur_2, tunjangan_masa_kerja, tunjangan_jabatan, insentif.</li>
                        <li><strong class="text-slate-800 font-black">Komponen Potongan Denda:</strong> Meliputi potongan_bpjs_kes, potongan_bpjs_tk, potongan_jam_kerja, potongan_lainnya.</li>
                    </ul>
                </div>

                <!-- C. FORM UTAMA DAN KOTAK DRAG-DROP FILE SPREADSHEET -->
                <form action="{{ route('payroll.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5 m-0">
                    @csrf
                    <input type="hidden" name="bulan_periode" value="{{ $bulanPeriode }}">
                    
                    <!-- Area Unggah Berkas Putus-Putus Premium -->
                    <div class="w-full border-2 border-dashed border-gray-200 hover:border-blue-400 bg-slate-50/50 hover:bg-white rounded-2xl p-6 transition-all relative flex flex-col items-center justify-center group cursor-pointer">
                        <input type="file" name="file_excel" class="absolute inset-0 opacity-0 cursor-pointer z-20" required onchange="document.getElementById('labelTeksFilePayroll').innerText = this.files ? '📂 ' + this.files.name : 'Pilih file Excel (.xlsx)'">
                        
                        <!-- Ikon Awan Abu Menawan -->
                        <div class="p-3 bg-white rounded-full shadow-xs text-gray-400 group-hover:text-blue-500 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>
                        </div>
                        
                        <span id="labelTeksFilePayroll" class="text-xs font-black text-slate-700 mt-3 block group-hover:text-blue-600 transition-colors">Pilih file Excel (.xlsx)</span>
                        <span class="text-[10px] text-gray-400 font-medium mt-0.5 block">Maksimal ukuran dokumen 2 MB</span>
                    </div>

                    <!-- D. TOMBOL AKSI BAWAH (SAMA PERSIS CONTOH GAMBAR) -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-between bg-white">
                        <!-- Tombol Download Template Di Pojok Kiri Bawah -->
                        <a href="{{ route('payroll.download_template') }}" class="px-4 py-2 border border-blue-200 text-blue-600 hover:bg-blue-50 font-black rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-xs decoration-none">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Template
                        </a>

                        <!-- Sepasang Tombol Aksi Kanan Bawah -->
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="document.getElementById('modalImportPayroll').classList.add('hidden')" class="px-4 py-2 text-gray-500 hover:text-slate-800 font-black rounded-xl text-xs uppercase tracking-wider cursor-pointer bg-transparent border-0">Batal</button>
                            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-xl text-xs uppercase tracking-wider shadow-md flex items-center gap-1.5 cursor-pointer transition-all border-0">Unggah & Proses Data</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- ================= 6. JAVASCRIPT LOGIK FINANCE & API HARIAN REAL-TIME ================= -->
    <script>
        // A. FUNGSI SAKTI: LIVE MEMASANG TITIK PEMISAH RIBUAN SAAT ADMIN MENGETIK
        function formatRupiahTeks(angka) {
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                var separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return rupiah;
        }

        document.querySelectorAll('.input-rupiah').forEach(input => {
            input.addEventListener('keyup', function(e) {
                this.value = formatRupiahTeks(this.value);
            });
        });

        // PERBAIKAN DI SINI: Memotong angka .00 sebelum diformat
        function formatAwalInputRupiah(idElement, nilaiRaw) {
            const targetInput = document.getElementById(idElement);
            if (targetInput) {
                // Mengubah nilai ke String, lalu memotong angka setelah tanda titik (.)
                let nilaiBersih = String(nilaiRaw || 0).split('.')[0];
                targetInput.value = formatRupiahTeks(nilaiBersih);
            }
        }

        // B. LOGIKA MODAL LACI GAJI BULANAN ADMIN (100% BEBAS BENTROK JODOH)
        function bukaModalGaji(employeeJson, payrollJson, button) {
            tahunKerja = 0;
            
            // 1. Bersihkan dahulu semua boks input ke angka 0 biar bersih dari sisa klik sebelumnya
            document.querySelectorAll('.input-rupiah').forEach(input => input.value = '0');

            // 2. Intip isi data mentah yang dikirim lewat Console Browser F12
            console.log("=== DETEKTIF DATA PAYROLL ===");
            console.log("Data Karyawan Mentah:", employeeJson);
            console.log("Data Payroll Mentah:", payrollJson);

            let employee = {};
            let payroll = null;

            // 3. Konversi data string menjadi Objek Javascript secara aman
            try {
                employee = typeof employeeJson === 'string' ? JSON.parse(employeeJson) : employeeJson;
            } catch (e) { 
                console.error("Gagal parse employee:", e); 
            }

            try {
                if (payrollJson && payrollJson !== 'null' && payrollJson !== '""' && payrollJson !== "") {
                    payroll = typeof payrollJson === 'string' ? JSON.parse(payrollJson) : payrollJson;
                }
            } catch (e) { 
                console.error("Gagal parse payroll:", e); 
            }

            console.log("Hasil Parse Objek Payroll:", payroll);

            // 4. Suntikkan Judul Kepala Laci
            document.getElementById('modalKaryawanTitle').innerText = `${employee.nama_karyawan || ''} (${employee.id_karyawan || ''})`;
            document.getElementById('form_employee_id').value = employee.id || payroll.employee_id || '';
            
            // PERBAIKAN: Set juga attribute data agar bisa dibaca dengan aman oleh fungsi hitung otomatis
            document.getElementById('form_employee_id').setAttribute('data-masa-kerja', employee.masa_kerja || employee.masaKerja || '0');

            // 5. PENYUNTIKAN NOMINAL RUPIAH LANGSUNG DENGAN SINKRONISASI COCOK DATABASE
            if (payroll) {
                formatAwalInputRupiah('in_gaji_perhari', payroll.gaji_perhari ?? 0);
                formatAwalInputRupiah('in_honor_lembur_1', payroll.honor_lembur_1 ?? 0);
                formatAwalInputRupiah('in_honor_lembur_2', payroll.honor_lembur_2 ?? 0);
                formatAwalInputRupiah('in_tunjangan_masa', payroll.tunjangan_masa ?? 0);
                formatAwalInputRupiah('in_tunjangan_jabatan', payroll.tunjangan_jabatan ?? 0);
                formatAwalInputRupiah('in_insentif', payroll.insentif ?? 0);
                formatAwalInputRupiah('in_bpjs_kes', payroll.potongan_bpjs_kes ?? 0);
                formatAwalInputRupiah('in_bpjs_tk', payroll.potongan_bpjs_tk ?? 0);
                formatAwalInputRupiah('in_potongan_jam', payroll.potongan_jam_kerja ?? payroll.potongan_jam ?? 0);
                formatAwalInputRupiah('in_potongan_lain', payroll.potongan_lainnya ?? payroll.potongan_lain ?? 0);
            }


            // 6. Munculkan Kamar Laci Samping ke Layar Belakang
            document.getElementById('modalGaji').classList.remove('hidden');
            setTimeout(() => { document.getElementById('modalContent').style.transform = 'translateX(0)'; }, 50);

            // =========================================================================
            // TAHAP A: HITUNG TAHUN KERJA DAHULU (Pindahkan bagian Gambar 4 ke sini)
            // =========================================================================
            tahunKerja = 0; 
            let teksMasaKerja = "0";

            // Jika tombol HTML berhasil membawa parameter 'this'
            if (button) {
                teksMasaKerja = button.getAttribute('data-masa-kerja') || "0";
            } else {
                teksMasaKerja = employee.masa_kerja || employee.masaKerja || "0";
            }

            let teksBersih = String(teksMasaKerja).toLowerCase().trim();

            if (teksBersih.includes('hari') || teksBersih.startsWith('0')) {
                tahunKerja = 0; 
            } else {
                let cocokAngka = teksBersih.match(/(\d+)/);
                tahunKerja = cocokAngka ? parseInt(cocokAngka[1]) : 0;
            }
            
            // =========================================================================
            // TAHAP B: BARU PILIH NOMINAL BERDASARKAN TAHUN KERJA (Gambar 3, baris 578)
            // =========================================================================
            let nominalOtomatis = 0;
            if (tahunKerja >= 20) {
                nominalOtomatis = 2550;
            } else if (tahunKerja >= 15 && tahunKerja < 20) {
                nominalOtomatis = 2200;
            } else if (tahunKerja >= 10 && tahunKerja < 15) {
                nominalOtomatis = 1700;
            } else if (tahunKerja >= 5 && tahunKerja < 10) {
                nominalOtomatis = 1000;
            } else {
                nominalOtomatis = 0;
            }

            if (payroll && typeof payroll.tunjangan_masa !== 'undefined' && payroll.tunjangan_masa !== null && payroll.tunjangan_masa > 0) {
                formatAwalInputRupiah('in_tunjangan_masa', payroll.tunjangan_masa);
            } else {
                // Jika database benar-benar kosong/baru, barulah pakai nominalOtomatis dari hitungan masa kerja
                formatAwalInputRupiah('in_tunjangan_masa', nominalOtomatis);
            }

            // Jalankan kalkulator hitung otomatis total laci
            if (typeof hitungLiveTotalGajiLaci === 'function') {
                hitungLiveTotalGajiLaci();
            }
        }
        

        function tutupModalGaji() {
            document.getElementById('modalContent').style.transform = 'translateX(100%)';
            setTimeout(() => { document.getElementById('modalGaji').classList.add('hidden'); }, 300);
        }

        // D. LOGIKA MODAL LACI AUDIT ABSENSI HARIAN (FETCH AJAX CERDAS)
        function bukaModalRiwayatHarian(id, nama, idKaryawan) {
            document.getElementById('modalRiwayatKaryawanTitle').innerText = `${nama} (${idKaryawan})`;
            const bodyTabel = document.getElementById('tabelRiwayatBody');
            bodyTabel.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-400 italic font-semibold">Sedang mengunduh riwayat absensi harian...</td></tr>`;

            fetch(`{{ route('payroll.daily_attendance') }}?employee_id=${id}&bulan_periode={{ $bulanPeriode }}`)
                .then(response => response.json())
                .then(data => {
                    bodyTabel.innerHTML = '';
                    if(data.length === 0) {
                        bodyTabel.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-400 italic">Tidak ada riwayat absensi masuk di periode bulan ini.</td></tr>`;
                        return;
                    }
                    data.forEach(row => {
                        let jamMasukTampil = (row.jam_masuk && row.jam_masuk !== '-') ? row.jam_masuk.substring(0, 5) : '-';
                        let jamPulangTampil = (row.jam_pulang && row.jam_pulang !== '-') ? row.jam_pulang.substring(0, 5) : '-';
                        bodyTabel.innerHTML += `
                            <tr class="hover:bg-slate-50 transition-all border-b border-gray-100">
                                <td class="px-4 py-3 text-center font-black text-slate-700">${row.tanggal}</td>
                                <td class="px-4 py-3 text-center text-gray-400 font-bold">${jamMasukTampil} - ${jamPulangTampil}</td>
                                <td class="px-4 py-3 text-center text-slate-800 font-bold">${row.durasi_kerja}</td>
                                <td class="px-4 py-3 text-right text-emerald-600 font-black">${row.lembur_1 !== '0 Jam' ? row.lembur_1 : '-'}</td>
                                <td class="px-4 py-3 text-right text-teal-600 font-black">${row.lembur_2 !== '0 Jam' ? row.lembur_2 : '-'}</td>
                                <td class="px-4 py-3 text-right text-rose-600 font-black">${row.potongan !== '0 Jam' ? row.potongan : '-'}</td>
                            </tr>
                        `;
                    });
                });

            document.getElementById('modalRiwayat').classList.remove('hidden');
            setTimeout(() => { document.getElementById('modalContentRiwayat').style.transform = 'translateX(0)'; }, 50);
        }

        function tutupModalRiwayatHarian() {
            document.getElementById('modalContentRiwayat').style.transform = 'translateX(100%)';
            setTimeout(() => { document.getElementById('modalRiwayat').classList.add('hidden'); }, 300);
        }

                // ================= JURUS LIVE EDIT: KALKULATOR TOTAL RUPIAH OTOMATIS DI FORM LACI =================
        function hitungLiveTotalGajiLaci() {
            // Ambil semua angka inputan, buang tanda titiknya agar bisa dijumlahkan matematika
            const getAngka = (id) => {
                const el = document.getElementById(id);
                return el ? parseFloat(el.value.replace(/\./g, '')) || 0 : 0;
            };

            // Hitung Rumpun Pendapatan
            const pendapatan = getAngka('in_gaji_perhari') + 
                               getAngka('in_honor_lembur_1') + 
                               getAngka('in_honor_lembur_2') + 
                               getAngka('in_tunjangan_masa') + 
                               getAngka('in_tunjangan_jabatan') + 
                               getAngka('in_insentif');

            // Hitung Rumpun Potongan
            const potongan = getAngka('in_bpjs_kes') + 
                             getAngka('in_bpjs_tk') + 
                             getAngka('in_potongan_jam') + 
                             getAngka('in_potongan_lain');

            const totalBersih = pendapatan - potongan;

            // Cetak kilat ke teks indikator bawah laci jika ada (Opsional untuk kenyamanan admin saat mengetik)
            console.log("Live Calculate - Pendapatan: " + pendapatan + " | Potongan: " + potongan + " | Bersih: " + totalBersih);
        }

        // Daftarkan pemicu otomatis setiap kali admin mengubah angka di dalam laci
        document.querySelectorAll('.input-rupiah').forEach(input => {
            input.addEventListener('input', hitungLiveTotalGajiLaci);
        });

    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Membuka ruang steril untuk pemicu awal halaman jika dibutuhkan
        });

        function hitungPayrollOtomatis() {
            // 1. SINKRONISASI ID INPUT UTAMA
            const inputGaji = document.getElementById('in_gaji_perhari');
            const inputLembur1 = document.getElementById('in_honor_lembur_1');
            const inputLembur2 = document.getElementById('in_honor_lembur_2');
            const inputPotongan = document.getElementById('in_potongan_jam');
            const inputTunjangan = document.getElementById('in_tunjangan_masa');

            // Jika elemen input gaji belum siap di layar, hentikan kalkulator agar tidak error
            if (!inputGaji) return;

            // 2. Ambil versi angka murni dari input Gaji Pokok untuk kalkulasi matematika
            let rawValue = inputGaji.value.replace(/[^0-9]/g, '');
            const gajiPerHari = parseFloat(rawValue) || 0;

            let masaKerja = 0;

            // KUNCI KEMENANGAN MUTLAK: Mengalirkan data tahun langsung dari variabel reaktif model employee ($data)
            const modalContainer = document.getElementById('modalGaji') || document.querySelector('[x-data]') || document.body;
            
            if (modalContainer && modalContainer.__x && modalContainer.__x.$data) {
                masaKerja = parseFloat(modalContainer.__x.$data.edit_masa_kerja) || 0;
            }

            // TAMENG CADANGAN JALUR HIDDEN INPUT
            if (masaKerja === 0) {
                const formEmployee = document.getElementById('form_employee_id');
                if (formEmployee) {
                    let attrMasa = formEmployee.getAttribute('data-masa-kerja') || "0";
                    if (attrMasa.includes('Hari') || attrMasa.startsWith('0')) {
                        masaKerja = 0;
                    } else {
                        masaKerja = parseFloat(attrMasa.replace(/[^\d]/g, '')) || 0;
                    }
                }
            }

            // 3. HITUNG LIVE NOMINAL LEMBUR DAN POTONGAN JAM
            if (gajiPerHari > 0) {
                const gajiPerJam = gajiPerHari / 7;

                if (inputLembur1) inputLembur1.value = Math.round(gajiPerJam * 1.5);
                if (inputLembur2) inputLembur2.value = Math.round(gajiPerJam * 2);
                if (inputPotongan) inputPotongan.value = Math.round(gajiPerJam);
            }

            // 4. LOGIKA PENETAPAN NOMINAL UPAH TUNJANGAN
            if (inputTunjangan) {
                let nilaiSaatIni = parseFloat(inputTunjangan.value.replace(/[^0-9]/g, '')) || 0;
                
                // KUNCI MATI: Hitungan otomatis hanya boleh jalan jika boksnya masih kosong (0)!
                if (nilaiSaatIni === 0) {
                    let nominalTunjangan = 0;
                    let acuanMasa = (masaKerja > 0) ? masaKerja : (typeof tahunKerja !== 'undefined' ? tahunKerja : 0);

                    if (acuanMasa >= 20) {
                        nominalTunjangan = 2550;
                    } else if (acuanMasa >= 15 && acuanMasa < 20) {
                        nominalTunjangan = 2200;
                    } else if (acuanMasa >= 10 && acuanMasa < 15) {
                        nominalTunjangan = 1700;
                    } else if (acuanMasa >= 5 && acuanMasa < 10) {
                        nominalTunjangan = 1000;
                    } else {
                        nominalTunjangan = 0;
                    }

                    inputTunjangan.value = nominalTunjangan;
                    formatAwalInputRupiah('in_tunjangan_masa', nominalTunjangan);
                    inputTunjangan.dispatchEvent(new Event('input'));
                }
            }
        } // <-- PENUTUP UTAMA FUNCTION SEBELUMNYA KURANG DI SINI

        // Picu fungsi berjalan secara LIVE setiap kali user mengetik nominal angka di Gaji Pokok
        const inputGajiUtama = document.getElementById('in_gaji_perhari');
        if (inputGajiUtama) {
            inputGajiUtama.addEventListener('input', hitungPayrollOtomatis);
            inputGajiUtama.addEventListener('keyup', hitungPayrollOtomatis);
        }
    </script>
</x-layout.beranda.app>
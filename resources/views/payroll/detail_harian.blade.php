<x-layout.beranda.app title="Detail Payroll Harian">
    <div class="p-6 space-y-6 bg-slate-50/50 min-h-screen font-sans">

        <!-- 1. HEADER RINGKASAN STRATEGIS (PESANAN OWNER) -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div>
                <h1 class="text-xl font-black text-slate-800 tracking-tight">ANALISIS BIAYA GAJI OPERASIONAL HARIAN</h1>
                <p class="text-xs font-semibold text-slate-400 mt-1">PT MIRASA FOOD INDUSTRY • Pemantauan Real-time Cost Tenaga Kerja per Hari</p>
            </div>       
        </div>
        <div class="flex flex-wrap items-center justify-end gap-3 shrink-0">
            
            <!-- 1. BOKS FILTER BULANAN (BAWAAN ASLI) -->
            <form action="{{ route('payroll.detail_harian') }}" method="GET" class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 shadow-sm">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Pilih Bulan:</label>
                <input type="month" name="bulan_period" value="{{ request('bulan_period', date('Y-m')) }}" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-black text-slate-700 p-0 focus:ring-0 cursor-pointer">
            </form>

            <!-- 2. TOMBOL AKSI BULANAN: CETAK LAPORAN HARIAN (WARNA BIRU BRILIAN) -->
            <a href="{{ route('payroll.printHarian', ['bulan_period' => request('bulan_period', date('Y-m'))]) }}" target="_blank" 
               class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-black text-xs px-4 py-2.5 rounded-xl shadow-sm space-x-1.5 transition-all transform hover:-translate-y-0.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.821l9.2-9.2M12 9.293c1.22-.9 2.22-2.2 2.22H6.72M19 12a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Cetak Laporan</span>
            </a>

            <!-- 3. BOKS FILTER PILIHAN TAHUNAN LIVE -->
            <form action="{{ route('payroll.detail_harian') }}" method="GET" class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 shadow-sm">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Pilih Tahun:</label>
                <select name="tahun_period" onchange="this.form.submit()" class="bg-white border-none text-xs font-black text-slate-700 p-0 focus:ring-0 cursor-pointer">
                    @for($i = date('Y'); $i >= date('Y')-5; $i--)
                        <option value="{{ $i }}" {{ request('tahun_period', date('Y')) == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </form>

            <!-- 4. TOMBOL AKSI TAHUNAN: CETAK REKAP TAHUNAN (WARNA PURPLE LUXURY) -->
            <a href="{{ route('payroll.printTahunan', ['tahun_period' => request('tahun_period', date('Y'))]) }}" target="_blank" 
               class="inline-flex items-center bg-purple-600 hover:bg-purple-700 text-white font-black text-xs px-4 py-2.5 rounded-xl shadow-sm space-x-1.5 transition-all transform hover:-translate-y-0.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Cetak Rekap Tahunan</span>
            </a>

        </div>

        <!-- 2. KARTU WIDGET INDIKATOR UTAMA -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Total Hari Terdata -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <div>
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Jumlah Hari Kerja Terdata</div>
                    <div class="text-xl font-black text-slate-700 mt-0.5">{{ count($dailyReports) }} Hari</div>
                </div>
            </div>

            <!-- Akumulasi Pengeluaran Terbesar Harian -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5M4.5 19.5h15M5.25 4.5V18.75m13.5-14.25V18.75M2.25 4.5l1.353-.135A2.25 2.25 0 016 6.3c0 .605-.13 1.192-.372 1.72l-.372.812A1.125 1.125 0 006.273 10.5h11.454a1.125 1.125 0 001.017-.668l.371-.812A3.75 3.75 0 0118 6.3a2.25 2.25 0 012.397-1.936l1.353.135" />
                    </svg>
                </div>
                <div>
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Estimasi Rata-Rata Cost Per Hari</div>
                    <div class="text-xl font-black text-emerald-700 mt-0.5">
                        @php
                            $totalGajiBulanIni = collect($dailyReports)->sum('total_gaji');
                            $rataRataHarian = count($dailyReports) > 0 ? $totalGajiBulanIni / count($dailyReports) : 0;
                        @endphp
                        Rp {{ number_format($rataRataHarian, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Total Anggaran Gaji Berjalan -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
                <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.75L3.75 19.5" />
                    </svg>
                </div>
                <div>
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Total Anggaran Periode Ini</div>
                    <div class="text-xl font-black text-indigo-700 mt-0.5">Rp {{ number_format($totalGajiBulanIni, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- 3. TABEL DATA RINCIAN HARIAN (4 POIN UTAMA PERMINTAAN OWNER) -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-50 flex items-center justify-between bg-white">
                <h3 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    Jurnal Buku Besar Pengeluaran Gaji Harian Pabrik
                </h3>
                <span class="px-2.5 py-0.5 text-[10px] font-black rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">
                    Saringan Kelompok Kerja Aktif
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-100 text-[10px] font-black text-slate-500 uppercase tracking-wider">
                            <th class="px-6 py-4 text-center" style="width: 5%;">No</th>
                            <th class="px-6 py-4" style="width: 20%;">Tanggal Absensi</th>
                            <th class="px-6 py-4 text-center" style="width: 15%;">Karyawan Masuk</th>
                            <th class="px-6 py-4 text-right" style="width: 20%;">Kelompok LANGSUNG (HPP)</th>
                            <th class="px-6 py-4 text-right" style="width: 20%;">Kelompok TIDAK LANGSUNG (Overhead)</th>
                            <th class="px-6 py-4 text-right bg-slate-100/50 text-slate-800 font-black" style="width: 20%;">Total Gaji Keseluruhan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-xs font-semibold text-slate-600">
                        @forelse($dailyReports as $index => $report)
                        <tr class="hover:bg-slate-50/50 transition-all">
                            <!-- Nomor Urut -->
                            <td class="px-6 py-4 text-center text-gray-400 font-bold">{{ $index + 1 }}</td>
                            
                            <!-- Tanggal Resfreshed -->
                            <td class="px-6 py-4 font-black text-slate-700">
                                {{ $report['tanggal'] }}
                            </td>
                            
                            <!-- 1. Jumlah Orang Karyawan Masuk Hari Itu -->
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-black">
                                    {{ $report['total_hadir'] }} Orang Masuk
                                </span>
                            </td>
                            
                            <!-- 3a. Total Gaji Kelompok LANGSUNG Hari Itu -->
                            <td class="px-6 py-4 text-right text-emerald-600 font-black">
                                Rp {{ number_format($report['gaji_langsung'], 0, ',', '.') }}
                            </td>
                            
                            <!-- 3b. Total Gaji Kelompok TIDAK LANGSUNG Hari Itu -->
                            <td class="px-6 py-4 text-right text-teal-600 font-black">
                                Rp {{ number_format($report['gaji_tidak_langsung'], 0, ',', '.') }}
                            </td>
                            
                            <!-- 2 & 4. TOTAL GAJI KESELURUHAN SELURUH KARYAWAN PER HARI -->
                            <td class="px-6 py-4 text-right bg-slate-50/50 text-blue-700 font-black text-xs">
                                <span class="px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-100 shadow-sm block text-right">
                                    Rp {{ number_format($report['total_gaji'], 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400 font-semibold italic">
                                Belum ada jurnal data absensi dan payroll yang terinput harian pada periode bulan ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                        {{--  Awal Perulangan Data Bawaan Kamu --}}
                        @php
                            $grandTotalHadir = 0;
                            $grandTotalLangsung = 0;
                            $grandTotalTidakLangsung = 0;
                            $grandTotalGaji = 0;
                        @endphp

                        @foreach($dailyReports as $index => $data)
                            @php
                                $grandTotalHadir += $data['total_hadir'];
                                $grandTotalLangsung += $data['gaji_langsung'];
                                $grandTotalTidakLangsung += $data['gaji_tidak_langsung'];
                                $grandTotalGaji += $data['total_gaji'];
                            @endphp
                            <tr>
                                {{-- ... Baris isi tabel bawaan milikmu yang sudah jalan rapi ... --}}
                            </tr>
                        @endforeach

                        <!-- 🛠️ SUNTIKAN BARIS AKUMULASI TOTAL BAWAH PREMIUM: SINKRON 100% DENGAN PRINT OUT -->
                        @if(count($dailyReports) > 0)
                            <tr class="bg-blue-50 font-bold border-t-2 border-blue-200">
                                <td class="px-4 py-3 text-center text-gray-500">-</td>
                                <td class="px-4 py-3 text-center text-blue-900 uppercase tracking-wider text-xs">GRAND TOTAL</td>
                                <td class="px-4 py-3 text-center text-blue-900">{{ $grandTotalHadir }} Orang Masuk</td>
                                <td class="px-4 py-3 text-right text-emerald-600">Rp {{ number_format($grandTotalLangsung, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-amber-600">Rp {{ number_format($grandTotalTidakLangsung, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-blue-700 bg-blue-100/50">Rp {{ number_format($grandTotalGaji, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                </table>
            </div>
        </div>

    </div>
</x-layout.beranda.app>

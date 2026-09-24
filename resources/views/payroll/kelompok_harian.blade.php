<x-layout.beranda.app title="Kelompok Kerja Harian">

<div class="p-6 pt-24 bg-white rounded-2xl border border-gray-100 shadow-sm mt-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm mb-6">
        <!-- Sisi Kiri: Judul Halaman -->
        <div>
            <h1 class="text-xl font-black text-slate-800 tracking-tight">ANALISIS BIAYA GAJI PER KELOMPOK KERJA HARIAN</h1>
            <p class="text-xs font-semibold text-gray-400 mt-1 uppercase tracking-wider">PT MIRASA FOOD INDUSTRY • Alokasi Real-Time Anggaran Lini Produksi Pabrik</p>
        </div>
        
        <!-- Sisi Kanan: Area Filter Interaktif & Satu Tombol Cetak Pintar (no-print) -->
        <div class="flex flex-wrap items-center gap-3 shrink-0 no-print">
            
            <!-- FORM PENCARIAN GABUNGAN -->
            <form action="{{ route('payroll.kelompok_harian') }}" method="GET" class="flex flex-wrap items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2 shadow-inner">
              
                <!-- 1. Dropdown Pilihan Tipe Filter -->
                <div class="flex items-center gap-1.5 border-r border-gray-300 pr-2">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider">JENIS:</span>
                    <select name="tipe_filter" id="tipe_filter" onchange="toggleFilterInputs()" class="bg-transparent border-none text-xs font-bold text-slate-700 p-0 focus:ring-0 cursor-pointer">
                        <option value="bulanan" {{ ($tipeFilter ?? 'bulanan') == 'bulanan' ? 'selected' : '' }}>PER BULAN</option>
                        <option value="tahunan" {{ ($tipeFilter ?? '') == 'tahunan' ? 'selected' : '' }}>PER TAHUN</option>
                    </select>
                </div>
                <!-- 2. Input Bulan (Otomatis Sembunyi Jika Memilih Tahunan) -->
                <div id="wrapper_bulan" class="flex items-center gap-1.5 {{ ($tipeFilter ?? 'bulanan') == 'tahunan' ? 'hidden' : '' }}">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider">PERIODE:</span>
                    <input type="month" name="bulan_period" value="{{ $bulanFilter ?? date('Y-m') }}" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold text-slate-700 p-0 focus:ring-0 cursor-pointer">
                </div>
                <!-- 3. Input Tahun (Otomatis Muncul Hanya Jika Memilih Tahunan) -->
                <div id="wrapper_tahun" class="flex items-center gap-1.5 {{ ($tipeFilter ?? 'bulanan') == 'bulanan' ? 'hidden' : '' }}">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider">TAHUN:</span>
                    <select name="tahun_period" onchange="this.form.submit()" class="bg-transparent border-none text-xs font-bold text-slate-700 p-0 focus:ring-0 cursor-pointer">
                        @for($t = date('Y'); $t >= date('Y')-3; $t--)
                            <option value="{{ $t }}" {{ ($tahunFilter ?? date('Y')) == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endfor
                    </select>
                </div>
                
            </form>
                <!-- 4. TOMBOL CETAK DUST DARI VARIABEL KONDISIONAL (HANYA ADA SATU) -->
                <a href="{{ ($tipeFilter ?? 'bulanan') == 'tahunan' ? route('payroll.print_laporan_kelompok_tahunan', ['tahun_period' => $tahunFilter ?? date('Y')]) : route('payroll.print_laporan_kelompok_harian', ['bulan_period' => $bulanFilter]) }}" 
                target="_blank" 
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 font-bold text-white text-xs uppercase tracking-wider px-4 py-3 rounded-xl shadow-md transition-all duration-150 hover:-translate-y-0.5">
                    <svg class="w-3.5 h-3.5 fill-none stroke-current stroke-[2.5]" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.821l2.9-2.9M12 9.212V3m6.28 6.212l-2.9 2.9M19.5 12a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z"/>
                    </svg>
                    <span>Cetak Laporan</span>
                </a>
                <!-- 5. Tombol Export Excel (no-print agar hilang saat dicetak) -->
                <a href="{{ route('payroll.export_excel_kelompok_harian', ['tipe_filter' => $tipeFilter ?? 'bulanan', 'bulan_period' => $bulanFilter, 'tahun_period' => $tahunFilter ?? date('Y')]) }}" 
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 font-bold text-white text-xs uppercase tracking-wider px-4 py-3 rounded-xl shadow-md transition-all duration-150 hover:-translate-y-0.5 no-print">
                    <svg class="w-3.5 h-3.5 fill-none stroke-current stroke-[2.5]" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Excel</span>
                </a>
        </div>
    </div>

    <!-- TAMPILAN TABEL MATRIKS ANALISIS BUKU BESAR -->
    <div class="overflow-x-auto rounded-xl border border-gray-100 shadow-inner">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-800 text-white font-bold uppercase tracking-wider text-[10px]">
                    <th class="p-4 text-center border-b border-slate-700">Tanggal Operasional</th>
                    <th class="p-4 text-center bg-blue-900 border-b border-blue-800">LANGSUNG - IFM</th>
                    <th class="p-4 text-center bg-emerald-950 border-b border-emerald-900">LANGSUNG - MANUAL</th>
                    <th class="p-4 text-center bg-orange-900 border-b border-orange-800">LANGSUNG - PACKING</th>
                    <th class="p-4 text-center border-b border-emerald-800 bg-emerald-950">TOTAL KELOMPOK LANGSUNG</th> 
                    <th class="p-4 text-center bg-slate-900 border-b border-slate-800">TIDAK LANGSUNG (OVERHEAD)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 font-semibold text-gray-700">
                @php
                    // Inisialisasi awal penampung Grand Total Baris Bawah (Per Kelompok)
                    $grandTotalIFMOring = 0; $grandTotalIFMGaji = 0;
                    $grandTotalManualOring = 0; $grandTotalManualGaji = 0;
                    $grandTotalPackingOring = 0; $grandTotalPackingGaji = 0;
                    $grandTotalOverheadOring = 0; $grandTotalOverheadGaji = 0;
                    $grandTotalSemuaOring = 0; $grandTotalSemuaGaji = 0;
                @endphp

                @forelse($rekapHarian as $hari)
                    @php
                        // Akumulasikan angka baris ini ke Grand Total Bawah secara berkelanjutan
                        $grandTotalIFMOring += $hari['IFM_orang']; $grandTotalIFMGaji += $hari['IFM_gaji'];
                        $grandTotalManualOring += $hari['MANUAL_orang']; $grandTotalManualGaji += $hari['MANUAL_gaji'];
                        $grandTotalPackingOring += $hari['PACKING_orang']; $grandTotalPackingGaji += $hari['PACKING_gaji'];
                        $grandTotalOverheadOring += $hari['TIDAK_LANGSUNG_orang']; $grandTotalOverheadGaji += $hari['TIDAK_LANGSUNG_gaji'];
                        $grandTotalSemuaOring += $hari['TOTAL_hari_orang']; $grandTotalSemuaGaji += $hari['TOTAL_hari_gaji'];
                    @endphp
                    
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 text-center font-bold text-gray-900 bg-gray-50/50">{{ date('d-m-Y', strtotime($hari['tanggal'])) }}</td>
                        
                        <!-- IFM -->
                        <td class="p-4 text-center border-x border-gray-50">
                            <span class="block text-blue-600 font-bold">{{ $hari['IFM_orang'] }} Orang Masuk</span>
                            <span class="block text-gray-900 text-[11px] mt-0.5">Rp {{ number_format($hari['IFM_gaji'], 0, ',', '.') }}</span>
                        </td>

                        <!-- MANUAL -->
                        <td class="p-4 text-center border-x border-gray-50">
                            <span class="block text-emerald-600 font-bold">{{ $hari['MANUAL_orang'] }} Orang Masuk</span>
                            <span class="block text-gray-900 text-[11px] mt-0.5">Rp {{ number_format($hari['MANUAL_gaji'], 0, ',', '.') }}</span>
                        </td>

                        <!-- PACKING -->
                        <td class="p-4 text-center border-x border-gray-50">
                            <span class="block text-orange-600 font-bold">{{ $hari['PACKING_orang'] }} Orang Masuk</span>
                            <span class="block text-gray-900 text-[11px] mt-0.5">Rp {{ number_format($hari['PACKING_gaji'], 0, ',', '.') }}</span>
                        </td>

                        <!-- TOTAL KELOMPOK LANGSUNG (SEBELUMNYA DI PALING KANAN) -->
                        <td class="p-4 text-center border-x border-gray-50 bg-emerald-50/20">
                            <span class="block text-emerald-700 font-bold">{{ $hari['TOTAL_hari_orang'] }} Orang Masuk</span>
                            <span class="block text-emerald-900 font-extrabold text-[12px] mt-0.5">Rp {{ number_format($hari['TOTAL_hari_gaji'], 0, ',', '.') }}</span>
                        </td>

                        <!-- OVERHEAD TIDAK LANGSUNG -->
                        <td class="p-4 text-center border-x border-gray-50 bg-gray-50/30">
                            <span class="block text-gray-700 font-bold">{{ $hari['TIDAK_LANGSUNG_orang'] }} Orang Masuk</span>
                            <span class="block text-gray-900 text-[11px] mt-0.5">Rp {{ number_format($hari['TIDAK_LANGSUNG_gaji'], 0, ',', '.') }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400 font-bold italic">Belum ada rekaman sirkuit payroll kelompok kerja aktif untuk periode bulan ini.</td>
                    </tr>
                @endforelse

                <!-- BARIS BARU: GRAND TOTAL AKUMULASI KELOMPOK (PALING BAWAH) -->
                @if(count($rekapHarian) > 0)
                    <tr class="bg-slate-900 text-white font-bold border-t-2 border-slate-900">
                        <td class="p-4 text-center uppercase tracking-wider text-[11px]">GRAND TOTAL</td>
                        
                        <!-- TOTAL IFM -->
                        <td class="p-4 text-center bg-blue-950">
                            <span class="block text-blue-300 text-[11px]">{{ $grandTotalIFMOring }} Orang</span>
                            <span class="block text-white text-[12px] mt-0.5">Rp {{ number_format($grandTotalIFMGaji, 0, ',', '.') }}</span>
                        </td>

                        <!-- TOTAL MANUAL -->
                        <td class="p-4 text-center bg-emerald-950">
                            <span class="block text-emerald-300 text-[11px]">{{ $grandTotalManualOring }} Orang</span>
                            <span class="block text-white text-[12px] mt-0.5">Rp {{ number_format($grandTotalManualGaji, 0, ',', '.') }}</span>
                        </td>

                        <!-- TOTAL PACKING -->
                        <td class="p-4 text-center bg-orange-950">
                            <span class="block text-orange-300 text-[11px]">{{ $grandTotalPackingOring }} Orang</span>
                            <span class="block text-white text-[12px] mt-0.5">Rp {{ number_format($grandTotalPackingGaji, 0, ',', '.') }}</span>
                        </td>

                        <!-- TOTAL KELOMPOK LANGSUNG (GESER KE SINI) -->
                        <td class="p-4 text-center bg-emerald-900 border-l border-emerald-800">
                            <span class="block text-emerald-300 text-[11px]">{{ $grandTotalSemuaOring }} Total Org</span>
                            <span class="block text-yellow-400 font-extrabold text-[13px] mt-0.5">Rp {{ number_format($grandTotalSemuaGaji, 0, ',', '.') }}</span>
                        </td>

                        <!-- TOTAL OVERHEAD -->
                        <td class="p-4 text-center bg-slate-850">
                            <span class="block text-gray-300 text-[11px]">{{ $grandTotalOverheadOring }} Orang</span>
                            <span class="block text-white text-[12px] mt-0.5">Rp {{ number_format($grandTotalOverheadGaji, 0, ',', '.') }}</span>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
    <!-- JAVASCRIPT TOGGLE INTERAKTIF AGAR INPUT BERGANTI OTOMATIS TANPA LAYAR BERKEDIP -->
    <script>
        function toggleFilterInputs() {
            var tipe = document.getElementById('tipe_filter').value;
            var wrapperBulan = document.getElementById('wrapper_bulan');
            var wrapperTahun = document.getElementById('wrapper_tahun');
            
            if (tipe === 'tahunan') {
                wrapperBulan.classList.add('hidden');
                wrapperTahun.classList.remove('hidden');
            } else {
                wrapperBulan.classList.remove('hidden');
                wrapperTahun.classList.add('hidden');
            }
            
            // Otomatis submit form agar halaman me-refresh data baru sesuai tipe
            document.getElementById('tipe_filter').form.submit();
        }
    </script>
</x-layout.beranda.app>

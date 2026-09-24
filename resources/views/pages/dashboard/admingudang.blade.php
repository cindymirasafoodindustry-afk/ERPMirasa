<x-layout.user.app title="Admin Gudang Dashboard">
    <div class="space-y-6" x-data="{ filterType: '{{ $filterType }}' }">

        {{-- GREETING SECTION --}}
        <div class="relative overflow-hidden bg-white border border-gray-100 rounded-3xl shadow-sm p-8">
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Gudang
                        {{ auth()->user()->perusahaan->nama_perusahaan ?? 'Mirasa' }} 📦</h1>
                    <p class="text-gray-500 max-w-md">Pantau ketersediaan bahan baku dan alur distribusi barang hari ini.
                    </p>
                </div>
                <div class="flex gap-3">
                    @can('produksi.index')
                        <a href="{{ route('produksi.index') }}"
                            class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-all">Riwayat
                            Produksi</a>
                    @endcan
                    @can('inventory.index')
                        <a href="{{ route('inventory.index') }}"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-2xl font-bold text-sm shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">Gudang</a>
                    @endcan
                </div>
            </div>
        </div>

        

        {{-- KPI CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <div
                        class="p-4 {{ $stats['stok_kritis'] > 0 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }} rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Stok
                            Kritis</p>
                        <h3 class="text-2xl font-black text-gray-800">{{ $stats['stok_kritis'] }} <span
                                class="text-xs font-medium text-gray-400">Item</span></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-blue-50 rounded-2xl text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">
                            Total Bahan Baku</p>
                        <h3 class="text-2xl font-black text-gray-800">{{ $stats['total_bahan_baku'] }} <span
                                class="text-xs font-medium text-gray-400">Jenis</span></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-purple-50 rounded-2xl text-purple-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">
                            Barang Produksi</p>
                        <h3 class="text-2xl font-black text-gray-800">{{ $stats['total_barang_produksi'] }} <span
                                class="text-xs font-medium text-gray-400">Jenis</span></h3>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-yellow-50 rounded-2xl text-yellow-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 16 16">
                            <path fill="currentColor"
                                d="m4.036 2.488l6.611 2.833L8 6.455L1.427 3.638c.148-.151.329-.273.535-.352zm1.338-.514l1.55-.596a3 3 0 0 1 2.153 0l4.962 1.908c.205.08.386.2.534.352l-2.656 1.138zm9.62 2.572L8.5 7.329v7.45q.295-.05.577-.158l4.962-1.909a1.5 1.5 0 0 0 .961-1.4V4.686q0-.07-.007-.14M7.5 14.779v-7.45L1.007 4.546a2 2 0 0 0-.007.14v6.626a1.5 1.5 0 0 0 .962 1.4l4.961 1.909q.282.108.577.158" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1">
                            Total Barang Penolong</p>
                        <h3 class="text-2xl font-black text-gray-800">{{ $stats['total_barang_penolong'] }} <span
                                class="text-xs font-medium text-gray-400">Jenis</span></h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAFIK SECTION --}}
        <div class="space-y-6 mb-6">

            {{-- SECTION 1: HEADER & FILTER --}}
            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight uppercase italic">Analisis Operasional
                    </h1>
                    <p class="text-sm text-gray-400 font-medium italic">Insight operasional periode ini</p>
                </div>

                <form action="{{ route('admin-gudang.dashboard') }}" method="GET"
                    class="flex flex-wrap items-center gap-2">

                    <select name="filter_type" x-model="filterType"
                        class="rounded-xl border-gray-200 text-xs font-bold py-2 px-3 outline-none bg-blue-50 text-blue-600 shadow-sm">
                        <option value="month">MODE BULANAN</option>
                        <option value="year">MODE TAHUNAN</option>
                    </select>

                    <div x-show="filterType === 'month'" x-transition>
                        <select name="month"
                            class="rounded-xl border-gray-200 text-xs font-bold py-2 px-3 outline-none shadow-sm">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}"
                                    {{ (int) $selectedMonth === $m ? 'selected' : '' }}>
                                    {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <select name="year"
                        class="rounded-xl border-gray-200 text-xs font-bold py-2 px-3 outline-none shadow-sm">
                        @foreach (range(date('Y') - 2, date('Y') + 1) as $y)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="bg-gray-900 text-white px-5 py-2 rounded-xl font-bold text-xs uppercase hover:bg-blue-600 transition-all shadow-md">Update</button>
                </form>
            </div>

            {{-- SECTION: GRAFIK TREND --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                {{-- GRAFIK BAHAN BAKU --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-1.5 h-6 bg-blue-600 rounded-full"></div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-gray-800">Grafik Bahan Baku Masuk
                        </h3>
                    </div>
                    <div class="w-full h-[300px]">
                        <canvas id="chart-bb"></canvas>
                    </div>
                </div>

                {{-- GRAFIK HASIL PRODUKSI --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-1.5 h-6 bg-emerald-600 rounded-full"></div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-gray-800">Grafik Hasil Produksi
                            (KG)
                        </h3>
                    </div>
                    <div class="w-full h-[300px]">
                        <canvas id="chart-produksi"></canvas>
                    </div>
                </div>

                {{-- GRAFIK PEMAKAIAN --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-1.5 h-6 bg-amber-600 rounded-full"></div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-gray-800">Grafik Pemakaian
                            Operasional</h3>
                    </div>
                    <div class="w-full h-[300px]">
                        <canvas id="chart-pemakaian"></canvas>
                    </div>
                </div>

                {{-- GRAFIK PENGELUARAN --}}
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-1.5 h-6 bg-rose-600 rounded-full"></div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-gray-800">Grafik Biaya Pengeluaran
                        </h3>
                    </div>
                    <div class="w-full h-[300px]">
                        <canvas id="chart-pengeluaran"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- RECENT MOVEMENTS TABLE --}}
            <div
                class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="font-black text-gray-800 uppercase text-xs tracking-widest italic">Aktivitas Stok
                        Terbaru
                    </h3>
                </div>
                <div class="flex-grow">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-8 py-4">Barang</th>
                                <th class="px-6 py-4 text-center">Batch</th>
                                <th class="px-8 py-4 text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($recent_stock_movements as $move)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-8 py-4">
                                        <p class="text-sm font-bold text-gray-800 leading-tight">
                                            {{ $move->Inventory->Barang->nama_barang ?? 'None' }}</p>
                                        <p class="text-[10px] text-gray-400 font-medium">
                                            {{ $move->tanggal_masuk ?? 'Baru' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="px-2 py-1 bg-gray-100 rounded-lg text-[10px] font-black text-gray-600 uppercase">{{ $move->nomor_batch ?? '-' }}</span>
                                    </td>
                                    <td class="px-8 py-4 text-right">
                                        <p class="text-sm font-black text-emerald-600">
                                            +{{ number_format($move->jumlah_diterima, 0) }}</p>
                                        <p class="text-[9px] font-bold text-gray-400 uppercase">
                                            {{ $move->Inventory->Barang->satuan ?? 'Tidak ada' }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-8 py-20 text-center text-gray-400 italic text-sm">
                                        Belum
                                        ada mutasi stok hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- NOTIFICATIONS / ALERT --}}
            <div class="space-y-6">
                <div class="bg-white rounded-3xl border border-gray-100 p-8 shadow-sm">
                    <h4 class="font-black uppercase text-xs tracking-[0.2em] mb-6 text-gray-400 leading-none">Status
                        Gudang</h4>
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="w-1.5 h-12 bg-emerald-500 rounded-full"></div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Sistem Normal</p>
                                <p class="text-xs text-gray-500">Semua sinkronisasi data dengan pusat berjalan lancar.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="w-1.5 h-12 {{ $stats['stok_kritis'] > 0 ? 'bg-red-500' : 'bg-gray-200' }} rounded-full">
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Cek Ketersediaan</p>
                                <p class="text-xs text-gray-500">
                                    {{ $stats['stok_kritis'] > 0 ? 'Beberapa bahan baku berada di bawah batas minimum.' : 'Stok bahan baku terpantau aman.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-3xl p-8 text-white shadow-xl">
                    <h4 class="font-black uppercase text-[10px] tracking-[0.2em] mb-4 opacity-50">Bantuan Gudang</h4>
                    <p class="text-lg font-bold mb-4">Butuh bantuan teknis?</p>
                    <button
                        class="w-full py-3 bg-white/10 hover:bg-white/20 transition-all rounded-2xl font-black text-xs uppercase tracking-widest border border-white/10">Hubungi
                        IT Support</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const data = @json($chartData);

            // 1. Fungsi meringkas nominal uang (Ribuan, Jutaan, Milyar)
            const formatShort = (val) => {
                if (val >= 1e9) return (val / 1e9).toFixed(1).replace(/\.0$/, '') + ' M';
                if (val >= 1e6) return (val / 1e6).toFixed(1).replace(/\.0$/, '') + ' Jt';
                if (val >= 1e3) return (val / 1e3).toFixed(1).replace(/\.0$/, '') + ' K';
                return val;
            };

            // 2. Fungsi untuk memformat berat (KG -> TON jika >= 1000)
            const formatWeight = (val) => {
                if (val >= 1000) {
                    return (val / 1000).toFixed(1).replace(/\.0$/, '') + ' TON';
                }
                return val.toLocaleString('id-ID') + ' KG';
            };

            // 3. Generator Opsi Chart.js
            const getBaseOptions = (mode = '', isCurrency = false) => ({
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        // Tampilkan legend jika mode 'multi' (Pemakaian & Pengeluaran)
                        display: (mode === 'multi' || mode === 'operasional'),
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            usePointStyle: true,
                            font: {
                                size: 9,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                let val = ctx.raw;
                                let label = ctx.dataset.label || '';
                                if (isCurrency) return ` ${label}: Rp ${val.toLocaleString('id-ID')}`;
                                if (mode === 'weight') return ` ${label}: ${formatWeight(val)}`;
                                // Untuk operasional (tanpa satuan tambahan di belakang)
                                return ` ${label}: ${val.toLocaleString('id-ID')}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        },
                        ticks: {
                            font: {
                                size: 9,
                                weight: 'bold'
                            },
                            callback: function(v) {
                                if (isCurrency) return 'Rp ' + formatShort(v);
                                if (mode === 'weight') return v >= 1000 ? (v / 1000).toFixed(1) + 't' :
                                    v;
                                return v; // Operasional hanya angka murni
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 9,
                                weight: 'bold'
                            }
                        }
                    }
                }
            });

            // --- RENDER CHARTS ---

            // 1. Chart Bahan Baku (Satu Garis Biru - Satuan Berat)
            new Chart(document.getElementById('chart-bb').getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'BB Masuk',
                        data: data.bb_masuk,
                        borderColor: '#2563eb',
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        tension: 0.3,
                        pointRadius: 0
                    }]
                },
                options: getBaseOptions('weight')
            });

            // 2. Chart Produksi (Satu Garis Hijau - Satuan Berat)
            new Chart(document.getElementById('chart-produksi').getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Hasil Produksi',
                        data: data.hasil_produksi,
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        tension: 0.3,
                        pointRadius: 0
                    }]
                },
                options: getBaseOptions('weight')
            });

            // 3. Chart Pemakaian (Multi Garis - Tanpa Satuan/Murni Angka)
            new Chart(document.getElementById('chart-pemakaian').getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: data.pemakaian_datasets
                },
                options: getBaseOptions('operasional')
            });

            // 4. Chart Pengeluaran (Multi Garis - Satuan Mata Uang)
            new Chart(document.getElementById('chart-pengeluaran').getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: data.pengeluaran_datasets
                },
                options: getBaseOptions('multi', true)
            });
        });
    </script>
</x-layout.user.app>

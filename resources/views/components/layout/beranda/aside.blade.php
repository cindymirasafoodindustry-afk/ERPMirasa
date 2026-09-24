{{-- ASIDE SEKUNDER (OFF-CANVAS) --}}
<div id="asideSekunder"
    class="fixed inset-y-0 right-0 w-80 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-[70] border-l border-gray-100 flex flex-col">

    {{-- Header Aside --}}
    <div class="p-6 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800">Menu Cepat</h2>
        <button onclick="closeAside('asideSekunder')" class="text-gray-400 hover:text-red-500 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M16.066 8.995a.75.75 0 1 0-1.06-1.061L12 10.939L8.995 7.934a.75.75 0 1 0-1.06 1.06L10.938 12l-3.005 3.005a.75.75 0 0 0 1.06 1.06L12 13.06l3.005 3.006a.75.75 0 0 0 1.06-1.06L13.062 12z" />
            </svg>
        </button>
    </div>

    {{-- Content Aside (Menu Cepat) --}}
    <div class="flex-1 overflow-y-auto p-4 space-y-2">

        @php
            if (auth()->user()->hasRole('Super Admin')) {
                $dashboardRoute = route('super-admin.dashboard');
            } elseif (auth()->user()->hasRole('Manager')) {
                $dashboardRoute = route('manager.dashboard');
            } elseif (auth()->user()->hasRole('Admin Gudang')) {
                $dashboardRoute = route('admin-gudang.dashboard');
            } else {
                $dashboardRoute = route('admin-gudang.dashboard');
            }
        @endphp

        {{-- Dashboard --}}
        <a href="{{ $dashboardRoute }}"
            class="flex items-center p-3 text-gray-700 rounded-xl hover:bg-purple-50 hover:text-purple-700 transition-all group">
            <div class="p-2 bg-purple-100 rounded-lg group-hover:bg-purple-200 transition-colors">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                    </path>
                </svg>
            </div>
            <span class="ms-3 font-medium">Dashboard</span>
        </a>

        @can('inventory.index')
            {{-- Gudang --}}
            <a href="{{ route('inventory.index') }}"
                class="flex items-center p-3 text-gray-700 rounded-xl hover:bg-yellow-50 hover:text-yellow-700 transition-all group">
                <div class="p-2 bg-yellow-100 rounded-lg group-hover:bg-yellow-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-600" viewBox="0 0 16 16">
                        <path fill="currentColor"
                            d="M16 4L7.94 0L0 4v1h1v11h2V7h10v9h2V5h1zM4 6V5h2v1zm3 0V5h2v1zm3 0V5h2v1z" />
                        <path fill="currentColor" d="M6 9H5V8H4v3h3V8H6zm0 4H5v-1H4v3h3v-3H6zm4 0H9v-1H8v3h3v-3h-1z" />
                    </svg>
                </div>
                <span class="ms-3 font-medium">Gudang</span>
            </a>
        @endcan

        @can('produksi.index')
            {{-- Produksi --}}
            <a href="{{ route('produksi.index') }}"
                class="flex items-center p-3 text-gray-700 rounded-xl hover:bg-gray-100 transition-all group">
                <div class="p-2 bg-gray-200 rounded-lg group-hover:bg-gray-300 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" viewBox="0 0 48 48">
                        <path fill="currentColor" fill-rule="evenodd"
                            d="M24 1.5q-1.847 0-3.47.019c.056 2.59.186 5.094.294 6.863l2.104-1.335a2 2 0 0 1 2.144 0l2.104 1.335c.108-1.77.238-4.273.295-6.863A313 313 0 0 0 24 1.5m-12.788.308c1.557-.089 3.647-.18 6.318-.24c.068 3.12.24 6.104.356 7.876c.125 1.903 2.235 2.939 3.82 1.932L24 9.92l2.295 1.457c1.585 1.006 3.694-.03 3.82-1.933a188 188 0 0 0 .355-7.876c2.67.06 4.76.151 6.318.24c2.793.16 5.106 2.213 5.377 5.089c.179 1.895.335 4.564.335 8.103s-.156 6.208-.335 8.103c-.271 2.876-2.584 4.93-5.377 5.089c-2.646.15-6.832.308-12.788.308s-10.142-.157-12.788-.308c-2.793-.16-5.106-2.213-5.377-5.089C5.656 21.208 5.5 18.54 5.5 15s.156-6.208.335-8.103c.271-2.876 2.584-4.93 5.377-5.089M27 20.5a1.5 1.5 0 0 0 0 3h8a1.5 1.5 0 0 0 0-3zm1.5-4.5a1.5 1.5 0 0 1 1.5-1.5h5a1.5 1.5 0 0 1 0 3h-5a1.5 1.5 0 0 1-1.5-1.5M24 46.5a735 735 0 0 1-14.19-.12C5.704 46.3 1.5 43.776 1.5 39s4.203-7.3 8.31-7.38c3.251-.063 7.921-.12 14.19-.12s10.939.057 14.189.12c4.108.08 8.311 2.603 8.311 7.38s-4.203 7.3-8.31 7.38c-3.251.063-7.921.12-14.19.12M9 39a3 3 0 1 0 6 0a3 3 0 0 0-6 0m15 3a3 3 0 1 1 0-6a3 3 0 0 1 0 6m9-3a3 3 0 1 0 6 0a3 3 0 0 0-6 0"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <span class="ms-3 font-medium">Produksi</span>
            </a>
        @endcan

        @can('bahan-baku.index')
            {{-- Bahan Baku --}}
            <a href="{{ route('bahan-baku.index') }}"
                class="flex items-center p-3 text-gray-700 rounded-xl hover:bg-green-50 hover:text-green-700 transition-all group">
                <div class="p-2 bg-green-100 rounded-lg group-hover:bg-green-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M3 6.25A3.25 3.25 0 0 1 6.25 3h11.5A3.25 3.25 0 0 1 21 6.25v4.762a3.28 3.28 0 0 0-2.61.95l-5.903 5.903a3.7 3.7 0 0 0-.931 1.57c-.345-.536-.87-.915-1.412-1.133c-.691-.278-1.385-.16-1.936.035a5.5 5.5 0 0 0-.729.326a.5.5 0 0 1-.089.07L5.693 19.77c-.467.275-.907.365-1.298.335a1.9 1.9 0 0 1-.9-.311c-.387-.255-.496-.683-.496-1.005V6.25m16.1 6.42l-5.903 5.902a2.7 2.7 0 0 0-.706 1.247l-.428 1.712c-.355.17-.71.202-1.133.105c-.126-.03-.18-.175-.127-.293c.43-.962-.19-1.776-1.03-2.113c-.955-.385-2.226.515-3.292 1.268c-.592.42-1.12.793-1.496.876c-.525.117-1.162-.123-1.631-.38c-.209-.113-.487.072-.388.288c.242.529.731 1.133 1.71 1.255c.98.121 1.766-.347 2.55-.815c.583-.348 1.165-.696 1.826-.799c.086-.013.144.088.105.166c-.242.484-.356 1.37.218 1.818c.848.662 3.237.292 3.828.088q.073-.007.148-.027l1.83-.457a2.7 2.7 0 0 0 1.248-.707l5.903-5.902a2.286 2.286 0 0 0-3.233-3.232" />
                    </svg>
                </div>
                <span class="ms-3 font-medium">Bahan Baku</span>
            </a>
        @endcan

        @canany(['barang-keluar.produksi', 'barang-keluar.penjualan', 'barang-keluar.bahan-baku'])
            {{-- Barang Keluar --}}
            <a href="{{ route('barang-keluar.index') }}"
                class="flex items-center p-3 text-gray-700 rounded-xl hover:bg-red-50 hover:text-red-700 transition-all group">
                <div class="p-2 bg-red-100 rounded-lg group-hover:bg-red-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-600" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="m21.706 5.292l-2.999-2.999A1 1 0 0 0 18 2H6a1 1 0 0 0-.707.293L2.294 5.292A1 1 0 0 0 2 6v13c0 1.103.897 2 2 2h16c1.103 0 2-.897 2-2V6a1 1 0 0 0-.294-.708M6.414 4h11.172l1 1H5.414zM14 14v3h-4v-3H7l5-5l5 5z" />
                    </svg>
                </div>
                <span class="ms-3 font-medium">Barang Keluar</span>
            </a>
        @endcanany

        @canany(['barang-masuk.produksi', 'barang-masuk.bahan-penolong'])
            {{-- Barang Masuk --}}
            <a href="{{ route('barang-masuk.index') }}"
                class="flex items-center p-3 text-gray-700 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all group">
                <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-600" viewBox="0 0 16 16">
                        <path fill="currentColor"
                            d="M13 1H3L0 4v10.5a.5.5 0 0 0 .5.5h15a.5.5 0 0 0 .5-.5V4zM8 13L3 9h3V6h4v3h3zM2.414 3l1-1h9.172l1 1z" />
                    </svg>
                </div>
                <span class="ms-3 font-medium">Barang Masuk</span>
            </a>
        @endcanany

        @canany(['pemesanan.index', 'pemesanan.create', 'pemesanan.edit', 'pemesanan.delete'])
        {{-- Proses Pemesanan Barang --}}
        <a href="{{ route('pemesanan-barang.index') }}" 
           class="flex items-center p-3 text-gray-700 rounded-xl hover:bg-orange-50 hover:text-orange-700 transition all group {{ request()->routeIs('pemesanan-barang.*') ? 'bg-orange-50 text-orange-700 font-semibold' : '' }}">
            <div class="p-2 bg-orange-100 rounded-lg group-hover:bg-orange-200 transition-colors {{ request()->routeIs('pemesanan-barang.*') ? 'bg-orange-200 text-orange-600' : 'text-gray-600' }}">
                <!-- Icon Keranjang Belanja / Pemesanan (SVG) -->
                <svg xmlns="http://w3.org" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <span class="ms-3 font-medium">Pemesanan Barang</span>
        </a>
        @endcanany

        {{-- TAMBAHKAN MENU ABSENSI DI SINI --}}
        <a href="{{ route('attendance.index') }}"
            class="flex items-center p-3 rounded-xl transition-all group {{ request()->routeIs('attendance.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-700' }}">
            <div class="p-2 rounded-lg transition-colors {{ request()->routeIs('attendance.*') ? 'bg-blue-200 text-blue-600' : 'bg-blue-100 group-hover:bg-blue-200 text-blue-600' }}">
                <!-- Icon Jam / Presensi (SVG) -->
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <span class="ms-3 font-medium">Absensi Karyawan</span>
        </a>

        <!-- ================= MASTER GRUP DROPDOWN PAYROLL & FINANCE PREMIUM ================= -->
        <div x-data="{ openPayroll: {{ request()->is('payroll*') ? 'true' : 'false' }} }" class="space-y-1">
            <!-- Tombol Pemicu Dropdown Utama -->
            <button @click="openPayroll = !openPayroll" 
                    type="button" 
                    class="w-full flex items-center justify-between px-4 py-3 text-xs font-black rounded-xl transition-all duration-200 cursor-pointer {{ request()->is('payroll*') ? 'bg-slate-100 text-slate-800' : 'text-slate-600 hover:bg-slate-50' }}">
                <div class="flex items-center gap-3">
                    <div class="p-1.5 rounded-lg {{ request()->is('payroll*') ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5M4.5 19.5h15M5.25 4.5V18.75m13.5-14.25V18.75M2.25 4.5l1.353-.135A2.25 2.25 0 016 6.3c0 .605-.13 1.192-.372 1.72l-.372.812A1.125 1.125 0 006.273 10.5h11.454a1.125 1.125 0 001.017-.668l.371-.812A3.75 3.75 0 0118 6.3a2.25 2.25 0 012.397-1.936l1.353.135" />
                        </svg>
                    </div>
                    <span class="tracking-wide">MANAJEMEN PAYROLL</span>
                </div>
                <!-- Panah Indikator Dropdown Terbuka/Tertutup -->
                <svg class="w-3.5 h-3.5 transform transition-transform duration-200" :class="openPayroll ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <!-- Anak Menu (Sub-Menus) yang Muncul Saat Dropdown Diklik Admin -->
            <div x-show="openPayroll" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 class="pl-9 space-y-1 pt-0.5">
                 
                <!-- Menu 1: Form Rekap Bulanan Admin -->
                <a href="{{ route('payroll.index') }}" 
                   class="flex items-center gap-2.5 px-3 py-2 text-[11px] font-bold rounded-lg transition-all {{ request()->routeIs('payroll.index') ? 'text-blue-600 bg-blue-50/50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                    <div class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('payroll.index') ? 'bg-blue-500' : 'bg-slate-300' }}"></div>
                    <span>Rekap Gaji Bulanan</span>
                </a>

                <!-- Menu 2: Buku Besar Laporan Cost Harian Pimpinan/Owner -->
                <a href="{{ route('payroll.detail_harian') }}" 
                   class="flex items-center gap-2.5 px-3 py-2 text-[11px] font-bold rounded-lg transition-all {{ request()->routeIs('payroll.detail_harian') ? 'text-emerald-600 bg-emerald-50/50' : 'text-slate-500 hover:text-emerald-600 hover:bg-slate-50' }}">
                    <div class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('payroll.detail_harian') ? 'bg-emerald-500' : 'bg-slate-300' }}"></div>
                    <span>Detail Payroll Harian</span>
                </a>
                <!-- Menu 3: Buku Besar Laporan Gaji Per Kelompok Kerja Harian -->
                <a href="{{ route('payroll.kelompok_harian') }}"
                class="flex items-center gap-2.5 px-3 py-2 text-[11px] font-bold rounded-lg transition-all {{ request()->routeIs('payroll.kelompok_harian') ? 'text-purple-600 bg-purple-50/50' : 'text-gray-500 hover:bg-slate-100' }}">
                    <div class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('payroll.kelompok_harian') ? 'bg-purple-500' : 'bg-slate-300' }}"></div>
                    <span>Gaji Per Kelompok Kerja</span>
                </a>
            </div>
        </div>

        @can('pemakaian.index')
            {{-- Pemakaian Operasional --}}
            <a href="{{ route('pemakaian.index') }}"
                class="flex items-center p-3 text-gray-700 rounded-xl hover:bg-cyan-50 hover:text-cyan-700 transition-all group">
                <div class="p-2 bg-cyan-100 rounded-lg group-hover:bg-cyan-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-cyan-600" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M16.45 8.425q.3.3.7.313t.7-.288l1.4-1.4q.3-.3.3-.712t-.3-.713q-.275-.275-.7-.275t-.7.275l-1.4 1.4q-.275.275-.275.688t.275.712M3 22q-.825 0-1.412-.587T1 20V6q0-.825.588-1.412T3 4h8v8q0 .825.588 1.413T13 14h6v6q0 .825-.587 1.413T17 22zm15-10q-.75 0-1.475-.225t-1.35-.65l-.375.35q-.3.275-.713.275t-.687-.275t-.275-.7t.275-.7l.4-.4q-.4-.6-.6-1.275T13 7q0-2.075 1.463-3.537T18 2h5v5q0 2.075-1.463 3.538T18 12M5 18h2v-7H5zm4 0h2V8H9zm4 0h2v-4h-2z" />
                    </svg>
                </div>
                <span class="ms-3 font-medium text-sm">Pemakaian Operasional</span>
            </a>
        @endcan

        @canany(['pengeluaran.maintenance', 'pengeluaran.kesejahteraan', 'pengeluaran.operasional',
            'pengeluaran.office', 'pengeluaran.limbah', 'pengeluaran.administrasi'])
            {{-- Pengeluaran --}}
            <a href="{{ route('pengeluaran.index') }}"
                class="flex items-center p-3 text-gray-700 rounded-xl hover:bg-amber-50 hover:text-amber-700 transition-all group">
                <div class="p-2 bg-amber-100 rounded-lg group-hover:bg-amber-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-600" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="1.5">
                            <path d="M14.5 14.001a2.5 2.5 0 1 1-5 0a2.5 2.5 0 0 1 5 0" />
                            <path
                                d="M8 7.89c-1.12-.006-2.44-.132-4.122-.465C2.921 7.235 2 7.946 2 8.922V18.94a1.47 1.47 0 0 0 1.145 1.441c6.965 1.536 8.104-.27 12.855-.27c1.51 0 2.736.143 3.676.32c1.096.207 2.324-.632 2.324-1.747V8.91c0-.569-.324-1.083-.867-1.251c-.81-.251-2.188-.57-4.133-.655" />
                            <path
                                d="M2 11.001c1.951 0 3.705-1.595 3.929-3.246M18.5 7.501c0 2.04 1.765 3.969 3.5 3.969m0 5.531c-1.9 0-3.74 1.31-3.898 3.098M6 20.497a4 4 0 0 0-4-4M9.5 5.501s1.8-2.5 2.5-2.5m2.5 2.5s-1.8-2.5-2.5-2.5m0 0v5.5" />
                        </g>
                    </svg>
                </div>
                <span class="ms-3 font-medium">Pengeluaran</span>
            </a>
        @endcanany

    </div>


</div>

{{-- Overlay Aside --}}
<div id="asideOverlay" onclick="closeAside('asideSekunder')"
    class="fixed inset-0 bg-black/20 backdrop-blur-[2px] z-[65] hidden">
</div>

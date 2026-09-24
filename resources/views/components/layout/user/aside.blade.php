<style>
    /* Custom Scrollbar untuk Sidebar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
        /* Sangat tipis */
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e5e7eb;
        /* Warna abu-abu terang (gray-200) */
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #d1d5db;
        /* Warna gray-300 saat hover */
    }

    /* Sembunyikan scrollbar jika tidak sedang di-hover (opsional) */
    .scrollbar-minimal {
        scrollbar-width: thin;
        scrollbar-color: #e5e7eb transparent;
    }
</style>
<aside id="logo-sidebar"
    class="fixed top-0 left-0 z-40 h-screen pt-20 transition-all duration-300 ease-in-out bg-white border-r border-gray-200 overflow-hidden group shadow-sm -translate-x-full w-64 sm:translate-x-0 sm:w-20 sm:hover:w-64"
    aria-label="Sidebar">

    <div class="h-full px-4 pb-4 overflow-y-auto overflow-x-hidden bg-white custom-scrollbar">
        <ul class="space-y-2 font-medium">

            <li>
                @php
                    // Menentukan route dashboard berdasarkan role
                    $urlDashboard = '#';
                    if (auth()->user()->hasRole('Super Admin')) {
                        $urlDashboard = route('super-admin.dashboard');
                    } elseif (auth()->user()->hasRole('Manager')) {
                        $urlDashboard = route('manager.dashboard');
                    } elseif (auth()->user()->hasRole('Admin Gudang')) {
                        $urlDashboard = route('admin-gudang.dashboard');
                    } else {
                        $urlDashboard = route('admin-gudang.dashboard');
                    }
                @endphp
                <a href="{{ $urlDashboard }}"
                    class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                    <div class="min-w-[32px] flex justify-center">
                        <svg class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                            </path>
                        </svg>
                    </div>
                    <span
                        class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">Dashboard</span>
                </a>
            </li>

            @can('beranda.view')
                <li>
                    <a href="{{ route('beranda') }}"
                        class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 24 24">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M20 19v-8.5a1 1 0 0 0-.4-.8l-7-5.25a1 1 0 0 0-1.2 0l-7 5.25a1 1 0 0 0-.4.8V19a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1" />
                            </svg>
                        </div>
                        <span
                            class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">Beranda</span>
                    </a>
                </li>
            @endcan

                             @can('absensi.index')
        <li x-data="{ openKaryawan: {{ request()->routeIs('attendance.*') ? 'true' : 'false' }} }">
            <!-- Tombol Induk Menu Karyawan (Peka Kursor) -->
            <button @click="openKaryawan = !openKaryawan" 
                    class="w-full flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group transition-all whitespace-nowrap {{ request()->routeIs('attendance.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                
                <div class="flex items-center min-w-[32px] justify-center">
                    <!-- Ikon Utama Grup Karyawan SVG -->
                    <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-600 transition-colors {{ request()->routeIs('attendance.*') ? 'text-blue-600' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                
                <!-- LOGIKA UTAMA: Teks Tampil Transparan & Hanya Muncul Saat Sidebar di-Hover -->
                <span class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold text-sm whitespace-nowrap {{ request()->routeIs('attendance.*') ? 'text-blue-600' : '' }}">
                    Menu Karyawan
                </span>

                <!-- Ikon Panah Dropdown Mungil di Ujung Kanan (Ikut Muncul Saat di-Hover) -->
                <svg :class="openKaryawan ? 'rotate-180' : ''" class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-all duration-200 ms-auto text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <!-- Wadah Anak Menu (Otomatis Menyesuaikan Mode Melayang) -->
            <div x-show="openKaryawan" x-cloak class="py-1.5 space-y-1 bg-gray-50/80 rounded-xl mt-1 flex flex-col items-start pl-4 group-hover:pl-12 transition-all">
                
                <!-- 1. Anak Menu: Data Karyawan -->
                <a href="{{ route('employee.index') }}" class="flex items-center py-1.5 text-xs font-medium transition-colors whitespace-nowrap {{ request()->routeIs('employee.*') ? 'text-blue-600 font-black' : 'text-gray-500 hover:text-blue-600' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('employee.*') ? 'bg-blue-600' : 'bg-gray-400' }} mr-2"></span>
                    <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">Data Karyawan</span>
                </a>

                <!-- 2. Anak Menu: Absensi -->
                <a href="{{ route('attendance.index') }}" class="flex items-center py-1.5 text-xs font-bold transition-colors whitespace-nowrap {{ request()->routeIs('attendance.*') ? 'text-blue-600 font-black' : 'text-gray-500 hover:text-blue-600' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('attendance.*') ? 'bg-blue-600' : 'bg-gray-400' }} mr-2"></span>
                    <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">Absensi Karyawan</span>
                </a>

                <!-- 3. Anak Menu: Penggajian Karyawan -->
                <a href="{{ route('payroll.index') }}" class="flex items-center py-1.5 text-xs font-medium text-gray-500 hover:text-blue-600 transition-colors whitespace-nowrap {{ request()->routeIs('payroll.*') ? 'text-blue-600 font-black' : 'text-gray-500 hover:text-blue-600' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('payroll.*') ? 'bg-blue-600' : 'bg-gray-400' }} mr-2"></span>
                    <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">Penggajian Karyawan</span>
                </a>
            </div>
        </li>
        @endcan

            @canany(['laporan.produksi', 'laporan.gudang', 'laporan.pengeluaran', 'laporan.hpp', 'laporan.transaksi'])
                <li x-data="{ open: false }">
                    {{-- Tombol Utama Laporan --}}
                    <button @click="open = !open"
                        class="w-full flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M13 9h5.5L13 3.5zM6 2h8l6 6v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4c0-1.11.89-2 2-2m1 18h2v-6H7zm4 0h2v-8h-2zm4 0h2v-4h-2z" />
                            </svg>
                        </div>
                        {{-- Di Mobile teks harus muncul jika sidebar terbuka (sm:translate-x-0) --}}
                        <span
                            class="ms-4 opacity-0 sm:group-hover:opacity-100 translate-x-[-10px] sm:group-hover:translate-x-0 transition-all duration-300 font-bold uppercase text-xs tracking-widest flex-1 text-left max-sm:opacity-100 max-sm:translate-x-0">
                            Laporan
                        </span>

                        {{-- Indikator Panah --}}
                        <svg xmlns="http://www.w3.org/2000/svg" :class="open ? 'rotate-180' : ''"
                            class="w-4 h-4 transition-transform duration-200 opacity-0 sm:group-hover:opacity-100 max-sm:opacity-100"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Sub-Menu (Children) --}}
                    <div x-show="open" x-cloak x-collapse {{-- Gunakan plugin collapse agar transisi smooth --}}
                        class="mt-1 space-y-1 px-2 overflow-hidden">

                        @can('laporan.produksi')
                            {{-- Link Item: Hapus opacity-0 agar langsung terlihat saat di-expand --}}
                            <a href="{{ route('laporan-produksi') }}"
                                class="flex items-center p-2.5 text-sm text-gray-500 rounded-lg hover:bg-gray-50 hover:text-blue-600 pl-12 font-medium transition-all">
                                Produksi
                            </a>
                        @endcan

                        @can('laporan.gudang')
                            <a href="{{ route('laporan-gudang') }}"
                                class="flex items-center p-2.5 text-sm text-gray-500 rounded-lg hover:bg-gray-50 hover:text-blue-600 pl-12 font-medium transition-all">
                                Gudang
                            </a>
                        @endcan

                        @can('laporan.pengeluaran')
                            <a href="{{ route('laporan-pengeluaran') }}"
                                class="flex items-center p-2.5 text-sm text-gray-500 rounded-lg hover:bg-gray-50 hover:text-blue-600 pl-12 font-medium transition-all">
                                Pengeluaran
                            </a>
                        @endcan

                        @can('laporan.hpp')
                            <a href="{{ route('laporan-hpp') }}"
                                class="flex items-center p-2.5 text-sm text-gray-500 rounded-lg hover:bg-gray-50 hover:text-blue-600 pl-12 font-medium transition-all">
                                HPP
                            </a>
                        @endcan

                        @can('laporan.transaksi')
                            <a href="{{ route('laporan-transaksi') }}"
                                class="flex items-center p-2.5 text-sm text-gray-500 rounded-lg hover:bg-gray-50 hover:text-blue-600 pl-12 font-medium transition-all">
                                Transaksi
                            </a>
                        @endcan
                    </div>
                </li>
            @endcanany

            @canany(['grafik.bahan-baku', 'grafik.produksi', 'grafik.pemakaian', 'grafik.hpp', 'grafik.transaksi'])
                <li>
                    <a href="{{ route('grafik.index') }}"
                        class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M7 16a1.5 1.5 0 0 0 1.5-1.5a1 1 0 0 0 0-.15l2.79-2.79h.46l1.61 1.61v.08a1.5 1.5 0 1 0 3 0v-.08L20 9.5A1.5 1.5 0 1 0 18.5 8a1 1 0 0 0 0 .15l-3.61 3.61h-.16L13 10a1.49 1.49 0 0 0-3 0l-3 3a1.5 1.5 0 0 0 0 3m13.5 4h-17V3a1 1 0 0 0-2 0v18a1 1 0 0 0 1 1h18a1 1 0 0 0 0-2" />
                            </svg>
                        </div>
                        <span
                            class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">Kurva</span>
                    </a>
                </li>
            @endcanany

            @can('produk.index')
                <li>
                    <a href="{{ route('produk.index') }}"
                        class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 1024 1024">
                                <path fill="currentColor" fill-rule="evenodd"
                                    d="M160 144h304c8.837 0 16 7.163 16 16v304c0 8.837-7.163 16-16 16H160c-8.837 0-16-7.163-16-16V160c0-8.837 7.163-16 16-16m564.314-25.333l181.019 181.02c6.248 6.248 6.248 16.378 0 22.627l-181.02 181.019c-6.248 6.248-16.378 6.248-22.627 0l-181.019-181.02c-6.248-6.248-6.248-16.378 0-22.627l181.02-181.019c6.248-6.248 16.378-6.248 22.627 0M160 544h304c8.837 0 16 7.163 16 16v304c0 8.837-7.163 16-16 16H160c-8.837 0-16-7.163-16-16V560c0-8.837 7.163-16 16-16m400 0h304c8.837 0 16 7.163 16 16v304c0 8.837-7.163 16-16 16H560c-8.837 0-16-7.163-16-16V560c0-8.837 7.163-16 16-16" />
                            </svg>
                        </div>
                        <span
                            class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">Produk</span>
                    </a>
                </li>
            @endcan

            @can('berita.index')
                <li>
                    <a href="{{ route('berita.index') }}"
                        class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 2048 2048">
                                <path fill="currentColor"
                                    d="M2048 512v896q0 53-20 99t-55 81t-82 55t-99 21H249q-51 0-96-20t-79-53t-54-79t-20-97V256h1792v256zm-128 128h-128v704q0 26-19 45t-45 19t-45-19t-19-45V384H128v1031q0 25 9 47t26 38t39 26t47 10h1543q27 0 50-10t40-27t28-41t10-50zm-384 0H256V512h1280zm0 768h-512v-128h512zm0-256h-512v-128h512zm0-256h-512V768h512zm-640 512H256V765h640zm-512-128h384V893H384z" />
                            </svg>
                        </div>
                        <span
                            class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">Berita</span>
                    </a>
                </li>
            @endcan

            @can('barang.index')
                <li>
                    <a href="{{ route('barang.index') }}"
                        class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="m17.578 4.432l-2-1.05C13.822 2.461 12.944 2 12 2s-1.822.46-3.578 1.382l-.321.169l8.923 5.099l4.016-2.01c-.646-.732-1.688-1.279-3.462-2.21m4.17 3.534l-3.998 2V13a.75.75 0 0 1-1.5 0v-2.286l-3.5 1.75v9.44c.718-.179 1.535-.607 2.828-1.286l2-1.05c2.151-1.129 3.227-1.693 3.825-2.708c.597-1.014.597-2.277.597-4.8v-.117c0-1.893 0-3.076-.252-3.978M11.25 21.904v-9.44l-8.998-4.5C2 8.866 2 10.05 2 11.941v.117c0 2.525 0 3.788.597 4.802c.598 1.015 1.674 1.58 3.825 2.709l2 1.049c1.293.679 2.11 1.107 2.828 1.286M2.96 6.641l9.04 4.52l3.411-1.705l-8.886-5.078l-.103.054c-1.773.93-2.816 1.477-3.462 2.21" />
                            </svg>
                        </div>
                        <span
                            class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">Barang</span>
                    </a>
                </li>
            @endcan

            @can('costumer.index')
                <li>
                    <a href="{{ route('costumer.index') }}"
                        class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 12 12">
                                <path fill="currentColor"
                                    d="M6.153 7.008A1.5 1.5 0 0 1 7.5 8.5c0 .771-.47 1.409-1.102 1.83c-.635.424-1.485.67-2.398.67s-1.763-.246-2.398-.67C.969 9.91.5 9.271.5 8.5A1.5 1.5 0 0 1 2 7h4zM10.003 7a1.5 1.5 0 0 1 1.5 1.5c0 .695-.432 1.211-.983 1.528c-.548.315-1.265.472-2.017.472q-.38-.001-.741-.056c.433-.512.739-1.166.739-1.944A2.5 2.5 0 0 0 7.997 7zM4.002 1.496A2.253 2.253 0 1 1 4 6.001a2.253 2.253 0 0 1 0-4.505m4.75 1.001a1.75 1.75 0 1 1 0 3.5a1.75 1.75 0 0 1 0-3.5" />
                            </svg>
                        </div>
                        <span
                            class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">Costumer</span>
                    </a>
                </li>
            @endcan

            @can('supplier.index')
                <li>
                    <a href="{{ route('supplier.index') }}"
                        class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M19.15 8a2 2 0 0 0-1.72-1H15V5a1 1 0 0 0-1-1H4a2 2 0 0 0-2 2v10a2 2 0 0 0 1 1.73a3.49 3.49 0 0 0 7 .27h3.1a3.48 3.48 0 0 0 6.9 0a2 2 0 0 0 2-2v-3a1.1 1.1 0 0 0-.14-.52zM15 9h2.43l1.8 3H15zM6.5 19A1.5 1.5 0 1 1 8 17.5A1.5 1.5 0 0 1 6.5 19m10 0a1.5 1.5 0 1 1 1.5-1.5a1.5 1.5 0 0 1-1.5 1.5" />
                            </svg>
                        </div>
                        <span
                            class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">Supplier</span>
                    </a>
                </li>
            @endcan

            @can('proses.index')
                <li>
                    <a href="{{ route('proses.index') }}"
                        class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M19.5 12c0-.23-.01-.45-.03-.68l1.86-1.41c.4-.3.51-.86.26-1.3l-1.87-3.23a.987.987 0 0 0-1.25-.42l-2.15.91c-.37-.26-.76-.49-1.17-.68l-.29-2.31c-.06-.5-.49-.88-.99-.88h-3.73c-.51 0-.94.38-1 .88l-.29 2.31c-.41.19-.8.42-1.17.68l-2.15-.91c-.46-.2-1-.02-1.25.42L2.41 8.62c-.25.44-.14.99.26 1.3l1.86 1.41a7.3 7.3 0 0 0 0 1.35l-1.86 1.41c-.4.3-.51.86-.26 1.3l1.87 3.23c.25.44.79.62 1.25.42l2.15-.91c.37.26.76.49 1.17.68l.29 2.31c.06.5.49.88.99.88h3.73c.5 0 .93-.38.99-.88l.29-2.31c.41-.19.8-.42 1.17-.68l2.15.91c.46.2 1 .02 1.25-.42l1.87-3.23c.25-.44.14-.99-.26-1.3l-1.86-1.41c.03-.23.04-.45.04-.68m-7.46 3.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5s3.5 1.57 3.5 3.5s-1.57 3.5-3.5 3.5" />
                            </svg>
                        </div>
                        <span
                            class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">Proses</span>
                    </a>
                </li>
            @endcan

            @can('perusahaan.index')
                <li>
                    <a href="{{ route('perusahaan.index') }}"
                        class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 24 24">
                                <path fill="currentColor" fill-rule="evenodd"
                                    d="M19.618 1H4.382L2 5.764V11h20V5.764zM3 17v-4.5h2V17h7v-4.5h2V21h5v-8.5h2V23H3z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span
                            class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">Perusahaan</span>
                    </a>
                </li>
            @endcan

            @can('user.index')
                <li>
                    <a href="{{ route('user.index') }}"
                        class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M12 14v8H4a8 8 0 0 1 8-8m0-1c-3.315 0-6-2.685-6-6s2.685-6 6-6s6 2.685 6 6s-2.685 6-6 6m9 4h1v5h-8v-5h1v-1a3 3 0 1 1 6 0zm-2 0v-1a1 1 0 1 0-2 0v1z" />
                            </svg>
                        </div>
                        <span
                            class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">User</span>
                    </a>
                </li>
            @endcan

            @can('logs.index')
                <li>
                    <a href="{{ route('logs.index') }}"
                        class="flex items-center p-3 text-gray-600 rounded-xl hover:bg-blue-50 hover:text-blue-600 group/item transition-all whitespace-nowrap">
                        <div class="min-w-[32px] flex justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-6 h-6 text-gray-400 group-hover/item:text-blue-600 transition-colors"
                                viewBox="0 0 640 512">
                                <path fill="currentColor"
                                    d="M224 0a128 128 0 1 1 0 256a128 128 0 1 1 0-256m-45.7 304h91.4c11.8 0 23.4 1.2 34.5 3.3c-2.1 18.5 7.4 35.6 21.8 44.8c-16.6 10.6-26.7 31.6-20 53.3c4 12.9 9.4 25.5 16.4 37.6s15.2 23.1 24.4 33c15.7 16.9 39.6 18.4 57.2 8.7v.9c0 9.2 2.7 18.5 7.9 26.3l-382.2.1C13.3 512 0 498.7 0 482.3C0 383.8 79.8 304 178.3 304M436 218.2c0-7 4.5-13.3 11.3-14.8c10.5-2.4 21.5-3.7 32.7-3.7s22.2 1.3 32.7 3.7c6.8 1.5 11.3 7.8 11.3 14.8v30.6c7.9 3.4 15.4 7.7 22.3 12.8l24.9-14.3c6.1-3.5 13.7-2.7 18.5 2.4c7.6 8.1 14.3 17.2 20.1 27.2s10.3 20.4 13.5 31c2.1 6.7-1.1 13.7-7.2 17.2l-25 14.4c.4 4 .7 8.1.7 12.3s-.2 8.2-.7 12.3l25 14.4c6.1 3.5 9.2 10.5 7.2 17.2c-3.3 10.6-7.8 21-13.5 31s-12.5 19.1-20.1 27.2c-4.8 5.1-12.5 5.9-18.5 2.4L546.3 442c-6.9 5.1-14.3 9.4-22.3 12.8v30.6c0 7-4.5 13.3-11.3 14.8c-10.5 2.4-21.5 3.7-32.7 3.7s-22.2-1.3-32.7-3.7c-6.8-1.5-11.3-7.8-11.3-14.8v-30.5c-8-3.4-15.6-7.7-22.5-12.9l-24.7 14.3c-6.1 3.5-13.7 2.7-18.5-2.4c-7.6-8.1-14.3-17.2-20.1-27.2s-10.3-20.4-13.5-31c-2.1-6.7 1.1-13.7 7.2-17.2l24.8-14.3c-.4-4.1-.7-8.2-.7-12.4s.2-8.3.7-12.4L343.8 325c-6.1-3.5-9.2-10.5-7.2-17.2c3.3-10.6 7.7-21 13.5-31s12.5-19.1 20.1-27.2c4.8-5.1 12.4-5.9 18.5-2.4l24.8 14.3c6.9-5.1 14.5-9.4 22.5-12.9v-30.5zm92.1 133.5a48.1 48.1 0 1 0-96.1 0a48.1 48.1 0 1 0 96.1 0" />
                            </svg>
                        </div>
                        <span
                            class="ms-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 font-semibold">Log
                            Aktifitas</span>
                    </a>
                </li>
            @endcan

        </ul>
    </div>
</aside>

<x-layout.user.app title="Daftar Supplier">

    <div class="space-y-2">
        <div class="flex flex-col gap-4 md:justify-between">

            <div class="flex w-full flex-col gap-3 md:w-auto md:flex-row md:items-center md:justify-between">
                <div class="flex gap-2">
                    @can('supplier.create')
                        {{-- Tombol Tambah --}}
                        <a type="button" href="{{ route('supplier.create') }}"
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-all shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10s10-4.477 10-10S17.523 2 12 2m5 11h-4v4h-2v-4H7v-2h4V7h2v4h4z" />
                            </svg>
                            <span class="hidden md:block md:ml-2">Tambah Supplier</span>
                        </a>
                    @endcan

                    @can('supplier.import')
                        <button onclick="openModal('importModal')"
                            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-green-600 transition-all shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 14h-3v3h-2v-3H8v-2h3V11h2v3h3v2zm-3-7V3.5L18.5 9H13z" />
                            </svg>
                            <span class="hidden md:block md:ml-2">Import Excel</span>
                        </button>
                    @endcan

                    {{-- Tombol Filter --}}
                    <button onclick="openModal('filterModal')"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M9 5a1 1 0 1 0 0 2a1 1 0 0 0 0-2M6.17 5a3.001 3.001 0 0 1 5.66 0H19a1 1 0 1 1 0 2h-7.17a3.001 3.001 0 0 1-5.66 0H5a1 1 0 0 1 0-2zM15 11a1 1 0 1 0 0 2a1 1 0 0 0 0-2m-2.83 0a3.001 3.001 0 0 1 5.66 0H19a1 1 0 1 1 0 2h-1.17a3.001 3.001 0 0 1-5.66 0H5a1 1 0 1 1 0-2zM9 17a1 1 0 1 0 0 2a1 1 0 0 0 0-2m-2.83 0a3.001 3.001 0 0 1 5.66 0H19a1 1 0 1 1 0 2h-7.17a3.001 3.001 0 0 1-5.66 0H5a1 1 0 1 1 0-2z" />
                        </svg>
                        <span class="hidden md:block md:ml-2">Filter Supplier</span>
                    </button>
                </div>

                {{-- Form Pencarian --}}
                <form action="{{ route('supplier.index') }}" method="GET" class="relative w-full md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full rounded-xl border border-gray-300 bg-gray-50 py-2 pl-10 pr-3 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-900/20 focus:border-blue-900 transition-all"
                        placeholder="CARI...">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="absolute left-3 top-5 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </form>
            </div>
        </div>

        {{-- TABLE DAN CARD --}}
        {{-- Pastikan komponen table menerima data $suppliers --}}
        <x-supplier.table :supplier="$supplier" />
        <x-supplier.card :supplier="$supplier" />

    </div>

    {{-- Filter Modal --}}
    <div id="filterModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/60" onclick="closeModal('filterModal')"></div>

        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl" id="modalContent">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h2 class="text-lg font-bold text-gray-800">Filter Lanjutan</h2>
                <button onclick="closeModal('filterModal')" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 15 15">
                        <path fill="currentColor"
                            d="M10.969 3.219a.574.574 0 1 1 .812.812L8.313 7.5l3.468 3.469l.074.09a.575.575 0 0 1-.796.796l-.09-.074L7.5 8.312l-3.469 3.47a.574.574 0 1 1-.812-.813L6.688 7.5l-3.47-3.469l-.073-.09a.575.575 0 0 1 .796-.797l.09.075L7.5 6.687z" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('supplier.index') }}" method="GET" class="p-6">
                <input type="hidden" name="search" value="{{ request('search') }}">

                <div class="space-y-5">
                    {{-- Filter Berdasarkan Perusahaan --}}
                    @if (auth()->user()->hasRole('Super Admin'))
                        <div>
                            <label for="id_perusahaan"
                                class="block text-sm font-semibold text-gray-700 mb-1">Perusahaan</label>
                            <select name="id_perusahaan" id="id_perusahaan"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-[#FFC829] outline-none">
                                <option value="">Semua Perusahaan</option>
                                @foreach ($perusahaan as $p)
                                    <option value="{{ $p->id }}"
                                        {{ request('id_perusahaan') == $p->id ? 'selected' : '' }}>
                                        {{ $p->nama_perusahaan }} ({{ $p->kota }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label for="filter_jenis_supplier" class="block text-sm font-semibold text-gray-700 mb-1">Jenis
                            Supplier</label>
                        <select name="jenis_supplier" id="filter_jenis_supplier"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-[#FFC829] focus:outline-none focus:ring-2 focus:ring-[#FFC829]/20">
                            <option value="">Semua Jenis</option>
                            <option value="Barang" {{ request('jenis_supplier') == 'Barang' ? 'selected' : '' }}>Barang
                            </option>
                            <option value="Bahan Baku"
                                {{ request('jenis_supplier') == 'Bahan Baku' ? 'selected' : '' }}>
                                Bahan Baku</option>
                        </select>
                    </div>

                    <div>
                        <label for="filter_status" class="block text-sm font-semibold text-gray-700 mb-1">Status
                            Supplier</label>
                        <select name="status" id="filter_status"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-[#FFC829] focus:outline-none focus:ring-2 focus:ring-[#FFC829]/20 outline-none">
                            <option value="aktif">Default (Aktif)</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif
                                (Tersedia)
                            </option>
                            <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>
                                Tidak
                                Aktif (Terhapus)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex items-center gap-3">
                    <a href="{{ route('supplier.index') }}"
                        class="flex-1 rounded-xl border border-gray-200 py-3 text-center text-sm font-bold text-gray-600 hover:bg-gray-50">Reset</a>
                    <button type="submit"
                        class="flex-1 rounded-xl bg-gray-600 py-3 text-sm font-bold text-white hover:bg-gray-800 transition-colors shadow-sm">Terapkan
                        Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Import Modal --}}
    <div id="importModal" class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 transition-opacity" onclick="closeModal('importModal')"></div>

            <div
                class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all">
                <div class="border-b border-gray-100 px-8 py-5 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Import Data Supplier</h3>
                        <p class="text-sm text-gray-500">Unggah data supplier secara massal.</p>
                    </div>
                    <button onclick="closeModal('importModal')"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-8">
                    {{-- Info Aturan --}}
                    <div class="mb-6 rounded-xl bg-gray-50 p-4 border border-gray-300">
                        <div class="flex">
                            <svg class="h-5 w-5 text-indigo-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <h4 class="text-sm font-bold text-indigo-500 uppercase tracking-wider">Aturan Penting:
                                </h4>
                                <ul class="mt-1 text-xs text-indigo-500 space-y-1 list-disc ml-4">
                                    <li><strong>Jenis Supplier</strong> harus diisi dengan tepat: <code class="bg-gray-200 px-1 rounded font-bold">Barang</code> atau <code class="bg-gray-200 px-1 rounded font-bold">Bahan Baku</code>.</li>
                                    <li><strong>Nama Supplier</strong> tidak boleh sama dengan data yang sudah ada di sistem.</li>
                                    <li><strong>Kode Supplier</strong> harus unik dan tidak boleh duplikat.</li>
                                    <li>
                                        <strong>Format Penulisan Kode Otomatis:</strong><br>
                                        - Jenis <code class="bg-gray-200 px-1 rounded font-bold">Bahan Baku</code> otomatis tersimpan dengan awalan <span class="font-bold text-red-600">SKG-</span><br>
                                        - Jenis <code class="bg-gray-200 px-1 rounded font-bold">Barang</code> otomatis tersimpan dengan awalan <span class="font-bold text-red-600">SUP-</span>
                                    </li>
                                    <li>Pastikan menggunakan format kolom sesuai <strong>Template</strong> yang
                                        tersedia.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('supplier.import') }}" method="POST" enctype="multipart/form-data"
                        class="form-prevent-multiple-submits space-y-6">
                        @csrf
                        <div class="form-prevent-multiple-submits relative group">
                            <input type="file" name="file" id="fileImport" class="hidden" required
                                onchange="document.getElementById('file-label').textContent = this.files[0].name">
                            <label for="fileImport" id="file-label"
                                class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50 group-hover:bg-gray-100 group-hover:border-indigo-400 transition-all">
                                <svg class="w-8 h-8 text-gray-400 group-hover:text-indigo-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span class="mt-2 text-sm text-gray-600 font-medium">Pilih file Excel (.xlsx)</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-4">
                            <a href="{{ route('supplier.download-template') }}"
                                class="flex-1 inline-flex items-center justify-center px-4 py-3 text-sm font-bold text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Template
                            </a>
                            <button type="submit"
                                class="btn-submit flex-[2] inline-flex items-center justify-center px-4 py-3 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">
                                <span class="btn-text">Unggah & Proses Data</span>
                                <svg class="btn-spinner hidden animate-spin ml-2 h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateFileName(input) {
            const fileName = input.files[0] ? input.files[0].name : 'Klik untuk pilih file Excel (.xlsx / .xls)';
            document.getElementById('file-name').textContent = fileName;
        }
    </script>

</x-layout.user.app>

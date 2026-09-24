<x-layout.user.app title="Edit Supplier">
    <div class="py-2">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Supplier</h1>
                <p class="text-sm text-gray-500">Ubah informasi untuk <strong>{{ $supplier->nama_supplier }}</strong></p>
            </div>
        </div>

        <form action="{{ route('supplier.update', $supplier->id) }}" method="POST" enctype="multipart/form-data"
            class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
            @csrf
            @method('PUT')

            <div class="p-6 md:p-8 space-y-8">
                <div class="space-y-6">
                    <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                        <div class="p-2 bg-yellow-50 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2.5 2.5 0 113.536 3.536L12 14.232l-4 1 1-4 9.732-9.732z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-800">Perbarui Data Supplier</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div class="space-y-1">
                            <label for="nama_supplier" class="block text-sm font-semibold text-gray-700">
                                Nama Supplier <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nama_supplier" name="nama_supplier"
                                value="{{ old('nama_supplier', $supplier->nama_supplier) }}" required
                                class="w-full rounded-xl border-gray-300 py-2.5 px-4 text-gray-900 shadow-sm focus:outline-none focus:border-blue-500 transition-colors border">
                        </div>

                        <div class="space-y-1">
                            @if (auth()->user()->hasRole('Super Admin'))
                                <label for="id_perusahaan" class="block text-sm font-semibold text-gray-700">Perusahaan
                                    <span class="text-red-500">*</span></label>
                                <select id="id_perusahaan" name="id_perusahaan" required
                                    class="w-full rounded-xl border-gray-300 py-2.5 px-4 shadow-sm focus:outline-none focus:border-[#FFC829] transition-colors border bg-white cursor-pointer">
                                    @foreach ($perusahaan as $p)
                                        <option value="{{ $p->id }}"
                                            {{ $supplier->id_perusahaan == $p->id ? 'selected' : '' }}>
                                            {{ $p->nama_perusahaan }} ({{ $p->kota }})</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="id_perusahaan" value="{{ auth()->user()->id_perusahaan }}">
                            @endif
                        </div>

                        <div class="space-y-1 md:col-span-2 lg:col-span-1">
                            <label for="jenis_supplier" class="block text-sm font-semibold text-gray-700">
                                Jenis Supplier <span class="text-red-500">*</span>
                            </label>
                            <select id="jenis_supplier" name="jenis_supplier" required
                                class="w-full rounded-xl border-gray-300 py-2.5 px-4 shadow-sm focus:outline-none focus:border-blue-500 transition-colors border bg-white cursor-pointer appearance-none">
                                <option value="Barang"
                                    {{ old('jenis_supplier', $supplier->jenis_supplier) == 'Barang' ? 'selected' : '' }}>
                                    Supplier Barang</option>
                                <option value="Bahan Baku"
                                    {{ old('jenis_supplier', $supplier->jenis_supplier) == 'Bahan Baku' ? 'selected' : '' }}>
                                    Supplier Bahan Baku</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label for="kode" class="block text-sm font-semibold text-gray-700">
                                Kode Supplier <span class="text-red-500">*</span>
                            </label>
                            <div class="relative flex">
                                <span class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-gray-300 bg-gray-50 text-gray-500 font-bold text-sm">
                                    <!-- Teks prefix otomatis mendeteksi kondisi awal data dari database -->
                                    <span id="prefix-text">{{ $supplier->jenis_supplier === 'Bahan Baku' ? 'SKG' : 'SUP' }}</span>
                                </span>
                                <input type="text" id="kode" name="kode" required placeholder="001"
                                    {{-- Menggunakan regex untuk membersihkan prefiks SUP- atau SKG- dari database --}}
                                    value="{{ old('kode', preg_replace('/^(SUP|SKG)-/i', '', $supplier->kode)) }}"
                                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                                    class="w-full rounded-r-xl border-gray-300 py-2.5 px-4 text-gray-900 shadow-sm focus:outline-none focus:border-[#FFC829] transition-colors border uppercase">
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 italic">
                                *Cukup masukkan kode/angka setelah <span id="format-text">{{ $supplier->jenis_supplier === 'Bahan Baku' ? 'SKG-KODE_SUPPLIER' : 'SUP-KODE_SUPPLIER' }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-gray-100">
                <p class="text-xs text-gray-500">Terakhir diperbarui:
                    {{ $supplier->updated_at->format('d M Y, H:i') }}</p>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('perusahaan.index') }}"
                        class="flex-1 sm:flex-none text-center px-6 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-800 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center px-8 py-2.5 text-sm font-bold text-white bg-yellow-600 hover:bg-yellow-700 rounded-xl transition-all active:scale-95">
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
    <script>
        document.getElementById('jenis_supplier').addEventListener('change', function() {
            let jenisSupplier = this.value;
            let prefixText = document.getElementById('prefix-text');
            let formatText = document.getElementById('format-text');
            
            // Menyesuaikan teks visual berdasarkan option value: 'Bahan Baku' atau 'Barang'
            if (jenisSupplier === 'Bahan Baku') {
                prefixText.innerText = 'SKG';
                formatText.innerText = 'SKG-KODE_SUPPLIER';
            } else {
                prefixText.innerText = 'SUP';
                formatText.innerText = 'SUP-KODE_SUPPLIER';
            }
        });
    </script>
</x-layout.user.app>

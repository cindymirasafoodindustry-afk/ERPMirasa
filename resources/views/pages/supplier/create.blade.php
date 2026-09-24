<x-layout.user.app title="Tambah Supplier">
    <div class="py-2">

        <form action="{{ route('supplier.store') }}" method="POST"
            class="form-prevent-multiple-submits bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transition-all hover:shadow-md">
            @csrf

            <div class="p-6 md:p-8 space-y-8">

                <div class="space-y-6">
                    <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                        <div class="p-2 bg-green-50 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M19.15 8a2 2 0 0 0-1.72-1H15V5a1 1 0 0 0-1-1H4a2 2 0 0 0-2 2v10a2 2 0 0 0 1 1.73a3.49 3.49 0 0 0 7 .27h3.1a3.48 3.48 0 0 0 6.9 0a2 2 0 0 0 2-2v-3a1.1 1.1 0 0 0-.14-.52zM15 9h2.43l1.8 3H15zM6.5 19A1.5 1.5 0 1 1 8 17.5A1.5 1.5 0 0 1 6.5 19m10 0a1.5 1.5 0 1 1 1.5-1.5a1.5 1.5 0 0 1-1.5 1.5" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-800">Data Suplier</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div class="space-y-1">
                            <label for="nama_supplier" class="block text-sm font-semibold text-gray-700">
                                Nama Supplier <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nama_supplier" name="nama_supplier" required
                                placeholder="Nama Supplier"
                                class="w-full rounded-xl border-gray-300 py-2.5 px-4 text-gray-900 shadow-sm focus:outline-none focus:border-[#FFC829] transition-colors border">
                        </div>

                        @if (auth()->user()->hasRole('Super Admin'))
                            <div class="space-y-1">
                                <label for="id_perusahaan" class="block text-sm font-semibold text-gray-700">Perusahaan
                                    <span class="text-red-500">*</span></label>
                                <select id="id_perusahaan" name="id_perusahaan" required
                                    class="w-full rounded-xl border-gray-300 py-2.5 px-4 shadow-sm focus:outline-none focus:border-[#FFC829] transition-colors border bg-white cursor-pointer">
                                    <option value="" disabled selected>-- Pilih Perusahaan --</option>
                                    @foreach ($perusahaan as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}
                                            ({{ $p->kota }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="space-y-1">
                                <label for="id_perusahaan" class="block text-sm font-semibold text-gray-700">Perusahaan
                                    <span class="text-red-500">*</span></label>
                                <input type="text"
                                    class="w-full rounded-xl border-gray-300 py-2.5 px-4 text-gray-900 shadow-sm focus:outline-none focus:border-[#FFC829] transition-colors border"
                                    disabled value="{{ auth()->user()->perusahaan->nama_perusahaan }}">
                                <input type="hidden" name="id_perusahaan" value="{{ auth()->user()->id_perusahaan }}">
                            </div>
                        @endif

                        <div class="space-y-1 md:col-span-2 lg:col-span-1">
                            <label for="jenis_supplier" class="block text-sm font-semibold text-gray-700">
                                Jenis Supplier <span class="text-red-500">*</span>
                            </label>
                            <select id="jenis_supplier" name="jenis_supplier" required
                                class="w-full rounded-xl border-gray-300 py-2.5 px-4 shadow-sm focus:outline-none focus:border-[#FFC829] transition-colors border bg-white appearance-none cursor-pointer">
                                <option value="" disabled selected>-- Pilih Jenis --</option>
                                <option value="Barang">Supplier Barang</option>
                                <option value="Bahan Baku">Supplier Bahan Baku</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label for="kode" class="block text-sm font-semibold text-gray-700">Kode Supplier <span class="text-red-500">*</span></label>
                            <div class="relative flex">
                                <span id="prefix-kode" class="inline-flex items-center px-4 rounded-l-xl border border-r-0 border-gray-300 bg-gray-50 text-gray-500 font-bold text-sm">
                                    <!-- Berikan tag span dengan id prefix-text di dalam sini -->
                                    <span id="prefix-text">SUP</span>
                                </span>
                                <input type="text" id="kode" name="kode" required placeholder="XXX" class="w-full rounded-r-xl border border-gray-300 py-2.5 px-4 shadow-sm focus:outline-none focus:border-[#FFC029] transition-colors">
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1 italic">
                                *Kode final akan tersimpan otomatis dengan format: <span id="format-text">SUP-KODE_SUPPLIER</span>
                            </p>
                        </div>

                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row justify-between gap-4 border-t border-gray-100">
                <p class="text-xs text-gray-500 italic">* Wajib diisi</p>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="{{ route('supplier.index') }}"
                        class="flex-1 sm:flex-none text-center border border-gray-200 px-6 py-2.5 text-sm font-semibold text-gray-600 rounded-xl hover:text-gray-800 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="btn-submit flex-1 sm:flex-none inline-flex items-center justify-center px-8 py-2.5 text-sm font-bold text-white bg-green-500 hover:bg-green-600 rounded-xl transition-all active:scale-95 shadow-sm disabled:opacity-70 disabled:cursor-not-allowed">
                        <span class="btn-text">Simpan</span>
                        <svg class="btn-spinner hidden animate-spin ml-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
    <script>
        document.getElementById('jenis_supplier').addEventListener('change', function() {
            let jenisSupplier = this.value.trim();
            let prefixText = document.getElementById('prefix-text');
            let formatText = document.getElementById('format-text');
            
            // Menggunakan pencarian kata 'Bahan Baku' agar lebih toleran terhadap variasi teks option
            if (jenisSupplier.includes('Bahan Baku')) {
                prefixText.innerText = 'SKG';
                if (formatText) formatText.innerText = 'SKG-KODE_SUPPLIER';
            } else {
                prefixText.innerText = 'SUP';
                if (formatText) formatText.innerText = 'SUP-KODE_SUPPLIER';
            }
        });

        // Jalankan fungsi sekali saat halaman pertama kali dimuat 
        // untuk mengantisipasi jika dropdown sudah otomatis terpilih oleh 'old' value Laravel
        window.addEventListener('DOMContentLoaded', function() {
            document.getElementById('jenis_supplier').dispatchEvent(new Event('change'));
        });
    </script>
</x-layout.user.app>

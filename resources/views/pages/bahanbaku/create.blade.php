<x-layout.beranda.app title="Tambah Bahan Baku">
    <div class="min-h-screen bg-gray-50/50 md:px-10 py-8">
        <div class="mx-auto flex flex-col pt-12" x-data="{
            jumlah: 0,
            harga: 0,
            diskon: 0,
            potongan: 0,
            selectedFoto: '',
            selectedKode: '-',
            selectedSatuan: '-',
            get total() {
                let subtotal = this.jumlah * this.harga;
                let potonganPersen = subtotal * (this.diskon / 100);
                let hasil = subtotal - potonganPersen - this.potongan;
                return hasil > 0 ? hasil : 0;
            },
            updateBarang(e) {
                const opt = e.target.options[e.target.selectedIndex];
                this.selectedKode = opt.dataset.kode || '-';
                this.selectedSatuan = opt.dataset.satuan || '-';
                this.selectedFoto = opt.dataset.foto ? '/storage/' + opt.dataset.foto : '';
            }
        }">

            {{-- Header Section --}}
            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4 text-left">
                <div>
                    <a href="{{ route('bahan-baku.index') }}"
                        class="group inline-flex items-center text-blue-600 hover:text-blue-700 text-sm font-semibold transition-all mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-1 transform group-hover:-translate-x-1 transition-transform" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </a>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Barang Masuk: <span class="text-purple-600">Bahan Baku</span>
                    </h1>
                </div>
            </div>

            {{-- Container Utama: Hapus overflow-hidden agar dropdown bisa 'keluar' dari box --}}
            <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100">
                <form action="{{ route('bahan-baku.store') }}" method="POST"
                    class="form-prevent-multiple-submits p-6 md:p-10">
                    @csrf
                    <input type="hidden" name="id_perusahaan" value="{{ auth()->user()->id_perusahaan }}">

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

                        {{-- Kiri: Pemilihan Barang & Supplier --}}
                        <div class="lg:col-span-5 space-y-6 relative z-30" x-data="{
                            // Data Barang
                            barangOpen: false,
                            barangSearch: '',
                            selectedBarangId: '',
                            selectedBarangName: '',
                            barangs: {{ $barang->map(
                                    fn($b) => [
                                        'id' => $b->id,
                                        'name' => $b->nama_barang,
                                        'kode' => $b->kode,
                                        'satuan' => $b->satuan,
                                        'foto' => $b->foto ? asset('storage/' . $b->foto) : '',
                                    ],
                                )->toJson() }},
                        
                            // Data Supplier
                            supplierOpen: false,
                            supplierSearch: '',
                            selectedSupplierId: '',
                            selectedSupplierName: '',
                            suppliers: {{ $supplier->map(fn($s) => ['id' => $s->id, 'name' => $s->nama_supplier])->toJson() }},
                        
                            // Filter Logic
                            get filteredBarangs() {
                                return this.barangs.filter(b => b.name.toLowerCase().includes(this.barangSearch.toLowerCase()))
                            },
                            get filteredSuppliers() {
                                return this.suppliers.filter(s => s.name.toLowerCase().includes(this.supplierSearch.toLowerCase()))
                            },
                        
                            // Selection Logic
                            selectBarang(b) {
                                this.selectedBarangId = b.id;
                                this.selectedBarangName = b.name;
                                this.selectedKode = b.kode;
                                this.selectedSatuan = b.satuan;
                                this.selectedFoto = b.foto;
                                this.barangSearch = '';
                                this.barangOpen = false;
                            },
                            selectSupplier(s) {
                                this.selectedSupplierId = s.id;
                                this.selectedSupplierName = s.name;
                                this.supplierSearch = '';
                                this.supplierOpen = false;
                            }
                        }">
                            <div
                                class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-6 border border-blue-100/50 text-left">
                                <label
                                    class="block text-sm font-bold text-blue-900 mb-4 uppercase tracking-wider">Identitas
                                    Logistik</label>

                                {{-- 1. Searchable Select: Supplier (Z-Index tertinggi di grup kiri) --}}
                                <div class="mb-4 space-y-2 relative z-[50]">
                                    <label class="text-[10px] font-bold text-blue-400 uppercase ml-1">Supplier /
                                        Vendor</label>
                                    <div class="relative">
                                        <input type="hidden" name="id_supplier" :value="selectedSupplierId">
                                        <button type="button" @click="supplierOpen = !supplierOpen; barangOpen = false"
                                            class="w-full px-5 py-3.5 bg-white border-0 rounded-2xl shadow-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-blue-500 text-left flex justify-between items-center transition-all">
                                            <span
                                                :class="selectedSupplierName ? 'text-gray-700 font-medium' : 'text-gray-400'"
                                                x-text="selectedSupplierName || '-- Cari & Pilih Supplier --'"></span>
                                            <svg class="w-4 h-4 text-gray-400 transition-transform"
                                                :class="supplierOpen ? 'rotate-180' : ''" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <div x-show="supplierOpen" @click.away="supplierOpen = false"
                                            class="absolute z-[100] w-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
                                            x-cloak x-transition>
                                            <div class="p-2 border-b border-gray-50 bg-gray-50/50">
                                                <input type="text" x-model="supplierSearch"
                                                    placeholder="Ketik nama supplier..."
                                                    class="w-full px-4 py-2 text-sm bg-white border border-gray-100 rounded-xl focus:ring-0 outline-none">
                                            </div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar">
                                                <template x-for="s in filteredSuppliers" :key="s.id">
                                                    <button type="button" @click="selectSupplier(s)"
                                                        class="w-full px-5 py-3 text-left text-sm hover:bg-blue-50 hover:text-blue-600 transition-colors flex items-center justify-between group">
                                                        <span x-text="s.name"></span>
                                                        <svg x-show="selectedSupplierId == s.id"
                                                            class="w-4 h-4 text-blue-500" fill="currentColor"
                                                            viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. Image Preview --}}
                                <div class="relative group mb-4 text-center z-10">
                                    <div
                                        class="aspect-square w-full max-w-[150px] mx-auto bg-white rounded-2xl flex items-center justify-center border-2 border-dashed border-blue-200 overflow-hidden shadow-inner transition-all">
                                        <template x-if="!selectedFoto">
                                            <div class="text-center p-4">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-10 w-10 mx-auto text-blue-200 mb-2" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        </template>
                                        <template x-if="selectedFoto">
                                            <img :src="selectedFoto"
                                                class="w-full h-full object-cover rounded-2xl animate-fade-in">
                                        </template>
                                    </div>
                                </div>

                                {{-- 3. Searchable Select: Nama Barang (Z-Index menengah) --}}
                                <div class="space-y-4 relative z-[40]">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-bold text-blue-400 uppercase ml-1">Pilih Bahan
                                            Baku</label>
                                        <div class="relative">
                                            <input type="hidden" name="id_barang" :value="selectedBarangId">
                                            <button type="button"
                                                @click="barangOpen = !barangOpen; supplierOpen = false"
                                                class="w-full px-5 py-3.5 bg-white border-0 rounded-2xl shadow-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-blue-500 text-left flex justify-between items-center transition-all">
                                                <span
                                                    :class="selectedBarangName ? 'text-gray-700 font-medium' : 'text-gray-400'"
                                                    x-text="selectedBarangName || '-- Cari & Pilih Nama Barang --'"></span>
                                                <svg class="w-4 h-4 text-gray-400 transition-transform"
                                                    :class="barangOpen ? 'rotate-180' : ''" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>

                                            <div x-show="barangOpen" @click.away="barangOpen = false"
                                                class="absolute z-[100] w-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
                                                x-cloak x-transition>
                                                <div class="p-2 border-b border-gray-50 bg-gray-50/50">
                                                    <input type="text" x-model="barangSearch"
                                                        placeholder="Ketik nama barang..."
                                                        class="w-full px-4 py-2 text-sm bg-white border border-gray-100 rounded-xl focus:ring-0 outline-none">
                                                </div>
                                                <div class="max-h-48 overflow-y-auto custom-scrollbar text-sm">
                                                    <template x-for="b in filteredBarangs" :key="b.id">
                                                        <button type="button" @click="selectBarang(b)"
                                                            class="w-full px-5 py-3 text-left hover:bg-blue-50 hover:text-blue-600 transition-colors flex flex-col gap-0.5">
                                                            <span
                                                                class="font-bold text-gray-700 group-hover:text-blue-600"
                                                                x-text="b.name"></span>
                                                            <span class="text-[10px] text-gray-400 font-mono"
                                                                x-text="b.kode"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 4. Info Kode & Satuan (Z-Index rendah) --}}
                                    <div class="grid grid-cols-2 gap-3 relative z-10">
                                        <div
                                            class="bg-white/80 backdrop-blur-sm p-3 rounded-xl border border-blue-100 shadow-sm">
                                            <p class="text-[10px] text-blue-400 font-bold uppercase tracking-tighter">
                                                SKU / Kode</p>
                                            <p x-text="selectedKode || '-'"
                                                class="font-mono font-bold text-blue-900 mt-1 text-sm">-</p>
                                        </div>
                                        <div
                                            class="bg-white/80 backdrop-blur-sm p-3 rounded-xl border border-blue-100 shadow-sm">
                                            <p class="text-[10px] text-blue-400 font-bold uppercase tracking-tighter">
                                                Satuan</p>
                                            <p x-text="selectedSatuan || '-'"
                                                class="font-bold text-blue-900 mt-1 text-sm">-</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kanan: Detail Input (Relative z-10 agar di bawah dropdown kolom kiri) --}}
                        <div class="lg:col-span-7 space-y-8 text-left relative z-10">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                                    <span
                                        class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center mr-3 shadow-lg shadow-blue-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path
                                                d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                            <path fill-rule="evenodd"
                                                d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    Informasi Kedatangan Bahan Baku
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="md:col-span-2 space-y-1.5 text-left">
                                        <label class="text-xs font-bold text-gray-500 uppercase ml-1">Tanggal
                                            Masuk</label>
                                        <input type="date" name="tanggal_masuk" value="{{ date('Y-m-d') }}"
                                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>

                                    <div class="space-y-1.5 text-left">
                                        <label class="text-xs font-bold text-blue-600 uppercase ml-1">Jumlah
                                            Diterima</label>
                                        <input type="number" step="any" name="jumlah_diterima"
                                            x-model.number="jumlah"
                                            class="w-full px-4 py-3 bg-blue-50/30 border border-blue-100 rounded-2xl focus:ring-2 focus:ring-blue-500 font-bold outline-none">
                                    </div>

                                    <div class="space-y-1.5 text-left">
                                        <label class="text-xs font-bold text-gray-500 uppercase ml-1">Harga Per
                                            Satuan</label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                                            <input type="number" step="any" name="harga"
                                                x-model.number="harga"
                                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 font-bold outline-none">
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 text-left">
                                        <label class="text-xs font-bold text-rose-500 uppercase ml-1">Potongan Diskon
                                            (%)</label>
                                        <div class="relative">

                                            <input type="number" step="any" name="diskon"
                                                x-model.number="diskon"
                                                class="w-full pl-4 pr-12 py-3 bg-rose-50/30 border border-rose-100 rounded-2xl focus:ring-2 focus:ring-rose-500 font-bold outline-none text-rose-700"
                                                placeholder="0" min="0" max="100">
                                            <span
                                                class="absolute right-4 top-1/2 -translate-y-1/2 text-rose-400 font-bold">%</span>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 text-left">
                                        <label class="text-xs font-bold text-gray-500 uppercase ml-1">Potongan Harga (Nominal)</label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                                            <input type="number" step="any" name="potongan_harga" 
                                                x-model.number="potongan"
                                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 font-bold outline-none"
                                                placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Total Section --}}
                            <div class="bg-green-100 rounded-3xl p-6 text-green-700 shadow-xl border border-green-200">
                                <div
                                    class="flex justify-between items-center mb-1 text-green-600 text-xs font-bold uppercase">
                                    <span>Total Biaya Bahan</span>
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="text-3xl font-black tracking-tight flex items-center gap-2">
                                    <span>Rp</span>
                                    <span x-text="new Intl.NumberFormat('id-ID').format(total)">0</span>
                                </div>
                                <input type="hidden" name="total_harga" :value="total()">
                                <input type="hidden" name="stok" value="0">
                            </div>

                            <button type="submit"
                                class="btn-submit w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-green-200 transition-all hover:scale-[1.01] active:scale-95 flex items-center justify-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="btn-text">Catat Pembelian Bahan</span>
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
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout.beranda.app>

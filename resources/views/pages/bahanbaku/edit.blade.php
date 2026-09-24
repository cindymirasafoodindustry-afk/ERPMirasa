<x-layout.beranda.app title="Edit Bahan Baku">
    <div class="min-h-screen bg-gray-50/50 md:px-10 py-8">
        <div class="mx-auto flex flex-col pt-12" x-data="{
            {{-- Data Awal --}}
            jumlah: {{ $bahanBaku->jumlah_diterima }},
                harga: {{ $bahanBaku->harga }},
                diskon: {{ $bahanBaku->diskon ?? 0 }},
                potongan: {{ $bahanBaku->potongan_harga ?? 0 }},
                selectedFoto: '{{ $bahanBaku->Inventory->Barang->foto ? asset('storage/' . $bahanBaku->Inventory->Barang->foto) : '' }}',
                selectedKode: '{{ $bahanBaku->Inventory->Barang->kode }}',
                selectedSatuan: '{{ $bahanBaku->Inventory->Barang->satuan }}',
        
                {{-- Data Barang & Supplier --}}
            barangOpen: false,
                barangSearch: '',
                selectedBarangId: '{{ $bahanBaku->Inventory->id_barang }}',
                selectedBarangName: '{{ $bahanBaku->Inventory->Barang->nama_barang }}',
                barangs: {{ $barang->map(
                        fn($b) => [
                            'id' => $b->id,
                            'name' => $b->nama_barang,
                            'kode' => $b->kode,
                            'satuan' => $b->satuan,
                            'foto' => $b->foto ? asset('storage/' . $b->foto) : '',
                        ],
                    )->toJson() }},
        
                supplierOpen: false,
                supplierSearch: '',
                selectedSupplierId: '{{ $bahanBaku->id_supplier }}',
                selectedSupplierName: '{{ $bahanBaku->Supplier->nama_supplier }}',
                suppliers: {{ $supplier->map(fn($s) => ['id' => $s->id, 'name' => $s->nama_supplier])->toJson() }},
        
                get total() {
                    let subtotal = this.jumlah * this.harga;
                    let potonganPersen = subtotal * (this.diskon / 100);
                    let hasil = subtotal - potonganPersen - this.potongan;
                    return hasil > 0 ? hasil : 0;
                },
        
                get filteredBarangs() {
                    return this.barangs.filter(b =>
                        b.name.toLowerCase().includes(this.barangSearch.toLowerCase()) ||
                        b.kode.toLowerCase().includes(this.barangSearch.toLowerCase())
                    )
                },
                get filteredSuppliers() {
                    return this.suppliers.filter(s => s.name.toLowerCase().includes(this.supplierSearch.toLowerCase()))
                },
        
                selectBarang(b) {
                    this.selectedBarangId = b.id;
                    this.selectedBarangName = b.name;
                    this.selectedKode = b.kode;
                    this.selectedSatuan = b.satuan;
                    this.selectedFoto = b.foto;
                    this.barangOpen = false;
                },
                selectSupplier(s) {
                    this.selectedSupplierId = s.id;
                    this.selectedSupplierName = s.name;
                    this.supplierOpen = false;
                }
        }">

            {{-- Header Section --}}
            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <a href="{{ route('bahan-baku.index') }}"
                        class="group inline-flex items-center text-blue-600 hover:text-blue-700 text-sm font-semibold transition-all mb-3">
                        <svg class="h-4 w-4 mr-1 transform group-hover:-translate-x-1 transition-transform" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </a>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        Edit Barang: <span class="text-purple-600">Bahan Baku</span>
                    </h1>

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl border border-gray-100">
                <form action="{{ route('bahan-baku.update', $bahanBaku->id) }}" method="POST" class="p-6 md:p-10">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        {{-- Kiri: Pemilihan Barang & Supplier --}}
                        <div class="lg:col-span-5 space-y-6 relative z-30">
                            <div
                                class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-6 border border-blue-100/50">
                                <label
                                    class="block text-sm font-bold text-blue-900 mb-4 uppercase tracking-wider">Identitas
                                    Logistik</label>

                                {{-- Supplier --}}
                                <div class="mb-4 space-y-2 relative z-[50]">
                                    <label class="text-[10px] font-bold text-blue-400 uppercase ml-1">Supplier /
                                        Vendor</label>
                                    <div class="relative">
                                        <input type="hidden" name="id_supplier" :value="selectedSupplierId">
                                        <button type="button" @click="supplierOpen = !supplierOpen; barangOpen = false"
                                            class="w-full px-5 py-3.5 bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 text-left flex justify-between items-center transition-all">
                                            <span class="text-gray-700 font-medium"
                                                x-text="selectedSupplierName"></span>
                                            <svg class="w-4 h-4 text-gray-400 transition-transform"
                                                :class="supplierOpen ? 'rotate-180' : ''" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <div x-show="supplierOpen" @click.away="supplierOpen = false"
                                            class="absolute z-[100] w-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
                                            x-cloak x-transition>
                                            <div class="p-2 border-b bg-gray-50/50">
                                                <input type="text" x-model="supplierSearch"
                                                    placeholder="Cari supplier..."
                                                    class="w-full px-4 py-2 text-sm rounded-xl outline-none border border-gray-100 font-normal">
                                            </div>
                                            <div class="max-h-48 overflow-y-auto">
                                                <template x-for="s in filteredSuppliers" :key="s.id">
                                                    <button type="button" @click="selectSupplier(s)"
                                                        class="w-full px-5 py-3 text-left text-sm hover:bg-blue-50"
                                                        x-text="s.name"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Image Preview --}}
                                <div class="relative group mb-4 text-center">
                                    <div
                                        class="aspect-square w-full max-w-[150px] mx-auto bg-white rounded-2xl flex items-center justify-center border-2 border-dashed border-blue-200 overflow-hidden shadow-inner">
                                        <template x-if="selectedFoto">
                                            <img :src="selectedFoto" class="w-full h-full object-cover rounded-2xl">
                                        </template>
                                        <template x-if="!selectedFoto">
                                            <svg class="h-10 w-10 text-blue-200" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </template>
                                    </div>
                                </div>

                                {{-- Nama Barang --}}
                                <div class="space-y-4 relative z-[40]">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-bold text-blue-400 uppercase ml-1">Pilih Nama
                                            Barang</label>
                                        <div class="relative">
                                            <input type="hidden" name="id_barang" :value="selectedBarangId">
                                            <button type="button"
                                                @click="barangOpen = !barangOpen; supplierOpen = false"
                                                class="w-full px-5 py-3.5 bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 text-left flex justify-between items-center transition-all">
                                                <span class="text-gray-700 font-medium"
                                                    x-text="selectedBarangName"></span>
                                                <svg class="w-4 h-4 text-gray-400 transition-transform"
                                                    :class="barangOpen ? 'rotate-180' : ''" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div x-show="barangOpen" @click.away="barangOpen = false"
                                                class="absolute z-[100] w-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
                                                x-cloak x-transition>
                                                <div class="p-2 border-b bg-gray-50/50">
                                                    <input type="text" x-model="barangSearch"
                                                        placeholder="Cari barang atau kode..."
                                                        class="w-full px-4 py-2 text-sm rounded-xl outline-none border border-gray-100 font-normal">
                                                </div>
                                                <div class="max-h-48 overflow-y-auto text-sm">
                                                    <template x-for="b in filteredBarangs" :key="b.id">
                                                        <button type="button" @click="selectBarang(b)"
                                                            class="w-full px-5 py-3 text-left hover:bg-blue-50 flex flex-col">
                                                            <span class="font-bold text-gray-700"
                                                                x-text="b.name"></span>
                                                            <span class="text-[10px] text-gray-400 font-mono"
                                                                x-text="b.kode"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Display Kode & Satuan --}}
                                    <div class="grid grid-cols-2 gap-3">
                                        <div
                                            class="bg-white/80 backdrop-blur-sm p-3 rounded-xl border border-blue-100 shadow-sm">
                                            <p class="text-[10px] text-blue-400 font-bold uppercase">SKU / Kode</p>
                                            <p x-text="selectedKode || '-'"
                                                class="font-mono font-bold text-blue-900 mt-1 text-sm"></p>
                                        </div>
                                        <div
                                            class="bg-white/80 backdrop-blur-sm p-3 rounded-xl border border-blue-100 shadow-sm">
                                            <p class="text-[10px] text-blue-400 font-bold uppercase">Satuan</p>
                                            <p x-text="selectedSatuan || '-'"
                                                class="font-bold text-blue-900 mt-1 text-sm"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kanan: Input Detail --}}
                        <div class="lg:col-span-7 space-y-8 text-left">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                                    <span
                                        class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center mr-3 shadow-lg">
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
                                    <div class="md:col-span-2 space-y-1.5">
                                        <label class="text-xs font-bold text-gray-500 uppercase">Tanggal Masuk</label>
                                        <input type="date" name="tanggal_masuk"
                                            value="{{ $bahanBaku->tanggal_masuk }}"
                                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-blue-600 uppercase">Jumlah
                                            Diterima</label>
                                        <input type="number" step="any" name="jumlah_diterima"
                                            x-model.number="jumlah"
                                            class="w-full px-4 py-3 bg-blue-50/30 border border-blue-100 rounded-2xl font-bold outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-gray-500 uppercase">Harga Per
                                            Satuan</label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                                            <input type="number" step="any" name="harga"
                                                x-model.number="harga"
                                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl font-bold outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>

                                    {{-- Input Diskon Persen --}}
                                    <div class="space-y-1.5 text-left">
                                        <label class="text-xs font-bold text-rose-500 uppercase ml-1">Potongan Diskon
                                            (%)</label>
                                        <div class="relative">
                                            {{-- Ganti span Rp menjadi % di sebelah kanan agar lebih intuitif --}}
                                            <input type="number" step="any" name="diskon"
                                                x-model.number="diskon"
                                                class="w-full pl-4 pr-12 py-3 bg-rose-50/30 border border-rose-100 rounded-2xl focus:ring-2 focus:ring-rose-500 font-bold outline-none text-rose-700"
                                                placeholder="0" min="0" max="100">
                                            <span
                                                class="absolute right-4 top-1/2 -translate-y-1/2 text-rose-400 font-bold">%</span>
                                        </div>
                                    </div>

                                    {{-- Input Potongan Harga Rupiah --}}
                                    <div class="space-y-1.5 text-left">
                                        <label class="text-xs font-bold text-gray-500 uppercase ml-1">Potongan Harga (Nominal)</label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                                            <input type="number" step="any" name="potongan_harga" x-model.number="potongan"
                                                class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 font-bold outline-none"
                                                placeholder="0">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-green-100 rounded-3xl p-6 text-green-700 shadow-xl border border-green-200">
                                <div
                                    class="flex justify-between items-center mb-1 text-green-600 text-xs font-bold uppercase">
                                    <span>Total Biaya Baru</span>
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                                    </svg>
                                </div>
                                <div class="text-3xl font-black tracking-tight flex items-center gap-2">
                                    <span>Rp</span>
                                    <span x-text="new Intl.NumberFormat('id-ID').format(total)">0</span>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-4 rounded-2xl font-black uppercase tracking-widest transition-all shadow-lg flex items-center justify-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                Perbarui Data
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout.beranda.app>

<x-layout.beranda.app title="Ubah Pemesanan Barang">
    <div class="pt-24 p-6">
        <!-- Header Halaman -->
        <div class="mb-6">
            <a href="{{ route('pemesanan-barang.index') }}" class="text-sm text-gray-500 hover:text-orange-600 transition-colors flex items-center gap-1 mb-2">
                <svg xmlns="http://w3.org" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Daftar Pemesanan
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Ubah Pemesanan Barang</h1>
            <p class="text-sm text-gray-500">Perbarui rincian data pemesanan logistik perusahaan</p>
        </div>

        <!-- Form Edit Data (Integrated with Alpine.js) -->
        <form action="{{ route('pemesanan-barang.update', $pemesanan->id) }}" method="POST" class="form-tambah-pemesanan-barang"
            x-data="{
                supplier_id: '{{ $pemesanan->supplier_id }}',
                kode_supplier: '{{ $pemesanan->supplier->kode ?? '-' }}',
                jenis_barang_id: '{{ $pemesanan->jenis_barang_id }}',
                barang_id: '{{ $pemesanan->barang_id }}',
                kode_barang: '{{ $pemesanan->barang->kode ?? '-' }}',
                jumlah: {{ $pemesanan->jumlah ?? 0 }},
                harga: {{ $pemesanan->harga_barang ?? 0 }},
                diskon: {{ $pemesanan->diskon ?? 0 }},
                potongan: {{ $pemesanan->potongan_harga ?? 0 }},
                pajak_ppn: '{{ $pemesanan->pajak_ppn ?? '0' }}',

                semuaBarang: [
                    @foreach($barangs as $barang)
                        {
                            id: '{{ $barang->id }}',
                            jenis_id: '{{ $barang->id_jenis }}', // SINKRON DATABASE: id_jenis
                            nama: '{{ $barang->nama_barang }}',
                            kode: '{{ $barang->kode }}'
                        },
                    @endforeach
                ],

                get barangTerfilter() {
                    if (!this.jenis_barang_id) return [];
                    return this.semuaBarang.filter(b => {
                        return String(b.jenis_id).trim() === String(this.jenis_barang_id).trim();
                    });
                },

                // Rumus Hitung Real-time Otomatis
                get subtotal() {
                    let totalKotor = this.jumlah * this.harga;
                    let nilaiDiskonPersen = totalKotor * (this.diskon / 100);
                    let totalBersih = totalKotor - nilaiDiskonPersen - this.potongan;
                    return totalBersih > 0 ? totalBersih : 0;
                },
                get nilaiPajak() {
                    if (this.pajak_ppn === '11') {
                        return Math.round(this.subtotal * 0.11);
                    }
                    return 0;
                },
                get totalAkhir() {
                    return this.subtotal + this.nilaiPajak;
                },

                // Handler perubahan dropdown data-attribute
                updateSupplier(e) {
                    let opt = e.target.options[e.target.selectedIndex];
                    this.kode_supplier = opt.getAttribute('data-kode') || '-';
                },
                updateBarang(e) {
                    let opt = e.target.options[e.target.selectedIndex];
                    this.kode_barang = opt.getAttribute('data-kode') || '-';
                }
            }">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- SISI KIRI: IDENTITAS LOGISTIK -->
                <div class="lg:col-span-6 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-5">
                    
                    <!-- Input Kode Pesanan (PO) -->
                    <div class="mb-4">
                        <label for="kode_pesanan" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Kode Pesanan / No. PO</label>
                        <input type="text" 
                               name="kode_pesanan" 
                               id="kode_pesanan" 
                               value="{{ $pemesanan->kode_pesanan }}" 
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 font-bold outline-none uppercase text-sm" 
                               placeholder="CONTOH: PO-2026-001" required>
                    </div>

                    <!-- Input Supplier -->
                    <div class="mb-4">
                        <label for="supplier_id" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Supplier / Vendor</label>
                        <select id="supplier_id" name="supplier_id" x-model="supplier_id" @change="updateSupplier($event)" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 transition-all outline-none text-sm font-bold">
                            <option value="" disabled>Pilih Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" data-kode="{{ $supplier->kode }}">
                                    {{ $supplier->nama_supplier }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tampil Kode Supplier (Readonly) -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Kode Supplier</label>
                        <input type="text" :value="kode_supplier" readonly placeholder="Kode Supplier Otomatis" class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-gray-500 outline-none font-mono text-sm cursor-not-allowed font-bold">
                    </div>

                    <!-- Input Jenis Barang -->
                    <div class="mb-4">
                        <label for="jenis_barang_id" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Jenis Barang</label>
                        <select id="jenis_barang_id" name="jenis_barang_id" x-model="jenis_barang_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 transition-all outline-none text-sm font-bold">
                            <option value="" disabled>Pilih Jenis Barang</option>
                            @foreach($jenis_barangs as $jb)
                                <option value="{{ $jb->id }}">{{ $jb->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input Nama Barang -->
                    <div class="mb-4">
                        <label for="barang_id" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Pilih Nama Barang</label>
                        <select name="barang_id" id="barang_id" x-model="barang_id" @change="updateBarang" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 font-bold text-sm outline-none" required>
                            <option value="" disabled>-- Cari & Pilih Nama Barang --</option>
                            <template x-for="barang in barangTerfilter" :key="barang.id">
                                <option :value="barang.id" x-text="barang.nama" :selected="barang.id == barang_id"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Tampil Kode Barang (Readonly) -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Kode Barang</label>
                        <input type="text" :value="kode_barang" readonly placeholder="Kode Barang Otomatis" class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-gray-500 outline-none font-mono text-sm cursor-not-allowed font-bold">
                    </div>
                </div>

                <!-- SISI KANAN: RINCIAN BIAYA & KUANTITAS -->
                <div class="lg:col-span-6 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-5">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Tanggal Pesan -->
                        <div>
                            <label for="tanggal_pesan" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Tanggal Pesan</label>
                            <input type="date" id="tanggal_pesan" name="tanggal_pesan" value="{{ $pemesanan->tanggal_pesan }}" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 transition-all outline-none text-sm font-bold">
                        </div>

                        <!-- Jumlah / Qty -->
                        <div>
                            <label for="jumlah" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Jumlah Pesanan (QTY)</label>
                            <input type="number" id="jumlah" name="jumlah" x-model.number="jumlah" min="1" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 transition-all outline-none text-sm font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Satuan -->
                        <div>
                            <label for="satuan" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Satuan</label>
                            <select id="satuan" name="satuan" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 transition-all outline-none text-sm font-bold">
                                <option value="Kg" {{ $pemesanan->satuan == 'Kg' ? 'selected' : '' }}>Kg</option>
                                <option value="Gram" {{ $pemesanan->satuan == 'Gram' ? 'selected' : '' }}>Gram</option>
                                <option value="Liter" {{ $pemesanan->satuan == 'Liter' ? 'selected' : '' }}>Liter</option>
                                <option value="Pcs" {{ $pemesanan->satuan == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                                <option value="Bks" {{ $pemesanan->satuan == 'Bks' ? 'selected' : '' }}>Bks</option>
                                <option value="Lembar" {{ $pemesanan->satuan == 'Lembar' ? 'selected' : '' }}>Lembar</option>
                                <option value="Roll" {{ $pemesanan->satuan == 'Roll' ? 'selected' : '' }}>Roll</option>
                                <option value="Karton" {{ $pemesanan->satuan == 'Karton' ? 'selected' : '' }}>Karton</option>
                                <option value="" disabled selected>-- Pilih Satuan --</option>
                            </select>
                        </div>
                        <!-- Harga Satuan -->
                        <div>
                            <label for="harga_barang" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Harga Satuan (Rp)</label>
                            <input type="number" id="harga_barang" name="harga_barang" x-model.number="harga" min="0" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 transition-all outline-none font-bold text-sm">
                        </div>
                    </div>

                    <!-- Input Diskon % dan Potongan Harga -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="diskon" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Diskon (%)</label>
                            <div class="relative">
                                <input type="number" id="diskon" name="diskon" x-model.number="diskon" min="0" max="100" class="w-full pl-4 pr-12 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 transition-all outline-none font-bold text-sm" placeholder="0">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">%</span>
                            </div>
                        </div>
                        
                        <div>
                            <label for="potongan_harga" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Potongan Harga (Nominal/Rp)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                                <input type="number" id="potongan_harga" name="potongan_harga" x-model.number="potongan" min="0" class="w-full pl-12 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 transition-all outline-none font-bold text-sm" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Radio PPN -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Pajak PPN</label>
                        <div class="flex items-center gap-6 mt-1">
                            <label class="inline-flex items-center cursor-pointer text-sm font-bold text-gray-700">
                                <input type="radio" name="pajak_ppn" value="11" x-model="pajak_ppn" class="w-4 h-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                <span class="ml-2">PPN 11%</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer text-sm font-bold text-gray-700">
                                <input type="radio" name="pajak_ppn" value="0" x-model="pajak_ppn" class="w-4 h-4 text-orange-600 border-gray-300 focus:ring-orange-500">
                                <span class="ml-2">Non PPN</span>
                            </label>
                        </div>
                    </div>

                    <!-- Kotak Total Pembayaran -->
                    <div class="p-4 bg-orange-50 border border-orange-100 rounded-2xl flex justify-between items-center mt-4">
                        <span class="text-sm font-semibold text-orange-600">Total Pembayaran:</span>
                        <span class="text-xl font-black text-orange-600">
                            Rp <span x-text="totalAkhir.toLocaleString('id-ID')">0</span>
                        </span>
                        <input type="hidden" name="total_pembayaran" :value="totalAkhir">
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold shadow-md transition-colors text-sm uppercase tracking-wider">
                            Simpan Perubahan Data
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</x-layout.beranda.app>

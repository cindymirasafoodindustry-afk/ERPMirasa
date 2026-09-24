<x-layout.beranda.app title="Form Tambah Pemesanan Barang">
    <div class="pt-24 p-6">
        <!-- Header Halaman -->
        <div class="mb-6">
            <a href="{{ route('pemesanan-barang.index') }}" class="text-sm text-gray-500 hover:text-orange-600 transition-colors flex items-center gap-1 mb-2">
                <svg xmlns="http://w3.org" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Pemesanan Barang: Baru</h1>
        </div>

        <!-- Form Utama (Integrated with Alpine.js) -->
        <form action="{{ route('pemesanan-barang.store') }}" method="POST"
            x-data="{
                supplier_id: '',
                kode_supplier: '-',
                jenis_barang_id: '',
                barang_id: '',
                kode_barang: '-',
                jumlah: 0,
                harga: 0,
                diskon: 0,
                potongan: 0,
                pajak_ppn: '0',

                // 1. Array Penampung Objek Data dengan Format Lurus & Aman
                semuaBarang: [
                    @foreach($barangs as $barang)
                        {
                            id: '{{ $barang->id }}',
                            jenis_id: '{{ $barang->id_jenis }}', 
                            nama: '{{ $barang->nama_barang }}',
                            kode: '{{ $barang->kode }}'
                        },
                    @endforeach
                ],

                // 2. Fungsi Filter Tipe Data Kebal Eror (Angka/String Dikonversi Otomatis)
                get barangTerfilter() {
                    if (!this.jenis_barang_id) return [];
                    return this.semuaBarang.filter(b => {
                        return String(b.jenis_id).trim() === String(this.jenis_barang_id).trim();
                    });
                },

                // 3. Sistem Hitung Otomatis Real-time
                get subtotal() {
                    let totalKotor = this.jumlah * this.harga;
                    let nilaiDiskonPersen = totalKotor * (this.diskon / 100);
                    let totalBersih = totalKotor - nilaiDiskonPersen - this.potongan;
                    return totalBersih > 0 ? totalBersih : 0;
                },
                get nilaiPajak() {
                    return this.pajak_ppn === '11' ? Math.round(this.subtotal * 0.11) : 0;
                },
                get totalAkhir() {
                    return this.subtotal + this.nilaiPajak;
                },

                // Handler Interaksi Dropdown Kode
                updateSupplier(e) {
                    let opt = e.target.options[e.target.selectedIndex];
                    this.kode_supplier = opt.getAttribute('data-id-supplier') || '-';
                },
                updateBarang() {
                    let barangTerpilih = this.semuaBarang.find(b => String(b.id) === String(this.barang_id));
                    this.kode_barang = barangTerpilih ? barangTerpilih.kode : '-';
                }
            }">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- SISI KIRI: IDENTITAS PEMESANAN -->
                <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-blue-100 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 text-blue-700 font-bold text-xs uppercase tracking-wide mb-2">
                        <svg xmlns="http://w3.org" class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Identitas Pemesanan
                    </div>

                    <!-- Input Kode Pesanan -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Kode Pesanan / No. PO</label>
                        <input type="text" name="kode_pesanan" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 font-bold text-sm uppercase" placeholder="CONTOH: PO-2026-001" required>
                    </div>

                    <!-- Input Supplier -->
                    <div class="mb-4">
                        <label for="supplier_id" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Supplier / Vendor</label>
                        <select id="supplier_id" name="supplier_id" x-model="supplier_id" @change="updateSupplier($event)" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 text-sm font-bold outline-none">
                            <option value="" disabled selected>-- Pilih Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" data-id-supplier="{{ $supplier->kode }}">{{ $supplier->nama_supplier }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kode Supplier (Readonly) -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Kode Supplier</label>
                        <input type="text" :value="kode_supplier" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-gray-500 outline-none font-mono text-sm cursor-not-allowed font-bold">
                    </div>

                    <!-- Input Jenis Barang (Mengirimkan ID angka ke Laravel) -->
                    <div class="mb-4">
                        <label for="jenis_barang_id" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Pilih Jenis Barang</label>
                        <select name="jenis_barang_id" id="jenis_barang_id" x-model="jenis_barang_id" @change="barang_id = ''; kode_barang = '-'" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 font-bold text-sm outline-none" required>
                            <option value="" disabled selected>-- Pilih Jenis Barang --</option>
                            @foreach($jenis_barangs as $jb)
                                <option value="{{ $jb->id }}">{{ $jb->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input Nama Barang (Dynamic Filter Terintegrasi) -->
                    <div class="mb-4">
                        <label for="barang_id" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Pilih Nama Barang</label>
                        <select name="barang_id" id="barang_id" x-model="barang_id" @change="updateBarang" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 font-bold text-sm outline-none" required>
                            <option value="" disabled selected>-- Cari & Pilih Nama Barang --</option>
                            <template x-for="barang in barangTerfilter" :key="barang.id">
                                <option :value="barang.id" x-text="barang.nama"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Kode Barang (Readonly) -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Kode Barang</label>
                        <input type="text" :value="kode_barang" readonly class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-gray-500 outline-none font-mono text-sm cursor-not-allowed font-bold">
                    </div>
                </div>

                <!-- SISI KANAN: RINCIAN BIAYA & KUANTITAS -->
                <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm space-y-5">
                    <div class="flex items-center gap-2 text-orange-600 font-bold text-xs uppercase tracking-wide mb-2">
                        <svg xmlns="http://w3.org" class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Informasi Pemesanan & Rincian Biaya
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_pesan" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Tanggal Pesan</label>
                            <input type="date" id="tanggal_pesan" name="tanggal_pesan" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 text-sm font-bold">
                        </div>
                        <div>
                            <label for="jumlah" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Jumlah Pesanan (QTY)</label>
                            <input type="number" id="jumlah" name="jumlah" x-model.number="jumlah" min="0" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 text-sm font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Satuan -->
                        <div>
                            <label for="satuan" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Satuan</label>
                            <select id="satuan" name="satuan" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 text-sm font-bold outline-none">
                                <option value="" disabled selected>-- Pilih Satuan --</option>
                                <option value="Kg">Kg</option>
                                <option value="Gram">Gram</option>
                                <option value="Bks">Bks</option>
                                <option value="Pcs">Pcs</option>
                                <option value="Liter">Liter</option>
                                <option value="Lembar">Lembar</option>
                                <option value="Roll">Roll</option>
                                <option value="Karton">Karton</option>
                            </select>
                        </div>
                        
                        <!-- Harga Barang Satuan -->
                        <div>
                            <label for="harga_barang" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Harga Barang Satuan (Rp)</label>
                            <input type="number" id="harga_barang" name="harga_barang" x-model.number="harga" min="0" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 text-sm font-bold">
                        </div>
                    </div>

                    <!-- Input Diskon % dan Potongan Harga -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="diskon" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Diskon (%)</label>
                            <div class="relative">
                                <input type="number" id="diskon" name="diskon" x-model.number="diskon" min="0" max="100" class="w-full pl-4 pr-12 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 text-sm font-bold outline-none" placeholder="0">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">%</span>
                            </div>
                        </div>
                        <div>
                            <label for="potongan_harga" class="block text-xs font-semibold text-gray-500 uppercase mb-2">Potongan Harga (Nominal/Rp)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                                <input type="number" id="potongan_harga" name="potongan_harga" x-model.number="potongan" min="0" class="w-full pl-12 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 text-sm font-bold outline-none" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Opsi Pajak PPN -->
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Opsi Pajak PPN</label>
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

                    <!-- Kotak Total Pembayaran Utama -->
                    <div class="p-4 bg-orange-50/50 border border-orange-100 rounded-2xl flex flex-col mt-4">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">TOTAL PEMBAYARAN</span>
                        <span class="text-2xl font-black text-orange-600 mt-1">
                            Rp <span x-text="totalAkhir.toLocaleString('id-ID')">0</span>
                        </span>
                        <input type="hidden" name="total_pembayaran" :value="totalAkhir">
                    </div>

                    <!-- Tombol Simpan Form -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold shadow-md transition-colors text-sm uppercase tracking-wider flex items-center justify-center gap-1">
                            <svg xmlns="http://w3.org" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Pemesanan
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</x-layout.beranda.app>

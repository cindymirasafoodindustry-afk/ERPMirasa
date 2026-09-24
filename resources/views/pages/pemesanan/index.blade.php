<x-layout.beranda.app title="Proses Pemesanan Barang">

    <div class="pt-24 p-6">
        <!-- Notifikasi Pesan Sukses Berhasil Simpan/Update/Hapus -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-semibold flex items-center gap-2">
                <span>✓</span> {{ session('success') }}
            </div>
        @endif

        <!-- Header Utama Halaman -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Proses Pemesanan Barang</h1>
                <p class="text-sm text-gray-500">Kelola dan pantau daftar pemesanan barang perusahaan</p>
            </div>
            
            <!-- Tombol Tambah Pesanan (Selalu Muncul) -->
            <a href="{{ route('pemesanan-barang.create') }}" class="px-4 py-2 bg-orange-600 text-white rounded-xl font-semibold text-sm shadow-md hover:bg-orange-700 transition-colors flex items-center gap-2">
                <svg xmlns="http://w3.org" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Pesanan
            </a>
        </div>

        <!-- Kotak Pencarian Dinamis -->
        <div class="mb-5 max-w-md">
            <form action="{{ route('pemesanan-barang.index') }}" method="GET" class="flex gap-2">
                <!-- Mempertahankan tab yang sedang aktif saat melakukan pencarian -->
                <input type="hidden" name="tab" value="{{ $tabAktif }}">
                
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Cari No. PO / Supplier / Nama Barang..." 
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 font-bold text-xs outline-none shadow-2xs">
                </div>
                
                <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-950 text-white rounded-xl font-bold text-xs shadow-2xs transition-colors">
                    Cari
                </button>
                
                @if(request('search'))
                    <a href="?tab={{ $tabAktif }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-xl font-bold text-xs flex items-center justify-center transition-colors" title="Clear Search">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Navigasi Sub-Tab Status Alur Kerja (Workflow Multi-Sheet) -->
        <div class="flex border-b border-gray-200 mb-6 bg-white px-4 py-2 rounded-xl shadow-sm gap-2">
            <a href="?tab=po" class="px-4 py-2 text-sm font-bold border-b-2 transition-all {{ $tabAktif == 'po' ? 'border-orange-600 text-orange-600 font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Daftar Pesanan (PO)
            </a>
            <a href="?tab=pengiriman" class="px-4 py-2 text-sm font-bold border-b-2 transition-all {{ $tabAktif == 'pengiriman' ? 'border-orange-600 text-orange-600 font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Pengiriman dari Supplier
            </a>
            <a href="?tab=diterima" class="px-4 py-2 text-sm font-bold border-b-2 transition-all {{ $tabAktif == 'diterima' ? 'border-orange-600 text-orange-600 font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Diterima / Selesai
            </a>
            <a href="?tab=pembayaran" class="px-4 py-2 text-sm font-bold border-b-2 transition-all {{ $tabAktif == 'pembayaran' ? 'border-orange-600 text-orange-600 font-extrabold' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                Pembayaran
            </a>
        </div>

        <!-- Container Utama Tabel Data -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    
                    <!-- ==================== HEADER TABEL (THEAD) ==================== -->
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200">
                        @if($tabAktif == 'Pembayaran')
                            <!-- Header Khusus Tab Pembayaran -->
                            <tr>
                                <tr>
                                    <th class="px-6 py-4">No</th>
                                    <th class="px-6 py-4">Tanggal Pesanan</th> <!-- TAMBAHAN BARU -->
                                    <th class="px-6 py-4">Nama & Kode Barang</th>
                                    <th class="px-6 py-4">Supplier</th>
                                    <th class="px-6 py-4">Jumlah Pesanan (QTY)</th>
                                    <th class="px-6 py-4 text-blue-600 bg-blue-50 text-center">Total Bayar</th>
                                    <th class="px-6 py-4 text-red-600 bg-red-50 text-center">Debet</th>
                                    <th class="px-6 py-4 text-green-600 bg-green-50 text-center">Kredit</th>
                                    <th class="px-6 py-4 text-orange-600 bg-orange-50 text-center">Tempo Pembayaran </th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                </tr>
                            </tr>
                        @elseif($tabAktif == 'pengiriman')
                            <!-- Kolom untuk memantau Batch Pengiriman yang sedang berjalan mencicil -->
                            <tr class="hidden">
                                <th colspan="10"></th>
                            </tr>
                        @elseif($tabAktif == 'diterima')
                            <!-- Kolom untuk melihat barang yang SUDAH SELESAI masuk gudang 100% -->
                            <tr class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-700">
                                <th class="px-4 py-3.5 text-center">No</th>
                                    <th class="px-4 py-3.5">Tanggal Pesanan</th>
                                    <th class="px-4 py-3.5">Tanggal Pengiriman</th>
                                    <th class="px-4 py-3.5">Kode Pesanan</th>
                                    <th class="px-4 py-3.5">Nama & Kode Barang</th>
                                    <th class="px-4 py-3.5">Supplier & Kode</th>
                                    <th class="px-4 py-3.5 text-right">QTY (Lolos QC)</th>
                                    <th class="px-4 py-3.5">Harga & Keterangan Biaya</th>
                                    <th class="px-4 py-3.5 text-center">Aksi Logistik & Keuangan</th>
                            </tr>
                        @else
                            <!-- Header Asli Tab Pemesanan Barang Master -->
                            <tr>
                                <th class="px-6 py-4">No</th>
                                <th class="px-6 py-4">Tanggal Pemesanan</th>
                                <th class="px-6 py-4">Kode Pesanan</th>
                                <th class="px-6 py-4">Nama & Kode Barang</th>
                                <th class="px-6 py-4">Supplier & Kode</th>
                                <th class="px-6 py-4">Jumlah Pesanan (QTY)</th>
                                <th class="px-6 py-4">Total Bayar</th>
                                <th class="px-6 py-4">Status Pesanan</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        @endif
                    </thead>
                    
                    <!-- ==================== ISI DATA TABEL (TBODY) ==================== -->
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($pemesanans as $index => $data)
                            
                            @if($tabAktif == 'Pembayaran')
                                <!-- ROW DATA UNTUK TAB PEMBAYARAN -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- 1. No -->
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $index + 1 }}</td>
                                    
                                    <!-- 1.B KOLOM BARU: Tanggal Pemesanan -->
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-medium">
                                        {{ \Carbon\Carbon::parse($data->tanggal_pesan)->format('d/m/Y') }}
                                    </td>

                                    <!-- 2. TAMBAHKAN KOLOM KODE PESANAN BARU DISINI -->
                                    <td class="px-6 py-4 font-mono font-bold text-orange-600 uppercase text-xs tracking-wide">
                                        {{ $data->kode_pesanan ?? 'NO-PO' }}
                                    </td>

                                    <!-- 2. Nama & Kode Barang -->
                                    <td class="px-6 py-4 text-gray-700">
                                        <div class="font-semibold">{{ $data->barang->nama_barang ?? '-' }}</div>
                                        <div class="text-xs text-blue-600 font-mono mt-0.5">[{{ $data->barang->kode ?? '-' }}]</div>
                                    </td>
                                    
                                    <!-- 3. Supplier -->
                                    <td class="px-6 py-4 text-gray-800 font-medium">
                                        {{ $data->supplier->nama_supplier ?? '-' }}
                                    </td>
                                    
                                    <!-- 4. Jumlah Pesanan & Qty -->
                                    <td class="px-6 py-4 text-gray-700">
                                        <div class="font-bold text-gray-900">
                                            {{ number_format($data->jumlah, 0, ',', '.') }} <span class="text-xs text-gray-400 font-normal">{{ $data->satuan }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            @Rp {{ number_format($data->harga_barang, 0, ',', '.') }}
                                        </div>
                                    </td>

                                    <!-- 4.B KOLOM BARU: Total Bayar (Tagihan Awal Asli) -->
                                    <td class="px-6 py-4 font-bold text-center bg-blue-50/20 text-gray-900">
                                        Rp {{ number_format($data->total_pembayaran, 0, ',', '.') }}
                                        <div class="text-[10px] text-gray-400 font-normal mt-0.5">Tagihan Utama</div>
                                    </td>

                                     <!-- 5. Debet (Hutang / Sisa yang Belum Dibayarkan) -->
                                    <td class="px-6 py-4 font-bold text-center bg-red-50/30 text-red-600">
                                        @php
                                            // RUMUS FIX: Jika sudah_dibayar bernilai 0, sisa hutang adalah total pembayaran penuh.
                                            // Jika sudah dicicil, maka sisa hutang diambil langsung dari data sisa_hutang di database.
                                            if ($data->sudah_dibayar > 0) {
                                                $sisaHutang = $data->sisa_hutang;
                                            } else {
                                                $sisaHutang = $data->total_pembayaran;
                                            }
                                        @endphp
                                        
                                        @if($sisaHutang > 0)
                                            Rp {{ number_format($sisaHutang, 0, ',', '.') }}
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>

                                    <!-- 6. Kredit (Total Uang yang Sudah Berhasil Dibayarkan) -->
                                    <td class="px-6 py-4 font-bold text-green-600 bg-green-50/30 text-center">
                                        @php
                                            $nominalKredit = $data->sudah_dibayar;
                                            if ($data->sudah_dibayar == 0 && $data->sisa_hutang == 0 && $data->status == 'Pembayaran') {
                                                $nominalKredit = $data->total_pembayaran - $sisaHutang;
                                            }
                                        @endphp

                                        @if($nominalKredit > 0)
                                            Rp {{ number_format($nominalKredit, 0, ',', '.') }}
                                            @if($sisaHutang == 0)
                                                <div class="text-[10px] text-green-600 font-bold mt-0.5">✓ LUNAS</div>
                                            @else
                                                <!-- MODIFIKASI: Menampilkan jumlah berapa kali cicilan diinput -->
                                                <div class="text-[10px] text-amber-600 font-bold mt-0.5 uppercase tracking-wide">
                                                    DICICIL ({{ $data->total_kali_cicil ?? 1 }}x)
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400 font-normal text-xs">Rp 0</span>
                                        @endif
                                    </td>

                                    <!-- 7. KOLOM BARU: Sisa Tempo (Durasi Jatuh Tempo Terpisah) -->
                                    <td class="px-6 py-4 font-bold text-center bg-orange-50/20">
                                        @php
                                            $tanggalPesan = \Carbon\Carbon::parse($data->tanggal_pesan)->startOfDay();
                                            $hariIni = \Carbon\Carbon::now()->startOfDay();
                                            $hariBerjalan = $tanggalPesan->diffInDays($hariIni, false); 
                                            $sisaHari = 30 - (int)$hariBerjalan;
                                        @endphp

                                        @if($sisaHutang > 0)
                                            @if($sisaHari <= 5 && $sisaHari >= 0)
                                                <div class="px-2 py-1 text-[11px] font-black rounded-lg bg-orange-100 text-orange-700 animate-pulse inline-block">
                                                    🚨 {{ $sisaHari }} Hari Lagi!
                                                </div>
                                            @elseif($sisaHari < 0)
                                                <div class="px-2 py-1 text-[11px] font-black rounded-lg bg-red-600 text-white inline-block">
                                                    ⚠️ LEWAT {{ abs($sisaHari) }} HARI!
                                                </div>
                                            @else
                                                <div class="text-sm text-gray-700 font-semibold">{{ $sisaHari }} Hari</div>
                                                <div class="text-[10px] text-gray-400 font-normal mt-0.5">Tenor 30 hari</div>
                                            @endif
                                        @else
                                            <span class="px-2 py-0.5 text-[10px] font-bold bg-green-100 text-green-700 rounded-md">Lunas</span>
                                        @endif
                                    </td>
                                    
                                    <!-- 9. Aksi Khusus Pembayaran (Modal Trigger & Faktur) -->
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center items-center gap-2">
                                            @if($data->sisa_hutang > 0 || $data->sudah_dibayar == 0)
                                                <button onclick="bukaModalBayar('{{ $data->id }}', '{{ $data->total_pembayaran }}', '{{ $data->sudah_dibayar ?? 0 }}')" class="px-3 py-1.5 bg-blue-600 text-white rounded-xl text-xs font-bold shadow hover:bg-blue-700 transition-colors cursor-pointer">
                                                    Bayar
                                                </button>
                                            @else
                                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold">Lunas</span>
                                            @endif

                                            <a href="{{ route('pemesanan-barang.invoice', $data->id) }}" target="_blank" class="p-1.5 text-amber-500 hover:bg-amber-50 rounded-xl transition-colors" title="Cetak Faktur">
                                                <svg xmlns="http://w3.org" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @elseif($tabAktif == 'pengiriman')
                                <!-- ================================================================== -->
                                <!-- PANEL MONITORING KHUSUS TAB PENGIRIMAN BATCH (KIRI-KANAN) -->
                                <!-- ================================================================== -->
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td colspan="10" class="p-0 border-none bg-gray-50/30">
                                        
                                        @forelse($pemesanans as $data)
                                            <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm mb-6 text-left m-4">
                                                <!-- Header Identitas PO Utama -->
                                                <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-4">
                                                    <div>
                                                        <span class="text-[10px] font-bold text-gray-400 font-mono uppercase tracking-wider">KODE PESANAN</span>
                                                        <h3 class="text-base font-black text-orange-600 uppercase tracking-wide leading-tight">{{ $data->kode_pesanan ?? 'NO-PO' }}</h3>
                                                        
                                                        <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                            <!-- Nama Barang -->
                                                            <span class="text-xs font-extrabold text-gray-800 bg-gray-100 px-2 py-0.5 rounded-md border border-gray-200">
                                                                📦 {{ $data->barang->nama_barang ?? '-' }}
                                                            </span>
                                                            <span class="text-[10px] font-mono font-bold text-blue-500 mr-2">
                                                                [{{ $data->barang->kode ?? '-' }}]
                                                            </span>
                                                            
                                                            <!-- TAMBAHAN BARU: TANGGAL PEMESANAN -->
                                                            <span class="text-[11px] font-bold text-gray-500 bg-orange-50 text-orange-700 px-2 py-0.5 rounded-md border border-orange-100 flex items-center gap-1">
                                                                📅 Tgl PO: {{ \Carbon\Carbon::parse($data->tanggal_pesan)->format('d/m/Y') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="text-[10px] font-bold text-gray-400 font-mono uppercase tracking-wider">SUPPLIER / VENDOR</span>
                                                        <p class="text-sm font-bold text-gray-800">{{ $data->supplier->nama_supplier ?? '-' }}</p>
                                                        <span class="text-[10px] font-mono font-bold text-gray-400">{{ $data->supplier->kode ?? '-' }}</span>
                                                    </div>
                                                </div>

                                                <!-- Tiga Kotak Ringkasan Akumulasi Logistik -->
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-gray-50 p-4 rounded-xl mb-6 text-xs font-bold font-mono">
                                                    <div class="p-3 bg-white rounded-lg border border-gray-100 shadow-2xs">
                                                        <span class="text-gray-400 uppercase">TOTAL DIPESAN:</span>
                                                        <p class="text-sm font-black text-gray-900 mt-1">{{ number_format($data->jumlah) }} {{ $data->satuan }}</p>
                                                    </div>
                                                    <div class="p-3 bg-white rounded-lg border border-gray-100 shadow-2xs">
                                                        <span class="text-green-500 uppercase">SUDAH DITERIMA (QC):</span>
                                                        <p class="text-sm font-black text-green-600 mt-1">{{ number_format($data->batches->sum('jumlah_diterima_qc')) }} {{ $data->satuan }}</p>
                                                    </div>
                                                    <div class="p-3 bg-white rounded-lg border border-gray-100 shadow-2xs">
                                                        <span class="text-red-500 uppercase">SISA KEKURANGAN PO:</span>
                                                        <p class="text-sm font-black text-red-600 mt-1">{{ number_format(max(0, $data->jumlah - $data->batches->sum('jumlah_diterima_qc'))) }} {{ $data->satuan }}</p>
                                                    </div>
                                                </div>

                                                <!-- Grid Pecah Kiri-Kanan -->
                                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                                                    
                                                    <!-- SISI KIRI (8 Kolom): TABEL HISTORI LOG GELOMBANG -->
                                                    <div class="lg:col-span-8 border border-gray-100 rounded-xl overflow-hidden shadow-2xs bg-white">
                                                        <table class="w-full text-xs text-left border-collapse">
                                                            <thead>
                                                                <tr class="bg-gray-100 text-gray-600 font-bold border-b border-gray-200 uppercase tracking-wider text-[10px]">
                                                                    <th class="px-4 py-3">Nama Batch</th>
                                                                    <th class="px-4 py-3">Tanggal Datang</th>
                                                                    <th class="px-4 py-3 text-right">Dikirim (Supir)</th>
                                                                    <th class="px-4 py-3 text-right">Lolos QC</th>
                                                                    <th class="px-4 py-3 text-right">Reject</th>
                                                                    <th class="px-4 py-3 text-center">Aksi</th> <!-- TAMBAH JUDUL KOLOM -->
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-100 font-medium text-gray-700 bg-white">
                                                                @forelse($data->batches as $batch)
                                                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                                                        <td class="px-4 py-2.5 font-bold text-orange-600">{{ $batch->nama_batch }}</td>
                                                                        <td class="px-4 py-2.5 text-gray-500">{{ \Carbon\Carbon::parse($batch->tanggal_datang)->format('d/m/Y') }}</td>
                                                                        <td class="px-4 py-2.5 text-right font-mono font-bold text-blue-600">{{ number_format($batch->jumlah_dikirim) }}</td>
                                                                        <td class="px-4 py-2.5 text-right font-mono font-bold text-green-600">{{ number_format($batch->jumlah_diterima_qc) }}</td>
                                                                        <td class="px-4 py-2.5 text-right font-mono font-bold text-red-600">{{ number_format($batch->jumlah_reject) }}</td>
                                                                        
                                                                        <!-- KOLOM AKSI: TOMBOL DETAIL CATATAN LOGISTIK (INTEGRASI ALPINE.JS) -->
                                                                        <td class="px-4 py-2.5 text-center relative">
                                                                            <div x-data="{ laciOpen: false }" class="inline-block">
                                                                                <!-- Tombol Pemicu Laci Kecil -->
                                                                                <button type="button" @click="laciOpen = !laciOpen" @click.away="laciOpen = false" class="p-1.5 bg-gray-50 hover:bg-orange-50 text-gray-400 hover:text-orange-600 rounded-lg transition-all flex items-center justify-center border border-gray-100 shadow-3xs" title="Klik untuk lihat/tutup catatan logistik">
                                                                                    <svg xmlns="http://w3.org" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                                    </svg>
                                                                                </button>

                                                                                <!-- TAMPILAN LACI KECIL POPOVER (MUNCUL PRESISI DI DEKAT TOMBOL) -->
                                                                                <div x-show="laciOpen" 
                                                                                    x-transition:enter="transition ease-out duration-100"
                                                                                    x-transition:enter-start="opacity-0 scale-95"
                                                                                    x-transition:enter-end="opacity-100 scale-100"
                                                                                    x-transition:leave="transition ease-in duration-75"
                                                                                    x-transition:leave-start="opacity-100 scale-100"
                                                                                    x-transition:leave-end="opacity-0 scale-95"
                                                                                    class="absolute right-full mr-2 top-1/2 -translate-y-1/2 z-40 w-52 p-3 bg-white border border-gray-200 rounded-xl shadow-lg text-left" 
                                                                                    x-cloak style="display: none;">
                                                                                    
                                                                                    <!-- Segitiga Kecil Penunjuk Laci -->
                                                                                    <div class="absolute top-1/2 -right-1.5 -translate-y-1/2 w-3 h-3 bg-white border-t border-r border-gray-200 rotate-45"></div>
                                                                                    
                                                                                    <!-- Konten Utama Laci -->
                                                                                    <div class="relative z-10">
                                                                                        <div class="text-[9px] font-black text-orange-600 uppercase tracking-wider mb-1">
                                                                                            📄 Catatan {{ $batch->nama_batch }}
                                                                                        </div>
                                                                                        <p class="text-[11px] text-gray-600 font-medium leading-relaxed whitespace-pre-line bg-gray-50/50 p-2 rounded-lg border border-gray-100">
                                                                                            {{ $batch->catatan ?? 'Tidak ada catatan logistik tertulis untuk gelombang ini.' }}
                                                                                        </p>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="6" class="px-4 py-8 text-center text-gray-400 font-normal italic bg-white">
                                                                            Belum ada pengiriman gelombang/batch yang tercatat untuk PO ini.
                                                                        </td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <!-- SISI KANAN (4 Kolom): FORM INPUT BATCH BARU -->
                                                    <div class="lg:col-span-4 bg-gray-50/50 border border-gray-100 rounded-xl p-4 shadow-2xs">
                                                        <h4 class="text-xs font-black text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-1">
                                                            <svg xmlns="http://w3.org" class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                            Input Gelombang Baru
                                                        </h4>
                                                        
                                                        <form action="{{ route('pemesanan-barang.simpan-batch', $data->id) }}" method="POST" class="space-y-3"
                                                            x-data="{ dikirim: 0, lolos: 0, get reject() { return Math.max(0, this.dikirim - this.lolos); } }">
                                                            @csrf
                                                            
                                                            <div class="grid grid-cols-2 gap-2 text-[11px]">
                                                                <div>
                                                                    <label class="block font-bold text-gray-500 uppercase mb-1">Nama Batch</label>
                                                                    <input type="text" name="nama_batch" value="Batch {{ $data->batches->count() + 1 }}" readonly class="w-full px-3 py-1.5 bg-gray-100 border rounded-lg font-bold outline-none text-gray-500 cursor-not-allowed text-xs">
                                                                </div>
                                                                <div>
                                                                    <label class="block font-bold text-gray-500 uppercase mb-1">Tgl Datang</label>
                                                                    <input type="date" name="tanggal_datang" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-1.5 border rounded-lg font-bold outline-none focus:border-orange-500 text-xs">
                                                                </div>
                                                            </div>

                                                            <div class="grid grid-cols-3 gap-2 text-[11px]">
                                                                <div>
                                                                    <label class="block font-bold text-gray-500 uppercase mb-1">Dikirim</label>
                                                                    <input type="number" name="jumlah_dikirim" x-model.number="dikirim" min="0" required class="w-full px-2 py-1.5 border rounded-lg font-bold text-blue-600 outline-none focus:border-blue-500 text-xs">
                                                                </div>
                                                                <div>
                                                                    <label class="block font-bold text-gray-500 uppercase mb-1">Lolos QC</label>
                                                                    <input type="number" name="jumlah_diterima_qc" x-model.number="lolos" min="0" :max="dikirim" required class="w-full px-2 py-1.5 border rounded-lg font-bold text-green-600 outline-none focus:border-green-500 text-xs">
                                                                </div>
                                                                <div>
                                                                    <label class="block font-bold text-gray-500 uppercase mb-1">Reject</label>
                                                                    <input type="number" name="jumlah_reject" :value="reject" readonly class="w-full px-2 py-1.5 bg-gray-100 border rounded-lg font-bold text-red-600 cursor-not-allowed outline-none text-xs">
                                                                </div>
                                                            </div>

                                                            <div class="text-[11px]">
                                                                <label class="block font-bold text-gray-500 uppercase mb-1">Catatan Logistik</label>
                                                                <textarea name="catatan" rows="2" class="w-full px-3 py-1.5 border rounded-lg text-xs outline-none focus:border-orange-500" placeholder="Contoh: Kiriman supir aman..."></textarea>
                                                            </div>

                                                            <button type="submit" class="w-full py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-xs font-black shadow-xs uppercase tracking-wider transition-colors">
                                                                Simpan Kiriman Batch
                                                            </button>
                                                        </form>
                                                    </div>

                                                </div>
                                            </div>
                                        @empty
                                            <div class="p-12 text-center text-gray-400 font-medium bg-white rounded-2xl border border-gray-100 m-4">
                                                Tidak ada pesanan barang yang sedang dalam proses pengiriman batch hari ini.
                                            </div>
                                        @endforelse

                                    </td>
                                </tr>

                                @elseif($tabAktif == 'diterima')
                                    <!-- ================================================================== -->
                                    <!-- BARIS DATA JURNAL KEDATANGAN LOGISTIK PER BATCH (REAL-TIME) -->
                                    <!-- ================================================================== -->
                                    @forelse($pemesanans as $batch)
                                        @php 
                                            // Ambil data PO induknya agar mempermudah pemanggilan variabel rincian nominal biaya
                                            $poInduk = $batch->pemesananBarang; 
                                        @endphp
                                        @if($poInduk)
                                            <tr class="hover:bg-gray-50/50 transition-colors text-xs">
                                                <!-- 1. Nomor Urut -->
                                                <td class="px-4 py-3 text-center font-medium text-gray-900">{{ $loop->iteration }}</td>
                                                
                                                <!-- 2. Tanggal Pesanan -->
                                                <td class="px-4 py-3 text-gray-600 font-medium font-mono text-[11px]">
                                                    {{ $poInduk ? \Carbon\Carbon::parse($poInduk->tanggal_pesan)->format('d/m/Y') : '-' }}
                                                </td>
                                                
                                                <!-- 3. Tanggal Pengiriman / Kedatangan Batch -->
                                                <td class="px-4 py-3 font-semibold text-gray-700 bg-gray-50/40 font-mono text-[11px]">
                                                    📅 {{ \Carbon\Carbon::parse($batch->tanggal_datang)->format('d/m/Y') }}
                                                    <span class="block text-[9px] text-orange-600 font-black tracking-wider uppercase mt-0.5">[{{ $batch->nama_batch }}]</span>
                                                </td>
                                                
                                                <!-- 4. Kode Pesanan -->
                                                <td class="px-4 py-3 font-mono font-black text-gray-900 uppercase tracking-wide">
                                                    {{ $poInduk->kode_pesanan ?? 'NO-PO' }}
                                                </td>
                                                
                                                <!-- 5. Nama Barang + Kode Barang -->
                                                <td class="px-4 py-3">
                                                    <div class="flex flex-col">
                                                        <span class="font-extrabold text-gray-800 text-xs">📦 {{ $poInduk->barang->nama_barang ?? '-' }}</span>
                                                        <span class="text-[10px] text-blue-500 font-mono font-bold">[{{ $poInduk->barang->kode ?? '-' }}]</span>
                                                    </div>
                                                </td>
                                                
                                                <!-- 6. Supplier + Kode Supplier -->
                                                <td class="px-4 py-3">
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-gray-700">{{ $poInduk->supplier->nama_supplier ?? '-' }}</span>
                                                        <span class="text-[10px] text-gray-400 font-mono">{{ $poInduk->supplier->kode ?? '-' }}</span>
                                                    </div>
                                                </td>
                                                
                                                <!-- 7. QTY (Kuantitas Lolos Uji Penerimaan QC) -->
                                                <td class="px-4 py-3 text-right font-mono font-black text-emerald-600 bg-emerald-50/20">
                                                    {{ number_format($batch->jumlah_diterima_qc) }} {{ $poInduk->satuan ?? 'Pcs' }}
                                                    @if($batch->jumlah_reject > 0)
                                                        <span class="block text-[10px] text-red-500 font-medium font-sans">Reject: -{{ number_format($batch->jumlah_reject) }}</span>
                                                    @endif
                                                </td>
                                                
                                                <!-- 8. Harga Satuan Beserta Rincian Diskon, Potongan & PPN Lengkap -->
                                                <td class="px-4 py-3">
                                                    <div class="flex flex-col space-y-0.5 text-[11px]">
                                                        <span class="font-extrabold text-gray-900">Rp {{ number_format($poInduk->harga_barang ?? 0) }} <span class="text-[10px] text-gray-400 font-normal">/satuan</span></span>
                                                        
                                                        @if(($poInduk->diskon ?? 0) > 0)
                                                            <span class="text-[10px] text-red-500 font-semibold">Disc: -{{ $poInduk->diskon }}%</span>
                                                        @endif
                                                        
                                                        @if(($poInduk->potongan_harga ?? 0) > 0)
                                                            <span class="text-[10px] text-red-500 font-semibold">Cut: -Rp {{ number_format($poInduk->potongan_harga) }}</span>
                                                        @endif
                                                        
                                                        <span class="text-[9px] w-max font-black px-1.5 py-0.5 rounded mt-0.5 {{ ($poInduk->pajak_ppn ?? 0) > 0 ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-400' }}">
                                                            {{ ($poInduk->pajak_ppn ?? 0) > 0 ? 'PPN ' . $poInduk->pajak_ppn . '%' : 'Non PPN' }}
                                                        </span>
                                                    </div>
                                                </td>
                                                
                                                <!-- 9. Tombol Aksi Logistik (Kirim Gudang) & Tombol Keuangan (Proses Bayar) -->
                                                <td class="px-4 py-3 text-center">
                                                    <div class="flex items-center justify-center gap-2">
                                                        
                                                        <!-- A. Form Tombol Kirim Barang Ke Gudang -->
                                                        <form action="{{ route('pemesanan-barang.kirim-gudang', $batch->id) }}" method="POST" onsubmit="return confirm('Validasi mutasi masuk barang ke dalam stok inventory gudang?')">
                                                            @csrf
                                                            <button type="submit" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors flex items-center gap-1 text-[10px] font-black uppercase tracking-wider shadow-2xs" title="Kirim Data Mutasi Masuk ke Stok Gudang">
                                                                <svg xmlns="http://w3.org" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                                                Ke Gudang
                                                            </button>
                                                        </form>
                                                        
                                                        <!-- B. Form Tombol Ajukan Ke Menu Pembayaran Keuangan -->
                                                        <form action="{{ route('pemesanan-barang.ajukan-pembayaran', $poInduk->id) }}" method="POST" onsubmit="return confirm('Ajukan PO ini ke proses administrasi pelunasan pembayaran keuangan?')">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="submit" class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-1 text-[10px] font-black uppercase tracking-wider shadow-2xs" title="Ajukan Dokumen Transaksi ini Ke Tab Pembayaran Finansial">
                                                                <svg xmlns="http://w3.org" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                                                Ke Bayar
                                                            </button>
                                                        </form>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-6 py-12 text-center text-gray-400 font-medium italic bg-gray-50/40">
                                                Belum ada lembar log gelombang kedatangan barang yang tercatat masuk oleh QC untuk saat ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                @else
                                <!-- ROW DATA UNTUK TAB MASTER PEMESANAN BARANG (ASLI NORMAL) -->
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- 1. No -->
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $index + 1 }}</td>
                                    
                                    <!-- 2. Tanggal Pemesanan -->
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        {{ \Carbon\Carbon::parse($data->tanggal_pesan)->format('d/m/Y') }}
                                    </td>

                                    <!-- 2. TAMBAHKAN KOLOM KODE PESANAN BARU DISINI -->
                                    <td class="px-6 py-4 font-mono font-bold text-orange-600 uppercase text-xs tracking-wide">
                                        {{ $data->kode_pesanan ?? 'NO-PO' }}
                                    </td>

                                    <!-- 3. Nama & Kode Barang -->
                                    <td class="px-6 py-4 text-gray-700">
                                        <div class="font-semibold">{{ $data->barang->nama_barang ?? '-' }}</div>
                                        <div class="text-xs text-blue-600 font-mono mt-0.5">[{{ $data->barang->kode ?? '-' }}]</div>
                                    </td>

                                    <!-- 4. Supplier & Kode Supplier -->
                                    <td class="px-6 py-4 text-gray-700">
                                        <div class="font-medium text-gray-800">{{ $data->supplier->nama_supplier ?? '-' }}</div>
                                        <div class="text-xs text-gray-400 font-mono mt-0.5">[{{ $data->supplier->kode ?? '-' }}]</div>
                                    </td>

                                    <!-- 5. Jumlah Pesanan (QTY) & Harga Satuan -->
                                    <td class="px-6 py-4 text-gray-700">
                                        <div class="font-bold text-gray-900">
                                            {{ number_format($data->jumlah, 0, ',', '.') }} <span class="text-xs text-gray-400 font-normal">{{ $data->satuan }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            @Rp {{ number_format($data->harga_barang, 0, ',', '.') }}
                                        </div>
                                    </td>

                                    <!-- 6. Total Bayar beserta Info Potongan & PPN -->
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col space-y-0.5">
                                            <!-- Total Bayar Utama -->
                                            <span class="font-extrabold text-gray-900">Rp {{ number_format($data->total_pembayaran) }}</span>
                                            
                                            <!-- Tampilkan Info Diskon Persen jika ada nilainya -->
                                            @if($data->diskon > 0)
                                                <span class="text-xs text-red-500 font-medium">Disc: -{{ $data->diskon }}%</span>
                                            @endif
                                            
                                            <!-- Tampilkan Info Potongan Harga Nominal jika ada nilainya -->
                                            @if($data->potongan_harga > 0)
                                                <span class="text-xs text-red-500 font-medium">Cut: -Rp {{ number_format($data->potongan_harga) }}</span>
                                            @endif
                                            
                                            <!-- Info PPN -->
                                            <span class="text-[10px] w-max font-bold px-1.5 py-0.5 rounded {{ $data->pajak_ppn > 0 ? 'bg-blue-50 text-blue-600' : 'bg-gray-50 text-gray-400' }}">
                                                {{ $data->pajak_ppn > 0 ? 'PPN ' . $data->pajak_ppn . '%' : 'Non PPN' }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- 7. Status Pesanan Dropdown Form -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($data->status == 'Pending')
                                            <span class="px-3 py-1.5 text-xs font-extrabold rounded-full bg-orange-100 text-orange-700 uppercase tracking-wider border border-orange-200 shadow-sm">
                                                PENDING (PO Baru)
                                            </span>
                                        @elseif($data->status == 'Proses')
                                            <span class="px-3 py-1.5 text-xs font-extrabold rounded-full bg-blue-100 text-blue-700 uppercase tracking-wider border border-blue-200 shadow-sm">
                                                PROSES (Sedang Dikirim)
                                            </span>
                                        @else
                                            <span class="px-3 py-1.5 text-xs font-extrabold rounded-full bg-green-100 text-green-700 uppercase tracking-wider border border-green-200 shadow-sm">
                                                {{ $data->status }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- 8. Aksi Master (Edit & Hapus) -->
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center items-center gap-2">
                                            @if($data->status == 'Pending')
                                                <form action="{{ route('pemesanan-barang.ajukan-proses', $data->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memproses PO ini ke tahap konfirmasi supplier/kedatangan barang?')">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="p-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors shadow-sm flex items-center justify-center" title="Proses PO ke Pengiriman & Penerimaan">
                                                        <svg xmlns="http://w3.org" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('pemesanan-barang.edit', $data->id) }}" class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                                <svg xmlns="http://w3.org" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('pemesanan-barang.destroy', $data->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                    <svg xmlns="http://w3.org" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        <!-- Sambungan Tepat Setelah Tag Path SVG Hapus Anda -->
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center text-gray-400 font-medium">
                                    Belum ada data pemesanan barang untuk kategori tab ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ==================== LOGIKA POP-UP MODAL BOX INPUT CICILAN BIAYA ==================== -->
    <div id="modalBayarCicilan" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-1">Form Pembayaran Transaksi</h3>
            <p class="text-xs text-gray-500 mb-4">Masukkan nominal pembayaran tagihan logistik supplier</p>
            
            <form id="formProsesBayar" method="POST" action="">
                @csrf
                @method('PATCH')
                
                <div class="space-y-4 mb-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Total Tagihan Utama</label>
                        <input type="text" id="modal_total_tagihan" readonly class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 font-bold text-sm outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Sudah Dibayar Sebelumnya</label>
                        <input type="text" id="modal_sudah_dibayar" readonly class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 font-bold text-sm outline-none">
                    </div>
                    <div>
                        <label for="nominal_bayar_input" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Jumlah Bayar Hari Ini (Rp)</label>
                        <input type="number" id="nominal_bayar_input" name="nominal_bayar" required min="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-100 focus:border-orange-500 outline-none font-black text-gray-800 text-base">
                    </div>
                </div>
                
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="tutupModalBayar()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm font-semibold cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow cursor-pointer">Konfirmasi Bayar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script JavaScript untuk Kontrol Buka/Tutup Pop-Up Modal -->
    <script>
        function bukaModalBayar(id, total, sudahDibayar) {
            const modal = document.getElementById('modalBayarCicilan');
            const form = document.getElementById('formProsesBayar');
            
            form.action = `/pemesanan-barang/${id}/proses-cicilan`;
            
            document.getElementById('modal_total_tagihan').value = 'Rp ' + parseInt(total).toLocaleString('id-ID');
            document.getElementById('modal_sudah_dibayar').value = 'Rp ' + parseInt(sudahDibayar).toLocaleString('id-ID');
            
            const sisa = total - sudahDibayar;
            document.getElementById('nominal_bayar_input').max = sisa;
            document.getElementById('nominal_bayar_input').value = sisa;
            
            modal.classList.remove('hidden');
        }

        function tutupModalBayar() {
            document.getElementById('modalBayarCicilan').classList.add('hidden');
        }
    </script>

    <!-- HTML Pop-Up Modal Cek QC Barang Masuk (Sheet 3) -->
<div id="modalCekQC" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-1">Cek & Konfirmasi Barang Datang</h3>
        <p class="text-xs text-gray-500 mb-4">Input hasil timbangan dan pemeriksaan fisik logistik masuk</p>
        
        <form id="formProsesQC" method="POST" action="">
            @csrf
            @method('PATCH')
            
            <div class="space-y-4 mb-5">
                <div>
                    <label for="tanggal_kirim_input" class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tanggal Pengiriman/Kedatangan</label>
                    <input type="date" id="tanggal_kirim_input" name="tanggal_kirim" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Total Jumlah Pesanan Awal</label>
                    <input type="text" id="modal_jumlah_pesan" readonly class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 font-bold text-sm outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="jumlah_diterima_input" class="block text-xs font-semibold text-green-700 uppercase mb-1">💚 Bagus (Diterima)</label>
                        <input type="number" id="jumlah_diterima_input" name="jumlah_diterima" required min="0" oninput="hitungOtomatisReject()" class="w-full px-4 py-2 border border-gray-200 rounded-xl font-bold text-green-600 outline-none">
                    </div>
                    <div>
                        <label for="jumlah_reject_input" class="block text-xs font-semibold text-red-700 uppercase mb-1">💔 Rusak (Reject)</label>
                        <input type="number" id="jumlah_reject_input" name="jumlah_reject" required min="0" class="w-full px-4 py-2 border border-gray-200 rounded-xl font-bold text-red-600 outline-none">
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="tutupModalQC()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm font-semibold cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-sm font-bold shadow cursor-pointer">Selesaikan & Masuk Stok</button>
            </div>
        </form>
    </div>
</div>

<script>
    let totalPesanGlobal = 0;

    function bukaModalQC(id, jumlahPesan, satuan) {
        const modal = document.getElementById('modalCekQC');
        const form = document.getElementById('formProsesQC');
        
        form.action = `/pemesanan-barang/${id}/konfirmasi-diterima`;
        totalPesanGlobal = parseFloat(jumlahPesan);
        
        document.getElementById('modal_jumlah_pesan').value = totalPesanGlobal.toLocaleString('id-ID') + ' ' + satuan;
        document.getElementById('jumlah_diterima_input').value = totalPesanGlobal;
        document.getElementById('jumlah_reject_input').value = 0;
        
        modal.classList.remove('hidden');
    }

    function hitungOtomatisReject() {
        const diterima = parseFloat(document.getElementById('jumlah_diterima_input').value) || 0;
        let reject = totalPesanGlobal - diterima;
        if(reject < 0) reject = 0;
        document.getElementById('jumlah_reject_input').value = reject;
    }

    function tutupModalQC() {
        document.getElementById('modalCekQC').classList.add('hidden');
    }
</script>

</x-layout.beranda.app>

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\GrafikController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProsesController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\CostumerController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PemakaianController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\JenisBarangController;
use App\Http\Controllers\LogActivityController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\ManagerDashboardController;
use App\Http\Controllers\KategoriPemakaianController;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\AdminGudangDashboardController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmployeeController;




Route::get('/sj-indofood', function () {
    return view('pages.print.sj-indofood');
});

Route::get('/sj-biasa', function () {
    return view('pages.print.sj-biasa');
});

Route::get('/', [LandingController::class, 'index']);
Route::get('/katalog', [LandingController::class, 'katalog'])->name('katalog');
Route::get('/news', [LandingController::class, 'allBerita'])->name('allBerita');
Route::get('/product/{slug}', [LandingController::class, 'showProduk'])->name('produk.show');
Route::get('/news/{slug}', [LandingController::class, 'showBerita'])->name('berita.show');

Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');
Route::get('/api/monitoring-data', [MonitoringController::class, 'data']);

// AUTH
Route::get('/internal/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/internal/login', [AuthController::class, 'login'])->middleware('throttle:5,2');
Route::get('/internal/autologin/{user}', [AuthController::class, 'autoLogin'])->name('login.auto');
Route::post('/internal/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/internal/logout', [AuthController::class, 'logout'])->name('logout.get');

Route::middleware('auth')->group(function () {
    // BERANDA
    Route::get('/beranda', function () {
        return view('pages.beranda');
    })->name('beranda')->middleware('permission:beranda.view');

    // DASHBOARD
    Route::get('dashboard/super-admin', [SuperAdminDashboardController::class, 'index'])->name('super-admin.dashboard');
    Route::get('dashboard/manager', [ManagerDashboardController::class, 'index'])->name('manager.dashboard');
    Route::get('dashboard/admin-gudang', [AdminGudangDashboardController::class, 'index'])->name('admin-gudang.dashboard');

    Route::middleware('role:Super Admin')->group(function () {
        // CRUD ROLE
        Route::get('/roles', [RolesController::class, 'index'])->name('roles.index')->middleware('permission:roles.index');
        Route::get('/roles/create', [RolesController::class, 'create'])->name('roles.create')->middleware('permission:roles.create');
        Route::post('/roles', [RolesController::class, 'store'])->name('roles.store')->middleware('permission:roles.create');
        Route::get('/roles/{id}/edit', [RolesController::class, 'edit'])->name('roles.edit')->middleware('permission:roles.edit');
        Route::put('/roles/{id}', [RolesController::class, 'update'])->name('roles.update')->middleware('permission:roles.edit');
        Route::delete('/roles/{id}', [RolesController::class, 'destroy'])->name('roles.destroy')->middleware('permission:roles.delete');
    });

    // CRUD PERUSAHAAN
    Route::get('/perusahaan', [PerusahaanController::class, 'index'])->name('perusahaan.index')->middleware('permission:perusahaan.index');
    Route::get('/perusahaan/create', [PerusahaanController::class, 'create'])->name('perusahaan.create')->middleware('permission:perusahaan.create');
    Route::post('/perusahaan', [PerusahaanController::class, 'store'])->name('perusahaan.store')->middleware('permission:perusahaan.create');
    Route::get('/perusahaan/{id}', [PerusahaanController::class, 'show'])->name('perusahaan.show')->middleware('permission:perusahaan.show');
    Route::get('/perusahaan/{id}/edit', [PerusahaanController::class, 'edit'])->name('perusahaan.edit')->middleware('permission:perusahaan.edit');
    Route::put('/perusahaan/{id}', [PerusahaanController::class, 'update'])->name('perusahaan.update')->middleware('permission:perusahaan.edit');
    Route::delete('/perusahaan/{id}', [PerusahaanController::class, 'destroy'])->name('perusahaan.destroy')->middleware('permission:perusahaan.delete');
    Route::patch('/perusahaan/activate/{id}', [PerusahaanController::class, 'activate'])->name('perusahaan.activate')->middleware('permission:perusahaan.activate');

    // CRUD COSTUMER
    Route::post('/costumer/import', [CostumerController::class, 'import'])->name('costumer.import')->middleware('permission:costumer.import');
    Route::get('/costumer/download-template', [CostumerController::class, 'downloadTemplate'])->name('costumer.download-template');
    Route::get('/costumer', [CostumerController::class, 'index'])->name('costumer.index')->middleware('permission:costumer.index');
    Route::get('/costumer/create', [CostumerController::class, 'create'])->name('costumer.create')->middleware('permission:costumer.create');
    Route::post('/costumer', [CostumerController::class, 'store'])->name('costumer.store')->middleware('permission:costumer.create');
    Route::get('/costumer/{id}/edit', [CostumerController::class, 'edit'])->name('costumer.edit')->middleware('permission:costumer.edit');
    Route::put('/costumer/{id}', [CostumerController::class, 'update'])->name('costumer.update')->middleware('permission:costumer.edit');
    Route::delete('/costumer/{id}', [CostumerController::class, 'destroy'])->name('costumer.destroy')->middleware('permission:costumer.delete');
    Route::patch('/costumer/activate/{id}', [CostumerController::class, 'activate'])->name('costumer.activate')->middleware('permission:costumer.activate');

    // CRUD BARANG DAN JENIS BARANG
    Route::prefix('barang')->name('barang.')->group(function () {
        Route::middleware('role:Super Admin')->group(function () {
            // CRUD JENIS BARANG
            Route::get('/jenis', [JenisBarangController::class, 'index'])->name('jenis.index')->middleware('permission:jenis-barang.index');
            Route::post('/jenis', [JenisBarangController::class, 'store'])->name('jenis.store')->middleware('permission:jenis-barang.create');
            Route::put('/jenis/{id}', [JenisBarangController::class, 'update'])->name('jenis.update')->middleware('permission:jenis-barang.edit');
            Route::delete('/jenis/{id}', [JenisBarangController::class, 'destroy'])->name('jenis.destroy')->middleware('permission:jenis-barang.delete');
        });

        // CRUD BARANG
        Route::get('/', [BarangController::class, 'index'])->name('index')->middleware('permission:barang.index');
        Route::get('/create', [BarangController::class, 'create'])->name('create')->middleware('permission:barang.create');
        Route::post('/', [BarangController::class, 'store'])->name('store')->middleware('permission:barang.create');
        Route::get('/{id}/edit', [BarangController::class, 'edit'])->name('edit')->middleware('permission:barang.edit');
        Route::put('/{id}', [BarangController::class, 'update'])->name('update')->middleware('permission:barang.edit');
        Route::delete('/{id}', [BarangController::class, 'destroy'])->name('destroy')->middleware('permission:barang.delete');
        Route::patch('/activate/{id}', [BarangController::class, 'activate'])->name('activate')->middleware('permission:barang.activate');
        Route::post('/import', [BarangController::class, 'import'])->name('import')->middleware('permission:barang.import');
        Route::get('/download-template', [BarangController::class, 'downloadTemplate'])->name('download-template');
    });

    // CRUD USER
    Route::get('/user', [UserController::class, 'index'])->name('user.index')->middleware('permission:user.index');
    Route::get('/user/create', [UserController::class, 'create'])->name('user.create')->middleware('permission:user.create');
    Route::post('/user', [UserController::class, 'store'])->name('user.store')->middleware('permission:user.create');
    Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('user.edit')->middleware('permission:user.edit');
    Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update')->middleware('permission:user.edit');
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy')->middleware('permission:user.delete');

    // CRUD SUPLIER
    Route::get('/supplier/download-template', [SupplierController::class, 'downloadTemplate'])->name('supplier.download-template');
    Route::post('/supplier/import', [SupplierController::class, 'import'])->name('supplier.import')->middleware('permission:supplier.import');
    Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index')->middleware('permission:supplier.index');
    Route::get('/supplier/create', [SupplierController::class, 'create'])->name('supplier.create')->middleware('permission:supplier.create');
    Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store')->middleware('permission:supplier.create');
    Route::get('/supplier/{id}/edit', [SupplierController::class, 'edit'])->name('supplier.edit')->middleware('permission:supplier.edit');
    Route::put('/supplier/{id}', [SupplierController::class, 'update'])->name('supplier.update')->middleware('permission:supplier.edit');
    Route::delete('/supplier/{id}', [SupplierController::class, 'destroy'])->name('supplier.destroy')->middleware('permission:supplier.delete');

    // CRUD PROSES
    Route::patch('/proses/activate/{id}', [ProsesController::class, 'activate'])->name('proses.activate')->middleware('permission:proses.activate');
    Route::get('/proses', [ProsesController::class, 'index'])->name('proses.index')->middleware('permission:proses.index');
    Route::post('/proses', [ProsesController::class, 'store'])->name('proses.store')->middleware('permission:proses.create');
    Route::put('/proses/{id}', [ProsesController::class, 'update'])->name('proses.update')->middleware('permission:proses.edit');
    Route::delete('/proses/{id}', [ProsesController::class, 'destroy'])->name('proses.destroy')->middleware('permission:proses.delete');

    // CRUD INVENTORY
    Route::get('inventory/create-produksi', [InventoryController::class, 'createProduksi'])->name('inventory.create-produksi')->middleware('permission:inventory.create-produksi');
    Route::get('inventory/create-bp', [InventoryController::class, 'createBp'])->name('inventory.create-bp')->middleware('permission:inventory.create-bahan-penolong');
    Route::get('inventory/create-bb', [InventoryController::class, 'createBb'])->name('inventory.create-bb')->middleware('permission:inventory.create-bahan-baku');
    Route::get('inventory/riwayat/{id}', [InventoryController::class, 'allRiwayat'])->name('inventory.riwayat')->middleware('permission:inventory.riwayat');
    Route::post('inventory/create-produksi', [InventoryController::class, 'storeProduksi'])->name('inventory.store-produksi')->middleware('permission:inventory.create-produksi');
    Route::post('inventory/create-bahan', [InventoryController::class, 'storeBahan'])->name('inventory.store-bahan');
    Route::patch('/inventory/{id}/update-minimum', [InventoryController::class, 'updateMinimum'])->name('inventory.update-minimum')->middleware('permission:inventory.minimum-edit');
    Route::patch('/inventory-details/{id}', [InventoryController::class, 'updateDetail'])->name('inventory-details.update')->middleware('permission:inventory.detail-edit');
    Route::patch('/inventory/quick-update', [InventoryController::class, 'quickUpdate'])->name('inventory.quick-update')->middleware('permission:inventory.quick-edit');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index')->middleware('permission:inventory.index');
    Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy')->middleware('permission:inventory.delete');
    Route::get('/inventory/{id}', [InventoryController::class, 'show'])->name('inventory.show')->middleware('permission:inventory.show');
    Route::post('/inventory/tutup-buku', [InventoryController::class, 'eksekusiTutupBuku'])->name('inventory.tutup-buku')->middleware('permission:inventory.tutup-buku');
    Route::get('/inventory/afkir-ulang/{id}', [InventoryController::class, 'Afkir'])->name('inventory.afkir-ulang')->middleware('permission:inventory.afkir-ulang');
    Route::get('/inventory/kartu-stok/{id}', [InventoryController::class, 'kartuStok'])->name('inventory.kartu-stok')->middleware('permission:inventory.kartu-stok');
    Route::get('/inventory/kartu-stok/{id}/pdf', [InventoryController::class, 'exportPdf'])->name('inventory.kartu-stok.pdf')->middleware('permission:inventory.cetak-kartu-stok');
    Route::post('/inventory/afkir-ulang/gudang/{id}', [InventoryController::class, 'afkirUlangGudang'])->name('inventory.afkir-ulang.gudang')->middleware('permission:inventory.afkir-ulang');
    Route::get('/return-barang', [InventoryController::class, 'daftarReturn'])->name('inventory.return-barang');
    Route::patch('/return-barang/{id}/status', [InventoryController::class, 'updateStatus'])->name('inventory.return-barang.update-status');
    Route::patch('/return-barang/bulk-status', [InventoryController::class, 'bulkUpdateStatus'])->name('inventory.return-barang.bulk-update');

    // CRUD BAHAN BAKU
    Route::get('/bahan-baku', [BahanBakuController::class, 'index'])->name('bahan-baku.index')->middleware('permission:bahan-baku.index');
    Route::get('/bahan-baku/create', [BahanBakuController::class, 'create'])->name('bahan-baku.create')->middleware('permission:bahan-baku.create');
    Route::post('/bahan-baku', [BahanBakuController::class, 'store'])->name('bahan-baku.store')->middleware('permission:bahan-baku.create');
    Route::get('/bahan-baku/{id}/edit', [BahanBakuController::class, 'edit'])->name('bahan-baku.edit')->middleware('permission:bahan-baku.edit');
    Route::put('/bahan-baku/{id}', [BahanBakuController::class, 'update'])->name('bahan-baku.update')->middleware('permission:bahan-baku.edit');
    Route::delete('/bahan-baku/{id}', [BahanBakuController::class, 'destroy'])->name('bahan-baku.destroy')->middleware('permission:bahan-baku.delete');


    // CRUD KATEGORI PEMAKAIAN
    Route::get('/kategori-pemakaian', [KategoriPemakaianController::class, 'index'])->name('kategori-pemakaian.index')->middleware('permission:kategori-pemakaian.index');
    Route::post('/kategori-pemakaian', [KategoriPemakaianController::class, 'store'])->name('kategori-pemakaian.store')->middleware('permission:kategori-pemakaian.create');
    Route::put('/kategori-pemakaian/{id}', [KategoriPemakaianController::class, 'update'])->name('kategori-pemakaian.update')->middleware('permission:kategori-pemakaian.edit');
    Route::delete('/kategori-pemakaian/{id}', [KategoriPemakaianController::class, 'destroy'])->name('kategori-pemakaian.destroy')->middleware('permission:kategori-pemakaian.delete');

    // CRUD PEMAKAIAN
    Route::get('/pemakaian', [PemakaianController::class, 'index'])->name('pemakaian.index')->middleware('permission:pemakaian.index');
    Route::post('/pemakaian', [PemakaianController::class, 'store'])->name('pemakaian.store')->middleware('permission:pemakaian.create');
    Route::put('/pemakaian/{id}', [PemakaianController::class, 'update'])->name('pemakaian.update')->middleware('permission:pemakaian.edit');
    Route::delete('/pemakaian/{id}', [PemakaianController::class, 'destroy'])->name('pemakaian.destroy')->middleware('permission:pemakaian.delete');

    // CRUD PENGELUARAN
    Route::get('pengeluaran/create-operasional', [PengeluaranController::class, 'createOperasional'])->name('pengeluaran.create-operasional')->middleware('permission:pengeluaran.operasional');
    Route::get('pengeluaran/create-office', [PengeluaranController::class, 'createOffice'])->name('pengeluaran.create-office')->middleware('permission:pengeluaran.office');
    Route::get('pengeluaran/create-limbah', [PengeluaranController::class, 'createPengolahanLimbah'])->name('pengeluaran.create-limbah')->middleware('permission:pengeluaran.limbah');
    Route::get('pengeluaran/create-kesejahteraan', [PengeluaranController::class, 'createGajiKaryawan'])->name('pengeluaran.create-kesejahteraan')->middleware('permission:pengeluaran.kesejahteraan');
    Route::get('pengeluaran/create-maintenance', [PengeluaranController::class, 'createMaintenance'])->name('pengeluaran.create-maintenance')->middleware('permission:pengeluaran.maintenance');
    Route::get('pengeluaran/create-admnisitrasi', [PengeluaranController::class, 'createAdministrasi'])->name('pengeluaran.create-administrasi')->middleware('permission:pengeluaran.administrasi');
    Route::get('/pengeluaran', [PengeluaranController::class, 'index'])->name('pengeluaran.index');
    Route::post('/pengeluaran', [PengeluaranController::class, 'store'])->name('pengeluaran.store');
    Route::get('/pengeluaran/{id}/edit', [PengeluaranController::class, 'edit'])->name('pengeluaran.edit')->middleware('permission:pengeluaran.edit');
    Route::put('/pengeluaran/{id}', [PengeluaranController::class, 'update'])->name('pengeluaran.update')->middleware('permission:pengeluaran.edit');
    Route::delete('/pengeluaran/{id}', [PengeluaranController::class, 'destroy'])->name('pengeluaran.destroy')->middleware('permission:pengeluaran.delete');

    // CRUD BARANG MASUK
    Route::get('barang-masuk/create-produksi', [BarangMasukController::class, 'createProduksi'])->name('barang-masuk.create-produksi')->middleware('permission:barang-masuk.create-produksi');
    Route::get('barang-masuk/create-bp', [BarangMasukController::class, 'createBp'])->name('barang-masuk.create-bp')->middleware('permission:barang-masuk.create-bahan-penolong');
    Route::get('barang-masuk/edit-produksi/{id}', [BarangMasukController::class, 'editProduksi'])->name('barang-masuk.edit-produksi')->middleware('permission:barang-masuk.edit-produksi');
    Route::get('barang-masuk/edit-bp/{id}', [BarangMasukController::class, 'editBp'])->name('barang-masuk.edit-bp')->middleware('permission:barang-masuk.edit-bahan-penolong');
    Route::put('/barang-masuk/{id}', [BarangMasukController::class, 'update'])->name('barang-masuk.update');
    Route::post('barang-masuk/create-produksi', [BarangMasukController::class, 'storeProduksi'])->name('barang-masuk.store-produksi')->middleware('permission:barang-masuk.create-produksi');
    Route::post('barang-masuk/create-bahan', [BarangMasukController::class, 'storeBahan'])->name('barang-masuk.store-bahan')->middleware('permission:barang-masuk.create-bahan-penolong');
    Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('barang-masuk.index');
    Route::delete('/barang-masuk/{id}', [BarangMasukController::class, 'destroy'])->name('barang-masuk.destroy')->middleware('permission:barang-masuk.delete');

    // CRUD PRODUKSI
    Route::put('/produksi/detail/{id}', [ProduksiController::class, 'updateDetail'])->name('produksi.update_detail')->middleware('permission:produksi.detail-edit');
    Route::get('/produksi', [ProduksiController::class, 'index'])->name('produksi.index')->middleware('permission:produksi.index');
    Route::get('/produksi/{id}', [ProduksiController::class, 'show'])->name('produksi.show')->middleware('permission:produksi.show');

    // CRUD BARANG KELUAR
    Route::get('barang-keluar/create-produksi', [BarangKeluarController::class, 'createProduksi'])->name('barang-keluar.create-produksi')->middleware('permission:barang-keluar.create-produksi');
    Route::get('barang-keluar/create-bahan-baku', [BarangKeluarController::class, 'createBahanBaku'])->name('barang-keluar.create-bahan-baku')->middleware('permission:barang-keluar.create-bahan-baku');
    Route::get('barang-keluar/create-penjualan', [BarangKeluarController::class, 'createPenjualan'])->name('barang-keluar.create-penjualan')->middleware('permission:barang-keluar.create-penjualan');
    Route::get('/barang-keluar/print-group', [BarangKeluarController::class, 'printGroup'])->name('barang-keluar.print-group')->middleware('permission:barang-keluar.print-group');
    Route::get('/barang-keluar', [BarangKeluarController::class, 'index'])->name('barang-keluar.index');
    Route::post('/barang-keluar', [BarangKeluarController::class, 'store'])->name('barang-keluar.store');
    Route::get('/barang-keluar/{id}/edit', [BarangKeluarController::class, 'edit'])->name('barang-keluar.edit')->middleware('permission:barang-keluar.edit');
    Route::put('/barang-keluar/{id}', [BarangKeluarController::class, 'update'])->name('barang-keluar.update')->middleware('permission:barang-keluar.edit');
    Route::delete('/barang-keluar/{id}', [BarangKeluarController::class, 'destroy'])->name('barang-keluar.destroy')->middleware('permission:barang-keluar.delete');
    Route::get('/barang-keluar/{id}/afkir-ulang', [BarangKeluarController::class, 'showAfkirUlang'])->name('barang-keluar.afkir-ulang')->middleware('permission:barang-keluar.afkir-ulang');
    Route::post('/barang-keluar/{id}/afkir-ulang', [BarangKeluarController::class, 'eksekusiAfkirUlang'])->name('barang-keluar.afkir-ulang.store')->middleware('permission:barang-keluar.afkir-ulang');

    // CRUD PRODUK
    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index')->middleware('permission:produk.index');
    Route::get('/produk/create', [ProdukController::class, 'create'])->name('produk.create')->middleware('permission:produk.create');
    Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store')->middleware('permission:produk.create');
    Route::get('/produk/{id}/edit', [ProdukController::class, 'edit'])->name('produk.edit')->middleware('permission:produk.edit');
    Route::put('/produk/{id}', [ProdukController::class, 'update'])->name('produk.update')->middleware('permission:produk.edit');
    Route::delete('/produk/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy')->middleware('permission:produk.delete');

    // CRUD BERITA
    Route::get('/berita', [BeritaController::class, 'index'])->name('berita.index')->middleware('permission:berita.index');
    Route::get('/berita/create', [BeritaController::class, 'create'])->name('berita.create')->middleware('permission:berita.create');
    Route::post('/berita', [BeritaController::class, 'store'])->name('berita.store')->middleware('permission:berita.create');
    Route::get('/berita/{id}/edit', [BeritaController::class, 'edit'])->name('berita.edit')->middleware('permission:berita.edit');
    Route::put('/berita/{id}', [BeritaController::class, 'update'])->name('berita.update')->middleware('permission:berita.edit');
    Route::delete('/berita/{id}', [BeritaController::class, 'destroy'])->name('berita.destroy')->middleware('permission:berita.delete');

    // LAPORAN
    Route::get('laporan-produksi', [LaporanController::class, 'laporanProduksi'])->name('laporan-produksi')->middleware('permission:laporan.produksi');
    Route::get('laporan-pengeluaran', [LaporanController::class, 'laporanPengeluaran'])->name('laporan-pengeluaran')->middleware('permission:laporan.pengeluaran');
    Route::get('laporan-gudang', [LaporanController::class, 'laporanGudang'])->name('laporan-gudang')->middleware('permission:laporan.gudang');
    Route::get('laporan-hpp', [LaporanController::class, 'laporanHpp'])->name('laporan-hpp')->middleware('permission:laporan.hpp');
    Route::get('laporan-transaksi', [LaporanController::class, 'laporanTransaksi'])->name('laporan-transaksi')->middleware('permission:laporan.transaksi');

    // GRAFIK
    // Route Gateway (Halaman Utama yang mengarahkan user)
    Route::get('/grafik', [GrafikController::class, 'index'])->name('grafik.index');

    // Grup Rute Grafik dengan Middleware Spatie
    Route::middleware(['auth'])->group(function () {
        Route::get('grafik-bahan-baku', [GrafikController::class, 'grafikBahanBaku'])
            ->name('grafik.bahan-baku')
            ->middleware('permission:grafik.bahan-baku');

        Route::get('grafik-produksi', [GrafikController::class, 'grafikProduksi'])
            ->name('grafik.produksi')
            ->middleware('permission:grafik.produksi');

        Route::get('grafik-pemakaian', [GrafikController::class, 'grafikPemakaian'])
            ->name('grafik.pemakaian')
            ->middleware('permission:grafik.pemakaian');

        Route::get('grafik-hpp', [GrafikController::class, 'grafikHpp'])
            ->name('grafik.hpp')
            ->middleware('permission:grafik.hpp');

        Route::get('grafik-transaksi', [GrafikController::class, 'grafikTransaksi'])
            ->name('grafik.transaksi')
            ->middleware('permission:grafik.transaksi');
    

    });

    // LOG ACTIVITY
    Route::get('logs', [LogActivityController::class, 'index'])->name('logs.index')->middleware('permission:logs.index');
    // RUTE API ASISTEN OTOMATIS PROFIL KARYAWAN BERDASARKAN ID
    Route::get('api/employee/{id_karyawan}', function($id_karyawan) {
        $employee = App\Models\Employee::where('id_karyawan', $id_karyawan)->first();
        return response()->json($employee);
    });
    
    Route::delete('/attendance/bulk-delete', [\App\Http\Controllers\AttendanceController::class, 'bulkDelete'])->name('attendance.bulkDelete');
    Route::get('attendance/print', [App\Http\Controllers\AttendanceController::class, 'print'])->name('attendance.print');
    Route::get('attendance/export', [App\Http\Controllers\AttendanceController::class, 'export'])->name('attendance.export');
    Route::post('attendance/import', [App\Http\Controllers\AttendanceController::class, 'import'])->name('attendance.import');
    Route::resource('attendance', App\Http\Controllers\AttendanceController::class);
    
    


    // CETAK LAPORAN EMPLOYEE
    Route::get('employee/export', [App\Http\Controllers\EmployeeController::class, 'export'])->name('employee.export');
    Route::get('employee/print', [App\Http\Controllers\EmployeeController::class, 'print']);
    Route::post('employee/print', [App\Http\Controllers\EmployeeController::class, 'print'])->name('employee.print');
    
    // CRUD EMPLOYEE (FORMASI PREMIUM ANTI-BENTROK RUTE)
    Route::resource('employee', App\Http\Controllers\EmployeeController::class);

    Route::post('employee/import', [App\Http\Controllers\EmployeeController::class, 'import'])->name('employee.import');
    Route::post('employee/import-template', [App\Http\Controllers\EmployeeController::class, 'importTemplate'])->name('employee.importTemplate');

    // ================= RUTE GRUP SISTEM PAYROLL & PENGGAJIAN PT MIRASA FOOD INDUSTRY =================
    Route::get('payroll', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
    Route::post('payroll/store', [\App\Http\Controllers\PayrollController::class, 'storeOrUpdate'])->name('payroll.store');
    

    // RUTE KHUSUS OWNER - ANALISIS BIAYA GAJI OPERASIONAL HARIAN PABRIKAN
    Route::get('payroll/multi-payroll', [App\Http\Controllers\PayrollController::class, 'MultiPayroll'])->name('payroll.MultiPayroll');
    Route::get('payroll/export-bca', [App\Http\Controllers\PayrollController::class, 'exportBcaCsv'])->name('payroll.exportBca');
    Route::get('payroll/print-tahunan', [App\Http\Controllers\PayrollController::class, 'printTahunan'])->name('payroll.printTahunan');
    Route::get('payroll/export-detail-excel', [App\Http\Controllers\PayrollController::class, 'exportDetailExcel'])->name('payroll.exportDetailExcel');
    Route::get('payroll/detail_harian/print', [App\Http\Controllers\PayrollController::class, 'printHarian'])->name('payroll.printHarian');
    Route::get('payroll/detail-harian', [\App\Http\Controllers\PayrollController::class, 'detailPayrollHarian'])->name('payroll.detail_harian');
    Route::get('payroll/api-absensi-harian', [\App\Http\Controllers\PayrollController::class, 'getDailyAttendance'])->name('payroll.daily_attendance');
    Route::post('payroll/import', [\App\Http\Controllers\PayrollController::class, 'importExcel'])->name('payroll.import');
    Route::get('payroll/download-template', [\App\Http\Controllers\PayrollController::class, 'downloadTemplatePayroll'])->name('payroll.download_template');
    Route::get('/payroll/print-slip', [\App\Http\Controllers\PayrollController::class, 'printSlip'])->name('payroll.print_slip');
    Route::get('/payroll/print-all-slips', [\App\Http\Controllers\PayrollController::class, 'printAllSlips'])->name('payroll.print_all_slips');
    Route::get('/payroll/kelompok-harian', [\App\Http\Controllers\PayrollController::class, 'gajiKelompokHarian'])->name('payroll.kelompok_harian');
    Route::get('/payroll/print-laporan-kelompok-harian', [\App\Http\Controllers\PayrollController::class, 'printKelompokHarian'])->name('payroll.print_laporan_kelompok_harian');
    Route::get('/payroll/print-laporan-kelompok-tahunan', [\App\Http\Controllers\PayrollController::class, 'printKelompokTahunan'])->name('payroll.print_laporan_kelompok_tahunan');
    Route::get('/payroll/export-excel-kelompok-harian', [\App\Http\Controllers\PayrollController::class, 'exportExcelKelompokHarian'])->name('payroll.export_excel_kelompok_harian');
    Route::get('/payroll/send-email-massal', [\App\Http\Controllers\PayrollController::class, 'sendEmailMassal'])->name('payroll.send-email-massal');

    Route::middleware(['auth'])->group(function () {
    // CRUD PEMESANAN BARANG (DILENGKAPI HAK AKSES PERMISSION)
    Route::get('/pemesanan-barang', [App\Http\Controllers\PemesananBarangController::class, 'index'])->name('pemesanan-barang.index')->middleware('permission:pemesanan.index');
    Route::get('/pemesanan-barang/create', [App\Http\Controllers\PemesananBarangController::class, 'create'])->name('pemesanan-barang.create')->middleware('permission:pemesanan.create');
    Route::post('/pemesanan-barang', [App\Http\Controllers\PemesananBarangController::class, 'store'])->name('pemesanan-barang.store')->middleware('permission:pemesanan.create');
    Route::get('/pemesanan-barang/{id}/edit', [App\Http\Controllers\PemesananBarangController::class, 'edit'])->name('pemesanan-barang.edit')->middleware('permission:pemesanan.edit');
    Route::put('/pemesanan-barang/{id}', [App\Http\Controllers\PemesananBarangController::class, 'update'])->name('pemesanan-barang.update')->middleware('permission:pemesanan.edit');
    Route::delete('/pemesanan-barang/{id}', [App\Http\Controllers\PemesananBarangController::class, 'destroy'])->name('pemesanan-barang.destroy')->middleware('permission:pemesanan.delete');
    Route::patch('/pemesanan-barang/{id}/update-status', [App\Http\Controllers\PemesananBarangController::class, 'updateStatus'])->name('pemesanan-barang.update-status');
    Route::get('pemesanan-barang/{id}/invoice', [App\Http\Controllers\PemesananBarangController::class, 'fakturPembelian'])->name('pemesanan-barang.invoice');
    Route::patch('pemesanan-barang/{id}/proses-cicilan', [App\Http\Controllers\PemesananBarangController::class, 'prosesCicilan'])->name('pemesanan-barang.proses-cicilan');
    Route::patch('pemesanan-barang/{id}/konfirmasi-diterima', [App\Http\Controllers\PemesananBarangController::class, 'konfirmasiDiterima'])->name('pemesanan-barang.konfirmasi-diterima');
    Route::put('/pemesanan-barang/{id}/ajukan-proses', [App\Http\Controllers\PemesananBarangController::class, 'ajukanProses'])->name('pemesanan-barang.ajukan-proses');
    Route::post('/pemesanan-barang/{id}/simpan-batch', [App\Http\Controllers\PemesananBarangController::class, 'simpanBatch'])->name('pemesanan-barang.simpan-batch');
    Route::post('/pemesanan-barang/batch/{id}/kirim-gudang', [App\Http\Controllers\PemesananBarangController::class, 'kirimKeGudang'])->name('pemesanan-barang.kirim-gudang');
    Route::put('/pemesanan-barang/{id}/ajukan-pembayaran', [App\Http\Controllers\PemesananBarangController::class, 'ajukanPembayaran'])->name('pemesanan-barang.ajukan-pembayaran');

});
});

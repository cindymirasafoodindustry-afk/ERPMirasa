<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'Master Data' => [
                'view' => [
                    'costumer.index',
                    'barang.index',
                    'jenis-barang.index',
                    'supplier.index',
                    'perusahaan.index',
                    'perusahaan.show',
                    'proses.index',
                    'pemesanan.index'
                ],
                'action' => [
                    'costumer.create',
                    'costumer.edit',
                    'costumer.delete',
                    'costumer.import',
                    'costumer.activate',

                    'barang.create',
                    'barang.edit',
                    'barang.delete',
                    'barang.import',
                    'barang.activate',

                    'jenis-barang.create',
                    'jenis-barang.edit',
                    'jenis-barang.delete',

                    'supplier.create',
                    'supplier.edit',
                    'supplier.delete',
                    'supplier.import',
                    'supplier.activate',

                    'perusahaan.create',
                    'perusahaan.edit',
                    'perusahaan.delete',
                    'perusahaan.activate',

                    'proses.create',
                    'proses.edit',
                    'proses.delete',
                    'proses.activate',

                    'pemesanan.create',
                    'pemesanan.edit',
                    'pemesanan.delete',
                ]
            ],
            'Inventory & Produksi' => [
                'view' => [
                    'inventory.index',
                    'inventory.show',
                    'inventory.riwayat',
                    'produksi.index',
                    'produksi.show',
                    'bahan-baku.index'
                ],
                'action' => [
                    'inventory.create-bahan-penolong',
                    'inventory.create-bahan-baku',
                    'inventory.create-produksi',
                    'inventory.quick-edit',
                    'inventory.detail-edit',
                    'inventory.minimum-edit',
                    'inventory.delete',
                    
                    'produksi.detail-edit',

                    'bahan-baku.create',
                    'bahan-baku.edit',
                    'bahan-baku.delete',
                ]
            ],
            'Transaksi' => [
                'view' => [
                    'barang-keluar.produksi',
                    'barang-keluar.penjualan',
                    'barang-keluar.bahan-baku',

                    'barang-masuk.produksi',
                    'barang-masuk.bahan-penolong',

                    'pemakaian.index',
                    
                    'kategori-pemakaian.index'
                ],
                'action' => [
                    'barang-keluar.create-produksi',
                    'barang-keluar.create-penjualan',
                    'barang-keluar.create-bahan-baku',
                    'barang-keluar.edit',
                    'barang-keluar.delete',
                    'barang-keluar.print-group',

                    'barang-masuk.create-produksi',
                    'barang-masuk.create-bahan-penolong',
                    'barang-masuk.edit-produksi',
                    'barang-masuk.edit-bahan-penolong',
                    'barang-masuk.delete',

                    'pemakaian.create',
                    'pemakaian.edit',
                    'pemakaian.delete',
                    
                    'kategori-pemakaian.create',
                    'kategori-pemakaian.edit',
                    'kategori-pemakaian.delete',
                ]
            ],
            'Pengeluaran' => [
                'view' => [
                    'pengeluaran.operasional',
                    'pengeluaran.office',
                    'pengeluaran.limbah',
                    'pengeluaran.kesejahteraan',
                    'pengeluaran.maintenance',
                    'pengeluaran.administrasi',
                ],
                'action' => [
                    'pengeluaran.create-operasional',
                    'pengeluaran.create-office',
                    'pengeluaran.create-limbah',
                    'pengeluaran.create-kesejahteraan',
                    'pengeluaran.create-maintenance',
                    'pengeluaran.create-administrasi',
                    'pengeluaran.edit',
                    'pengeluaran.delete',
                ]
            ],
            'Laporan & Grafik' => [
                'view' => [
                    'laporan.produksi',
                    'laporan.pengeluaran',
                    'laporan.gudang',
                    'laporan.hpp',
                    'laporan.transaksi',

                    'grafik.bahan-baku',
                    'grafik.pemakaian',
                    'grafik.hpp',
                    'grafik.transaksi',
                    'grafik.produksi'
                ],
                'action' => []
            ],
            'System Custom' => [
                'view' => [
                    'beranda.view', 
                    'user.index', 
                    'roles.index', 
                    'logs.index', 
                    'produk.index', 
                    'berita.index',
                    'absensi.index',
                    'pemesanan.index',
                    ],
                'action' => [
                    'user.create',
                    'user.edit',
                    'user.delete',

                    'roles.create',
                    'roles.edit',
                    'roles.delete',

                    'produk.create',
                    'produk.edit',
                    'produk.delete',

                    'berita.create',
                    'berita.edit',
                    'berita.delete',

                    'absensi.create',
                    'absensi.edit',
                    'absensi.delete',
                    
                    'pemesanan.create',
                    'pemesanan.edit',
                    'pemesanan.delete',
                ]
            ]
        ];

        // Loop untuk mendaftarkan permission
        foreach ($modules as $moduleName => $types) {
            foreach ($types as $type => $permissions) {
                foreach ($permissions as $permission) {
                    Permission::findOrCreate($permission, 'web');
                }
            }
        }
    }
}

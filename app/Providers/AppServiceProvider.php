<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🚀 ROBOT SAPU JAGAT: 1000% Suci Murni Tanpa Ada Tipe Data Array (Kebal dari TypeError trim!)
        Blade::directive('translate', function ($expression) {
            return "<?php
                \$text = trim($expression, \"'\\\"\");
                
                // Ambil string teks kode bahasa utama (Misal EN-US otomatis dipotong menjadi EN)
                \$rawLang = strtoupper(request()->get('lang', 'ID'));
                \$targetLang = strtoupper(trim(strtok(\$rawLang, '-')));

                if (\$targetLang === 'ID' || \$targetLang === 'INDONESIA' || empty(\$text)) {
                    echo \$text;
                } else {
                    // 📑 DATABASE KAMUS LOKAL SUPER LENGKAP PT MIRASA FOOD INDUSTRY
                    \$dictionary = [
                        // 1. Kelompok Navigasi Atas (Kapital & Huruf Campuran)
                        'sejarah' => 'History',
                        'strategi' => 'Strategy',
                        'produksi' => 'Production',
                        'berita' => 'News',
                        'mitra' => 'Partner',
                        'pemilik' => 'Owner',
                        'industri unggul' => 'Leading Industry',
                        'warisan kami' => 'Our Heritage',
                        'mengukir sejarah di setiap musim.' => 'Carving History in Every Season.',
                        'kualitas lokal, standar global.' => 'Local Quality, Global Standard.',
                        'berawal dari industri rumahan pada tahun 1979 di mungkid, magelang, pt mirasa food industry bertransformasi menjadi pionir camilan berkualitas global.' => 'Starting from a cottage industry in 1979 in Mungkid, Magelang, PT Mirasa Food Industry has transformed into a pioneer of global quality snacks.',
                        'cap payung bukan sekadar logo; ini adalah janji perlindungan terhadap kualitas produk dan kepuasan anda di segala kondisi.' => '\"Cap Payung\" is not just a logo; it is a promise of protection for product quality and your satisfaction in all conditions.',
                        'lihat visi kami' => 'View Our Vision',
                        'berdiri sejak 1979' => 'Established Since 1979',
                        'tahun dedikasi' => 'Years of Dedication',
                        'bahan baku' => 'Raw Materials',
                        'kualitas terbaik' => 'Best Quality',
                        
                        // 2. Antisipasi Teks Kapital Besar Semua dari HTML Komponen
                        'SINERGI STRATEGIS DUNIA.' => 'GLOBAL STRATEGIC SYNERGY.',
                        'Membangun ekosistem distribusi dan standar mutu bersama pemimpin industri terpercaya di seluruh dunia.' => 'Building a distribution ecosystem and quality standards together with trusted industry leaders worldwide.',
                        'MENGUKIR SEJARAH DI SETIAP MUSIM.' => 'CARVING HISTORY IN EVERY SEASON.',
                        'VISI KAMI' => 'OUR VISION',
                        'MISI KAMI' => 'OUR MISSION',
                        'WAWASAN & PEMBARUAN MIRASA.' => 'MIRASA INSIGHTS & UPDATES.',
                        'LIHAT SEMUA BERITA' => 'VIEW ALL NEWS',
                        'KAPASITAS & JANGKAUAN GLOBAL' => 'GLOBAL CAPACITY & REACH',
                        'SERTIFIKASI RESMI KAMI.' => 'OUR OFFICIAL CERTIFICATIONS.',
                        'Komitmen nyata kami terhadap keamanan pangan dan standar kualitas internasional yang terjamin.' => 'Our real commitment to food safety and guaranteed international quality standards.',
                        'PRODUK & LAYANAN' => 'PRODUCTS & SERVICES',
                        'SUMBER DAYA MANUSIA' => 'HUMAN RESOURCES',
                        'TATA KELOLA & PERTUMBUHAN' => 'GOVERNANCE & GROWTH',
                        'PRODUKSI HARIAN' => 'DAILY PRODUCTION',
                        'KAPASITAS BULANAN' => 'MONTHLY CAPACITY',
                        'MITRA PETANI' => 'PARTNER FARMERS',
                        'VARIAN PRODUK' => 'PRODUCT VARIANTS',
                        'BAHAN BAKU' => 'RAW MATERIALS',
                        'KUALITAS TERBAIK' => 'BEST QUALITY',
                        'BERDIRI SEJAK 1979' => 'ESTABLISHED SINCE 1979',
                        'TAHUN DEDIKASI' => 'YEARS OF DEDICATION',
                        'BACA SELENGKAPNYA' => 'READ MORE'
                    ];

                    // Taktik Paksa Huruf Kecil murni saat mencocokkan ke kamus (Anti-Meleset!)
                    \$cleanKey = strtolower(trim(\$text));
                    \$translated = \$dictionary[\$cleanKey] ?? \$text;
                    
                    if (\$text === strtoupper(\$text)) {
                        echo strtoupper(\$translated);
                    } else {
                        echo \$translated;
                    }
                }
            ?>";
        });
    }
}

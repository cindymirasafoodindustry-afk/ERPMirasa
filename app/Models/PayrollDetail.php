<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    use HasFactory;

    // 1. KUNCI 11 KOMPONEN GAJI IDAMANMU AGAR BISA DIINPUT SECARA MASSAL (ANTI-EROR)
    protected $fillable = [
        'employee_id',
        'bulan_tahun',
        'gaji_perhari',
        'honor_lembur_1',
        'honor_lembur_2',
        'tunjangan_masa_kerja',
        'tunjangan_jabatan',
        'insentif',
        'potongan_bpjs_kes',
        'potongan_bpjs_tk',
        'potongan_jam_kerja',
        'potongan_lainnya',
        'total_gaji_bersih'
    ];

    // 2. JEMBATAN GAIB HUBUNGKAN BALIK PER-ORANG KE DATA MASTER KARYAWAN (EMPLOYEE)
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}

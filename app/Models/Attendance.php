<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    // Mendaftarkan kolom yang diizinkan untuk diisi massal
    protected $fillable = [
        'employee_id',
        'tanggal',
        'kelompok_kerja_harian',
        'jam_masuk',
        'jam_pulang',
        'keterangan'
    ];

    // RELASI: Menghubungkan data absensi ke master data karyawan
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}

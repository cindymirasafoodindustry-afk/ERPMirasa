<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara spesifik
    protected $table = 'employees';

    // Mendaftarkan kolom yang boleh diisi massal
    protected $fillable = [
        'id_karyawan',
        'nama_karyawan',
        'kelompok',
        'shift',
        'status_karyawan',
        'tanggal_masuk_kerja',
        'bagian',
        'no_hp',
        'email',
        'no_rekening',
        'nama_bank',
    ];
     public function payrollDetails()
    {
        return $this->hasMany(PayrollDetail::class, 'employee_id');
    }
}

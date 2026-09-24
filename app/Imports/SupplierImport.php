<?php

namespace App\Imports;

use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class SupplierImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    private $rowsImported = 0;

    public function model(array $row)
    {
        // Bersihkan data kode di excel dari ketidaksengajaan user menulis prefix
        $cleanKode = preg_replace('/^(SUP|SKG)[-\s]*/i', '', trim($row['kode']));

        // Tentukan prefix berdasarkan jenis supplier yang ditulis di excel
        if (strcasecmp(trim($row['jenis_supplier']), 'Bahan Baku') === 0) {
            $prefix = 'SKG-';
        } else {
            $prefix = 'SUP-';
        }

        return new Supplier([
            'id_perusahaan'  => auth()->user()->id_perusahaan,
            'jenis_supplier' => ucwords(strtolower($row['jenis_supplier'])),
            'nama_supplier'  => $row['nama_supplier'],
            'kode'           => strtoupper($prefix . $cleanKode),
        ]);
    }

    public function rules(): array
    {
        $id_perusahaan = auth()->user()->id_perusahaan;

        return [
            'jenis_supplier' => 'required|in:Barang,Bahan Baku,BARANG,BAHAN BAKU,barang,bahan baku',
            'nama_supplier'  => [
                'required',
                Rule::unique('supplier', 'nama_supplier')->where('id_perusahaan', $id_perusahaan)
            ],
            'kode'           => [
                'required',
                Rule::unique('supplier', 'kode')->where('id_perusahaan', $id_perusahaan)
            ],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'jenis_supplier.in'      => 'Jenis Supplier harus diisi "Barang" atau "Bahan Baku".',
            'nama_supplier.unique'   => 'Nama supplier ":input" sudah terdaftar.',
            'kode.unique'            => 'Kode supplier ":input" sudah digunakan.',
        ];
    }

    public function getRowCount(): int
    {
        return $this->rowsImported;
    }
}

<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        // Kode satuan dipertahankan persis seperti Excel perusahaan.
        // unit_name hanya keterangan; yang ditandai "(konfirmasi)" perlu dicek ke perusahaan.
        $units = [
            'zak' => 'Zak',
            'm3' => 'Meter kubik',
            'btg' => 'Batang',
            'roll' => 'Roll',
            'lbr' => 'Lembar',
            'dus' => 'Dus',
            'ktk' => 'Kotak (konfirmasi)',
            'pcs' => 'Pieces',
            'klg' => 'Kaleng (konfirmasi)',
            'bh' => 'Buah',
            'unit' => 'Unit',
            'mtr' => 'Meter',
            'pail' => 'Pail',
            'drgn' => 'drgn (konfirmasi)',
            'set' => 'Set',
            'btl' => 'Botol (konfirmasi)',
        ];

        foreach ($units as $code => $name) {
            Unit::firstOrCreate(['unit_code' => $code], ['unit_name' => $name, 'status' => 'aktif']);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Cluster;
use App\Models\Material;
use App\Models\Unit;
use Illuminate\Database\Seeder;

/**
 * DATA DUMMY untuk uji coba saja. TIDAK dipanggil oleh DatabaseSeeder.
 * Jalankan manual: php artisan db:seed --class=DemoDataSeeder
 * Tidak ada transaksi palsu yang dibuat.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        Cluster::firstOrCreate(['cluster_code' => 'DUMMY-A'], ['cluster_name' => '[DUMMY] Cluster A']);
        Cluster::firstOrCreate(['cluster_code' => 'DUMMY-B'], ['cluster_name' => '[DUMMY] Cluster B']);

        $rows = [
            ['DUMMY-001', '[DUMMY] Semen 50 kg', 'Semen', 'zak'],
            ['DUMMY-002', '[DUMMY] Pasir', 'Pasir', 'm3'],
            ['DUMMY-003', '[DUMMY] Besi Ulir', 'Besi', 'btg'],
        ];

        foreach ($rows as [$code, $name, $cat, $unit]) {
            Material::firstOrCreate(['material_code' => $code], [
                'material_name' => $name,
                'category_id' => Category::where('category_name', $cat)->value('id'),
                'default_unit_id' => Unit::where('unit_code', $unit)->value('id'),
                'status' => 'aktif',
            ]);
        }
    }
}

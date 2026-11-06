<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Semen', 'Beton', 'Besi', 'Kayu', 'Hebel', 'Pasir', 'Pipa dan Knie',
            'Pipa Wetspek', 'Keramik', 'Cat', 'Sanitary', 'Dekson', 'Lain-lain',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['category_name' => $name], ['status' => 'aktif']);
        }
    }
}

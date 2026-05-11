<?php

namespace Database\Seeders;

use App\Models\Suppliers;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Nordic Timber Co.'],
            ['name' => 'Alpine CLT Solutions'],
            ['name' => 'MassivWood Ltd.'],
            ['name' => 'TimberStruct Inc.'],
            ['name' => 'EuroLam Systems'],
            ['name' => 'GreenLam Co.'],
        ];

        foreach ($suppliers as $supplier) {
            Suppliers::create($supplier);
        }
    }
}

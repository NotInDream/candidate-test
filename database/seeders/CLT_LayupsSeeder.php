<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\CLT_Layups;
use Illuminate\Database\Seeder;

class CLT_LayupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $layups = [
            ['supplier_id' => 1, 'name' => 'Standard 3-Ply Wall'],
            ['supplier_id' => 1, 'name' => 'Heavy Floor Panel'],
            ['supplier_id' => 1, 'name' => 'Custom Span Beam'],
            ['supplier_id' => 1, 'name' => 'Standard 3-Ply Floor'],
        ];

        foreach ($layups as $layup) {
            CLT_Layups::create($layup);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\CLT_Layers;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CLT_LayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $layers = [
            ['layup_id' => 1, 'layer_order' => 1, 'thickness' => 40, 'width' => 1200, 'angle' => 0],
            ['layup_id' => 1, 'layer_order' => 2, 'thickness' => 20, 'width' => 1200, 'angle' => 90],
            ['layup_id' => 1, 'layer_order' => 3, 'thickness' => 40, 'width' => 1200, 'angle' => 0],
            ['layup_id' => 1, 'layer_order' => 4, 'thickness' => 20, 'width' => 1200, 'angle' => 90],
            ['layup_id' => 1, 'layer_order' => 5, 'thickness' => 40, 'width' => 1200, 'angle' => 0],
        ];

        foreach ($layers as $layer) {
            CLT_Layers::create($layer);
        }
    }
}

<?php

namespace Database\Factories;

use App\Models\CLT_Layers;
use App\Models\CLT_Layups;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CLT_Layers>
 */
class CltLayersFactory extends Factory
{
    protected $model = CLT_Layers::class;

    public function definition(): array
    {
        static $order = 0;

        return [
            'layup_id'    => CLT_Layups::factory(),
            'layer_order' => ++$order,
            'thickness'   => fake()->numberBetween(20, 80),
            'width'       => fake()->numberBetween(80, 200),
            'angle'       => fake()->randomElement([0, 45, 90, -45]),
        ];
    }
}

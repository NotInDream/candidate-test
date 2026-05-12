<?php

namespace Database\Factories;

use App\Models\CLT_Layups;
use App\Models\Suppliers;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CLT_Layups>
 */
class CltLayupsFactory extends Factory
{
    protected $model = CLT_Layups::class;

    public function definition(): array
    {
        return [
            'supplier_id' => Suppliers::factory(),
            'name'        => fake()->unique()->words(3, true),
        ];
    }
}

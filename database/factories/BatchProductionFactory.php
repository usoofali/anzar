<?php

namespace Database\Factories;

use App\Models\BatchProduction;
use App\Models\ProductionBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BatchProduction>
 */
class BatchProductionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bags = fake()->numberBetween(50, 300);

        return [
            'production_batch_id' => ProductionBatch::factory(),
            'production_date' => fake()->date(),
            'packing_nylon_used' => $bags,
            'bags_produced' => $bags,
            'produced_by' => User::factory(),
            'remarks' => fake()->sentence(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\RawMaterialPurchase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RawMaterialPurchase>
 */
class RawMaterialPurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantityKg = fake()->randomFloat(2, 10, 50);
        $unitPrice = fake()->randomFloat(2, 1000, 2000);
        $packingNylonPieces = fake()->numberBetween(200, 1000);
        $packingUnitPrice = fake()->randomFloat(2, 10, 50);
        $totalCost = ($quantityKg * $unitPrice) + ($packingNylonPieces * $packingUnitPrice);

        return [
            'purchase_no' => 'RMP-'.fake()->unique()->numerify('###'),
            'supplier' => fake()->company(),
            'purchase_date' => fake()->date(),
            'quantity_kg' => $quantityKg,
            'unit_price' => $unitPrice,
            'packing_nylon_pieces' => $packingNylonPieces,
            'packing_unit_price' => $packingUnitPrice,
            'total_cost' => $totalCost,
            'remarks' => fake()->sentence(),
        ];
    }
}

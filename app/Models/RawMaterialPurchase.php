<?php

namespace App\Models;

use Database\Factories\RawMaterialPurchaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['purchase_no', 'supplier', 'purchase_date', 'quantity_kg', 'unit_price', 'total_cost', 'remarks'])]
class RawMaterialPurchase extends Model
{
    /** @use HasFactory<RawMaterialPurchaseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date:Y-m-d',
            'quantity_kg' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    public function productionBatch(): HasOne
    {
        return $this->hasOne(ProductionBatch::class);
    }
}

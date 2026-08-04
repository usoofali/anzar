<?php

namespace App\Models;

use Database\Factories\BatchProductionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['production_batch_id', 'production_date', 'production_time', 'packing_nylon_used', 'bags_produced', 'produced_by', 'remarks'])]
class BatchProduction extends Model
{
    /** @use HasFactory<BatchProductionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'production_date' => 'date:Y-m-d',
            'packing_nylon_used' => 'integer',
            'bags_produced' => 'integer',
        ];
    }

    public function productionBatch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class);
    }

    public function producedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'produced_by');
    }
}

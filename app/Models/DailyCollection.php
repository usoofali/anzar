<?php

namespace App\Models;

use Database\Factories\DailyCollectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['batch_id', 'collection_date', 'cash_amount', 'transfer_amount', 'recorded_by', 'remarks'])]
class DailyCollection extends Model
{
    /** @use HasFactory<DailyCollectionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'collection_date' => 'date:Y-m-d',
            'cash_amount' => 'decimal:2',
            'transfer_amount' => 'decimal:2',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getTotalCollectionAttribute(): float
    {
        return (float) ($this->cash_amount + $this->transfer_amount);
    }
}

<?php

namespace App\Models;

use Database\Factories\LeakageReturnFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['delivery_id', 'batch_id', 'customer_id', 'date', 'returned_pieces', 'replacement_issued', 'remarks'])]
class LeakageReturn extends Model
{
    /** @use HasFactory<LeakageReturnFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'returned_pieces' => 'integer',
            'replacement_issued' => 'integer',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

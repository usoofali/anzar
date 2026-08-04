<?php

namespace App\Models;

use Database\Factories\DeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['delivery_no', 'batch_id', 'customer_id', 'delivery_date', 'bags_delivered', 'unit_price', 'total_amount', 'paid_amount', 'delivered_by'])]
class Delivery extends Model
{
    /** @use HasFactory<DeliveryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date:Y-m-d',
            'bags_delivered' => 'integer',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function debts(): HasMany
    {
        return $this->hasMany(CustomerDebt::class, 'delivery_id');
    }

    public function leakageReturns(): HasMany
    {
        return $this->hasMany(LeakageReturn::class, 'delivery_id');
    }
}

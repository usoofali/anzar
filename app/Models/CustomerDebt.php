<?php

namespace App\Models;

use Database\Factories\CustomerDebtFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['delivery_id', 'customer_id', 'batch_id', 'outstanding_amount', 'status'])]
class CustomerDebt extends Model
{
    /** @use HasFactory<CustomerDebtFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'outstanding_amount' => 'decimal:2',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class, 'debt_id');
    }
}

<?php

namespace App\Models;

use Database\Factories\DebtPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['debt_id', 'customer_id', 'batch_id', 'delivery_id', 'payment_date', 'payment_method', 'amount', 'received_by'])]
class DebtPayment extends Model
{
    /** @use HasFactory<DebtPaymentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'payment_date' => 'date:Y-m-d',
            'amount' => 'decimal:2',
        ];
    }

    public function debt(): BelongsTo
    {
        return $this->belongsTo(CustomerDebt::class, 'debt_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}

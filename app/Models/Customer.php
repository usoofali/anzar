<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['shop_name', 'owner_name', 'phone', 'address', 'status'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(CustomerDebt::class);
    }

    public function debtPayments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function leakageReturns(): HasMany
    {
        return $this->hasMany(LeakageReturn::class);
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return (float) $this->debts()->where('status', 'open')->sum('outstanding_amount');
    }
}

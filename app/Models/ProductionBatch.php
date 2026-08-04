<?php

namespace App\Models;

use Database\Factories\ProductionBatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['batch_no', 'raw_material_purchase_id', 'production_date', 'quantity_used_kg', 'bags_produced', 'produced_by', 'status'])]
class ProductionBatch extends Model
{
    /** @use HasFactory<ProductionBatchFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'production_date' => 'date:Y-m-d',
            'quantity_used_kg' => 'decimal:2',
            'bags_produced' => 'integer',
        ];
    }

    public function rawMaterialPurchase(): BelongsTo
    {
        return $this->belongsTo(RawMaterialPurchase::class);
    }

    public function producedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'produced_by');
    }

    public function batchProductions(): HasMany
    {
        return $this->hasMany(BatchProduction::class);
    }

    public function updateAggregates(): void
    {
        $this->quantity_used_kg = (float) $this->batchProductions()->sum('nylon_used_kg');
        $this->bags_produced = (int) $this->batchProductions()->sum('bags_produced');
        $this->save();
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'batch_id');
    }

    public function dailyCollections(): HasMany
    {
        return $this->hasMany(DailyCollection::class, 'batch_id');
    }

    public function customerDebts(): HasMany
    {
        return $this->hasMany(CustomerDebt::class, 'batch_id');
    }

    public function debtPayments(): HasMany
    {
        return $this->hasMany(DebtPayment::class, 'batch_id');
    }

    public function leakageReturns(): HasMany
    {
        return $this->hasMany(LeakageReturn::class, 'batch_id');
    }

    // Calculated metrics
    public function getBagsDeliveredAttribute(): int
    {
        return (int) $this->deliveries()->sum('bags_delivered');
    }

    public function getRemainingStockAttribute(): int
    {
        return max(0, $this->bags_produced - $this->bags_delivered);
    }

    public function getExpectedRevenueAttribute(): float
    {
        return (float) $this->deliveries()->sum('total_amount');
    }

    public function getCashCollectedAttribute(): float
    {
        $directCash = (float) $this->dailyCollections()->sum('cash_amount');
        $debtCash = (float) $this->debtPayments()->where('payment_method', 'cash')->sum('amount');
        $upfrontCash = (float) $this->deliveries()->sum('paid_amount'); // upfront payment on deliveries

        return $directCash + $debtCash + $upfrontCash;
    }

    public function getTransferCollectedAttribute(): float
    {
        $directTransfer = (float) $this->dailyCollections()->sum('transfer_amount');
        $debtTransfer = (float) $this->debtPayments()->where('payment_method', 'transfer')->sum('amount');

        return $directTransfer + $debtTransfer;
    }

    public function getTotalCollectedAttribute(): float
    {
        return $this->cash_collected + $this->transfer_collected;
    }

    public function getOutstandingCreditAttribute(): float
    {
        return (float) $this->customerDebts()->where('status', 'open')->sum('outstanding_amount');
    }

    public function getReturnedPiecesAttribute(): int
    {
        return (int) $this->leakageReturns()->sum('returned_pieces');
    }

    public function getReplacementIssuedAttribute(): int
    {
        return (int) $this->leakageReturns()->sum('replacement_issued');
    }
}

<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['expense_date', 'category', 'description', 'amount', 'recorded_by'])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    public const CATEGORIES = [
        'Salaries',
        'Fuel',
        'Generator',
        'Electricity',
        'Vehicle Maintenance',
        'Factory Repairs',
        'Water Treatment Chemicals',
        'Office Expenses',
        'Miscellaneous',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date:Y-m-d',
            'amount' => 'decimal:2',
        ];
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('raw_material_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_no')->unique();
            $table->string('supplier')->nullable();
            $table->date('purchase_date');
            $table->decimal('quantity_kg', 10, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_material_purchases');
    }
};

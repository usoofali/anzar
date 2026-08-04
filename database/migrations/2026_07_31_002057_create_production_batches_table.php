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
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no')->unique();
            $table->foreignId('raw_material_purchase_id')->constrained('raw_material_purchases')->cascadeOnDelete();
            $table->date('production_date');
            $table->decimal('quantity_used_kg', 10, 2);
            $table->integer('bags_produced');
            $table->foreignId('produced_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active'); // active, closed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_batches');
    }
};

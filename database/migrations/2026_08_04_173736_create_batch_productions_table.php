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
        Schema::create('batch_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_batch_id')->constrained()->cascadeOnDelete();
            $table->date('production_date');
            $table->decimal('nylon_used_kg', 10, 2);
            $table->integer('packing_nylon_used')->default(0);
            $table->integer('bags_produced');
            $table->foreignId('produced_by')->constrained('users')->cascadeOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_productions');
    }
};

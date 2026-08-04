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
        Schema::create('daily_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('production_batches')->cascadeOnDelete();
            $table->date('collection_date');
            $table->decimal('cash_amount', 15, 2)->default(0);
            $table->decimal('transfer_amount', 15, 2)->default(0);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_collections');
    }
};

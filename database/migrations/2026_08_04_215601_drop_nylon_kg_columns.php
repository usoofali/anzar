<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batch_productions', function (Blueprint $table) {
            $table->dropColumn('nylon_used_kg');
        });

        Schema::table('production_batches', function (Blueprint $table) {
            $table->dropColumn('quantity_used_kg');
        });
    }

    public function down(): void
    {
        Schema::table('batch_productions', function (Blueprint $table) {
            $table->decimal('nylon_used_kg', 10, 2)->after('production_time');
        });

        Schema::table('production_batches', function (Blueprint $table) {
            $table->decimal('quantity_used_kg', 10, 2)->after('production_date');
        });
    }
};

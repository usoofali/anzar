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
        Schema::table('batch_productions', function (Blueprint $table) {
            $table->string('production_time')->default('morning')->after('production_date'); // morning, afternoon, evening, night
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_productions', function (Blueprint $table) {
            $table->dropColumn('production_time');
        });
    }
};

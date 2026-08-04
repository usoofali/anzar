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
        Schema::table('raw_material_purchases', function (Blueprint $table) {
            $table->integer('packing_nylon_pieces')->default(0)->after('unit_price');
            $table->decimal('packing_unit_price', 15, 2)->default(0)->after('packing_nylon_pieces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raw_material_purchases', function (Blueprint $table) {
            $table->dropColumn(['packing_nylon_pieces', 'packing_unit_price']);
        });
    }
};

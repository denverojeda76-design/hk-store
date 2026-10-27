
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('inventory_type', 20)->default('generic');
            $table->string('stock_unit', 30)->default('kg');
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->decimal('price_per_unit', 12, 2)->nullable();
            $table->decimal('low_stock_threshold_quantity', 12, 2)->default(20);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('quantity_unit', 30)->nullable();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->nullable();
            $table->string('unit', 30)->nullable();
        });

        // Copy existing product inventory values without changing legacy columns.
        DB::table('products')->update([
            'stock_quantity' => DB::raw('stock_kg'),
            'low_stock_threshold_quantity' => DB::raw('low_stock_threshold'),
        ]);

        // All products that existed before this migration are rice products.
        // This project currently has no generic products in the database.
        DB::table('products')->update([
            'inventory_type' => 'rice',
            'stock_unit' => 'kg',
        ]);

        // Preserve the original unit saved with each order item.
        DB::table('order_items')->whereIn('unit_type', ['kg', 'sack'])
            ->update([
                'quantity_unit' => DB::raw('unit_type'),
            ]);
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('quantity_unit');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'inventory_type',
                'stock_unit',
                'stock_quantity',
                'price_per_unit',
                'low_stock_threshold_quantity',
            ]);
        });
    }
};

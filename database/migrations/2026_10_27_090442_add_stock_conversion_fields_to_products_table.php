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
            $table->string('base_unit', 20)->default('piece');
            $table->decimal('stock_base_quantity', 14, 3)->default(0);
            $table->decimal('unit_conversion_factor', 14, 3)->default(1);
        });

        // Preserve existing generic inventory in its base unit.
        DB::table('products')
            ->where('inventory_type', 'generic')
            ->orderBy('id')
            ->get([
                'id',
                'stock_unit',
                'stock_quantity',
                'low_stock_threshold_quantity',
            ])
            ->each(function ($product) {
                $unit = strtolower(trim($product->stock_unit ?? 'piece'));
                $quantity = (float) $product->stock_quantity;
                $threshold = (float) ($product->low_stock_threshold_quantity ?? 0);

                if ($unit === 'dozen') {
                    $baseUnit = 'piece';
                    $baseQuantity = $quantity * 12;
                    $factor = 12;
                    $baseThreshold = $threshold * 12;
                } else {
                    $baseUnit = $unit;
                    $baseQuantity = $quantity;
                    $factor = 1;
                    $baseThreshold = $threshold;
                }

                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'base_unit' => $baseUnit,
                        'stock_base_quantity' => $baseQuantity,
                        'unit_conversion_factor' => $factor,
                        'low_stock_threshold_quantity' => $baseThreshold,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'base_unit',
                'stock_base_quantity',
                'unit_conversion_factor',
            ]);
        });
    }
};

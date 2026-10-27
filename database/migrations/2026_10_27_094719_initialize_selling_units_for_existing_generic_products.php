
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where('inventory_type', 'generic')
            ->orderBy('id')
            ->get([
                'id',
                'stock_unit',
                'price_per_unit',
                'cost_per_unit',
                'base_unit',
                'stock_base_quantity',
            ])
            ->each(function ($product) {
                $stockUnit = strtolower(trim($product->stock_unit ?: 'piece'));
                $baseUnit = strtolower(trim($product->base_unit ?: $stockUnit));

                $factor = $stockUnit === 'dozen' && $baseUnit === 'piece'
                    ? 12
                    : 1;

                DB::table('product_selling_units')->updateOrInsert(
                    [
                        'product_id' => $product->id,
                        'unit_name' => $stockUnit,
                    ],
                    [
                        'conversion_factor' => $factor,
                        'selling_price' => $product->price_per_unit ?? 0,
                        'cost_price' => $product->cost_per_unit,
                        'is_active' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                if (
                    strtolower(trim($product->base_unit ?? '')) === 'piece'
                    && $stockUnit === 'dozen'
                ) {
                    DB::table('product_selling_units')->updateOrInsert(
                        [
                            'product_id' => $product->id,
                            'unit_name' => 'piece',
                        ],
                        [
                            'conversion_factor' => 1,
                            'selling_price' => $factor > 0
                                ? round((float) ($product->price_per_unit ?? 0) / 12, 2)
                                : 0,
                            'cost_price' => $product->cost_per_unit !== null
                                ? round((float) $product->cost_per_unit / 12, 2)
                                : null,
                            'is_active' => true,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            });
    }

    public function down(): void
    {
        // Intentionally leave selling-unit data intact to avoid
        // accidentally deleting prices or units edited after migration.
    }
};

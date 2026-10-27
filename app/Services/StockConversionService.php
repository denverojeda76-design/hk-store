<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSellingUnit;
use Illuminate\Validation\ValidationException;

class StockConversionService
{
    public function toBaseQuantity(
        Product $product,
        float $quantity,
        ?ProductSellingUnit $sellingUnit = null
    ): float {
        $factor = $sellingUnit
            ? (float) $sellingUnit->conversion_factor
            : (float) $product->unit_conversion_factor;

        if ($quantity <= 0 || $factor <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Invalid quantity or conversion factor.',
            ]);
        }

        return round($quantity * $factor, 3);
    }

    public function remainingStock(Product $product): float
    {
        return (float) $product->stock_base_quantity;
    }

    public function deduct(
        Product $product,
        float $quantity,
        ?ProductSellingUnit $sellingUnit = null
    ): void {
        $baseQuantity = $this->toBaseQuantity(
            $product,
            $quantity,
            $sellingUnit
        );

        $available = $this->remainingStock($product);

        if ($baseQuantity > $available) {
            throw ValidationException::withMessages([
                'quantity' => "Insufficient stock for {$product->name}.",
            ]);
        }

        $product->stock_base_quantity = round(
            $available - $baseQuantity,
            3
        );

        // Keep the legacy stock quantity synchronized with the base stock.
        $baseUnit = strtolower(trim((string) $product->base_unit));
        $stockUnit = strtolower(trim((string) $product->stock_unit));
        $legacyFactor = (float) $product->unit_conversion_factor;

        if ($legacyFactor > 0 && $baseUnit === $stockUnit) {
            $product->stock_quantity = round(
                $product->stock_base_quantity,
                3
            );
        } elseif ($legacyFactor > 0) {
            $product->stock_quantity = round(
                $product->stock_base_quantity / $legacyFactor,
                3
            );
        }

        $product->save();
    }
}

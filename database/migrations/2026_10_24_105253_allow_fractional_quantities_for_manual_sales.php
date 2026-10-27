<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('stock_kg', 12, 2)->default(0)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->change();
            $table->decimal('quantity_kg', 12, 2)->change();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('quantity_kg', 12, 2)->change();
            $table->decimal('sacks', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        // Intentionally do not convert fractional values back to integers.
        // Doing so could lose inventory and transaction data.
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('rice_type')->nullable();
            $table->text('description')->nullable();

            $table->decimal('price_per_kg', 10, 2)->nullable();
            $table->decimal('price_per_sack', 10, 2)->nullable();
            $table->unsignedInteger('sack_weight_kg')->nullable();

            $table->unsignedInteger('stock_kg')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(20);

            $table->string('image')->nullable();
            $table->boolean('is_active')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

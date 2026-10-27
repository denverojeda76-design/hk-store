
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_per_sack', 10, 2)->nullable();
            $table->decimal('profit_percentage', 5, 2)->nullable();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->string('category', 100);
            $table->string('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('spent_at');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('spent_at');
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            // Snapshot so the record remains understandable if a product changes.
            $table->string('product_name');
            $table->string('movement_type', 30);
            $table->unsignedInteger('quantity_kg');
            $table->unsignedInteger('sacks')->default(0);
            $table->date('movement_date');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('movement_date');
            $table->index(['product_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('expenses');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'cost_per_sack',
                'profit_percentage',
            ]);
        });
    }
};

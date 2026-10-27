<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('label')->default('Home');
            $table->string('recipient_name');
            $table->string('phone', 30);
            $table->string('address_line');
            $table->string('barangay');
            $table->string('municipality')->default('Abuyog');
            $table->string('province')->default('Leyte');
            $table->string('postal_code', 10)->nullable();
            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            // Snapshot of the delivery/contact details at order time.
            $table->string('recipient_name');
            $table->string('phone', 30);
            $table->string('address_line')->nullable();
            $table->string('barangay')->nullable();
            $table->string('municipality')->default('Abuyog');
            $table->string('province')->default('Leyte');
            $table->string('postal_code', 10)->nullable();

            $table->string('delivery_method')->default('delivery');
            $table->string('payment_method')->default('cash');
            $table->string('payment_status')->default('pending');
            $table->string('status')->default('pending');

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->boolean('receipt_requested')->default(false);
            $table->string('receipt_file')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Preserve product details even if the product changes later.
            $table->string('product_name');
            $table->string('brand')->nullable();

            // "kg" or "sack"
            $table->string('unit_type');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('sack_weight_kg')->nullable();
            $table->unsignedInteger('quantity_kg');

            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);

            // For future profit reports; nullable until cost is recorded.
            $table->decimal('unit_cost', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customer_addresses');
    }
};

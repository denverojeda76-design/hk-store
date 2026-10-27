
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('method'); // cash or gcash
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');

            $table->string('reference_number')->nullable();
            $table->string('proof_path')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('rider_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('assigned_at')->nullable();

            $table->decimal('cash_collected_amount', 10, 2)
                ->nullable();

            $table->timestamp('cash_collected_at')->nullable();

            $table->text('delivery_notes')->nullable();

            $table->index(['rider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['rider_id', 'status']);
            $table->dropConstrainedForeignId('rider_id');
            $table->dropConstrainedForeignId('assigned_by');
            $table->dropColumn([
                'assigned_at',
                'cash_collected_amount',
                'cash_collected_at',
                'delivery_notes',
            ]);
        });

        Schema::dropIfExists('payments');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('qr_code')->nullable()->unique();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->string('delivery_reference')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropUnique(['delivery_reference']);
            $table->dropColumn('delivery_reference');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['qr_code']);
            $table->dropColumn('qr_code');
        });
    }
};

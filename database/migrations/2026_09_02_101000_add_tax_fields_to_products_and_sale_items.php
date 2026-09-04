<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0)->after('sale_price');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0)->after('unit_price');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('total', 12, 2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'tax_amount', 'total']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
        });
    }
};

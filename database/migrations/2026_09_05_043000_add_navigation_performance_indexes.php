<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['payment_method', 'credit_status', 'balance']);
        });
        Schema::table('products', fn (Blueprint $table) => $table->index(['active', 'stock']));
        Schema::table('customers', fn (Blueprint $table) => $table->index('name'));
        Schema::table('expenses', fn (Blueprint $table) => $table->index('expense_date'));
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['payment_method', 'credit_status', 'balance']);
        });
        Schema::table('products', fn (Blueprint $table) => $table->dropIndex(['active', 'stock']));
        Schema::table('customers', fn (Blueprint $table) => $table->dropIndex(['name']));
        Schema::table('expenses', fn (Blueprint $table) => $table->dropIndex(['expense_date']));
    }
};

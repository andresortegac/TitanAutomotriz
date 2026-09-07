<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('sale_price', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('products')->whereNull('sale_price')->update(['sale_price' => 0]);

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('sale_price', 12, 2)->change();
        });
    }
};

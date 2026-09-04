<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->enum('type', ['CODE128', 'EAN13', 'EAN8', 'UPCA'])->default('CODE128');
            $table->enum('source', ['internal', 'manufacturer', 'presentation', 'manual'])->default('internal');
            $table->boolean('is_primary')->default(false);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'active']);
            $table->index(['product_id', 'is_primary']);
        });

        DB::table('products')
            ->orderBy('id')
            ->select(['id'])
            ->chunkById(100, function ($products): void {
                foreach ($products as $product) {
                    DB::table('product_barcodes')->insert([
                        'product_id' => $product->id,
                        'code' => 'FER'.str_pad((string) $product->id, 8, '0', STR_PAD_LEFT),
                        'type' => 'CODE128',
                        'source' => 'internal',
                        'is_primary' => true,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_barcodes');
    }
};

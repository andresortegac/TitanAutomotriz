<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['efectivo', 'transferencia', 'tarjeta', 'otro'])->default('efectivo');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'payment_date']);
            $table->index('sale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_payments');
    }
};

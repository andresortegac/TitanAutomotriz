<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('expense_date');
            $table->string('category');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['efectivo', 'transferencia', 'tarjeta', 'otro'])->default('efectivo');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['expense_date', 'category']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

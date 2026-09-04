<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sales MODIFY payment_method ENUM('efectivo', 'transferencia', 'tarjeta', 'mixto', 'credito') NOT NULL DEFAULT 'efectivo'");

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('balance', 12, 2)->default(0)->after('change_amount');
            $table->date('credit_due_date')->nullable()->after('balance');
            $table->enum('credit_status', ['sin_credito', 'pendiente', 'abonado', 'pagado', 'vencido'])->default('sin_credito')->after('credit_due_date');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['balance', 'credit_due_date', 'credit_status']);
        });

        DB::statement("ALTER TABLE sales MODIFY payment_method ENUM('efectivo', 'transferencia', 'tarjeta', 'mixto') NOT NULL DEFAULT 'efectivo'");
    }
};

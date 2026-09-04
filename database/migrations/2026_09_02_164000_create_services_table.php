<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        DB::table('services')->insert([
            [
                'code' => 'SER-000001',
                'name' => 'Remachadora de frenos',
                'description' => 'Servicio de remachadora de frenos.',
                'sale_price' => 0,
                'tax_rate' => 0,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SER-000002',
                'name' => 'Taladro',
                'description' => 'Servicio de taladro.',
                'sale_price' => 0,
                'tax_rate' => 0,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SER-000003',
                'name' => 'Grafadora de mangueras',
                'description' => 'Servicio de grafadora de mangueras.',
                'sale_price' => 0,
                'tax_rate' => 0,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

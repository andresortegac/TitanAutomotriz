<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('identification_document_code', 5)->default('13')->after('document');
            $table->string('dv', 2)->nullable()->after('identification_document_code');
            $table->string('legal_organization_code', 2)->default('2')->after('dv');
            $table->string('trade_name')->nullable()->after('name');
            $table->string('tribute_code', 20)->default('ZZ')->after('legal_organization_code');
            $table->json('responsibilities')->nullable()->after('tribute_code');
            $table->string('country_code', 2)->default('CO')->after('address');
            $table->string('municipality_code', 10)->nullable()->after('country_code');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->enum('invoice_type', ['normal', 'electronica'])->default('normal')->after('invoice_number');
            $table->string('electronic_number')->nullable()->after('invoice_type');
            $table->string('electronic_cufe')->nullable()->after('electronic_number');
            $table->text('electronic_qr_url')->nullable()->after('electronic_cufe');
            $table->string('electronic_status')->nullable()->after('electronic_qr_url');
            $table->json('electronic_response')->nullable()->after('electronic_status');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['invoice_type', 'electronic_number', 'electronic_cufe', 'electronic_qr_url', 'electronic_status', 'electronic_response']);
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['identification_document_code', 'dv', 'legal_organization_code', 'trade_name', 'tribute_code', 'responsibilities', 'country_code', 'municipality_code']);
        });
    }
};

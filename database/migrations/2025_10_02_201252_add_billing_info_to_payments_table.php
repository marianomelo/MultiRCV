<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('billing_rut')->nullable()->after('payer_email');
            $table->string('billing_name')->nullable()->after('billing_rut');
            $table->string('billing_type')->nullable()->after('billing_name'); // empresa, persona_natural
            $table->string('document_type')->nullable()->after('billing_type'); // boleta, factura
            $table->text('billing_address')->nullable()->after('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['billing_rut', 'billing_name', 'billing_type', 'document_type', 'billing_address']);
        });
    }
};

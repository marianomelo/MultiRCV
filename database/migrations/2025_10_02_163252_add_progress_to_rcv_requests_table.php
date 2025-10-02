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
        Schema::table('rcv_requests', function (Blueprint $table) {
            $table->integer('total_companies')->default(0)->after('company_ids');
            $table->integer('processed_companies')->default(0)->after('total_companies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rcv_requests', function (Blueprint $table) {
            $table->dropColumn(['total_companies', 'processed_companies']);
        });
    }
};

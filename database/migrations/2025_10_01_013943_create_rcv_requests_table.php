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
        Schema::create('rcv_requests', function (Blueprint $table) {
            $table->id();
            $table->string('period'); // e.g., "2021-01"
            $table->string('type'); // "compra" or "venta"
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->json('company_ids'); // Array of company IDs
            $table->json('response_data')->nullable(); // API response data
            $table->text('error_message')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rcv_requests');
    }
};

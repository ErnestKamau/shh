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
        if (Schema::hasTable('method_validation_requests')) {
            return;
        }
        Schema::create('method_validation_requests', function (Blueprint $table) {
            $table->uuid('id');
            $table->unsignedBigInteger('method_id');
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('lab_assigned')->nullable();
            $table->enum('status', ['pending', 'accepted', 'in_progress', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->json('validation_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('method_validation_requests');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('method_validation_requests')) {
            return;
        }

        Schema::create('method_validation_requests', function (Blueprint $table) {
            $table->id();
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

            $table->foreign('method_id')->references('id')->on('analysis_methods')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('lab_assigned')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('method_validation_requests')) {
            return;
        }

        Schema::dropIfExists('method_validation_requests');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_form_instance_intrays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('submission_form_instance_id');
            $table->uuid('from_user_id')->nullable();
            $table->uuid('to_user_id');
            $table->uuid('assigned_by_user_id');
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->uuid('completed_by')->nullable();
            $table->timestamps();

            $table->foreign('submission_form_instance_id')
                ->references('id')
                ->on('submission_form_instances')
                ->cascadeOnDelete();
            $table->foreign('from_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
            $table->foreign('to_user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->foreign('assigned_by_user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->foreign('completed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['submission_form_instance_id', 'created_at']);
            $table->index(['to_user_id', 'status']);
            $table->index(['submission_form_instance_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_form_instance_intrays');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills_training_session_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->uuid('training_planner_detail_id');
            $table->uuid('user_id');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('marked_present_at')->nullable();
            $table->uuid('confirmed_by_user_id')->nullable();
            $table->uuid('marked_by_user_id')->nullable();
            $table->unique(['training_planner_detail_id', 'user_id'], 'skills_training_attendance_unique');
        });

        Schema::create('skills_training_session_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->uuid('training_planner_detail_id');
            $table->uuid('uploaded_by');
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('skills_training_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->uuid('training_planner_detail_id');
            $table->uuid('user_id');
            $table->uuid('competency_id')->nullable();
            $table->uuid('evaluator_id');
            $table->uuid('proposed_proficiency_id')->nullable();
            $table->text('score_notes')->nullable();
            $table->string('status', 32)->default('draft');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->uuid('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
        });

        Schema::create('skills_induction_checklist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->uuid('inventory_location_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skills_induction_checklist_items');
        Schema::dropIfExists('skills_training_evaluations');
        Schema::dropIfExists('skills_training_session_materials');
        Schema::dropIfExists('skills_training_session_attendance');
    }
};

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
        if (Schema::hasTable('submission_form_instances')) {
            return;
        }
        Schema::create('submission_form_instances', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_id');
            $table->string('form_number', 100)->nullable()->index('idx_submission_form_instances_form_number_ea5bf122');
            $table->string('title')->nullable();
            $table->uuid('submitted_by')->index('idx_submission_form_instances_submitted_by_e99bc9dc');
            $table->enum('status', ['draft', 'submitted', 'in_review', 'approved', 'rejected', 'cancelled'])->default('draft')->index('idx_submission_form_instances_draft_0d3f83b1');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->date('due_date')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->uuid('reviewed_by')->nullable()->index('idx_submission_form_instances_reviewed_by_2d871fc3');
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->integer('sequence_number')->nullable();

            $table->index(['submission_form_id', 'status'], 'idx_submission_form_instances_submission_form_id_statu_7b8bc0e1');
            $table->unique(['form_number']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submission_form_instances');
    }
};

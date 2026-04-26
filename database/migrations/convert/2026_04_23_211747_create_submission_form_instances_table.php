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
        Schema::create('submission_form_instances', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('submission_form_id');
            $table->string('form_number', 100)->nullable()->index('sf_instances_form_number_idx');
            $table->string('title')->nullable();
            $table->uuid('submitted_by')->index('sf_instances_submitted_by_idx');
            $table->enum('status', ['draft', 'submitted', 'in_review', 'approved', 'rejected', 'cancelled'])->default('draft')->index('sf_instances_status_idx');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->date('due_date')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->uuid('reviewed_by')->nullable()->index('sf_instances_reviewed_by_foreign');
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->integer('sequence_number')->nullable();

            $table->index(['submission_form_id', 'status'], 'sf_instances_form_status_idx');
            $table->unique(['form_number']);
            $table->foreign(['submission_form_id'], 'sf_instances_form_id_foreign')->references(['id'])->on('submission_forms')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['reviewed_by'], 'sf_instances_reviewed_by_foreign')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['submitted_by'], 'sf_instances_submitted_by_foreign')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
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

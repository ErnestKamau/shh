<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionFormInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_form_instances', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('submission_form_id');
            $table->string('form_number', 100)->unique();
            $table->string('title')->nullable(); // User-defined title for the instance
            $table->bigInteger('submitted_by');
            $table->enum('status', ['draft', 'submitted', 'in_review', 'approved', 'rejected', 'cancelled'])->default('draft');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->date('due_date')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->bigInteger('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('submission_form_id', 'sf_instances_form_id_foreign')->references('id')->on('submission_forms');
            $table->foreign('submitted_by', 'sf_instances_submitted_by_foreign')->references('id')->on('users');
            $table->foreign('reviewed_by', 'sf_instances_reviewed_by_foreign')->references('id')->on('users');

            // Indexes for performance
            $table->index('form_number', 'sf_instances_form_number_idx');
            $table->index('status', 'sf_instances_status_idx');
            $table->index('submitted_by', 'sf_instances_submitted_by_idx');
            $table->index(['submission_form_id', 'status'], 'sf_instances_form_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_form_instances');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_workflow_forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('form_type', 64)->index();
            $table->string('sample_header_id', 64)->nullable()->index();
            $table->uuid('sample_submission_request_id')->nullable()->index();
            $table->uuid('submission_form_instance_id')->nullable()->index();
            $table->string('batch_code', 120)->nullable()->index();
            $table->string('request_reference', 180)->nullable();
            $table->json('payload')->nullable();
            $table->string('pdf_path', 255)->nullable();
            $table->uuid('created_by')->nullable()->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['form_type', 'sample_submission_request_id'], 'rwf_type_submission_request_idx');
            $table->index(['form_type', 'submission_form_instance_id'], 'rwf_type_form_instance_idx');
            $table->index(['form_type', 'sample_header_id'], 'rwf_type_sample_header_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_workflow_forms');
    }
};

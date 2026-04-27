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
        Schema::create('supporting_document_instances', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supporting_document_template_id')->index('sdoc_instances_template_fk');
            $table->unsignedInteger('template_version');
            $table->uuid('sample_submission_request_id')->nullable()->index('idx_supporting_document_instances_sample_submission_re_1bb7e9cf');
            $table->uuid('sample_header_id')->index('idx_supporting_document_instances_sample_header_id_0c3bb42e');
            $table->string('status', 30)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_supporting_document_instances_created_by_055deaeb');
            $table->timestamps();

            $table->index(['sample_header_id', 'status'], 'sdoc_instances_sample_status_idx');
            $table->foreign(['sample_submission_request_id'], 'sdoc_instances_submission_request_fk')->references(['id'])->on('sample_submission_requests')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supporting_document_template_id'], 'sdoc_instances_template_fk')->references(['id'])->on('supporting_document_templates')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_supporting_document_instances_sample_header_id_09f8aa51')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_supporting_document_instances_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supporting_document_instances');
    }
};

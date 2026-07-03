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
        if (Schema::hasTable('supporting_document_instances')) {
            return;
        }
        Schema::create('supporting_document_instances', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supporting_document_template_id')->index('idx_supporting_document_instances_supporting_document_b09f8591');
            $table->unsignedInteger('template_version');
            $table->uuid('sample_submission_request_id')->nullable()->index('idx_supporting_document_instances_sample_submission_re_e854953e');
            $table->uuid('sample_header_id')->index('idx_supporting_document_instances_sample_header_id_71e6b6d1');
            $table->string('status', 30)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_supporting_document_instances_created_by_2ecff9f2');
            $table->timestamps();

            $table->index(['sample_header_id', 'status'], 'idx_supporting_document_instances_sample_header_id_sta_f7b804ad');

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

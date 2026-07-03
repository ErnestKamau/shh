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
        if (Schema::hasTable('sample_submission_request_supporting_document_templates')) {
            return;
        }
        Schema::create('sample_submission_request_supporting_document_templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_submission_request_id');
            $table->uuid('supporting_document_template_id')->index('idx_sample_submission_request_supporting_document_temp_dfb7549b');
            $table->timestamps();

            $table->unique(['sample_submission_request_id', 'supporting_document_template_id'], 'ssr_sdoc_templates_unique');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_submission_request_supporting_document_templates');
    }
};

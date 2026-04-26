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
        Schema::create('sample_submission_request_supporting_document_templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('sample_submission_request_id');
            $table->uuid('supporting_document_template_id')->index('ssr_sdoc_templates_template_fk');
            $table->timestamps();

            $table->unique(['sample_submission_request_id', 'supporting_document_template_id'], 'ssr_sdoc_templates_unique');
            $table->foreign(['sample_submission_request_id'], 'ssr_sdoc_templates_request_fk')->references(['id'])->on('sample_submission_requests')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['supporting_document_template_id'], 'ssr_sdoc_templates_template_fk')->references(['id'])->on('supporting_document_templates')->onUpdate('no action')->onDelete('cascade');
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

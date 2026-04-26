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
        Schema::create('certificate_template_reports', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('certificate_template_id')->index('idx_certificate_template_reports_certificate_template_c79cbc98');
            $table->uuid('submission_form_instance_id')->index('idx_certificate_template_reports_submission_form_insta_fc5fe0de');
            $table->uuid('generated_by')->index('idx_certificate_template_reports_generated_by_3706875c');
            $table->string('file_path');
            $table->timestamp('generated_at')->useCurrentOnUpdate()->useCurrent()->index('idx_certificate_template_reports_generated_at_5bd26d17');
            $table->enum('status', ['pending', 'generating', 'completed', 'failed'])->default('pending')->index('idx_certificate_template_reports_pending_44994d18');
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->foreign(['generated_by'], 'ct_reports_generated_by_fk')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['submission_form_instance_id'], 'ct_reports_instance_fk')->references(['id'])->on('submission_form_instances')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['certificate_template_id'], 'ct_reports_template_fk')->references(['id'])->on('certificate_templates')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_template_reports');
    }
};

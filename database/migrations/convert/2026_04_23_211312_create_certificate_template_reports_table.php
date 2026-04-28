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
            $table->uuid('certificate_template_id')->index('idx_certificate_template_reports_certificate_template_4269da2a');
            $table->uuid('submission_form_instance_id')->index('idx_certificate_template_reports_submission_form_insta_221baf31');
            $table->uuid('generated_by')->index('idx_certificate_template_reports_generated_by_d7546de5');
            $table->string('file_path');
            $table->timestamp('generated_at')->useCurrentOnUpdate()->useCurrent()->index('idx_certificate_template_reports_generated_at_ca33bc6a');
            $table->enum('status', ['pending', 'generating', 'completed', 'failed'])->default('pending')->index('idx_certificate_template_reports_pending_d4a33915');
            $table->text('error_message')->nullable();
            $table->timestamps();
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

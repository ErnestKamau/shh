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
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(false)->index('idx_certificate_templates_is_published_a4ec0141');
            $table->boolean('is_active')->default(true)->index('idx_certificate_templates_is_active_ca452fec');
            $table->string('version')->default('1.0');
            $table->longText('page_settings')->nullable();
            $table->longText('header_settings')->nullable();
            $table->longText('footer_settings')->nullable();
            $table->uuid('submission_form_id')->index('idx_certificate_templates_submission_form_id_f23488cf');
            $table->uuid('created_by')->index('idx_certificate_templates_created_by_d66341c3');
            $table->timestamps();

            $table->index(['submission_form_id', 'is_published', 'is_active'], 'idx_certificate_templates_submission_form_id_is_publis_934497fe');
            $table->index(['is_published', 'is_active'], 'idx_certificate_templates_is_published_is_active_fa9c187d');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};

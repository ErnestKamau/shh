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
        if (Schema::hasTable('audit_email_templates')) {
            return;
        }
        Schema::create('audit_email_templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('template_code');
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->longText('variables')->nullable();
            $table->unsignedBigInteger('notification_type_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable()->index('idx_audit_email_templates_company_id_130dbc68');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active'], 'idx_audit_email_templates_company_id_is_active_ce174f4e');
            $table->unique(['company_id', 'template_code']);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_email_templates');
    }
};

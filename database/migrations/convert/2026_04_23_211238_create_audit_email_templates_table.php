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
        Schema::create('audit_email_templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('template_code');
            $table->string('name');
            $table->string('subject');
            $table->longText('body');
            $table->longText('variables')->nullable();
            $table->unsignedBigInteger('notification_type_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->default(0)->index('idx_audit_email_templates_company_id_3652a953');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active'], 'idx_audit_email_templates_company_id_is_active_81f3f799');
            $table->unique(['company_id', 'template_code']);
            $table->foreign(['company_id'], 'fk_audit_email_templates_company_id_243ca6fd')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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

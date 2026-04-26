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
        Schema::create('audit_module_findings', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('finding_number')->index('idx_audit_module_findings_finding_number_139fedd4');
            $table->uuid('audit_id');
            $table->uuid('finding_category_id')->nullable()->index('audit_module_findings_finding_category_id_foreign');
            $table->string('finding_category_name')->nullable();
            $table->string('iso_clause')->nullable();
            $table->string('sop_reference')->nullable();
            $table->text('requirement')->nullable();
            $table->text('observation')->nullable();
            $table->text('objective_evidence')->nullable();
            $table->uuid('risk_level_id')->nullable()->index('audit_module_findings_risk_level_id_foreign');
            $table->string('risk_level_name')->nullable();
            $table->string('responsible_person')->nullable();
            $table->unsignedBigInteger('responsible_user_id')->nullable();
            $table->date('response_due_date')->nullable();
            $table->uuid('status_id')->nullable()->index('audit_module_findings_status_id_foreign');
            $table->string('status_name')->nullable();
            $table->integer('order_index')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['audit_id', 'status_id'], 'idx_audit_module_findings_audit_id_status_id_008b62d5');
            $table->unique(['finding_number']);
            $table->foreign(['audit_id'], 'fk_audit_module_findings_audit_id_c06ca483')->references(['id'])->on('iso_audits')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['finding_category_id'], 'fk_audit_module_findings_finding_category_id_6d005db6')->references(['id'])->on('finding_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['risk_level_id'], 'fk_audit_module_findings_risk_level_id_e0f3c164')->references(['id'])->on('risk_levels')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_audit_module_findings_status_id_89f8ae96')->references(['id'])->on('finding_statuses')->onUpdate('no action')->onDelete('set null');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_module_findings');
    }
};

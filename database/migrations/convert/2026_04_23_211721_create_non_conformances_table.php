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
        Schema::create('non_conformances', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('nc_number')->index('idx_non_conformances_nc_number_c11ad669');
            $table->uuid('origin_id')->nullable()->index('non_conformances_origin_id_foreign');
            $table->string('origin_name')->nullable();
            $table->uuid('audit_id')->nullable()->index('non_conformances_audit_id_foreign');
            $table->uuid('audit_finding_id')->nullable()->index('non_conformances_audit_finding_id_foreign');
            $table->unsignedBigInteger('sample_id')->nullable();
            $table->string('sample_reference')->nullable();
            $table->uuid('equipment_id')->nullable()->index('idx_non_conformances_equipment_id_3585063f');
            $table->string('equipment_reference')->nullable();
            $table->unsignedBigInteger('method_id')->nullable();
            $table->string('method_reference')->nullable();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->string('personnel_reference')->nullable();
            $table->string('sop_reference')->nullable();
            $table->string('title');
            $table->text('description');
            $table->string('iso_clause_violated')->nullable();
            $table->date('date_identified')->index('idx_non_conformances_date_identified_27addd7c');
            $table->string('identified_by')->nullable();
            $table->unsignedBigInteger('identified_by_user_id')->nullable();
            $table->string('department')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->uuid('risk_level_id')->nullable()->index('non_conformances_risk_level_id_foreign');
            $table->string('risk_level_name')->nullable();
            $table->integer('severity_score')->nullable();
            $table->uuid('severity_scale_id')->nullable()->index('non_conformances_severity_scale_id_foreign');
            $table->integer('likelihood_score')->nullable();
            $table->uuid('likelihood_scale_id')->nullable()->index('non_conformances_likelihood_scale_id_foreign');
            $table->text('risk_assessment_notes')->nullable();
            $table->text('immediate_correction')->nullable();
            $table->date('immediate_correction_date')->nullable();
            $table->string('immediate_correction_by')->nullable();
            $table->uuid('status_id')->nullable();
            $table->string('status_name')->nullable();
            $table->date('target_closure_date')->nullable();
            $table->date('actual_closure_date')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->text('closure_notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->default(0)->index('idx_non_conformances_company_id_dddcc827');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['nc_number']);
            $table->index(['status_id', 'company_id'], 'idx_non_conformances_status_id_company_id_c14e1716');
            $table->foreign(['audit_finding_id'], 'fk_non_conformances_audit_finding_id_2eac81eb')->references(['id'])->on('audit_module_findings')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['audit_id'], 'fk_non_conformances_audit_id_3b51564e')->references(['id'])->on('iso_audits')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['likelihood_scale_id'], 'fk_non_conformances_likelihood_scale_id_f306b6b8')->references(['id'])->on('likelihood_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['origin_id'], 'fk_non_conformances_origin_id_693b6bf0')->references(['id'])->on('nc_origins')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['risk_level_id'], 'fk_non_conformances_risk_level_id_93ea508a')->references(['id'])->on('risk_levels')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['severity_scale_id'], 'fk_non_conformances_severity_scale_id_79a8b53e')->references(['id'])->on('severity_scales')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['status_id'], 'fk_non_conformances_status_id_5c054baa')->references(['id'])->on('nc_statuses')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_non_conformances_company_id_85b6a89e')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['equipment_id'], 'fk_non_conformances_equipment_id_d579c041')->references(['id'])->on('equipment')->onUpdate('no action')->onDelete('set null');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_conformances');
    }
};

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
            $table->string('nc_number')->index('idx_non_conformances_nc_number_1bd63198');
            $table->uuid('origin_id')->nullable()->index('idx_non_conformances_origin_id_721e8397');
            $table->string('origin_name')->nullable();
            $table->uuid('audit_id')->nullable()->index('idx_non_conformances_audit_id_35611c58');
            $table->uuid('audit_finding_id')->nullable()->index('idx_non_conformances_audit_finding_id_c9406724');
            $table->unsignedBigInteger('sample_id')->nullable();
            $table->string('sample_reference')->nullable();
            $table->uuid('equipment_id')->nullable()->index('idx_non_conformances_equipment_id_9fdf5761');
            $table->string('equipment_reference')->nullable();
            $table->unsignedBigInteger('method_id')->nullable();
            $table->string('method_reference')->nullable();
            $table->unsignedBigInteger('personnel_id')->nullable();
            $table->string('personnel_reference')->nullable();
            $table->string('sop_reference')->nullable();
            $table->string('title');
            $table->text('description');
            $table->string('iso_clause_violated')->nullable();
            $table->date('date_identified')->index('idx_non_conformances_date_identified_13ebe329');
            $table->string('identified_by')->nullable();
            $table->unsignedBigInteger('identified_by_user_id')->nullable();
            $table->string('department')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->uuid('risk_level_id')->nullable()->index('idx_non_conformances_risk_level_id_fcc359c5');
            $table->string('risk_level_name')->nullable();
            $table->integer('severity_score')->nullable();
            $table->uuid('severity_scale_id')->nullable()->index('idx_non_conformances_severity_scale_id_639fa205');
            $table->integer('likelihood_score')->nullable();
            $table->uuid('likelihood_scale_id')->nullable()->index('idx_non_conformances_likelihood_scale_id_3458fd41');
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
            $table->uuid('created_by')->nullable()->index('idx_non_conformances_created_by_e606b2bf');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_non_conformances_company_id_43281b0b');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['nc_number']);
            $table->index(['status_id', 'company_id'], 'idx_non_conformances_status_id_company_id_81a18088');

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

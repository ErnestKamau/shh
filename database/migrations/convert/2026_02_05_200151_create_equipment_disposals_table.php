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
        Schema::create('equipment_disposals', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('equipment_id')->index('idx_equipment_disposals_equipment_id_b80253d3');
            $table->uuid('evaluation_id')->nullable()->index('idx_equipment_disposals_evaluation_id_13b16cfb');
            $table->text('justification');
            $table->enum('proposed_method', ['scrap', 'donation', 'auction', 'recycling', 'destruction'])->nullable();
            $table->enum('risk_level', ['Low', 'Medium', 'High', 'Critical'])->nullable();
            $table->string('regulatory_category')->nullable();
            $table->uuid('requested_by')->index('idx_equipment_disposals_requested_by_38216b72');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'executed', 'closed'])->default('draft')->index('idx_equipment_disposals_draft_2dda4c0a');
            $table->string('final_disposal_method')->nullable();
            $table->date('disposal_date')->nullable();
            $table->uuid('executed_by')->nullable()->index('idx_equipment_disposals_executed_by_30e3afda');
            $table->uuid('witness_id')->nullable()->index('idx_equipment_disposals_witness_id_878f9ef7');
            $table->string('transport_company')->nullable();
            $table->text('transport_details')->nullable();
            $table->string('waste_handler_company')->nullable();
            $table->string('waste_handler_license')->nullable();
            $table->string('disposal_certificate_path')->nullable();
            $table->longText('compliance_checklist_json')->nullable();
            $table->longText('decommissioning_checklist_json')->nullable();
            $table->date('decommissioning_date')->nullable();
            $table->uuid('decommissioned_by')->nullable()->index('idx_equipment_disposals_decommissioned_by_164647f3');
            $table->boolean('equipment_labeled')->default(false);
            $table->string('label_photo_path')->nullable();
            $table->boolean('removed_from_calibration_schedule')->default(false);
            $table->boolean('removed_from_maintenance_schedule')->default(false);
            $table->boolean('utilities_disconnected')->default(false);
            $table->boolean('data_wiped')->default(false);
            $table->boolean('storage_devices_removed')->default(false);
            $table->string('sha_hash', 64)->nullable();
            $table->uuid('company_id')->index('idx_equipment_disposals_company_id_20578097');
            $table->string('pdf_report_path')->nullable();
            $table->timestamp('created_at')->nullable()->index('idx_equipment_disposals_created_at_ba496eef');
            $table->timestamp('updated_at')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposals');
    }
};

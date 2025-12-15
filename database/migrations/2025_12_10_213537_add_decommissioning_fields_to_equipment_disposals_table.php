<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDecommissioningFieldsToEquipmentDisposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('equipment_disposals', function (Blueprint $table) {
            // Decommissioning fields
            $table->json('decommissioning_checklist_json')->nullable()->after('compliance_checklist_json');
            $table->date('decommissioning_date')->nullable()->after('decommissioning_checklist_json');
            $table->bigInteger('decommissioned_by')->nullable()->after('decommissioning_date');
            $table->boolean('equipment_labeled')->default(false)->after('decommissioned_by');
            $table->string('label_photo_path')->nullable()->after('equipment_labeled');
            $table->boolean('removed_from_calibration_schedule')->default(false)->after('label_photo_path');
            $table->boolean('removed_from_maintenance_schedule')->default(false)->after('removed_from_calibration_schedule');
            $table->boolean('utilities_disconnected')->default(false)->after('removed_from_maintenance_schedule');
            $table->boolean('data_wiped')->default(false)->after('utilities_disconnected');
            $table->boolean('storage_devices_removed')->default(false)->after('data_wiped');
            
            // Additional execution fields
            $table->string('transport_company')->nullable()->after('witness_id');
            $table->text('transport_details')->nullable()->after('transport_company');
            $table->string('waste_handler_company')->nullable()->after('transport_details');
            $table->string('waste_handler_license')->nullable()->after('waste_handler_company');
            $table->string('disposal_certificate_path')->nullable()->after('waste_handler_license');
            
            // Evaluation reference
            $table->bigInteger('evaluation_id')->nullable()->after('equipment_id');
            
            // Foreign key for decommissioned_by
            $table->foreign('decommissioned_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('evaluation_id')->references('id')->on('equipment_evaluations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('equipment_disposals', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['decommissioned_by']);
            $table->dropForeign(['evaluation_id']);
            
            // Drop columns
            $table->dropColumn([
                'decommissioning_checklist_json',
                'decommissioning_date',
                'decommissioned_by',
                'equipment_labeled',
                'label_photo_path',
                'removed_from_calibration_schedule',
                'removed_from_maintenance_schedule',
                'utilities_disconnected',
                'data_wiped',
                'storage_devices_removed',
                'transport_company',
                'transport_details',
                'waste_handler_company',
                'waste_handler_license',
                'disposal_certificate_path',
                'evaluation_id',
            ]);
        });
    }
}

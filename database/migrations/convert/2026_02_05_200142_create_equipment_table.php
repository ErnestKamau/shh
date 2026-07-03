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
        if (Schema::hasTable('equipment')) {
            return;
        }
        Schema::create('equipment', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('equipment_number');
            $table->text('description');
            $table->string('picture')->nullable();
            $table->string('make');
            $table->string('model');
            $table->date('date_purchased');
            $table->integer('maintainance_days');
            $table->integer('maintainance_notification_in_days')->default(0);
            $table->integer('calibration_days');
            $table->integer('calibration_notification_in_days')->default(0);
            $table->uuid('company_id')->index('idx_equipment_company_id_4c865e92');
            $table->integer('status_id')->nullable();
            $table->string('asset_code')->nullable();
            $table->string('barcode_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('condition')->nullable();
            $table->integer('assigned_employee_id')->nullable();
            $table->string('assigned_department')->nullable();
            $table->string('market_value')->nullable();
            $table->date('warranty_date')->nullable();
            $table->uuid('inventory_item_id')->nullable()->index('idx_equipment_inventory_item_id_a094a348');
            $table->timestamps();
            $table->string('asset_description')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('status')->nullable();
            $table->integer('employee_dispose_id')->nullable();
            $table->date('dispose_date')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_disposal')->default(false);
            $table->boolean('active')->default(false);
            $table->boolean('requires_daily_log')->default(false);
            $table->string('daily_log_value_type')->nullable();
            $table->string('daily_log_nature')->nullable();
            $table->unsignedTinyInteger('daily_log_tolerance')->nullable();
            $table->string('daily_log_expected_value')->nullable();
            $table->decimal('daily_log_expected_min', 10, 4)->nullable();
            $table->decimal('daily_log_expected_max', 10, 4)->nullable();
            $table->string('daily_log_reporting_unit')->nullable();
            $table->unsignedTinyInteger('daily_log_frequency')->default(1);
            $table->string('daily_log_time_interval')->nullable();
            $table->uuid('asset_type_id')->nullable()->index('idx_equipment_asset_type_id_586f0bc6');
            $table->uuid('asset_location_id')->nullable()->index('idx_equipment_asset_location_id_4ed2518c');
            $table->integer('repair_days')->nullable();
            $table->integer('repair_notification_in_days')->nullable();
            $table->integer('verificaction_days')->nullable();
            $table->integer('verification_notification_in_days')->nullable();
            $table->uuid('lab_id')->nullable()->index('idx_equipment_lab_id_c4cc6a83');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};

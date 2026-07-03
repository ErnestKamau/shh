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
        if (Schema::hasTable('maintainance_calibration_logs')) {
            return;
        }
        Schema::create('maintainance_calibration_logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('equipment_id')->index('idx_maintainance_calibration_logs_equipment_id_8c03140a');
            $table->text('notes');
            $table->string('type');
            $table->date('date');
            $table->string('certificate')->default('no-document');
            $table->integer('overseen_by');
            $table->integer('edit_by');
            $table->integer('maintainance_notification_in_days')->nullable();
            $table->integer('calibration_notification_in_days')->nullable();
            $table->date('replacement_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->integer('operator_id')->nullable();
            $table->uuid('supplier_id')->nullable()->index('idx_maintainance_calibration_logs_supplier_id_3520ecb0');
            $table->timestamps();
            $table->integer('employee_id')->nullable();
            $table->string('maintainance_type')->default('Not assigned');
            $table->text('cause')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->text('remedy')->nullable();
            $table->text('anomalies')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('operation_carried')->nullable();
            $table->text('comments')->nullable();
            $table->string('labour', 100)->nullable();
            $table->string('samaco_no', 200)->nullable();
            $table->text('barcode_no')->nullable();
            $table->string('area_code', 300)->nullable();
            $table->string('stage', 200)->nullable();
            $table->boolean('operator_approve')->default(false);
            $table->boolean('proccess_owner_approve')->default(false);
            $table->integer('proccess_owner_id')->nullable();
            $table->string('description', 500)->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintainance_calibration_logs');
    }
};

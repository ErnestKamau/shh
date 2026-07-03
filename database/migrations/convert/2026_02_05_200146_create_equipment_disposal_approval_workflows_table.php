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
        if (Schema::hasTable('equipment_disposal_approval_workflows')) {
            return;
        }
        Schema::create('equipment_disposal_approval_workflows', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('workflow_name');
            $table->text('description')->nullable();
            $table->uuid('equipment_type_id')->nullable()->index('idx_equipment_disposal_approval_workflows_equipment_ty_a61a1709');
            $table->uuid('location_id')->nullable()->index('idx_equipment_disposal_approval_workflows_location_id_c87a14c2');
            $table->boolean('is_active')->default(true)->index('idx_equipment_disposal_approval_workflows_is_active_be8ddb1c');
            $table->uuid('created_by')->nullable()->index('idx_equipment_disposal_approval_workflows_created_by_e54a09a4');
            $table->uuid('company_id')->index('idx_equipment_disposal_approval_workflows_company_id_9f931cc4');
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_approval_workflows');
    }
};

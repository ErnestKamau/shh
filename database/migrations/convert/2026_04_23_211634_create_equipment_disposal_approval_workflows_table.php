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
        Schema::create('equipment_disposal_approval_workflows', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('workflow_name');
            $table->text('description')->nullable();
            $table->uuid('equipment_type_id')->nullable()->index('idx_equipment_disposal_approval_workflows_equipment_ty_dff04417');
            $table->uuid('location_id')->nullable()->index('idx_equipment_disposal_approval_workflows_location_id_31a63ef3');
            $table->boolean('is_active')->default(true)->index('idx_equipment_disposal_approval_workflows_is_active_d731fbdb');
            $table->uuid('created_by')->nullable()->index('equipment_disposal_approval_workflows_created_by_foreign');
            $table->uuid('company_id')->index('idx_equipment_disposal_approval_workflows_company_id_83c118b0');
            $table->timestamps();
            $table->foreign(['created_by'], 'fk_equipment_disposal_approval_workflows_created_by_267693a0')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['equipment_type_id'], 'fk_equipment_disposal_approval_workflows_equipment_typ_9ddba46a')->references(['id'])->on('asset_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['location_id'], 'fk_equipment_disposal_approval_workflows_location_id_944a1db3')->references(['id'])->on('asset_locations')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_equipment_disposal_approval_workflows_company_id_3f4e6918')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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

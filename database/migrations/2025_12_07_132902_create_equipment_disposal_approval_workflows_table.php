<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentDisposalApprovalWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('equipment_disposal_approval_workflows', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('workflow_name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('equipment_type_id')->nullable(); // Filter by asset type
            $table->unsignedBigInteger('location_id')->nullable(); // Filter by location (plant)
            $table->boolean('is_active')->default(true);
            $table->bigInteger('created_by')->nullable();
            $table->integer('company_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('equipment_type_id')->references('id')->on('asset_types')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('asset_locations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            // Note: company_id foreign key omitted to match existing equipment table pattern

            // Indexes
            $table->index('equipment_type_id');
            $table->index('location_id');
            $table->index('is_active');
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_approval_workflows');
    }
}


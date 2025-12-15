<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentDisposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('equipment_disposals', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->unsignedInteger('equipment_id');
            $table->text('justification');
            $table->enum('proposed_method', ['scrap', 'donation', 'auction', 'recycling', 'destruction'])->nullable();
            $table->enum('risk_level', ['Low', 'Medium', 'High', 'Critical'])->nullable();
            $table->string('regulatory_category')->nullable(); // hazardous, non-hazardous, e-waste, etc.
            $table->bigInteger('requested_by');
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'executed', 'closed'])->default('draft');
            $table->string('final_disposal_method')->nullable();
            $table->date('disposal_date')->nullable();
            $table->bigInteger('executed_by')->nullable();
            $table->bigInteger('witness_id')->nullable();
            $table->json('compliance_checklist_json')->nullable();
            $table->string('sha_hash', 64)->nullable(); // SHA-256 hash for anti-tamper
            $table->integer('company_id');
            $table->string('pdf_report_path')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('executed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('witness_id')->references('id')->on('users')->onDelete('set null');
            // Note: company_id foreign key omitted to match existing equipment table pattern

            // Indexes
            $table->index('equipment_id');
            $table->index('requested_by');
            $table->index('status');
            $table->index('company_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposals');
    }
}


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
        Schema::create('sample_workflow_decontamination_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->date('log_date');
            $table->string('officer_name');
            $table->uuid('lab_id');
            $table->string('status', 30)->default('saved');
            $table->uuid('company_id')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->index(['lab_id', 'log_date']);
            $table->index('company_id');
            $table->index('created_by');

            $table->foreign('lab_id')->references('id')->on('labs')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_workflow_decontamination_logs');
    }
};

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
        Schema::create('risk_process_links', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('risk_id')->index('idx_risk_process_links_risk_id_5d760a01');
            $table->uuid('business_process_id')->index('idx_risk_process_links_business_process_id_bfa2d141');
            $table->text('description')->nullable();
            $table->uuid('created_by')->nullable()->index('idx_risk_process_links_created_by_fbdd72f6');
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_process_links');
    }
};

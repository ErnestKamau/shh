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
            $table->uuid('risk_id')->index('risk_process_links_risk_id_foreign');
            $table->uuid('business_process_id')->index('risk_process_links_business_process_id_foreign');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign(['business_process_id'], 'fk_risk_process_links_business_process_id_1371d30f')->references(['id'])->on('risk_business_processes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['risk_id'], 'fk_risk_process_links_risk_id_ceac4996')->references(['id'])->on('risks')->onUpdate('no action')->onDelete('cascade');
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

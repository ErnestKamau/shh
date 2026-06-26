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
        Schema::create('capa_records', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            
            $table->uuid('complaint_id');
            $table->foreign('complaint_id')->references('id')->on('complaints')->cascadeOnDelete();
            
            $table->text('details_of_non_conformance')->nullable();
            $table->string('identified_by')->nullable();
            $table->text('root_cause')->nullable();
            $table->string('effectiveness_verified_by')->nullable();
            $table->date('effectiveness_date')->nullable();
            $table->json('why_why_analysis')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capa_records');
    }
};

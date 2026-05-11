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
        Schema::create('audit_checklist_sample_type', function (Blueprint $table) {
            $table->id();
            $table->uuid('audit_checklist_id')->index();
            $table->uuid('sample_type_id')->index();
            $table->timestamps();
            
            $table->foreign('audit_checklist_id')
                ->references('id')
                ->on('audit_checklists')
                ->onDelete('cascade');
            
            $table->foreign('sample_type_id')
                ->references('id')
                ->on('sample_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_checklist_sample_type');
    }
};

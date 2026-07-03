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
        if (Schema::hasTable('document_approval_workflows')) {
            return;
        }
        Schema::create('document_approval_workflows', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('document_type_id')->index('idx_document_approval_workflows_document_type_id_6baec126');
            $table->string('workflow_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index('idx_document_approval_workflows_is_active_578b2a74');
            $table->uuid('created_by')->nullable()->index('idx_document_approval_workflows_created_by_3fd373db');
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_approval_workflows');
    }
};

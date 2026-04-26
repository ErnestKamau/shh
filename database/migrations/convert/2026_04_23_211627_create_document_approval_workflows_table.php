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
        Schema::create('document_approval_workflows', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('document_type_id')->index('idx_document_approval_workflows_document_type_id_9b3b5328');
            $table->string('workflow_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index('idx_document_approval_workflows_is_active_c1ac1f4f');
            $table->uuid('created_by')->nullable()->index('document_approval_workflows_created_by_foreign');
            $table->timestamps();
            $table->foreign(['created_by'], 'fk_document_approval_workflows_created_by_77cf15e3')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['document_type_id'], 'fk_document_approval_workflows_document_type_id_c5f3d142')->references(['id'])->on('document_types')->onUpdate('no action')->onDelete('cascade');
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

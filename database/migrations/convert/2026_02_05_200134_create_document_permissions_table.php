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
        Schema::create('document_permissions', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('permissionable_type');
            $table->bigInteger('permissionable_id');
            $table->string('subject_type');
            $table->bigInteger('subject_id');
            $table->enum('permission_type', ['view', 'add', 'edit', 'delete', 'amend', 'authorize_amendment', 'approve_amendment']);
            $table->uuid('granted_by')->nullable()->index('idx_document_permissions_granted_by_a22b1241');
            $table->timestamps();

            $table->index(['permissionable_type', 'permissionable_id'], 'idx_document_permissions_permissionable_type_permissio_50f52d3d');
            $table->index(['subject_type', 'subject_id'], 'idx_document_permissions_subject_type_subject_id_b5139021');
            $table->unique(['permissionable_type', 'permissionable_id', 'subject_type', 'subject_id', 'permission_type'], 'document_permissions_unique');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_permissions');
    }
};

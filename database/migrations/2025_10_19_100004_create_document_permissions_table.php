<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('document_permissions', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('permissionable_type');
            $table->bigInteger('permissionable_id');
            $table->string('subject_type');
            $table->bigInteger('subject_id');
            $table->enum('permission_type', [
                'view',
                'add',
                'edit',
                'delete',
                'amend',
                'authorize_amendment',
                'approve_amendment'
            ]);
            $table->bigInteger('granted_by')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('granted_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['permissionable_type', 'permissionable_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->unique(['permissionable_type', 'permissionable_id', 'subject_type', 'subject_id', 'permission_type'], 'document_permissions_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('document_permissions');
    }
}


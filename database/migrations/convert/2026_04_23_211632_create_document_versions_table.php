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
        Schema::create('document_versions', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('document_id')->index('idx_document_versions_document_id_ca8fbf90');
            $table->integer('version_number')->index('idx_document_versions_version_number_c2f5b0e7');
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('version_reason')->nullable();
            $table->uuid('created_by')->index('document_versions_created_by_foreign');
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();

            $table->unique(['document_id', 'version_number']);
            $table->foreign(['created_by'], 'fk_document_versions_created_by_705fce77')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['document_id'], 'fk_document_versions_document_id_02518f89')->references(['id'])->on('documents')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};

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
        if (Schema::hasTable('document_versions')) {
            return;
        }
        Schema::create('document_versions', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('document_id')->index('idx_document_versions_document_id_9b3d6129');
            $table->integer('version_number')->index('idx_document_versions_version_number_678b552b');
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->string('version_reason')->nullable();
            $table->uuid('created_by')->index('idx_document_versions_created_by_bfa34a5c');
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();

            $table->unique(['document_id', 'version_number']);
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

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
        Schema::create('equipment_disposal_files', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('disposal_id')->index('idx_equipment_disposal_files_disposal_id_20b58b98');
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->enum('file_type', ['photo', 'document', 'evidence'])->default('document')->index('idx_equipment_disposal_files_document_fd71c510');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->text('description')->nullable();
            $table->uuid('uploaded_by')->index('idx_equipment_disposal_files_uploaded_by_76dc88aa');
            $table->timestamps();
            $table->foreign(['disposal_id'], 'fk_equipment_disposal_files_disposal_id_b9a6df72')->references(['id'])->on('equipment_disposals')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['uploaded_by'], 'fk_equipment_disposal_files_uploaded_by_bb963d2e')->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_files');
    }
};

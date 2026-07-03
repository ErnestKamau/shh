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
        if (Schema::hasTable('equipment_disposal_files')) {
            return;
        }
        Schema::create('equipment_disposal_files', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('disposal_id')->index('idx_equipment_disposal_files_disposal_id_3d4bd768');
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->enum('file_type', ['photo', 'document', 'evidence'])->default('document')->index('idx_equipment_disposal_files_document_8d0b8196');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->text('description')->nullable();
            $table->uuid('uploaded_by')->index('idx_equipment_disposal_files_uploaded_by_50053b54');
            $table->timestamps();
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

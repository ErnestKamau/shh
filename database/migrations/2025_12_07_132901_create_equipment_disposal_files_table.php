<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentDisposalFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('equipment_disposal_files', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('disposal_id');
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->enum('file_type', ['photo', 'document', 'evidence'])->default('document');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->text('description')->nullable();
            $table->bigInteger('uploaded_by');
            $table->timestamps();

            // Foreign keys
            $table->foreign('disposal_id')->references('id')->on('equipment_disposals')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('restrict');

            // Indexes
            $table->index('disposal_id');
            $table->index('file_type');
            $table->index('uploaded_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_disposal_files');
    }
}


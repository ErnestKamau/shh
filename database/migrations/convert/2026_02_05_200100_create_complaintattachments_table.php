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
        if (Schema::hasTable('complaintattachments')) {
            return;
        }
        Schema::create('complaintattachments', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('title')->nullable();
            $table->string('type');
            $table->boolean('is_public')->default(false);
            $table->enum('file_type', ['screenshot', 'document', 'other'])->default('document');
            $table->unsignedInteger('file_size')->nullable();
            $table->string('file_path');
            $table->string('description')->nullable();
            $table->uuid('complaint_id')->index('idx_complaintattachments_complaint_id_fddd411b');
            $table->string('posted_by');
            $table->boolean('is_delete')->default(false);
            $table->softDeletes();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaintattachments');
    }
};

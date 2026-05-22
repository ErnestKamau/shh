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
        Schema::create('audit_attachments', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('attachable_type');
            $table->uuid('attachable_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('original_name')->nullable();
            $table->uuid('attachment_type_id')->nullable()->index('idx_audit_attachments_attachment_type_id_90390576');
            $table->string('attachment_type_name')->nullable();
            $table->text('description')->nullable();
            $table->uuid('uploaded_by')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_audit_attachments_company_id_43ec311c');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['attachable_type', 'attachable_id'], 'idx_audit_attachments_attachable_type_attachable_id_2b26d9a3');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_attachments');
    }
};

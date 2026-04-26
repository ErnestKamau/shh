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
            $table->unsignedBigInteger('attachable_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('original_name')->nullable();
            $table->uuid('attachment_type_id')->nullable()->index('audit_attachments_attachment_type_id_foreign');
            $table->string('attachment_type_name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->uuid('company_id')->default(0)->index('idx_audit_attachments_company_id_f87c6eff');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['attachable_type', 'attachable_id'], 'idx_audit_attachments_attachable_type_attachable_id_882cb102');
            $table->foreign(['attachment_type_id'], 'fk_audit_attachments_attachment_type_id_1ae5dc97')->references(['id'])->on('attachment_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['company_id'], 'fk_audit_attachments_company_id_a2bf5550')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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

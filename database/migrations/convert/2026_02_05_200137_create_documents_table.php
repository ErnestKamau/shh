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
        if (Schema::hasTable('documents')) {
            return;
        }
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('document_type_id')->index('idx_documents_document_type_id_39e377f5');
            $table->string('document_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->uuid('owner_id')->index('idx_documents_owner_id_7e136d08');
            $table->uuid('created_by')->index('idx_documents_created_by_3b9f61d9');
            $table->integer('version_number')->default(1);
            $table->integer('amendment_count')->default(0);
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'superseded', 'archived'])->default('draft')->index('idx_documents_draft_2873da53');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->uuid('approved_by')->nullable()->index('idx_documents_approved_by_f462d03d');
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_archived')->default(false)->index('idx_documents_is_archived_affd24cd');
            $table->uuid('archived_by')->nullable()->index('idx_documents_archived_by_dd97599b');
            $table->timestamp('archived_at')->nullable();
            $table->text('archive_reason')->nullable();
            $table->date('expiry_date')->nullable()->index('idx_documents_expiry_date_a435503a');
            $table->boolean('is_expiring')->default(false)->index('idx_documents_is_expiring_31673570');
            $table->timestamp('last_expiry_notification_sent_at')->nullable();
            $table->longText('tags')->nullable();
            $table->timestamp('created_at')->nullable()->index('idx_documents_created_at_4a33b65f');
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

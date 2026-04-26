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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('document_type_id')->index('idx_documents_document_type_id_bde1fcf6');
            $table->string('document_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->uuid('owner_id')->index('idx_documents_owner_id_7896d847');
            $table->uuid('created_by')->index('documents_created_by_foreign');
            $table->integer('version_number')->default(1);
            $table->integer('amendment_count')->default(0);
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'superseded', 'archived'])->default('draft')->index('idx_documents_draft_91644468');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->uuid('approved_by')->nullable()->index('documents_approved_by_foreign');
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_archived')->default(false)->index('idx_documents_is_archived_f70de825');
            $table->uuid('archived_by')->nullable()->index('documents_archived_by_foreign');
            $table->timestamp('archived_at')->nullable();
            $table->text('archive_reason')->nullable();
            $table->date('expiry_date')->nullable()->index('idx_documents_expiry_date_535ec1c0');
            $table->boolean('is_expiring')->default(false)->index('idx_documents_is_expiring_6cd09506');
            $table->timestamp('last_expiry_notification_sent_at')->nullable();
            $table->longText('tags')->nullable();
            $table->timestamp('created_at')->nullable()->index('idx_documents_created_at_c28ee011');
            $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
            $table->foreign(['approved_by'], 'fk_documents_approved_by_087cc358')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['archived_by'], 'fk_documents_archived_by_6999a458')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'], 'fk_documents_created_by_ba25b021')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['document_type_id'], 'fk_documents_document_type_id_5c10dc63')->references(['id'])->on('document_types')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['owner_id'], 'fk_documents_owner_id_a7abd97d')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
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

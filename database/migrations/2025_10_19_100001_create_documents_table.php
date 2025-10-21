<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('document_type_id');
            $table->string('document_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->bigInteger('owner_id');
            $table->bigInteger('created_by');
            $table->integer('version_number')->default(1);
            $table->integer('amendment_count')->default(0);
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'superseded', 'archived'])->default('draft');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->bigInteger('archived_by')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->text('archive_reason')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('is_expiring')->default(false);
            $table->timestamp('last_expiry_notification_sent_at')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('document_type_id');
            $table->index('owner_id');
            $table->index('status');
            $table->index('is_archived');
            $table->index('expiry_date');
            $table->index('is_expiring');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
}


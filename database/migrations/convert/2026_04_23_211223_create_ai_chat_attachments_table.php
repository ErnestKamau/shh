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
        Schema::create('ai_chat_attachments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('ai_conversation_id')->index('idx_ai_chat_attachments_ai_conversation_id_03156ace');
            $table->uuid('ai_message_id')->nullable()->index('idx_ai_chat_attachments_ai_message_id_b77dbb62');
            $table->string('original_name');
            $table->string('stored_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->enum('processing_status', ['pending', 'extracted', 'indexed', 'failed'])->default('pending')->index('idx_ai_chat_attachments_pending_b34ef283');
            $table->longText('extracted_text')->nullable();
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chat_attachments');
    }
};

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
            $table->uuid('ai_conversation_id')->index('idx_ai_chat_att_convo');
            $table->uuid('ai_message_id')->nullable()->index('fk_ai_chat_att_msg');
            $table->string('original_name');
            $table->string('stored_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->enum('processing_status', ['pending', 'extracted', 'indexed', 'failed'])->default('pending')->index('idx_ai_chat_att_status');
            $table->longText('extracted_text')->nullable();
            $table->timestamps();
            $table->foreign(['ai_conversation_id'], 'fk_ai_chat_att_convo')->references(['id'])->on('ai_conversations')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ai_message_id'], 'fk_ai_chat_att_msg')->references(['id'])->on('ai_messages')->onUpdate('no action')->onDelete('set null');
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

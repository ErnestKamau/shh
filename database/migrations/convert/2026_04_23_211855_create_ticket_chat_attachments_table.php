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
        Schema::create('ticket_chat_attachments', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('chat_message_id')->index('idx_ticket_chat_attachments_chat_message_id_8bbef7aa');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign(['chat_message_id'], 'fk_ticket_chat_attachments_chat_message_id_c1d53826')->references(['id'])->on('ticket_chat')->onUpdate('no action')->onDelete('cascade');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_chat_attachments');
    }
};

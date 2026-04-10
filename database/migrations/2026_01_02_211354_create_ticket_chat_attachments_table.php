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
            $table->id();
            $table->unsignedBigInteger('chat_message_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable(); // image, document, etc.
            $table->integer('file_size')->nullable(); // in KB
            $table->string('mime_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('chat_message_id')->references('id')->on('ticket_chat')->onDelete('cascade');
            $table->index('chat_message_id');
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

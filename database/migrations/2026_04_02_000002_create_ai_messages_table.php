<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ai_conversation_id');
            $table->enum('role', ['user', 'bot']);
            $table->longText('content');
            $table->json('sources')->nullable();
            $table->timestamps();

            $table->foreign('ai_conversation_id')
                  ->references('id')
                  ->on('ai_conversations')
                  ->onDelete('cascade');

            $table->index('ai_conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};

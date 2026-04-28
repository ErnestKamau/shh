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
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->uuid('id');
            $table->char('generation_session_id', 36)->nullable()->index()->comment('Links to AiGenerationSession for tracking active generations and stop control');
            $table->uuid('ai_conversation_id')->index('idx_ai_messages_ai_conversation_id_cd1d9aec');
            $table->uuid('parent_message_id')->nullable()->index('idx_ai_messages_parent_message_id_909da9c0');
            $table->enum('role', ['user', 'bot']);
            $table->longText('content')->fulltext();
            $table->text('reference_resolved_input')->nullable()->comment('If user input contained resolved references, store the resolved version here');
            $table->unsignedSmallInteger('compressed_from_count')->nullable()->comment('If this message is a compression summary, indicates how many original turns were summarized');
            $table->enum('message_type', ['text', 'image', 'mixed'])->default('text')->comment('Type of message content: text-only, image, or mixed');
            $table->boolean('is_edited')->default(false);
            $table->json('sources')->nullable();
            $table->enum('feedback', ['up', 'down'])->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_messages', function (Blueprint $table) {
            // Add support for linking edited/retry messages to originals
            $table->unsignedBigInteger('parent_message_id')->nullable()->after('ai_conversation_id');
            
            // Track if this message is an edit of a prior message
            $table->boolean('is_edited')->default(false)->after('content');
            
            // Store metadata: edit history, retry counts, etc.
            $table->json('metadata')->nullable()->after('feedback');
            
            // Add foreign key constraint for parent_message_id
            $table->foreign('parent_message_id')
                ->references('id')
                ->on('ai_messages')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_messages', function (Blueprint $table) {
            $table->dropForeign(['parent_message_id']);
            $table->dropColumn(['parent_message_id', 'is_edited', 'metadata']);
        });
    }
};

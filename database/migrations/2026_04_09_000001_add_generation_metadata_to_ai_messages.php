<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds Phase 1 Sprint 1 columns for:
     * - Generation session tracking (generation_session_id)
     * - Message type classification (message_type: text, image, mixed)
     * - Reference resolution (reference_resolved_input, compressed_from_count)
     *
     * All columns are nullable and backwards-compatible.
     * No existing data migration required.
     */
    public function up(): void
    {
        Schema::table('ai_messages', function (Blueprint $table) {
            // Generation session tracking (Sprint 3 needs this)
            $table->uuid('generation_session_id')
                ->nullable()
                ->after('id')
                ->index()
                ->comment('Links to AiGenerationSession for tracking active generations and stop control');

            // Message content type classification (Sprint 4 - multimodal)
            $table->enum('message_type', ['text', 'image', 'mixed'])
                ->default('text')
                ->after('content')
                ->comment('Type of message content: text-only, image, or mixed');

            // Reference resolution support (Sprint 1)
            $table->text('reference_resolved_input')
                ->nullable()
                ->after('content')
                ->comment('If user input contained resolved references, store the resolved version here');

            // Compression marker (Sprint 1b future)
            $table->unsignedSmallInteger('compressed_from_count')
                ->nullable()
                ->after('reference_resolved_input')
                ->comment('If this message is a compression summary, indicates how many original turns were summarized');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_messages', function (Blueprint $table) {
            $table->dropColumn([
                'generation_session_id',
                'message_type',
                'reference_resolved_input',
                'compressed_from_count',
            ]);
        });
    }
};

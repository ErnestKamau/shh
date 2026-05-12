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
        Schema::table('ai.ai_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('ai.ai_conversations', 'context')) {
                $table->string('context', 50)->default('general')->after('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai.ai_conversations', function (Blueprint $table) {
            if (Schema::hasColumn('ai.ai_conversations', 'context')) {
                $table->dropColumn('context');
            }
        });
    }
};

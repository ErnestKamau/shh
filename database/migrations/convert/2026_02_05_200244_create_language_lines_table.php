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
        if (Schema::hasTable('language_lines')) {
            return;
        }
        Schema::create('language_lines', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('group')->index('idx_language_lines_group_5d772e72');
            $table->string('key');
            $table->json('text');
            $table->timestamps();

            $table->index(['group', 'key'], 'idx_language_lines_group_key_6c3b4f78');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_lines');
    }
};

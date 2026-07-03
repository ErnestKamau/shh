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
        if (Schema::hasTable('languages')) {
            return;
        }
        Schema::create('languages', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name', 100)->unique();
            $table->string('code', 20)->unique();
            $table->boolean('is_active')->default(true)->index('idx_languages_is_active_e001ad5b');
            $table->boolean('is_default')->default(false)->index('idx_languages_is_default_683c01c4');
            $table->timestamps();

            $table->index(['is_active', 'is_default'], 'idx_languages_is_active_is_default_118020d8');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};

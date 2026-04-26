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
        Schema::create('languages', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name', 100)->unique();
            $table->string('code', 20)->unique();
            $table->boolean('is_active')->default(true)->index('idx_languages_is_active_c1446671');
            $table->boolean('is_default')->default(false)->index('idx_languages_is_default_b07e3f95');
            $table->timestamps();

            $table->index(['is_active', 'is_default'], 'languages_active_default_index');
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

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
        Schema::create('ai_model_registry', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('model_name');
            $table->string('model_type');
            $table->string('version')->default('1.0.0');
            $table->string('framework')->nullable();
            $table->integer('training_rows')->default(0);
            $table->json('metrics')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deprecated')->default(false);
            $table->timestamp('deployed_at')->nullable();
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_model_registry');
    }
};

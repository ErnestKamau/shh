<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS ai');

        Schema::create('ai.ai_model_registry', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model_name');
            $table->string('model_type');
            $table->string('version')->default('1.0.0');
            $table->string('framework')->nullable();
            $table->text('artifact_path')->nullable();
            $table->uuid('feature_snapshot_id')->nullable();
            $table->integer('training_rows')->default(0);
            $table->double('training_duration_seconds')->nullable();
            $table->jsonb('hyperparameters')->nullable();
            $table->jsonb('metrics')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_deprecated')->default(false);
            $table->timestampTz('deployed_at')->nullable();
            $table->timestampsTz();

            $table->unique(['model_type', 'version']);
            $table->index('model_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai.ai_model_registry');
    }
};

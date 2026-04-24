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
        // 1. Model Registry
        Schema::create('ai_model_registry', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->string('model_type'); // 'llm', 'classifier', 'regression'
            $table->string('version')->default('1.0.0');
            $table->string('framework')->nullable();
            $table->integer('training_rows')->default(0);
            $table->json('metrics')->nullable(); // Accuracy, F1, etc.
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deprecated')->default(false);
            $table->timestamp('deployed_at')->nullable();
            $table->timestamps();
        });

        // 2. Analytics Logs (Telemetry)
        Schema::create('ai_analytics_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('intent')->nullable()->index();
            $table->string('model_used')->index();
            $table->integer('latency_ms')->default(0);
            $table->integer('tokens_used')->default(0);
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->float('confidence')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 3. Feature Snapshots (For Drift Detection)
        Schema::create('ai_feature_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('feature_type'); // 'sample', 'equipment', 'qc'
            $table->unsignedBigInteger('snapshot_id')->index();
            $table->string('feature_name');
            $table->float('mean_value')->default(0);
            $table->float('variance')->default(0);
            $table->integer('record_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_feature_snapshots');
        Schema::dropIfExists('ai_analytics_logs');
        Schema::dropIfExists('ai_model_registry');
    }
};

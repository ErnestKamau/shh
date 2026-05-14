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

        Schema::create('ai.ai_feature_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->timestampTz('snapshot_time')->useCurrent();
            $table->text('description')->nullable();
            $table->jsonb('metadata')->nullable();

            // Legacy aggregate columns kept nullable for existing dashboard code.
            $table->string('feature_type')->nullable();
            $table->unsignedBigInteger('snapshot_id')->nullable()->index('idx_ai_feature_snapshots_snapshot_id');
            $table->string('feature_name')->nullable();
            $table->double('mean_value')->default(0);
            $table->double('variance')->default(0);
            $table->integer('record_count')->default(0);

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai.ai_feature_snapshots');
    }
};

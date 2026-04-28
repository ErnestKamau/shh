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
        Schema::create('ai_feature_snapshots', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('feature_type');
            $table->unsignedBigInteger('snapshot_id')->index('idx_ai_feature_snapshots_snapshot_id_fa17645f');
            $table->string('feature_name');
            $table->double('mean_value')->default(0);
            $table->double('variance')->default(0);
            $table->integer('record_count')->default(0);
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_feature_snapshots');
    }
};

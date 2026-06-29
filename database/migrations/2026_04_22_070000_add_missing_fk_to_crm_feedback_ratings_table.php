<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crm_feedback_ratings')) {
            return;
        }

        if (Schema::hasTable('crm_evaluation_metrics')) {
            Schema::table('crm_feedback_ratings', function (Blueprint $table) {
                try {
                    $table->foreign('evaluation_metric_id')
                        ->references('id')
                        ->on('crm_evaluation_metrics')
                        ->onDelete('cascade');
                } catch (\Throwable $e) {
                    // Foreign key may already exist or column/table missing in some environments.
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('crm_feedback_ratings')) {
            return;
        }

        Schema::table('crm_feedback_ratings', function (Blueprint $table) {
            try {
                $table->dropForeign(['evaluation_metric_id']);
            } catch (\Throwable $e) {
                // Ignore if it doesn't exist.
            }
        });
    }
};


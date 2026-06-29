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
        Schema::table('complaints', function (Blueprint $table) {
            if (!Schema::hasColumn('complaints', 'feedback_id')) {
                $table->uuid('feedback_id')->nullable();
                $table->foreign('feedback_id')->references('id')->on('customerfeedbacks')->nullOnDelete();
            }
            if (!Schema::hasColumn('complaints', 'is_feedback_related')) {
                $table->boolean('is_feedback_related')->default(false);
            }
            if (!Schema::hasColumn('complaints', 'origin')) {
                $table->string('origin')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (Schema::hasColumn('complaints', 'feedback_id')) {
                try {
                    $table->dropForeign(['feedback_id']);
                } catch (\Exception $e) {}
            }
            
            $columnsToDrop = [];
            foreach (['feedback_id', 'is_feedback_related', 'origin'] as $col) {
                if (Schema::hasColumn('complaints', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

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
        if (!Schema::hasTable('crm_feedback_configs')) {
            Schema::create('crm_feedback_configs', function (Blueprint $table) {
                $table->id();
                $table->decimal('lowest_rating_threshold', 4, 2)->default(1.50);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_feedback_configs');
    }
};

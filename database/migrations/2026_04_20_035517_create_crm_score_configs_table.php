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
        if (!Schema::hasTable('crm_score_configs')) {
            Schema::create('crm_score_configs', function (Blueprint $table) {
                $table->id();
                $table->integer('base_score')->default(50);
                $table->integer('sample_weight')->default(10);
                $table->integer('feedback_weight')->default(5);
                $table->integer('complaint_weight')->default(-5);
                $table->integer('account_registry_weight')->default(10);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_score_configs');
    }
};

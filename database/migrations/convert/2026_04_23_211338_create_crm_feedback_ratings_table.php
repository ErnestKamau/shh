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
        Schema::create('crm_feedback_ratings', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('customer_feedback_id')->index('idx_crm_feedback_ratings_customer_feedback_id_29cbc95d');
            $table->uuid('evaluation_metric_id')->index('idx_crm_feedback_ratings_evaluation_metric_id_23e098f4');
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_feedback_ratings');
    }
};

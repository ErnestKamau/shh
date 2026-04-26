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
            $table->uuid('customer_feedback_id')->index('crm_feedback_ratings_customer_feedback_id_foreign');
            $table->uuid('evaluation_metric_id')->index('crm_feedback_ratings_evaluation_metric_id_foreign');
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->foreign(['customer_feedback_id'], 'fk_crm_feedback_ratings_customer_feedback_id_07e530aa')->references(['id'])->on('customerfeedbacks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['evaluation_metric_id'], 'fk_crm_feedback_ratings_evaluation_metric_id_1a3865e9')->references(['id'])->on('crm_evaluation_metrics')->onUpdate('no action')->onDelete('cascade');
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

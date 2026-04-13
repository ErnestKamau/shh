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
            $table->id();
            $table->unsignedBigInteger('customer_feedback_id');
            $table->unsignedBigInteger('evaluation_metric_id');
            $table->integer('rating');
            $table->timestamps();

            $table->foreign('customer_feedback_id')->references('id')->on('customerfeedbacks')->onDelete('cascade');
            $table->foreign('evaluation_metric_id')->references('id')->on('crm_evaluation_metrics')->onDelete('cascade');
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

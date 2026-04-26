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
        Schema::create('crm_evaluation_metrics', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->text('prompt_text')->nullable();
            $table->integer('max_rating')->default(4);
            $table->longText('rating_labels')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_evaluation_metrics');
    }
};

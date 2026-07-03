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
        if (Schema::hasTable('rating_criterias')) {
            return;
        }
        Schema::create('rating_criterias', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('title');
            $table->double('max_score');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_criterias');
    }
};

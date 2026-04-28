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
        Schema::create('skill_training_header', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('capability_id');
            $table->dateTime('deleted_at')->nullable();
            $table->string('name');
            $table->uuid('created_by')->nullable()->index('idx_skill_training_header_created_by_41a3b73d');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_training_header');
    }
};

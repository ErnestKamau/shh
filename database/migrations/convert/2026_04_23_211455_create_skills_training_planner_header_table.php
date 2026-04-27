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
        Schema::create('skills_training_planner_header', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('name');
            $table->integer('training_need_header_id');
            $table->boolean('is_complete')->default(false);
            $table->uuid('created_by')->nullable()->index('idx_skills_training_planner_header_created_by');
            $table->dateTime('deleted_at')->nullable();
            $table->foreign(['created_by'], 'fk_skills_training_planner_header_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills_training_planner_header');
    }
};

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
        Schema::create('skills_training_planner_detail', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('training_plan_header_id');
            $table->integer('training_need_detail_id')->nullable();
            $table->dateTime('training_start_date')->nullable();
            $table->dateTime('training_end_date')->nullable();
            $table->string('week_no')->nullable();
            $table->string('organizer_trainer')->nullable();
            $table->text('remark')->nullable();
            $table->integer('status')->default(0);
            $table->dateTime('deleted_at')->nullable();
            $table->text('other_competency')->nullable();
            $table->string('other_participants_ids')->nullable();
            $table->boolean('is_others')->nullable()->default(false);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skills_training_planner_detail');
    }
};

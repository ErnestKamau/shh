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
        Schema::create('personnel_work_histories', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('department_id');
            $table->integer('job_id');
            $table->uuid('user_id')->index('idx_personnel_work_histories_user_id_7bc32686');
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->foreign(['user_id'], 'fk_personnel_work_histories_user_id_e5e8c97b')->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_work_histories');
    }
};

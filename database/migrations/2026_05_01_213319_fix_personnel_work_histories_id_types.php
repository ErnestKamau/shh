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
        Schema::table('personnel_work_histories', function (Blueprint $table) {
            $table->dropColumn(['department_id', 'job_id']);
        });
        Schema::table('personnel_work_histories', function (Blueprint $table) {
            $table->uuid('department_id')->nullable();
            $table->uuid('job_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnel_work_histories', function (Blueprint $table) {
            $table->dropColumn(['department_id', 'job_id']);
        });
        Schema::table('personnel_work_histories', function (Blueprint $table) {
            $table->integer('department_id')->nullable();
            $table->integer('job_id')->nullable();
        });
    }
};

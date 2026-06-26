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
        Schema::table('sample_points', function (Blueprint $table) {
            $table->string('gps', 512)->nullable()->change();
        });

        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->text('action_taken')->nullable()->change();
            $table->string('officer_responsible', 255)->nullable()->change();
            $table->string('registered_by', 255)->nullable()->change();
            $table->integer('workflow_stage')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_points', function (Blueprint $table) {
            $table->string('gps', 512)->nullable(false)->change();
        });

        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->text('action_taken')->nullable(false)->change();
            $table->string('officer_responsible', 255)->nullable(false)->change();
            $table->string('registered_by', 255)->nullable(false)->change();
            $table->integer('workflow_stage')->nullable(false)->change();
        });
    }
};

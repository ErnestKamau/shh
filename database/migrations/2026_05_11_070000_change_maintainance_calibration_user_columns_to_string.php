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
        Schema::table('maintainance_calibration_logs', function (Blueprint $table): void {
            $table->string('overseen_by', 255)->nullable()->change();
            $table->string('edit_by', 255)->nullable()->change();
            $table->string('employee_id', 255)->nullable()->change();
            $table->string('operator_id', 255)->nullable()->change();
            $table->string('proccess_owner_id', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintainance_calibration_logs', function (Blueprint $table): void {
            $table->integer('overseen_by')->nullable()->change();
            $table->integer('edit_by')->nullable()->change();
            $table->integer('employee_id')->nullable()->change();
            $table->integer('operator_id')->nullable()->change();
            $table->integer('proccess_owner_id')->nullable()->change();
        });
    }
};

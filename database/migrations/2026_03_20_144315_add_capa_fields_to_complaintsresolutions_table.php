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
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->text('cause_of_complaint')->nullable();
            $table->date('action_taken_date')->nullable();
            $table->date('corrective_action_date')->nullable();
            $table->boolean('car_issued')->default(false);
            $table->text('client_remarks')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $table->dropColumn([
                'cause_of_complaint',
                'action_taken_date',
                'corrective_action_date',
                'car_issued',
                'client_remarks'
            ]);
        });
    }
};

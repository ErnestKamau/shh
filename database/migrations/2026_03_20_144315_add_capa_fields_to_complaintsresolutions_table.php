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
            if (!Schema::hasColumn('complaintsresolutions', 'cause_of_complaint')) {
                $table->text('cause_of_complaint')->nullable();
            }
            if (!Schema::hasColumn('complaintsresolutions', 'action_taken_date')) {
                $table->date('action_taken_date')->nullable();
            }
            if (!Schema::hasColumn('complaintsresolutions', 'corrective_action_date')) {
                $table->date('corrective_action_date')->nullable();
            }
            if (!Schema::hasColumn('complaintsresolutions', 'car_issued')) {
                $table->boolean('car_issued')->default(false);
            }
            if (!Schema::hasColumn('complaintsresolutions', 'client_remarks')) {
                $table->text('client_remarks')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaintsresolutions', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach ([
                'cause_of_complaint',
                'action_taken_date',
                'corrective_action_date',
                'car_issued',
                'client_remarks'
            ] as $col) {
                if (Schema::hasColumn('complaintsresolutions', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

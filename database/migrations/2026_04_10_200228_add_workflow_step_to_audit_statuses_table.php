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
        if (!Schema::hasColumn('audit_statuses', 'workflow_step')) {
            Schema::table('audit_statuses', function (Blueprint $table) {
                $table->integer('workflow_step')->default(0)->after('order_index');
            });
        }

        \DB::table('audit_statuses')->where('name', 'Scheduled')->update(['workflow_step' => 1]);
        \DB::table('audit_statuses')->where('name', 'In Progress')->update(['workflow_step' => 2]);
        \DB::table('audit_statuses')->where('name', 'Record Findings & NC')->update(['workflow_step' => 3]);
        \DB::table('audit_statuses')->where('name', 'Findings Review')->update(['workflow_step' => 4]);
        \DB::table('audit_statuses')->where('name', 'Root Cause Analysis')->update(['workflow_step' => 5]);
        \DB::table('audit_statuses')->where('name', 'CAPA Assigned')->update(['workflow_step' => 6]);
        \DB::table('audit_statuses')->where('name', 'Pending Closure')->update(['workflow_step' => 7]);
        \DB::table('audit_statuses')->where('name', 'Closed')->update(['workflow_step' => 8]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('audit_statuses', 'workflow_step')) {
            Schema::table('audit_statuses', function (Blueprint $table) {
                $table->dropColumn('workflow_step');
            });
        }
    }
};

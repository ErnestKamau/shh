<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            $table->timestamp('submitted_at')->nullable()->after('last_reminded_at');
        });

        // Backfill submitted_at with updated_at for existing submitted records
        DB::table('customerfeedbacks')
            ->where('status', 0) // STATUS_SUBMITTED
            ->update(['submitted_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customerfeedbacks', function (Blueprint $table) {
            $table->dropColumn('submitted_at');
        });
    }
};

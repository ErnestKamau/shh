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
        Schema::table('ticket_assignments', function (Blueprint $table) {
            // First, add the columns if they don't exist
            if (!Schema::hasColumn('ticket_assignments', 'tat_value')) {
                $table->integer('tat_value')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('ticket_assignments', 'tat_unit')) {
                $table->string('tat_unit', 12)->nullable()->after('tat_value');
            }
        });

        // Set default values for any NULL records
        DB::table('ticket_assignments')
            ->whereNull('tat_value')
            ->orWhereNull('tat_unit')
            ->update([
                'tat_value' => 24,
                'tat_unit' => 'hours',
            ]);

        // Now make the columns NOT NULL
        Schema::table('ticket_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_assignments', 'tat_value')) {
                $table->integer('tat_value')->nullable(false)->change();
            }
            if (Schema::hasColumn('ticket_assignments', 'tat_unit')) {
                $table->string('tat_unit', 12)->nullable(false)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_assignments', function (Blueprint $table) {
            $table->integer('tat_value')->nullable()->change();
            $table->string('tat_unit', 12)->nullable()->change();
        });
    }
};

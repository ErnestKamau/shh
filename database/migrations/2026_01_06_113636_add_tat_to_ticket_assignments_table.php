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
        Schema::table('ticket_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('ticket_assignments', 'tat_value')) {
                $table->integer('tat_value')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('ticket_assignments', 'tat_unit')) {
                $table->string('tat_unit', 12)->nullable()->after('tat_value'); // hours, days, weeks, months
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('ticket_assignments', 'tat_value')) {
                $table->dropColumn('tat_value');
            }
            if (Schema::hasColumn('ticket_assignments', 'tat_unit')) {
                $table->dropColumn('tat_unit');
            }
        });
    }
};

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
        Schema::table('calendar_events', function (Blueprint $table) {
            if (!Schema::hasColumn('calendar_events', 'contract_valid_from')) {
                $table->date('contract_valid_from')->nullable();
            }
            if (!Schema::hasColumn('calendar_events', 'contract_valid_to')) {
                $table->date('contract_valid_to')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn(['contract_valid_from', 'contract_valid_to']);
        });
    }
};

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
        Schema::table('lookup_tables', function (Blueprint $table) {
            $table->boolean('is_standard')->default(false)->after('is_active');
            $table->boolean('show_on_report')->default(false)->after('is_standard');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lookup_tables', function (Blueprint $table) {
            $table->dropColumn(['is_standard', 'show_on_report']);
        });
    }
};

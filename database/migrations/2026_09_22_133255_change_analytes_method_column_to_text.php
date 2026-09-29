<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legacy analytes.method still mirrors comma-separated method UUIDs alongside
     * the analyte_methods pivot. Multiple UUIDs exceed varchar(255).
     */
    public function up(): void
    {
        Schema::table('analytes', function (Blueprint $table) {
            $table->text('method')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('analytes', function (Blueprint $table) {
            $table->string('method')->nullable()->change();
        });
    }
};

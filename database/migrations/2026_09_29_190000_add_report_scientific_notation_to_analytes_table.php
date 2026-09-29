<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('analytes') || Schema::hasColumn('analytes', 'report_scientific_notation')) {
            return;
        }

        Schema::table('analytes', function (Blueprint $table) {
            $table->boolean('report_scientific_notation')->default(false);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('analytes') || ! Schema::hasColumn('analytes', 'report_scientific_notation')) {
            return;
        }

        Schema::table('analytes', function (Blueprint $table) {
            $table->dropColumn('report_scientific_notation');
        });
    }
};

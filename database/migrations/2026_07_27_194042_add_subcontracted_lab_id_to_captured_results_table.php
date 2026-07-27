<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('captured_results')) {
            return;
        }

        Schema::table('captured_results', function (Blueprint $table): void {
            if (! Schema::hasColumn('captured_results', 'subcontracted_lab_id')) {
                $table->uuid('subcontracted_lab_id')->nullable()->index()->after('analyte_status_contracted');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('captured_results')) {
            return;
        }

        Schema::table('captured_results', function (Blueprint $table): void {
            if (Schema::hasColumn('captured_results', 'subcontracted_lab_id')) {
                $table->dropColumn('subcontracted_lab_id');
            }
        });
    }
};

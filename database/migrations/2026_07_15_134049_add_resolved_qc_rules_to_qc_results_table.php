<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('qc_results')) {
            return;
        }

        Schema::table('qc_results', function (Blueprint $table) {
            if (! Schema::hasColumn('qc_results', 'resolved_qc_rules')) {
                $table->jsonb('resolved_qc_rules')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('qc_results')) {
            return;
        }

        Schema::table('qc_results', function (Blueprint $table) {
            if (Schema::hasColumn('qc_results', 'resolved_qc_rules')) {
                $table->dropColumn('resolved_qc_rules');
            }
        });
    }
};

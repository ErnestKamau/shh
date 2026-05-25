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

        Schema::table('captured_results', function (Blueprint $table) {
            if (! Schema::hasColumn('captured_results', 'grouped_worksheet_holder_id')) {
                $table->uuid('grouped_worksheet_holder_id')->nullable()->after('has_procedure_worksheet');
            }
            if (! Schema::hasColumn('captured_results', 'has_grouped_worksheet')) {
                $table->boolean('has_grouped_worksheet')->default(false)->after('grouped_worksheet_holder_id');
            }
            if (! Schema::hasColumn('captured_results', 'hybrid_worksheet_id')) {
                $table->uuid('hybrid_worksheet_id')->nullable()->after('has_grouped_worksheet');
            }
            if (! Schema::hasColumn('captured_results', 'has_hybrid_worksheet')) {
                $table->boolean('has_hybrid_worksheet')->default(false)->after('hybrid_worksheet_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('captured_results')) {
            return;
        }

        Schema::table('captured_results', function (Blueprint $table) {
            $columns = [
                'has_hybrid_worksheet',
                'hybrid_worksheet_id',
                'has_grouped_worksheet',
                'grouped_worksheet_holder_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('captured_results', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

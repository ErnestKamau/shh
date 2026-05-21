<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->convertUserIdColumn('hybrid_worksheet_versions', 'created_by', true);
        $this->convertUserIdColumn('hybrid_worksheet_versions', 'approved_by', false);
        $this->convertUserIdColumn('grouped_worksheet_runs', 'started_by', true);
        $this->convertUserIdColumn('grouped_worksheet_run_items', 'completed_by', false);
    }

    public function down(): void
    {
        // Intentionally empty: do not revert UUID user columns to bigint.
    }

    protected function convertUserIdColumn(string $table, string $column, bool $indexed): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        if (Schema::getColumnType($table, $column) === 'uuid') {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $indexed) {
            if ($indexed) {
                $blueprint->dropIndex([$column]);
            }
            $blueprint->dropColumn($column);
        });

        Schema::table($table, function (Blueprint $blueprint) use ($column, $indexed) {
            $columnDef = $blueprint->uuid($column)->nullable();
            if ($indexed) {
                $columnDef->index();
            }
        });
    }
};

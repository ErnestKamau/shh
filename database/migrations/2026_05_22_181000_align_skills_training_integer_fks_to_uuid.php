<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * @var array<string, list<string>>
     */
    private array $columnsByTable = [
        'skill_training_header_staff' => ['training_header_id', 'capability_matrix_role_id'],
        'skills_training_detail' => [
            'training_header_id',
            'capability_detail_id',
            'skill_matrix_role_proficiency_id',
        ],
        'skills_training_planner_header' => ['training_need_header_id'],
        'skills_training_planner_detail' => ['training_plan_header_id', 'training_need_detail_id'],
    ];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($this->columnsByTable as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $type = Schema::getColumnType($table, $column);
                if ($type === 'uuid' || $type === 'guid') {
                    continue;
                }

                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP DEFAULT");
                DB::statement(
                    "ALTER TABLE {$table}
                     ALTER COLUMN {$column} TYPE uuid
                     USING (
                        CASE
                            WHEN {$column}::text ~ '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$'
                                THEN {$column}::text::uuid
                            ELSE NULL
                        END
                     )"
                );
            }
        }
    }

    public function down(): void
    {
        //
    }
};

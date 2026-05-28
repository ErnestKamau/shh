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
        'skillsmatrices' => ['department_id'],
        'skills_matrix_role' => ['skills_matrix_id', 'job_description_id'],
        'skills_matrix_detail' => [
            'skill_matrix_id',
            'competency_area_id',
            'competency_type_id',
            'competency_description_id',
        ],
        'skills_matrix_detail_role' => ['matrix_detail_id', 'matrix_role_id', 'proficiency_id'],
        'skills_capability_matrix' => ['matrix_id'],
        'skills_capability_matrix_role' => ['skill_matrix_role_id'],
        'skill_capability_detail' => ['competency_id', 'proficiency_id', 'skill_matrix_role_id'],
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

        $this->dropMisalignedRoleForeignKeys();
    }

    protected function dropMisalignedRoleForeignKeys(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        $constraints = [
            'skills_matrix_detail_role' => 'fk_skills_matrix_detail_role_role_id_e5c99e56',
            'skills_matrix_role_requirments' => 'fk_skills_matrix_role_requirments_role_id_0ecc33c0',
            'skills_capability_matrix_role' => 'fk_skills_capability_matrix_role_role_id_eac5517b',
        ];

        foreach ($constraints as $table => $constraint) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraint}");
        }
    }

    public function down(): void
    {
        // Irreversible without data loss when UUIDs are stored.
    }
};

<?php

namespace App\Services\Lab;

use App\AnalysisMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class AnalysisMethodPurgeService
{
    /**
     * Delete analysis methods for a company only.
     *
     * Does not update stage headers, samples, analysis elements, results, or other
     * workflow rows. Blocking foreign keys are temporarily dropped and re-added as
     * NOT VALID so existing references can remain as orphans.
     *
     * @return array<string, int>
     */
    public function purgeForCompany(string $companyId): array
    {
        $summary = [
            'analysis_methods' => 0,
        ];

        DB::transaction(function () use ($companyId, &$summary): void {
            $methodIds = AnalysisMethod::query()
                ->where('company_id', $companyId)
                ->pluck('id')
                ->all();

            if ($methodIds === []) {
                return;
            }

            // Break LTM -> reference links among methods being deleted.
            if (Schema::hasColumn('analysis_methods', 'reference_type_id')) {
                $summary['analysis_methods_reference_cleared'] = DB::table('analysis_methods')
                    ->whereIn('id', $methodIds)
                    ->whereNotNull('reference_type_id')
                    ->update(['reference_type_id' => null]);
            }

            $this->deleteMethodOwnedRecords($methodIds, $summary);

            $droppedForeignKeys = $this->dropNonCascadeForeignKeysToMethods();

            try {
                $summary['analysis_methods'] = AnalysisMethod::query()
                    ->whereIn('id', $methodIds)
                    ->delete();
            } finally {
                $this->restoreForeignKeys($droppedForeignKeys);
            }
        });

        return $summary;
    }

    /**
     * @param  array<int, string>  $methodIds
     * @param  array<string, int>  $summary
     */
    private function deleteMethodOwnedRecords(array $methodIds, array &$summary): void
    {
        foreach ([
            'method_reagents' => 'method_id',
            'method_qc_scheme' => 'method_id',
            'analyte_methods' => 'analysis_method_id',
            'analysis_method_elements' => 'analysis_method_id',
            'method_validation_requests' => 'method_id',
            'qc_scheme_bindings' => 'method_id',
        ] as $table => $column) {
            if (! $this->canMatchUuidMethodIds($table, $column)) {
                continue;
            }

            $deleted = DB::table($table)->whereIn($column, $methodIds)->delete();
            if ($deleted > 0) {
                $summary[$table.'_deleted'] = $deleted;
            }
        }
    }

    /**
     * Skip legacy integer/bigint method FKs that cannot accept UUID method IDs.
     */
    private function canMatchUuidMethodIds(string $table, string $column): bool
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return false;
        }

        if (DB::getDriverName() !== 'pgsql') {
            return true;
        }

        $type = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->value('udt_name');

        return in_array($type, ['uuid', 'varchar', 'text', 'bpchar'], true);
    }

    /**
     * Drop FKs that would block method deletion or auto-null other tables.
     *
     * @return array<int, array{constraint_name: string, table_name: string, column_name: string, delete_rule: string}>
     */
    private function dropNonCascadeForeignKeysToMethods(): array
    {
        if (DB::getDriverName() !== 'pgsql') {
            return [];
        }

        $foreignKeys = DB::select("
            SELECT
                tc.constraint_name,
                tc.table_name,
                kcu.column_name,
                rc.delete_rule
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
                ON tc.constraint_name = kcu.constraint_name
                AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage ccu
                ON ccu.constraint_name = tc.constraint_name
                AND ccu.table_schema = tc.table_schema
            JOIN information_schema.referential_constraints rc
                ON rc.constraint_name = tc.constraint_name
                AND rc.constraint_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY'
              AND tc.table_schema = 'public'
              AND ccu.table_name = 'analysis_methods'
              AND rc.delete_rule <> 'CASCADE'
        ");

        $dropped = [];

        foreach ($foreignKeys as $foreignKey) {
            $constraint = (string) $foreignKey->constraint_name;
            $table = (string) $foreignKey->table_name;

            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraint}");

            $dropped[] = [
                'constraint_name' => $constraint,
                'table_name' => $table,
                'column_name' => (string) $foreignKey->column_name,
                'delete_rule' => (string) $foreignKey->delete_rule,
            ];
        }

        return $dropped;
    }

    /**
     * @param  array<int, array{constraint_name: string, table_name: string, column_name: string, delete_rule: string}>  $foreignKeys
     */
    private function restoreForeignKeys(array $foreignKeys): void
    {
        if (DB::getDriverName() !== 'pgsql' || $foreignKeys === []) {
            return;
        }

        foreach ($foreignKeys as $foreignKey) {
            $table = $foreignKey['table_name'];
            $column = $foreignKey['column_name'];
            $constraint = $foreignKey['constraint_name'];
            $onDelete = strtoupper($foreignKey['delete_rule']);

            // NOT VALID keeps existing rows untouched (including orphaned method references).
            DB::statement("
                ALTER TABLE {$table}
                ADD CONSTRAINT {$constraint}
                FOREIGN KEY ({$column})
                REFERENCES analysis_methods(id)
                ON DELETE {$onDelete}
                NOT VALID
            ");
        }
    }
}

<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ResolvesQcApproverColumns
{
    protected function qcApproverPersonnelColumnSupportsUuid(): bool
    {
        if (! Schema::connection('pgsql')->hasTable('qc_approvers_config')) {
            return false;
        }

        $type = $this->qcApproverColumnDataType('personnel_id');

        return in_array($type, ['uuid', 'character varying'], true);
    }

    protected function qcApproverColumnDataType(string $column): ?string
    {
        $row = DB::connection('pgsql')->selectOne(
            'SELECT data_type
             FROM information_schema.columns
             WHERE table_schema = current_schema()
               AND table_name = ?
               AND column_name = ?',
            ['qc_approvers_config', $column]
        );

        return $row?->data_type;
    }

    protected function qcResultProcessedColumnSupportsUuid(): bool
    {
        if (! Schema::connection('pgsql')->hasTable('qc_results')) {
            return false;
        }

        $type = DB::connection('pgsql')->selectOne(
            'SELECT data_type
             FROM information_schema.columns
             WHERE table_schema = current_schema()
               AND table_name = ?
               AND column_name = ?',
            ['qc_results', 'analyte_processed_id']
        )?->data_type;

        return in_array($type, ['uuid', 'character varying'], true);
    }
}

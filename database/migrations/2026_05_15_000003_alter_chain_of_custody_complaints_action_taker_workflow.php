<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chain_of_custody_complaints')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $this->upgradeActionTakerIdPgsql();
            $this->upgradeWorkflowStagePgsql();

            return;
        }

        if ($driver === 'mysql') {
            $this->upgradeActionTakerIdMysql();
            $this->upgradeWorkflowStageMysql();
        }
    }

    private function upgradeActionTakerIdPgsql(): void
    {
        $row = DB::selectOne(
            'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
            ['chain_of_custody_complaints', 'action_taker_id']
        );
        if (! $row || $row->data_type === 'uuid') {
            return;
        }

        if (! in_array($row->data_type, ['integer', 'bigint', 'smallint'], true)) {
            return;
        }

        DB::statement('ALTER TABLE chain_of_custody_complaints ALTER COLUMN action_taker_id DROP DEFAULT');
        DB::statement('ALTER TABLE chain_of_custody_complaints ALTER COLUMN action_taker_id DROP NOT NULL');
        DB::statement('ALTER TABLE chain_of_custody_complaints ALTER COLUMN action_taker_id TYPE uuid USING (NULL::uuid)');
    }

    private function upgradeWorkflowStagePgsql(): void
    {
        $row = DB::selectOne(
            'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
            ['chain_of_custody_complaints', 'workflow_stage']
        );
        if (! $row || in_array($row->data_type, ['character varying', 'text'], true)) {
            return;
        }

        if ($row->data_type !== 'integer' && $row->data_type !== 'bigint' && $row->data_type !== 'smallint') {
            return;
        }

        DB::statement(<<<'SQL'
ALTER TABLE chain_of_custody_complaints ALTER COLUMN workflow_stage TYPE varchar(191) USING (
    CASE workflow_stage::integer
        WHEN 0 THEN 'All Complaints'
        WHEN 1 THEN 'Open Complaints'
        WHEN 2 THEN 'Complaints Approval'
        WHEN 3 THEN 'Complaints Resolution'
        WHEN 4 THEN 'Resolution Approval'
        WHEN 5 THEN 'Closed Complaints'
        WHEN 6 THEN 'Cancelled Complaints'
        ELSE workflow_stage::text
    END
)
SQL);
    }

    private function upgradeActionTakerIdMysql(): void
    {
        $row = DB::selectOne(
            'select DATA_TYPE as data_type from information_schema.COLUMNS where TABLE_SCHEMA = database() and TABLE_NAME = ? and COLUMN_NAME = ?',
            ['chain_of_custody_complaints', 'action_taker_id']
        );
        if (! $row) {
            return;
        }
        $t = strtolower((string) $row->data_type);
        if ($t === 'char' || str_contains($t, 'binary')) {
            return;
        }
        if (! in_array($t, ['int', 'integer', 'bigint', 'smallint', 'tinyint'], true)) {
            return;
        }

        DB::statement('ALTER TABLE chain_of_custody_complaints MODIFY action_taker_id CHAR(36) NULL');
    }

    private function upgradeWorkflowStageMysql(): void
    {
        $row = DB::selectOne(
            'select DATA_TYPE as data_type from information_schema.COLUMNS where TABLE_SCHEMA = database() and TABLE_NAME = ? and COLUMN_NAME = ?',
            ['chain_of_custody_complaints', 'workflow_stage']
        );
        if (! $row) {
            return;
        }
        $t = strtolower((string) $row->data_type);
        if (in_array($t, ['varchar', 'char', 'text'], true)) {
            return;
        }

        DB::statement('ALTER TABLE chain_of_custody_complaints MODIFY workflow_stage VARCHAR(191) NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('chain_of_custody_complaints')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'pgsql') {
            return;
        }

        $row = DB::selectOne(
            'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
            ['chain_of_custody_complaints', 'workflow_stage']
        );
        if ($row && $row->data_type === 'character varying') {
            DB::statement('ALTER TABLE chain_of_custody_complaints ALTER COLUMN workflow_stage TYPE integer USING 0');
        }

        $row2 = DB::selectOne(
            'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
            ['chain_of_custody_complaints', 'action_taker_id']
        );
        if ($row2 && $row2->data_type === 'uuid') {
            DB::statement('ALTER TABLE chain_of_custody_complaints ALTER COLUMN action_taker_id TYPE integer USING 0');
            DB::statement('ALTER TABLE chain_of_custody_complaints ALTER COLUMN action_taker_id SET NOT NULL');
        }
    }
};

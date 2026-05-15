<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('complaints')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $row = DB::selectOne(
                'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                ['complaints', 'client_id']
            );
            if (! $row || $row->data_type === 'uuid') {
                return;
            }

            if (! in_array($row->data_type, ['integer', 'bigint', 'smallint'], true)) {
                return;
            }

            DB::statement('ALTER TABLE complaints ALTER COLUMN client_id DROP DEFAULT');
            DB::statement('ALTER TABLE complaints ALTER COLUMN client_id DROP NOT NULL');
            DB::statement('ALTER TABLE complaints ALTER COLUMN client_id TYPE uuid USING (NULL::uuid)');

            return;
        }

        if ($driver === 'mysql') {
            $row = DB::selectOne(
                'select DATA_TYPE as data_type from information_schema.COLUMNS where TABLE_SCHEMA = database() and TABLE_NAME = ? and COLUMN_NAME = ?',
                ['complaints', 'client_id']
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

            DB::statement('ALTER TABLE complaints MODIFY client_id INT NULL');
            DB::table('complaints')->update(['client_id' => null]);
            DB::statement('ALTER TABLE complaints MODIFY client_id CHAR(36) NULL');

            return;
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('complaints')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $row = DB::selectOne(
                'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                ['complaints', 'client_id']
            );
            if (! $row || $row->data_type !== 'uuid') {
                return;
            }

            DB::statement('ALTER TABLE complaints ALTER COLUMN client_id TYPE integer USING 0');
            DB::statement('ALTER TABLE complaints ALTER COLUMN client_id SET DEFAULT 0');
            DB::statement('ALTER TABLE complaints ALTER COLUMN client_id SET NOT NULL');
        }
    }
};

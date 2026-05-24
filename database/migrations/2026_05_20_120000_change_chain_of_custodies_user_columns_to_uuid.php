<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Users use UUID primary keys; chain_of_custodies still stored moved_in_by / moved_out_by as integers.
     */
    public function up(): void
    {
        if (! Schema::hasTable('chain_of_custodies')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $this->upPgsql();

            return;
        }

        if ($driver === 'mysql') {
            $this->upMysql();
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('chain_of_custodies')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE chain_of_custodies ALTER COLUMN moved_in_by DROP NOT NULL');
            DB::statement('ALTER TABLE chain_of_custodies ALTER COLUMN moved_out_by DROP NOT NULL');
            DB::statement('ALTER TABLE chain_of_custodies ALTER COLUMN moved_in_by TYPE integer USING 0');
            DB::statement('ALTER TABLE chain_of_custodies ALTER COLUMN moved_out_by TYPE integer USING NULL');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE chain_of_custodies MODIFY moved_in_by BIGINT NOT NULL');
            DB::statement('ALTER TABLE chain_of_custodies MODIFY moved_out_by BIGINT NULL');
        }
    }

    private function upPgsql(): void
    {
        $schema = $this->pgsqlSchema();

        $movedInUdt = DB::table('information_schema.columns')
            ->where('table_schema', $schema)
            ->where('table_name', 'chain_of_custodies')
            ->where('column_name', 'moved_in_by')
            ->value('udt_name');

        if ($movedInUdt !== 'uuid') {
            $fallbackUserId = DB::table('users')->orderBy('created_at')->value('id') ?? '00000000-0000-0000-0000-000000000000';

            DB::statement('ALTER TABLE chain_of_custodies ALTER COLUMN moved_in_by DROP NOT NULL');

            DB::statement("
                ALTER TABLE chain_of_custodies
                ALTER COLUMN moved_in_by TYPE uuid
                USING (
                    CASE
                        WHEN moved_in_by::text ~ '^[0-9a-fA-F-]{36}$'
                            THEN moved_in_by::text::uuid
                        ELSE NULL
                    END
                )
            ");

            DB::update(
                'UPDATE chain_of_custodies SET moved_in_by = ? WHERE moved_in_by IS NULL',
                [$fallbackUserId]
            );

            DB::statement('ALTER TABLE chain_of_custodies ALTER COLUMN moved_in_by SET NOT NULL');
        }

        $movedOutUdt = DB::table('information_schema.columns')
            ->where('table_schema', $schema)
            ->where('table_name', 'chain_of_custodies')
            ->where('column_name', 'moved_out_by')
            ->value('udt_name');

        if ($movedOutUdt !== 'uuid') {
            DB::statement("
                ALTER TABLE chain_of_custodies
                ALTER COLUMN moved_out_by TYPE uuid
                USING (
                    CASE
                        WHEN moved_out_by IS NULL THEN NULL
                        WHEN moved_out_by::text ~ '^[0-9a-fA-F-]{36}$'
                            THEN moved_out_by::text::uuid
                        ELSE NULL
                    END
                )
            ");
        }
    }

    private function upMysql(): void
    {
        $movedIn = DB::selectOne("SHOW COLUMNS FROM chain_of_custodies WHERE Field = 'moved_in_by'");
        if ($movedIn && str_contains(strtolower((string) $movedIn->Type), 'char')) {
            return;
        }

        $fallbackUserId = DB::table('users')->orderBy('created_at')->value('id') ?? '00000000-0000-0000-0000-000000000000';

        DB::statement('ALTER TABLE chain_of_custodies MODIFY moved_in_by VARCHAR(36) NOT NULL');
        DB::statement('ALTER TABLE chain_of_custodies MODIFY moved_out_by VARCHAR(36) NULL');

        DB::update(
            "UPDATE chain_of_custodies SET moved_in_by = ? WHERE CHAR_LENGTH(moved_in_by) <> 36 OR moved_in_by NOT REGEXP '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$'",
            [$fallbackUserId]
        );

        DB::update(
            "UPDATE chain_of_custodies SET moved_out_by = NULL WHERE moved_out_by IS NOT NULL AND (CHAR_LENGTH(moved_out_by) <> 36 OR moved_out_by NOT REGEXP '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$')"
        );

        DB::statement('ALTER TABLE chain_of_custodies MODIFY moved_in_by CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE chain_of_custodies MODIFY moved_out_by CHAR(36) NULL');
    }

    private function pgsqlSchema(): string
    {
        $path = Schema::getConnection()->getConfig('search_path');
        if (is_string($path) && $path !== '') {
            return trim(explode(',', $path)[0]);
        }

        return 'public';
    }
};

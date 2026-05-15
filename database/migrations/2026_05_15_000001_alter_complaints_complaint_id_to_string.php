<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
                ['complaints', 'complaint_id']
            );
            if (! $row || $row->data_type !== 'uuid') {
                return;
            }

            DB::statement('ALTER TABLE complaints DROP CONSTRAINT IF EXISTS fk_complaints_complaint_id_68f339c0');
            DB::statement('ALTER TABLE complaints ALTER COLUMN complaint_id TYPE varchar(64) USING complaint_id::text');

            return;
        }

        if ($driver === 'mysql') {
            $row = DB::selectOne(
                'select DATA_TYPE as data_type from information_schema.COLUMNS where TABLE_SCHEMA = database() and TABLE_NAME = ? and COLUMN_NAME = ?',
                ['complaints', 'complaint_id']
            );
            if (! $row || strtolower((string) $row->data_type) !== 'char') {
                return;
            }

            try {
                Schema::table('complaints', function (Blueprint $table) {
                    $table->dropForeign('fk_complaints_complaint_id_68f339c0');
                });
            } catch (\Throwable) {
            }

            DB::statement('ALTER TABLE complaints MODIFY complaint_id VARCHAR(64) NOT NULL');

            return;
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('complaints')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'pgsql') {
            return;
        }

        $row = DB::selectOne(
            'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
            ['complaints', 'complaint_id']
        );
        if (! $row || $row->data_type !== 'character varying') {
            return;
        }

        DB::statement('ALTER TABLE complaints ALTER COLUMN complaint_id TYPE uuid USING complaint_id::uuid');
    }
};

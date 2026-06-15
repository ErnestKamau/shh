<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (Schema::hasTable('calendar_events')) {
            if ($driver === 'pgsql') {
                // client_id conversion
                $rowClient = DB::selectOne(
                    'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                    ['calendar_events', 'client_id']
                );
                if ($rowClient && in_array($rowClient->data_type, ['integer', 'bigint', 'smallint'], true)) {
                    DB::statement('ALTER TABLE calendar_events ALTER COLUMN client_id DROP DEFAULT');
                    DB::statement('ALTER TABLE calendar_events ALTER COLUMN client_id DROP NOT NULL');
                    DB::statement('ALTER TABLE calendar_events ALTER COLUMN client_id TYPE uuid USING (NULL::uuid)');
                }

                // parent_id conversion
                $rowParent = DB::selectOne(
                    'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                    ['calendar_events', 'parent_id']
                );
                if ($rowParent && in_array($rowParent->data_type, ['integer', 'bigint', 'smallint'], true)) {
                    DB::statement('ALTER TABLE calendar_events ALTER COLUMN parent_id DROP DEFAULT');
                    DB::statement('ALTER TABLE calendar_events ALTER COLUMN parent_id DROP NOT NULL');
                    DB::statement('ALTER TABLE calendar_events ALTER COLUMN parent_id TYPE uuid USING (NULL::uuid)');
                }
            }

            if ($driver === 'mysql') {
                // client_id conversion
                $rowClient = DB::selectOne(
                    'select DATA_TYPE as data_type from information_schema.COLUMNS where TABLE_SCHEMA = database() and TABLE_NAME = ? and COLUMN_NAME = ?',
                    ['calendar_events', 'client_id']
                );
                if ($rowClient && in_array(strtolower((string) $rowClient->data_type), ['int', 'integer', 'bigint', 'smallint', 'tinyint'], true)) {
                    DB::statement('ALTER TABLE calendar_events MODIFY client_id INT NULL');
                    DB::table('calendar_events')->update(['client_id' => null]);
                    DB::statement('ALTER TABLE calendar_events MODIFY client_id CHAR(36) NULL');
                }

                // parent_id conversion
                $rowParent = DB::selectOne(
                    'select DATA_TYPE as data_type from information_schema.COLUMNS where TABLE_SCHEMA = database() and TABLE_NAME = ? and COLUMN_NAME = ?',
                    ['calendar_events', 'parent_id']
                );
                if ($rowParent && in_array(strtolower((string) $rowParent->data_type), ['int', 'integer', 'bigint', 'smallint', 'tinyint'], true)) {
                    DB::statement('ALTER TABLE calendar_events MODIFY parent_id INT NULL');
                    DB::table('calendar_events')->update(['parent_id' => null]);
                    DB::statement('ALTER TABLE calendar_events MODIFY parent_id CHAR(36) NULL');
                }
            }
        }

        if (Schema::hasTable('event_history')) {
            if ($driver === 'pgsql') {
                // event_id conversion
                $rowEvent = DB::selectOne(
                    'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                    ['event_history', 'event_id']
                );
                if ($rowEvent && in_array($rowEvent->data_type, ['integer', 'bigint', 'smallint'], true)) {
                    DB::statement('ALTER TABLE event_history ALTER COLUMN event_id DROP DEFAULT');
                    DB::statement('ALTER TABLE event_history ALTER COLUMN event_id DROP NOT NULL');
                    DB::statement('ALTER TABLE event_history ALTER COLUMN event_id TYPE uuid USING (NULL::uuid)');
                }

                // action_by conversion
                $rowActionBy = DB::selectOne(
                    'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                    ['event_history', 'action_by']
                );
                if ($rowActionBy && in_array($rowActionBy->data_type, ['integer', 'bigint', 'smallint'], true)) {
                    DB::statement('ALTER TABLE event_history ALTER COLUMN action_by DROP DEFAULT');
                    DB::statement('ALTER TABLE event_history ALTER COLUMN action_by DROP NOT NULL');
                    DB::statement('ALTER TABLE event_history ALTER COLUMN action_by TYPE uuid USING (NULL::uuid)');
                }
            }

            if ($driver === 'mysql') {
                // event_id conversion
                $rowEvent = DB::selectOne(
                    'select DATA_TYPE as data_type from information_schema.COLUMNS where TABLE_SCHEMA = database() and TABLE_NAME = ? and COLUMN_NAME = ?',
                    ['event_history', 'event_id']
                );
                if ($rowEvent && in_array(strtolower((string) $rowEvent->data_type), ['int', 'integer', 'bigint', 'smallint', 'tinyint'], true)) {
                    DB::statement('ALTER TABLE event_history MODIFY event_id INT NULL');
                    DB::table('event_history')->update(['event_id' => null]);
                    DB::statement('ALTER TABLE event_history MODIFY event_id CHAR(36) NULL');
                }

                // action_by conversion
                $rowActionBy = DB::selectOne(
                    'select DATA_TYPE as data_type from information_schema.COLUMNS where TABLE_SCHEMA = database() and TABLE_NAME = ? and COLUMN_NAME = ?',
                    ['event_history', 'action_by']
                );
                if ($rowActionBy && in_array(strtolower((string) $rowActionBy->data_type), ['int', 'integer', 'bigint', 'smallint', 'tinyint'], true)) {
                    DB::statement('ALTER TABLE event_history MODIFY action_by INT NULL');
                    DB::table('event_history')->update(['action_by' => null]);
                    DB::statement('ALTER TABLE event_history MODIFY action_by CHAR(36) NULL');
                }
            }
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (Schema::hasTable('calendar_events')) {
            if ($driver === 'pgsql') {
                // client_id reversion
                $rowClient = DB::selectOne(
                    'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                    ['calendar_events', 'client_id']
                );
                if ($rowClient && $rowClient->data_type === 'uuid') {
                    DB::statement('ALTER TABLE calendar_events ALTER COLUMN client_id TYPE integer USING (NULL::integer)');
                }

                // parent_id reversion
                $rowParent = DB::selectOne(
                    'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                    ['calendar_events', 'parent_id']
                );
                if ($rowParent && $rowParent->data_type === 'uuid') {
                    DB::statement('ALTER TABLE calendar_events ALTER COLUMN parent_id TYPE integer USING (NULL::integer)');
                }
            }
        }

        if (Schema::hasTable('event_history')) {
            if ($driver === 'pgsql') {
                // event_id reversion
                $rowEvent = DB::selectOne(
                    'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                    ['event_history', 'event_id']
                );
                if ($rowEvent && $rowEvent->data_type === 'uuid') {
                    DB::statement('ALTER TABLE event_history ALTER COLUMN event_id TYPE integer USING (NULL::integer)');
                }

                // action_by reversion
                $rowActionBy = DB::selectOne(
                    'select data_type from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?',
                    ['event_history', 'action_by']
                );
                if ($rowActionBy && $rowActionBy->data_type === 'uuid') {
                    DB::statement('ALTER TABLE event_history ALTER COLUMN action_by TYPE integer USING (NULL::integer)');
                }
            }
        }
    }
};

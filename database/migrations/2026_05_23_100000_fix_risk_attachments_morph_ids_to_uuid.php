<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Risk module models use UUID primary keys; morph and user FK columns must not be bigint.
     */
    public function up(): void
    {
        if (Schema::hasTable('risk_attachments') && Schema::hasColumn('risk_attachments', 'attachable_id')) {
            $this->convertColumnToUuidString('risk_attachments', 'attachable_id', nullable: false);
            $this->convertColumnToUuidString('risk_attachments', 'uploaded_by', nullable: true);
        }

        if (Schema::hasTable('risk_notifications') && Schema::hasColumn('risk_notifications', 'notifiable_id')) {
            $this->convertColumnToUuidString('risk_notifications', 'notifiable_id', nullable: false);
            $this->convertColumnToUuidString('risk_notifications', 'recipient_user_id', nullable: true);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('risk_attachments') && Schema::hasColumn('risk_attachments', 'attachable_id')) {
            $this->convertColumnToBigInt('risk_attachments', 'attachable_id', nullable: false);
            $this->convertColumnToBigInt('risk_attachments', 'uploaded_by', nullable: false);
        }

        if (Schema::hasTable('risk_notifications') && Schema::hasColumn('risk_notifications', 'notifiable_id')) {
            $this->convertColumnToBigInt('risk_notifications', 'notifiable_id', nullable: false);
            $this->convertColumnToBigInt('risk_notifications', 'recipient_user_id', nullable: false);
        }
    }

    private function convertColumnToUuidString(string $table, string $column, bool $nullable): void
    {
        if (DB::getDriverName() === 'pgsql') {
            if ($nullable) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL");
            }

            DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE VARCHAR(36) USING {$column}::text");

            return;
        }

        if (DB::getDriverName() === 'mysql') {
            $nullSql = $nullable ? ' NULL' : ' NOT NULL';
            DB::statement("ALTER TABLE {$table} MODIFY {$column} VARCHAR(36){$nullSql}");
        }
    }

    private function convertColumnToBigInt(string $table, string $column, bool $nullable): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE bigint USING CASE WHEN {$column} ~ '^[0-9]+$' THEN {$column}::bigint ELSE 0 END");

            if (! $nullable) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} SET NOT NULL");
            }

            return;
        }

        if (DB::getDriverName() === 'mysql') {
            $nullSql = $nullable ? ' NULL' : ' NOT NULL';
            DB::statement("ALTER TABLE {$table} MODIFY {$column} BIGINT UNSIGNED{$nullSql}");
        }
    }
};

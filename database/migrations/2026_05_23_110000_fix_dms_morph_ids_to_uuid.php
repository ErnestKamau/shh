<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DMS models use UUID primary keys; morph and assignee columns must not be bigint.
     */
    public function up(): void
    {
        if (Schema::hasTable('document_audit_logs') && Schema::hasColumn('document_audit_logs', 'auditable_id')) {
            $this->convertColumnToUuidString('document_audit_logs', 'auditable_id', nullable: false);
        }

        if (Schema::hasTable('document_permissions')) {
            if (Schema::hasColumn('document_permissions', 'permissionable_id')) {
                $this->convertColumnToUuidString('document_permissions', 'permissionable_id', nullable: false);
            }
            if (Schema::hasColumn('document_permissions', 'subject_id')) {
                $this->convertColumnToUuidString('document_permissions', 'subject_id', nullable: false);
            }
        }

        if (Schema::hasTable('document_approval_workflow_steps') && Schema::hasColumn('document_approval_workflow_steps', 'assignee_id')) {
            $this->convertColumnToUuidString('document_approval_workflow_steps', 'assignee_id', nullable: false);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('document_audit_logs') && Schema::hasColumn('document_audit_logs', 'auditable_id')) {
            $this->convertColumnToBigInt('document_audit_logs', 'auditable_id', nullable: false);
        }

        if (Schema::hasTable('document_permissions')) {
            if (Schema::hasColumn('document_permissions', 'permissionable_id')) {
                $this->convertColumnToBigInt('document_permissions', 'permissionable_id', nullable: false);
            }
            if (Schema::hasColumn('document_permissions', 'subject_id')) {
                $this->convertColumnToBigInt('document_permissions', 'subject_id', nullable: false);
            }
        }

        if (Schema::hasTable('document_approval_workflow_steps') && Schema::hasColumn('document_approval_workflow_steps', 'assignee_id')) {
            $this->convertColumnToBigInt('document_approval_workflow_steps', 'assignee_id', nullable: false);
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

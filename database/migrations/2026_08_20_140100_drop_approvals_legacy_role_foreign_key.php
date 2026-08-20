<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Approvals now resolve assignees via Spatie role_group_name.
     * The legacy FK to roles blocks saving spatie_roles IDs.
     */
    public function up(): void
    {
        if (! Schema::hasTable('approvals')) {
            return;
        }

        Schema::table('approvals', function (Blueprint $table) {
            if ($this->foreignKeyExists('approvals', 'fk_approvals_role_id_a3ff9e9b')) {
                $table->dropForeign('fk_approvals_role_id_a3ff9e9b');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('approvals') || ! Schema::hasTable('roles')) {
            return;
        }

        Schema::table('approvals', function (Blueprint $table) {
            if (! $this->foreignKeyExists('approvals', 'fk_approvals_role_id_a3ff9e9b')) {
                $table->foreign(['role_id'], 'fk_approvals_role_id_a3ff9e9b')
                    ->references(['id'])
                    ->on('roles')
                    ->onUpdate('no action')
                    ->onDelete('cascade');
            }
        });
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        $driver = $connection->getDriverName();

        if ($driver === 'pgsql') {
            return (bool) $connection->selectOne(
                'select 1 from information_schema.table_constraints
                 where constraint_type = ?
                   and table_name = ?
                   and constraint_name = ?
                 limit 1',
                ['FOREIGN KEY', $table, $constraint]
            );
        }

        if ($driver === 'mysql') {
            return (bool) $connection->selectOne(
                'select 1 from information_schema.table_constraints
                 where constraint_type = ?
                   and table_schema = ?
                   and table_name = ?
                   and constraint_name = ?
                 limit 1',
                ['FOREIGN KEY', $database, $table, $constraint]
            );
        }

        return true;
    }
};

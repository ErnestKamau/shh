<?php

namespace App\Services\Ops\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

trait PurgesDatabaseTables
{
    protected function nullColumn(string $table, string $column): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        return $this->withSavepoint(function () use ($table, $column): int {
            if (! $this->columnIsNullable($table, $column)) {
                $this->dropNotNull($table, $column);
            }

            return (int) DB::table($table)->whereNotNull($column)->update([$column => null]);
        });
    }

    protected function deleteAllRows(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return (int) DB::table($table)->delete();
    }

    /**
     * @param  callable(): int  $callback
     */
    protected function withSavepoint(callable $callback): int
    {
        if (DB::getDriverName() !== 'pgsql') {
            return $callback();
        }

        $savepoint = 'sp_'.bin2hex(random_bytes(8));

        DB::statement("SAVEPOINT {$savepoint}");

        try {
            $result = $callback();
            DB::statement("RELEASE SAVEPOINT {$savepoint}");

            return $result;
        } catch (Throwable $e) {
            DB::statement("ROLLBACK TO SAVEPOINT {$savepoint}");
            DB::statement("RELEASE SAVEPOINT {$savepoint}");

            throw new RuntimeException(
                "Database cleanup failed on savepoint: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    protected function columnIsNullable(string $table, string $column): bool
    {
        if (DB::getDriverName() !== 'pgsql') {
            return true;
        }

        $row = DB::selectOne(
            'SELECT is_nullable
             FROM information_schema.columns
             WHERE table_schema = current_schema()
               AND table_name = ?
               AND column_name = ?',
            [$table, $column]
        );

        return strtoupper((string) ($row->is_nullable ?? 'NO')) === 'YES';
    }

    protected function dropNotNull(string $table, string $column): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            throw new RuntimeException(
                "Column {$table}.{$column} is NOT NULL; cannot detach safely on this driver."
            );
        }

        DB::statement(sprintf(
            'ALTER TABLE %s ALTER COLUMN %s DROP NOT NULL',
            $this->quoteIdent($table),
            $this->quoteIdent($column)
        ));
    }

    protected function quoteIdent(string $ident): string
    {
        return '"'.str_replace('"', '""', $ident).'"';
    }
}

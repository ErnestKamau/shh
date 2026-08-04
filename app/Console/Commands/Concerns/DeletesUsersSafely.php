<?php

namespace App\Console\Commands\Concerns;

use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

trait DeletesUsersSafely
{
    protected function usePgsqlConnection(): void
    {
        config(['database.default' => 'pgsql']);
    }

    /**
     * @param  list<string>  $emails
     * @return Collection<int, User>
     */
    protected function usersMatchingEmails(array $emails): Collection
    {
        $normalized = array_map('strtolower', $emails);

        return User::query()
            ->whereIn(DB::raw('LOWER(email)'), $normalized)
            ->orderBy('email')
            ->get();
    }

    /**
     * @param  list<string>  $emails
     * @param  list<string>  $names
     * @return Collection<int, User>
     */
    protected function usersExcludingEmailsAndNames(array $emails, array $names = []): Collection
    {
        $normalizedEmails = array_map('strtolower', $emails);
        $normalizedNames = array_map(
            static fn (string $name): string => strtolower(trim(preg_replace('/\s+/', ' ', $name) ?? $name)),
            $names,
        );

        return User::query()
            ->orderBy('email')
            ->get()
            ->reject(function (User $user) use ($normalizedEmails, $normalizedNames): bool {
                $email = strtolower(trim((string) $user->email));
                if (in_array($email, $normalizedEmails, true)) {
                    return true;
                }

                $name = strtolower(trim(preg_replace('/\s+/', ' ', (string) $user->name) ?? (string) $user->name));

                return in_array($name, $normalizedNames, true);
            })
            ->values();
    }

    /**
     * @param  Collection<int, User>  $users
     */
    protected function renderUserTable(Collection $users): void
    {
        $this->table(
            ['ID', 'Email', 'Name', 'Active'],
            $users->map(fn (User $user): array => [
                $user->id,
                $user->email,
                $user->name,
                $user->active ? 'yes' : 'no',
            ])->all()
        );
    }

    /**
     * @param  Collection<int, User>  $users
     * @return array{deleted: int, failed: int}
     */
    protected function deleteUsers(Collection $users, bool $dryRun): array
    {
        $deleted = 0;
        $failed = 0;

        foreach ($users as $user) {
            if ($dryRun) {
                $this->line("[dry-run] Would delete {$user->email} ({$user->id})");
                $deleted++;

                continue;
            }

            try {
                DB::connection('pgsql')->transaction(function () use ($user): void {
                    $this->detachUserRelations($user);
                    $this->clearBlockingUserForeignKeys((string) $user->id);
                    $user->delete();
                });

                $this->info("Deleted {$user->email} ({$user->id})");
                $deleted++;
            } catch (Throwable $exception) {
                $failed++;
                $this->error("Failed {$user->email} ({$user->id}): {$exception->getMessage()}");
            }
        }

        return ['deleted' => $deleted, 'failed' => $failed];
    }

    protected function detachUserRelations(User $user): void
    {
        if (method_exists($user, 'roles')) {
            $user->roles()->detach();
        }

        if (method_exists($user, 'permissions')) {
            $user->permissions()->detach();
        }
    }

    /**
     * Null nullable NO ACTION/RESTRICT FKs, and remove non-nullable NO ACTION child rows.
     */
    protected function clearBlockingUserForeignKeys(string $userId): void
    {
        $connection = DB::connection('pgsql');

        // Use pg_catalog instead of information_schema: constraint_column_usage is
        // privilege-filtered and can return zero FKs for the app DB role.
        $constraints = $connection->select("
            SELECT
                c.conrelid::regclass::text AS table_name,
                a.attname AS column_name,
                CASE c.confdeltype
                    WHEN 'a' THEN 'NO ACTION'
                    WHEN 'r' THEN 'RESTRICT'
                    WHEN 'c' THEN 'CASCADE'
                    WHEN 'n' THEN 'SET NULL'
                    WHEN 'd' THEN 'SET DEFAULT'
                END AS delete_rule,
                CASE WHEN a.attnotnull THEN 'NO' ELSE 'YES' END AS is_nullable
            FROM pg_constraint c
            JOIN pg_attribute a
                ON a.attrelid = c.conrelid
                AND a.attnum = ANY (c.conkey)
                AND NOT a.attisdropped
            WHERE c.contype = 'f'
                AND c.confrelid = 'public.users'::regclass
                AND c.confdeltype IN ('a', 'r')
            ORDER BY 1, 2
        ");

        foreach ($constraints as $constraint) {
            $table = trim((string) $constraint->table_name, '"');
            $column = (string) $constraint->column_name;
            $nullable = strtoupper((string) $constraint->is_nullable) === 'YES';

            if ($nullable) {
                $updated = $connection->table($table)
                    ->where($column, $userId)
                    ->update([$column => null]);

                if ($updated > 0) {
                    $this->line("  Cleared {$updated} {$table}.{$column} reference(s)");
                }

                continue;
            }

            $deleted = $connection->table($table)
                ->where($column, $userId)
                ->delete();

            if ($deleted > 0) {
                $this->warn("  Removed {$deleted} {$table} row(s) blocking delete via {$column}");
            }
        }
    }
}

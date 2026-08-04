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

        $constraints = $connection->select("
            SELECT
                tc.table_name,
                kcu.column_name,
                rc.delete_rule,
                c.is_nullable
            FROM information_schema.table_constraints AS tc
            JOIN information_schema.key_column_usage AS kcu
                ON tc.constraint_name = kcu.constraint_name
                AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage AS ccu
                ON ccu.constraint_name = tc.constraint_name
                AND ccu.table_schema = tc.table_schema
            JOIN information_schema.referential_constraints AS rc
                ON tc.constraint_name = rc.constraint_name
                AND tc.table_schema = rc.constraint_schema
            JOIN information_schema.columns AS c
                ON c.table_schema = tc.table_schema
                AND c.table_name = tc.table_name
                AND c.column_name = kcu.column_name
            WHERE tc.constraint_type = 'FOREIGN KEY'
                AND tc.table_schema = 'public'
                AND ccu.table_name = 'users'
                AND rc.delete_rule IN ('NO ACTION', 'RESTRICT')
            ORDER BY tc.table_name, kcu.column_name
        ");

        foreach ($constraints as $constraint) {
            $table = (string) $constraint->table_name;
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

<?php

namespace Database\Seeders\Concerns;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ClearsAmSpecPersonnelData
{
    protected function clearAmSpecPersonnelData(): void
    {
        [$local, $domain] = explode('@', AmSpecSeedData::SEED_USER_EMAIL, 2);
        $userIds = User::query()
            ->where('email', 'like', $local.'+%@'.$domain)
            ->pluck('id');

        if ($userIds->isEmpty()) {
            $this->command?->info('No AmSpec personnel users to clear.');

            return;
        }

        foreach (['user_lab_relation', 'user_zone_relation', 'user_directorate_relation'] as $table) {
            if (Schema::connection('pgsql')->hasTable($table)) {
                DB::connection('pgsql')->table($table)->whereIn('user_id', $userIds)->delete();
            }
        }

        $protectedUserIds = collect();
        if (Schema::connection('pgsql')->hasTable('submission_forms')) {
            $protectedUserIds = $protectedUserIds->merge(
                DB::connection('pgsql')->table('submission_forms')
                    ->whereNotNull('created_by')
                    ->distinct()
                    ->pluck('created_by')
            );
        }

        $deletableUserIds = $userIds->diff($protectedUserIds->filter()->unique())->values();
        if ($deletableUserIds->isEmpty()) {
            $this->command?->info('No deletable AmSpec personnel users (all are referenced by forms).');

            return;
        }

        if ($protectedUserIds->isNotEmpty()) {
            $skipped = $userIds->diff($deletableUserIds)->count();
            if ($skipped > 0) {
                $this->command?->info("Skipped {$skipped} AmSpec personnel user(s) referenced by submission forms.");
            }
        }

        $deletedUsers = User::query()
            ->whereIn('id', $deletableUserIds)
            ->delete();

        $this->command?->info("Cleared {$deletedUsers} AmSpec personnel user(s).");
    }
}

<?php

namespace Database\Seeders\Concerns;

use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ClearsAmSpecPersonnelData
{
    protected function clearAmSpecPersonnelData(): void
    {
        $emailDomain = '@'.AmSpecSeedData::PERSONNEL_EMAIL_DOMAIN;
        $userIds = User::query()
            ->where('email', 'like', '%'.$emailDomain)
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

        $deletedUsers = User::query()
            ->whereIn('id', $userIds)
            ->delete();

        $this->command?->info("Cleared {$deletedUsers} AmSpec personnel user(s).");
    }
}

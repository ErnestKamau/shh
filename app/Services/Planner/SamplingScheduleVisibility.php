<?php

namespace App\Services\Planner;

use App\Models\SamplingSchedule;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

final class SamplingScheduleVisibility
{
    public static function canViewAll(?User $user = null): bool
    {
        $user ??= Auth::user();

        if ($user === null) {
            return false;
        }

        return $user->hasRole('admin') || $user->can('actual-collections.view.all');
    }

    /**
     * Limit a schedules query to rows the current user may see.
     *
     * @param  Builder<\App\Models\SamplingSchedule>  $query
     * @return Builder<\App\Models\SamplingSchedule>
     */
    public static function constrain(Builder $query, ?User $user = null): Builder
    {
        $user ??= Auth::user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if (self::canViewAll($user)) {
            return $query;
        }

        return self::constrainAssigned($query, (string) $user->id);
    }

    /**
     * Only schedules where the user is assigned (personnel_id or personnel_ids).
     *
     * @param  Builder<\App\Models\SamplingSchedule>  $query
     * @return Builder<\App\Models\SamplingSchedule>
     */
    public static function constrainAssigned(Builder $query, string $userId): Builder
    {
        return $query->where(function (Builder $scoped) use ($userId): void {
            $scoped
                ->where('personnel_id', $userId)
                ->orWhereJsonContains('personnel_ids', $userId);
        });
    }

    /**
     * Schedules assigned to the given user (for dashboard "My Schedules").
     *
     * @return Builder<\App\Models\SamplingSchedule>
     */
    public static function assignedQuery(string $userId, ?string $companyId = null): Builder
    {
        $companyId ??= getUserCompany();

        return self::constrainAssigned(
            SamplingSchedule::query()->where('company_id', $companyId),
            $userId
        );
    }
}

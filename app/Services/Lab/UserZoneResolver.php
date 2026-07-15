<?php

namespace App\Services\Lab;

use App\User;
use Illuminate\Support\Collection;

/**
 * Zone scoping removed from Labs organization. Methods retained for call sites
 * that previously filtered by zone; they now operate company-wide / permission-wide.
 */
class UserZoneResolver
{
    /**
     * @return array<int, string>
     */
    public function zoneIdsForUser(User $user): array
    {
        return [];
    }

    public function userHasZone(User $user, ?string $zoneId): bool
    {
        return true;
    }

    /**
     * @return Collection<int, User>
     */
    public function usersWithApprovePermissionInZone(?string $zoneId = null): Collection
    {
        return User::query()
            ->where('active', 1)
            ->where(function ($query): void {
                $query->whereHas('permissions', fn ($q) => $q->where('name', 'laboratory.components.equipment-requests.approve'))
                    ->orWhereHas('roles.permissions', fn ($q) => $q->where('name', 'laboratory.components.equipment-requests.approve'));
            })
            ->get();
    }
}

<?php

namespace App\Services\Lab;

use App\Lab;
use App\User;
use Illuminate\Support\Collection;

class UserZoneResolver
{
    /**
     * @return array<int, string>
     */
    public function zoneIdsForUser(User $user): array
    {
        $zoneIds = collect();

        if ((string) $user->zone_id !== '') {
            $zoneIds->push((string) $user->zone_id);
        }

        if (method_exists($user, 'assignedZones')) {
            $zoneIds = $zoneIds->merge(
                $user->assignedZones()->pluck('zones.id')
            );
        }

        if (method_exists($user, 'assignedLabs')) {
            $labZoneIds = $user->assignedLabs()
                ->whereNotNull('zone_id')
                ->pluck('zone_id');

            $zoneIds = $zoneIds->merge($labZoneIds);
        }

        return $zoneIds
            ->filter(fn ($id): bool => (string) $id !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function userHasZone(User $user, ?string $zoneId): bool
    {
        if ($zoneId === null || $zoneId === '') {
            return false;
        }

        return in_array($zoneId, $this->zoneIdsForUser($user), true);
    }

    /**
     * @return Collection<int, User>
     */
    public function usersWithApprovePermissionInZone(string $zoneId): Collection
    {
        return User::query()
            ->where('active', 1)
            ->where(function ($query): void {
                $query->whereHas('permissions', fn ($q) => $q->where('name', 'laboratory.components.equipment-requests.approve'))
                    ->orWhereHas('roles.permissions', fn ($q) => $q->where('name', 'laboratory.components.equipment-requests.approve'));
            })
            ->where(function ($query) use ($zoneId): void {
                $query->where('zone_id', $zoneId)
                    ->orWhereHas('assignedZones', fn ($q) => $q->where('zones.id', $zoneId))
                    ->orWhereHas('assignedLabs', fn ($q) => $q->where('labs.zone_id', $zoneId));
            })
            ->get();
    }
}

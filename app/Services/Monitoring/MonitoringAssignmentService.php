<?php

namespace App\Services\Monitoring;

use App\Lab;
use App\User;
use Illuminate\Support\Collection;

class MonitoringAssignmentService
{
    public function assignedLabsForUser(User $user): Collection
    {
        if (method_exists($user, 'assignedLabs')) {
            return $user->assignedLabs()
                ->where('labs.active', true)
                ->orderBy('labs.name')
                ->get(['labs.id', 'labs.name', 'labs.code']);
        }

        return Lab::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }
}

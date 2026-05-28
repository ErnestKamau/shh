<?php

namespace App\Services\Monitoring;

use App\Lab;
use App\User;
use Illuminate\Support\Arr;
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

    /**
     * Labs available in monitoring UI (template wizard + dashboard).
     * Uses all active labs so multi-lab templates match what operators can select.
     */
    public function monitoringLabsForUser(User $user): Collection
    {
        $activeLabs = Lab::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        if ($activeLabs->isNotEmpty()) {
            return $activeLabs;
        }

        return $this->assignedLabsForUser($user);
    }

    /**
     * @return list<string>
     */
    public function labIdsFromTemplateMeta(?MonitoringTemplateField $metaField): array
    {
        if ($metaField === null) {
            return [];
        }

        $labs = Arr::get($metaField->field_config ?? [], 'labs', []);

        return array_values(array_unique(array_map(
            fn ($id) => (string) $id,
            is_array($labs) ? $labs : [],
        )));
    }
}

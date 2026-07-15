<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\User;
use Illuminate\Database\Eloquent\Builder;

class LabSectionResultAccess
{
    /**
     * @return list<string>
     */
    public function allowedLabSectionIds(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        $ids = $user->labsectionids;

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $ids)));
    }

    public function hasLabSectionAssignment(?User $user): bool
    {
        return $this->allowedLabSectionIds($user) !== [];
    }

    /**
     * Authenticated users may view worksheets/results across lab sections.
     * Editing is gated separately by canEditCapturedResult().
     */
    public function canViewCapturedResult(?User $user, CapturedResult $row): bool
    {
        return $user !== null;
    }

    public function canEditCapturedResult(?User $user, CapturedResult $row): bool
    {
        if ($user === null) {
            return false;
        }

        if (! $this->hasLabSectionAssignment($user)) {
            return false;
        }

        $sectionId = $this->normalizeSectionId($row->lab_section_id);

        if ($sectionId === null) {
            return false;
        }

        return in_array($sectionId, $this->allowedLabSectionIds($user), true);
    }

    /**
     * Listing/query visibility is not restricted by lab section.
     * Assigned users can see other sections' worksheets but cannot edit them.
     *
     * @param  Builder<\App\CapturedResult>  $query
     * @return Builder<\App\CapturedResult>
     */
    public function scopeVisibleCapturedResults(Builder $query, ?User $user): Builder
    {
        return $query;
    }

    /**
     * Keep all sections visible in analytes holder UIs.
     * Edit actions must still call canEditCapturedResult().
     *
     * @param  array<string, array<string, mixed>>  $holder
     * @return array<string, array<string, mixed>>
     */
    public function filterAnalytesHolderForUser(array $holder, ?User $user): array
    {
        if ($user === null) {
            return [];
        }

        return $holder;
    }

    public function denyEditMessage(?User $user): string
    {
        if ($this->hasLabSectionAssignment($user)) {
            return 'You can only capture results for your assigned lab section(s).';
        }

        return 'Assign a lab section in your profile before capturing results.';
    }

    private function normalizeSectionId(mixed $candidate): ?string
    {
        if ($candidate === null || $candidate === '' || $candidate === 0 || $candidate === '0') {
            return null;
        }

        return (string) $candidate;
    }
}

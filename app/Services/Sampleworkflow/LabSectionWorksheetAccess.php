<?php

namespace App\Services\Sampleworkflow;

use App\Models\Sampleworkflow\LabSectionWorksheet;
use App\User;

final class LabSectionWorksheetAccess
{
    public function __construct(
        private readonly LabSectionResultAccess $labSectionAccess,
    ) {}

    public function canViewWorksheet(?User $user, LabSectionWorksheet $worksheet): bool
    {
        return $user !== null;
    }

    public function canDownloadWorksheet(?User $user, LabSectionWorksheet $worksheet): bool
    {
        return $this->canViewWorksheet($user, $worksheet);
    }

    public function canImportAgainstWorksheet(?User $user, LabSectionWorksheet $worksheet): bool
    {
        if ($user === null) {
            return false;
        }

        if ($this->isAssignedAnalyst($user, $worksheet)) {
            return true;
        }

        $sectionId = trim((string) ($worksheet->lab_section_id ?? ''));
        if ($sectionId === '') {
            return false;
        }

        return in_array($sectionId, $this->labSectionAccess->allowedLabSectionIds($user), true);
    }

    public function denyImportMessage(?User $user): string
    {
        return 'You can only import results for worksheets assigned to you or for your lab section(s).';
    }

    private function isAssignedAnalyst(?User $user, LabSectionWorksheet $worksheet): bool
    {
        if ($user === null) {
            return false;
        }

        $userId = (string) $user->id;
        $assigned = is_array($worksheet->assigned_analyst_ids)
            ? array_values(array_filter(array_map('strval', $worksheet->assigned_analyst_ids)))
            : [];

        return in_array($userId, $assigned, true);
    }
}

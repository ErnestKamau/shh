<?php

namespace App\Services\Sampleworkflow;

use App\Models\Sampleworkflow\LabSectionWorksheet;
use App\User;

final class LabSectionWorksheetAccess
{
    /**
     * Elevated roles that may view/download any lab-section worksheet on a job.
     *
     * @var list<string>
     */
    private const ELEVATED_ROLES = [
        'Admin',
        'admin',
        'Lab Manager',
        'super admin',
        'super-admin',
        'system admin',
        'system-admin',
    ];

    public function __construct(
        private readonly LabSectionResultAccess $labSectionAccess,
    ) {}

    public function canViewWorksheet(?User $user, LabSectionWorksheet $worksheet): bool
    {
        if ($user === null) {
            return false;
        }

        if ($this->isElevated($user)) {
            return true;
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

    public function canDownloadWorksheet(?User $user, LabSectionWorksheet $worksheet): bool
    {
        return $this->canViewWorksheet($user, $worksheet);
    }

    public function canImportAgainstWorksheet(?User $user, LabSectionWorksheet $worksheet): bool
    {
        if ($user === null) {
            return false;
        }

        if ($this->isElevated($user)) {
            return true;
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

    public function denyViewMessage(?User $user): string
    {
        return 'You can only view worksheets assigned to you, your lab section(s), or as a lab manager/admin.';
    }

    private function isElevated(User $user): bool
    {
        return $user->hasRole(self::ELEVATED_ROLES);
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

<?php

namespace App\Services\SkillsMatrix;

use App\InventoryDepartment;
use App\User;

final class SkillsMatrixDemoSeedContext
{
    public function __construct(
        public string $companyId,
        public string $locationId,
        public string $departmentId,
        public User $createdBy,
    ) {}

    public static function fromDepartment(InventoryDepartment $department, User $createdBy): self
    {
        return new self(
            companyId: (string) $department->company_id,
            locationId: (string) $department->location_id,
            departmentId: (string) $department->id,
            createdBy: $createdBy,
        );
    }
}

<?php

namespace App\Services\SkillsMatrix;

use App\Models\SkillsMatrix\SkillMatrixDetailRole;
use App\Models\SkillsMatrix\SkillMatrixDetails;
use App\Models\SkillsMatrix\SkillsMatrixConfiguration;
use App\Models\SkillsMatrix\SkillsMatrixRoleRequirment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyTopologyMigrationService
{
    /**
     * @return array{migrated_details: int, migrated_roles: int, skipped: int, errors: array<int, string>}
     */
    public function migrate(?string $matrixId = null, bool $dryRun = false): array
    {
        $stats = [
            'migrated_details' => 0,
            'migrated_roles' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $configsQuery = SkillsMatrixConfiguration::query()
            ->where('active', 1)
            ->whereNotNull('competence_description_id');

        if ($matrixId) {
            $configsQuery->where('skills_matrix_id', $matrixId);
        }

        $configs = $configsQuery->get();

        foreach ($configs as $config) {
            try {
                $existing = SkillMatrixDetails::query()
                    ->where('skill_matrix_id', $config->skills_matrix_id)
                    ->where('competency_area_id', $config->competence_area_id)
                    ->where('competency_type_id', $config->competence_type_id)
                    ->where('competency_description_id', $config->competence_description_id)
                    ->first();

                if ($existing) {
                    $detail = $existing;
                    $stats['skipped']++;
                } else {
                    if ($dryRun) {
                        $stats['migrated_details']++;

                        continue;
                    }
                    $detail = SkillMatrixDetails::create([
                        'id' => (string) Str::uuid(),
                        'skill_matrix_id' => $config->skills_matrix_id,
                        'competency_area_id' => $config->competence_area_id,
                        'competency_type_id' => $config->competence_type_id,
                        'competency_description_id' => $config->competence_description_id,
                    ]);
                    $stats['migrated_details']++;
                }

                $requirements = SkillsMatrixRoleRequirment::query()
                    ->where('skills_matrix_config_id', $config->id)
                    ->get();

                foreach ($requirements as $req) {
                    $exists = SkillMatrixDetailRole::query()
                        ->where('matrix_detail_id', $detail->id)
                        ->where('role_id', $req->role_id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    if ($dryRun) {
                        $stats['migrated_roles']++;

                        continue;
                    }

                    SkillMatrixDetailRole::create([
                        'id' => (string) Str::uuid(),
                        'matrix_detail_id' => $detail->id,
                        'role_id' => $req->role_id,
                        'matrix_role_id' => $req->role_id,
                        'proficiency_id' => $req->color_code,
                    ]);
                    $stats['migrated_roles']++;
                }
            } catch (\Throwable $e) {
                $stats['errors'][] = 'Config '.$config->id.': '.$e->getMessage();
            }
        }

        return $stats;
    }
}

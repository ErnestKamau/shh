<?php

namespace App\Services\SkillsMatrix;

use App\InventoryDepartment;
use App\InventoryLocation;
use App\InventoryLocationUser;
use App\ModulePreConfigs;
use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\CapabilityMatrixDetail;
use App\Models\SkillsMatrix\CapabilityMatrixRoles;
use App\Models\SkillsMatrix\SkillMarixRole;
use App\Models\SkillsMatrix\SkillMatrixDetailRole;
use App\Models\SkillsMatrix\SkillMatrixDetails;
use App\Models\SkillsMatrix\SkillsMatrix;
use App\Models\SkillsMatrix\TrainingHeader;
use App\Models\SkillsMatrix\TrainingPlannerDetails;
use App\Models\SkillsMatrix\TrainingPlannerHeader;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SkillsMatrixDemoSeedService
{
    private const MODULE = 'Skills-Matrix';

    /** @var array<string, string> */
    private array $jobDescriptionIds = [];

    /** @var array<int, string> */
    private array $proficiencyIdsByCode = [];

    /** @var array<string, string> */
    private array $matrixRoleIdsByKey = [];

    /** @var array<string, string> */
    private array $userIdsByKey = [];

    /** @var array<string, string> */
    private array $competencyDetailIdsByName = [];

    public function __construct(
        protected TrainingNeedsService $trainingNeeds,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function run(?string $locationId = null, bool $fresh = false): array
    {
        $ctx = $this->resolveContext($locationId);

        if ($fresh) {
            $this->purgeDemoData();
        }

        return DB::transaction(function () use ($ctx, $fresh): array {
            $this->seedPreConfigs($ctx);
            $matrix = $this->seedSkillsMatrix($ctx);
            $this->seedMatrixCompetencies($matrix);
            $this->seedDemoUsers($ctx);
            $capability = $this->seedCapabilityMatrix($ctx, $matrix);

            $trainingNeed = $this->trainingNeeds->createTrainingNeedForCapability(
                $capability,
                SkillsMatrixDemoDataset::TRAINING_NEED_NAME,
                $ctx->createdBy,
            );

            $trainingPlan = $this->trainingNeeds->createTrainingPlanFromNeed(
                $trainingNeed,
                SkillsMatrixDemoDataset::TRAINING_PLAN_NAME,
                $ctx->createdBy,
            );

            $this->enrichDemoTrainingPlanSessions($trainingPlan->id);

            return [
                'location_id' => $ctx->locationId,
                'skills_matrix_id' => $matrix->id,
                'capability_id' => $capability->id,
                'training_need_id' => $trainingNeed->id,
                'training_plan_id' => $trainingPlan->id,
                'users' => count($this->userIdsByKey),
                'competencies' => count($this->competencyDetailIdsByName),
                'training_details' => \App\Models\SkillsMatrix\TrainingDetail::where('training_header_id', $trainingNeed->id)->count(),
            ];
        });
    }

    public function resolveContext(?string $locationId): SkillsMatrixDemoSeedContext
    {
        $admin = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first()
            ?? User::query()->where('active', 1)->orderBy('created_at')->first();

        if (! $admin) {
            throw new \RuntimeException('No active user found to use as created_by for demo seed.');
        }

        $resolvedLocationId = $locationId
            ?? env('SKILLS_MATRIX_SEED_LOCATION_ID')
            ?? InventoryLocationUser::where('user_id', $admin->id)->value('inventory_location_id')
            ?? InventoryLocation::query()->orderBy('name')->value('id');

        if (! $resolvedLocationId) {
            throw new \RuntimeException('Could not resolve inventory location. Pass --location-id= or set SKILLS_MATRIX_SEED_LOCATION_ID.');
        }

        $department = InventoryDepartment::query()
            ->where('module', 'organizational')
            ->where('location_id', $resolvedLocationId)
            ->where('active', 1)
            ->orderBy('name')
            ->first();

        if (! $department) {
            $location = InventoryLocation::findOrFail($resolvedLocationId);
            $department = InventoryDepartment::create([
                'name' => 'Laboratory',
                'module' => 'organizational',
                'company_id' => $location->company_id,
                'location_id' => $location->id,
                'active' => 1,
            ]);
        }

        return SkillsMatrixDemoSeedContext::fromDepartment($department, $admin);
    }

    protected function purgeDemoData(): void
    {
        $matrix = SkillsMatrix::where('name', SkillsMatrixDemoDataset::MATRIX_NAME)->first();
        $capability = CapabilityMatrix::where('name', SkillsMatrixDemoDataset::CAPABILITY_NAME)->first();

        if ($capability) {
            $trainingHeaders = TrainingHeader::where('capability_id', $capability->id)->pluck('id');
            foreach ($trainingHeaders as $headerId) {
                $planIds = TrainingPlannerHeader::where('training_need_header_id', $headerId)->pluck('id');
                if ($planIds->isNotEmpty()) {
                    DB::table('skills_training_planner_detail')
                        ->whereIn('training_plan_header_id', $planIds)
                        ->delete();
                    TrainingPlannerHeader::whereIn('id', $planIds)->delete();
                }
                DB::table('skills_training_detail')->where('training_header_id', $headerId)->delete();
                DB::table('skill_training_header_staff')->where('training_header_id', $headerId)->delete();
                TrainingHeader::where('id', $headerId)->delete();
            }

            CapabilityMatrixDetail::where('capability_id', $capability->id)->delete();
            CapabilityMatrixRoles::where('capability_id', $capability->id)->delete();
            $capability->delete();
        }

        if ($matrix) {
            $detailIds = SkillMatrixDetails::where('skill_matrix_id', $matrix->id)->pluck('id');
            if ($detailIds->isNotEmpty()) {
                SkillMatrixDetailRole::whereIn('matrix_detail_id', $detailIds)->delete();
                SkillMatrixDetails::whereIn('id', $detailIds)->delete();
            }
            SkillMarixRole::where('skills_matrix_id', $matrix->id)->delete();
            $matrix->delete();
        }

        foreach (SkillsMatrixDemoDataset::roles() as $role) {
            User::where('email', $role['user']['email'])->delete();
        }
    }

    protected function seedPreConfigs(SkillsMatrixDemoSeedContext $ctx): void
    {
        foreach (SkillsMatrixDemoDataset::proficiencyLevels() as $level) {
            $record = ModulePreConfigs::firstOrCreate(
                [
                    'name' => 'Proficiency '.$level['code'],
                    'type' => 'Proficiency',
                    'module' => self::MODULE,
                    'inventory_location_id' => $ctx->locationId,
                ],
                [
                    'description' => $level['description'],
                    'level' => $level['level'],
                    'code' => (string) $level['code'],
                    'color' => $level['color'],
                    'active' => 1,
                ],
            );
            $this->proficiencyIdsByCode[$level['code']] = (string) $record->id;
        }

        foreach (SkillsMatrixDemoDataset::roles() as $role) {
            $record = ModulePreConfigs::firstOrCreate(
                [
                    'name' => $role['name'],
                    'type' => 'Job Description',
                    'module' => self::MODULE,
                    'inventory_location_id' => $ctx->locationId,
                ],
                [
                    'description' => $role['name'],
                    'level' => 1,
                    'active' => 1,
                ],
            );
            $this->jobDescriptionIds[$role['key']] = (string) $record->id;
        }
    }

    protected function seedSkillsMatrix(SkillsMatrixDemoSeedContext $ctx): SkillsMatrix
    {
        $matrix = SkillsMatrix::firstOrCreate(
            ['name' => SkillsMatrixDemoDataset::MATRIX_NAME],
            [
                'department_id' => $ctx->departmentId,
                'matrix_role_ids' => '',
                'status' => 1,
            ],
        );

        $matrix->update(['status' => 1, 'department_id' => $ctx->departmentId]);

        foreach (SkillsMatrixDemoDataset::roles() as $role) {
            $jobId = $this->jobDescriptionIds[$role['key']];
            $matrixRole = SkillMarixRole::firstOrCreate(
                [
                    'skills_matrix_id' => $matrix->id,
                    'job_description_id' => $jobId,
                ],
            );
            $this->matrixRoleIdsByKey[$role['key']] = (string) $matrixRole->id;
        }

        return $matrix;
    }

    protected function seedMatrixCompetencies(SkillsMatrix $matrix): void
    {
        $typeCache = [];

        foreach (SkillsMatrixDemoDataset::competencySections() as $section) {
            $area = ModulePreConfigs::firstOrCreate(
                [
                    'name' => $section['area'],
                    'type' => 'Competence',
                    'module' => self::MODULE,
                ],
                [
                    'description' => $section['area'],
                    'level' => 1,
                    'active' => 1,
                    'inventory_location_id' => null,
                ],
            );

            $typeKey = $section['area'].' — Skills';
            if (! isset($typeCache[$typeKey])) {
                $typeCache[$typeKey] = ModulePreConfigs::firstOrCreate(
                    [
                        'name' => $typeKey,
                        'type' => 'Competence Type',
                        'module' => self::MODULE,
                    ],
                    [
                        'description' => $typeKey,
                        'level' => 1,
                        'active' => 1,
                        'inventory_location_id' => null,
                    ],
                );
            }
            $type = $typeCache[$typeKey];

            foreach ($section['rows'] as $row) {
                $description = ModulePreConfigs::firstOrCreate(
                    [
                        'name' => $row['name'],
                        'type' => 'Competence Description',
                        'module' => self::MODULE,
                    ],
                    [
                        'description' => $row['name'],
                        'level' => 1,
                        'active' => 1,
                    ],
                );

                $detail = SkillMatrixDetails::firstOrCreate(
                    [
                        'skill_matrix_id' => $matrix->id,
                        'competency_description_id' => $description->id,
                    ],
                    [
                        'competency_area_id' => $area->id,
                        'competency_type_id' => $type->id,
                    ],
                );

                $this->competencyDetailIdsByName[$row['name']] = (string) $detail->id;

                foreach (SkillsMatrixDemoDataset::roles() as $roleIndex => $role) {
                    $reqIndex = SkillsMatrixDemoDataset::REQ_INDEX_BY_ROLE[$roleIndex];
                    $requiredCode = $row['req'][$reqIndex] ?? $row['req'][array_key_last($row['req'])];
                    $jobId = $this->jobDescriptionIds[$role['key']];
                    $matrixRoleId = $this->matrixRoleIdsByKey[$role['key']];

                    SkillMatrixDetailRole::updateOrCreate(
                        [
                            'matrix_detail_id' => $detail->id,
                            'role_id' => $jobId,
                        ],
                        [
                            'matrix_role_id' => $matrixRoleId,
                            'proficiency_id' => $this->proficiencyIdsByCode[$requiredCode],
                        ],
                    );
                }
            }
        }
    }

    protected function seedDemoUsers(SkillsMatrixDemoSeedContext $ctx): void
    {
        foreach (SkillsMatrixDemoDataset::roles() as $role) {
            $user = User::updateOrCreate(
                ['email' => $role['user']['email']],
                [
                    'name' => $role['user']['name'],
                    'first_name' => explode(' ', $role['user']['name'])[0],
                    'last_name' => explode(' ', $role['user']['name'], 2)[1] ?? '',
                    'password' => Hash::make('Demo@2026!'),
                    'company_id' => $ctx->companyId,
                    'department_id' => $ctx->departmentId,
                    'location_id' => $ctx->locationId,
                    'position' => $this->jobDescriptionIds[$role['key']],
                    'active' => 1,
                ],
            );

            $this->userIdsByKey[$role['key']] = (string) $user->id;

            InventoryLocationUser::firstOrCreate([
                'user_id' => $user->id,
                'inventory_location_id' => $ctx->locationId,
            ]);
        }
    }

    protected function seedCapabilityMatrix(SkillsMatrixDemoSeedContext $ctx, SkillsMatrix $matrix): CapabilityMatrix
    {
        $capability = CapabilityMatrix::firstOrCreate(
            ['name' => SkillsMatrixDemoDataset::CAPABILITY_NAME],
            [
                'matrix_id' => $matrix->id,
                'created_by' => $ctx->createdBy->id,
                'status' => 1,
            ],
        );

        $capability->update([
            'matrix_id' => $matrix->id,
            'status' => 1,
            'deleted_at' => null,
        ]);

        foreach (SkillsMatrixDemoDataset::roles() as $roleIndex => $role) {
            $capRole = CapabilityMatrixRoles::firstOrCreate(
                [
                    'capability_id' => $capability->id,
                    'user_id' => $this->userIdsByKey[$role['key']],
                ],
                [
                    'skill_matrix_role_id' => $this->matrixRoleIdsByKey[$role['key']],
                    'role_id' => $this->jobDescriptionIds[$role['key']],
                    'code' => strtoupper(substr($role['key'], 0, 3)),
                ],
            );

            foreach (SkillsMatrixDemoDataset::competencySections() as $section) {
                foreach ($section['rows'] as $row) {
                    $competencyId = $this->competencyDetailIdsByName[$row['name']] ?? null;
                    if (! $competencyId) {
                        continue;
                    }

                    $actualCode = $row['cap'][$roleIndex] ?? 1;

                    CapabilityMatrixDetail::updateOrCreate(
                        [
                            'capability_id' => $capability->id,
                            'competency_id' => $competencyId,
                            'user_id' => $this->userIdsByKey[$role['key']],
                        ],
                        [
                            'proficiency_id' => $this->proficiencyIdsByCode[$actualCode],
                            'skill_matrix_role_id' => $capRole->skill_matrix_role_id,
                            'deleted_at' => null,
                        ],
                    );
                }
            }
        }

        return $capability;
    }

    protected function enrichDemoTrainingPlanSessions(string $planId): void
    {
        $samples = [
            ['week' => 10, 'trainer' => 'In-house', 'status' => 1],
            ['week' => 15, 'trainer' => 'In-house', 'status' => 1],
            ['week' => 16, 'trainer' => 'External / E-learning', 'status' => 0],
            ['week' => 19, 'trainer' => 'In-house', 'status' => 0],
            ['week' => 25, 'trainer' => 'In-house', 'status' => 0],
            ['week' => 39, 'trainer' => 'External / E-learning', 'status' => 0],
            ['week' => 50, 'trainer' => 'In-house', 'status' => 0],
        ];

        $details = TrainingPlannerDetails::query()
            ->where('training_plan_header_id', $planId)
            ->where('is_others', 0)
            ->orderBy('created_at')
            ->get();

        foreach ($details as $index => $detail) {
            $sample = $samples[$index % count($samples)];
            $detail->update([
                'week_no' => $sample['week'],
                'organizer_trainer' => $sample['trainer'],
                'status' => $sample['status'],
            ]);
        }

        TrainingPlannerDetails::create([
            'training_plan_header_id' => $planId,
            'other_competency' => 'PCR multiplex',
            'week_no' => 16,
            'organizer_trainer' => 'External / E-learning',
            'status' => 0,
            'is_others' => 1,
        ]);

        TrainingPlannerDetails::create([
            'training_plan_header_id' => $planId,
            'other_competency' => 'QMS audits training',
            'week_no' => 15,
            'organizer_trainer' => 'In-house',
            'status' => 1,
            'is_others' => 1,
        ]);
    }

}

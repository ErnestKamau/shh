<?php

namespace Database\Seeders;

use App\Company;
use App\InventoryDepartment;
use App\InventoryLocation;
use App\User;
use Database\Seeders\Concerns\AmSpecSeedData;
use App\ModulePreConfigs;
use App\Models\SkillsMatrix\SkillsMatrix;
use App\Models\SkillsMatrix\SkillsMatrixConfiguration;
use App\Models\SkillsMatrix\SkillMarixRole;
use App\Models\SkillsMatrix\SkillMatrixDetails;
use App\Models\SkillsMatrix\SkillMatrixDetailRole;
use App\Models\SkillsMatrix\SkillsMatrixRoleRequirment;
use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\CapabilityMatrixRoles;
use App\Models\SkillsMatrix\CapabilityMatrixDetail;
use App\Models\SkillsMatrix\TrainingHeader;
use App\Models\SkillsMatrix\TrainingHeaderStaff;
use App\Models\SkillsMatrix\TrainingDetail;
use App\Models\SkillsMatrix\TrainingPlannerHeader;
use App\Models\SkillsMatrix\TrainingPlannerDetails;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SkillsMatrixSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Enforce pgsql connection context.
        config(['database.default' => 'pgsql']);
        Model::unguard();

        // Clear existing Skills Matrix records before schema conversion so UUID column migrations can succeed on reruns.
        try {
            DB::connection('pgsql')->table('skills_training_planner_detail')->delete();
            DB::connection('pgsql')->table('skills_training_planner_header')->delete();
            DB::connection('pgsql')->table('skills_training_detail')->delete();
            DB::connection('pgsql')->table('skill_training_header_staff')->delete();
            DB::connection('pgsql')->table('skill_training_header')->delete();
            DB::connection('pgsql')->table('skills_matrix_role_requirments')->delete();
            DB::connection('pgsql')->table('skills_matrix_configurations')->delete();
            DB::connection('pgsql')->table('skill_capability_detail')->delete();
            DB::connection('pgsql')->table('skills_capability_matrix_role')->delete();
            DB::connection('pgsql')->table('skills_capability_matrix')->delete();
            DB::connection('pgsql')->table('skills_matrix_detail_role')->delete();
            DB::connection('pgsql')->table('skills_matrix_detail')->delete();
            DB::connection('pgsql')->table('skills_matrix_role')->delete();
            DB::connection('pgsql')->table('skillsmatrices')->delete();
        } catch (\Exception $e) {
            $this->command?->warn('Initial Skills Matrix cleanup failed: ' . $e->getMessage());
        }

        // ----------------------------------------------------------------
        // 0. Defensive Table Alterations for PostgreSQL UUID compatibility
        // IMPORTANT: Must run OUTSIDE the transaction. In PostgreSQL, a failed
        // statement inside a transaction aborts the entire block — even if caught
        // in PHP — making all subsequent queries fail. Running these beforehand
        // in their own statements avoids this "current transaction is aborted" error.
        //
        // NOTE on skillsmatrices.department_id:
        //   - It must stay/become `uuid` so the LEFT JOIN with
        //     inventory_departments.id (uuid) works in the controller.
        //   - The original migration used integer(default 0); we upgrade it to uuid.
        // ----------------------------------------------------------------

        // First, revert department_id to uuid in case a previous run left it as varchar.
        try {
            DB::connection('pgsql')->statement(
                "ALTER TABLE skillsmatrices ALTER COLUMN department_id TYPE uuid USING NULLIF(department_id, '')::uuid"
            );
        } catch (\Exception $e) {
            // Already uuid or table is empty — safe to skip.
        }

        $typeChanges = [
            'skillsmatrices' => [
                // department_id is handled above (must be uuid, not varchar)
            ],
            'skills_matrix_role' => [
                'skills_matrix_id' => 'uuid',
                'job_description_id' => 'uuid'
            ],
            'skills_matrix_configurations' => [
                'skills_matrix_id' => 'uuid',
                'competence_area_id' => 'uuid',
                'competence_type_id' => 'uuid',
                'competence_description_id' => 'uuid'
            ],
            'skills_matrix_role_requirments' => [
                'skills_matrix_config_id' => 'uuid',
                'skills_matrix_id' => 'uuid',
                'role_id' => 'uuid',
                'color_code' => 'uuid'
            ],
            'skills_matrix_detail' => [
                'skill_matrix_id' => 'uuid',
                'competency_area_id' => 'uuid',
                'competency_type_id' => 'uuid',
                'competency_description_id' => 'uuid'
            ],
            'skills_matrix_detail_role' => [
                'matrix_detail_id' => 'uuid',
                'role_id' => 'uuid',
                'matrix_role_id' => 'uuid',
                'proficiency_id' => 'uuid'
            ],
            'skills_capability_matrix' => [
                'matrix_id' => 'uuid',
                'created_by' => 'uuid'
            ],
            'skills_capability_matrix_role' => [
                'capability_id' => 'uuid',
                'skill_matrix_role_id' => 'uuid',
                'role_id' => 'uuid',
                'user_id' => 'uuid'
            ],
            'skill_capability_detail' => [
                'capability_id' => 'uuid',
                'competency_id' => 'uuid',
                'user_id' => 'uuid',
                'proficiency_id' => 'uuid',
                'skill_matrix_role_id' => 'uuid'
            ],
            'skill_training_header' => [
                'capability_id' => 'uuid',
                'created_by' => 'uuid'
            ],
            'skill_training_header_staff' => [
                'capability_matrix_role_id' => 'uuid',
                'training_header_id' => 'uuid'
            ],
            'skills_training_detail' => [
                'training_header_id' => 'uuid',
                'capability_detail_id' => 'uuid',
                'skill_matrix_role_proficiency_id' => 'uuid'
            ],
            'skills_training_planner_header' => [
                'training_need_header_id' => 'uuid',
                'created_by' => 'uuid'
            ],
            'skills_training_planner_detail' => [
                'training_plan_header_id' => 'uuid',
                'training_need_detail_id' => 'uuid'
            ]
        ];

        try {
            DB::connection('pgsql')->statement('ALTER TABLE skills_matrix_configurations ALTER COLUMN parent DROP DEFAULT');
            DB::connection('pgsql')->statement('ALTER TABLE skills_matrix_configurations ALTER COLUMN parent DROP NOT NULL');
            DB::connection('pgsql')->statement('ALTER TABLE skills_matrix_configurations ALTER COLUMN parent TYPE uuid USING NULL::uuid');
        } catch (\Exception $e) {
            // Legacy integer default/type or missing table — safe to skip.
        }

        foreach ($typeChanges as $table => $columns) {
            foreach ($columns as $column => $type) {
                try {
                    // Each statement runs independently — no wrapping transaction,
                    // so a failure on one column doesn't poison the connection state.
                    $usingClause = $type === 'uuid'
                        ? 'USING NULL::uuid'
                        : "USING {$column}::{$type}";

                    DB::connection('pgsql')->statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE {$type} {$usingClause}");
                } catch (\Exception $e) {
                    // Column already correct type or table doesn't exist yet — safe to skip.
                }
            }
        }

        try {
            DB::connection('pgsql')->statement('ALTER TABLE skills_matrix_configurations ALTER COLUMN parent DROP NOT NULL');
        } catch (\Exception $e) {
            // Already nullable or table missing — safe to skip.
        }

        // ----------------------------------------------------------------
        // Drop mismatched FK constraints that point `role_id` at the Spatie
        // `roles` table — the application actually stores module_pre_configs
        // (job description) IDs in these columns. Each runs independently.
        // ----------------------------------------------------------------
        $fksToDrop = [
            'skills_matrix_detail_role'   => 'fk_skills_matrix_detail_role_role_id_e5c99e56',
            'skills_capability_matrix_role' => 'fk_skills_capability_matrix_role_role_id_eac5517b',
            'skills_matrix_role_requirments' => 'fk_skills_matrix_role_requirments_role_id_0ecc33c0',
        ];
        foreach ($fksToDrop as $table => $constraint) {
            try {
                DB::connection('pgsql')->statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$constraint}");
            } catch (\Exception $e) {
                // Constraint already absent — safe to skip.
            }
        }

        $this->command?->info('Applied schema compatibility updates for UUID foreign keys.');

        DB::connection('pgsql')->transaction(function () {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING SKILLS MATRIX SEEDING: Initializing Module');
            $this->command?->info('====================================================');

            // ----------------------------------------------------------------
            // 1. Fetch Company & Location Context
            // ----------------------------------------------------------------
            $company = Company::first();
            if (!$company) {
                $this->command?->error('No company found! Please seed foundation tables first.');
                return;
            }
            $companyId = $company->id;

            $location = InventoryLocation::first();
            if (!$location) {
                $location = InventoryLocation::create([
                    'name' => 'Dubai Head Office Lab',
                    'company_id' => $companyId,
                    'address' => 'Dubai HQ Building',
                ]);
            }
            $locationId = $location->id;
            $locationIds = InventoryLocation::pluck('id')->all();
            if (!in_array($locationId, $locationIds, true)) {
                $locationIds[] = $locationId;
            }

            // Fetch or create organizational department
            $department = InventoryDepartment::where('name', 'Laboratory Analysis Section')->first();
            if (!$department) {
                $department = InventoryDepartment::create([
                    'name' => 'Laboratory Analysis Section',
                    'company_id' => $companyId,
                    'location_id' => $locationId,
                    'module' => 'organizational'
                ]);
            }
            $departmentId = $department->id;

            $this->command?->info('Cleared existing Skills Matrix records for seeding.');

            // ----------------------------------------------------------------
            // 2. Seed Module Pre-Configurations (`module_pre_configs`)
            // ----------------------------------------------------------------
            // Seed Proficiencies
            $proficienciesData = [
                ['name' => 'Basic', 'code' => '1', 'level' => 1, 'color' => '#FFC107', 'description' => 'Has elementary knowledge and can perform simple tasks under guidance.'],
                ['name' => 'Intermediate', 'code' => '2', 'level' => 2, 'color' => '#17A2B8', 'description' => 'Can perform routine tasks independently with standard competence.'],
                ['name' => 'Advanced', 'code' => '3', 'level' => 3, 'color' => '#007BFF', 'description' => 'Performs complex tasks independently, troubleshoots issues, and guides others.'],
                ['name' => 'Expert', 'code' => '4', 'level' => 4, 'color' => '#28A745', 'description' => 'Has deep subject matter expertise, designs new protocols, and acts as leading consultant.']
            ];
            $proficiencies = [];
            foreach ($proficienciesData as $p) {
                $prof = ModulePreConfigs::updateOrCreate(
                    [
                        'name' => $p['name'],
                        'type' => 'Proficiency',
                        'module' => 'Skills-Matrix',
                        'inventory_location_id' => $locationId
                    ],
                    [
                        'code' => $p['code'],
                        'level' => $p['level'],
                        'color' => $p['color'],
                        'description' => $p['description'],
                        'active' => true
                    ]
                );
                $proficiencies[$p['name']] = $prof;
            }

            // Seed Job Descriptions
            $jobDescriptionsData = [
                ['name' => 'Senior Analytical Chemist', 'description' => 'Responsible for complex sample analysis, method validation, and quality leadership.'],
                ['name' => 'Laboratory Analyst', 'description' => 'Performs routine and advanced analytical testing on client samples.'],
                ['name' => 'Quality Assurance Officer', 'description' => 'Maintains ISO 17025 compliance, audits procedures, and manages SOP documentation.']
            ];
            $jobDescriptions = [];
            foreach ($jobDescriptionsData as $j) {
                $jd = ModulePreConfigs::updateOrCreate(
                    [
                        'name' => $j['name'],
                        'type' => 'Job Description',
                        'module' => 'Personnel-Management',
                        'inventory_location_id' => $locationId
                    ],
                    [
                        'description' => $j['description'],
                        'active' => true
                    ]
                );
                $jobDescriptions[$j['name']] = $jd;
            }

            // Seed Competency Categories (Competence Areas)
            $competenceAreasData = [
                ['name' => 'Analytical Testing', 'level' => 1, 'description' => 'Sample chemical and elemental analysis competency.'],
                ['name' => 'Instrument Operation', 'level' => 2, 'description' => 'Operation and daily maintenance of sophisticated scientific instruments.'],
                ['name' => 'Quality & Compliance', 'level' => 3, 'description' => 'SOP protocols, laboratory safety, and general compliance standards.']
            ];
            $competenceAreas = [];
            foreach ($competenceAreasData as $ca) {
                $cArea = ModulePreConfigs::updateOrCreate(
                    [
                        'name' => $ca['name'],
                        'type' => 'Competence',
                        'module' => 'Skills-Matrix',
                        'inventory_location_id' => $locationId
                    ],
                    [
                        'level' => $ca['level'],
                        'description' => $ca['description'],
                        'active' => true
                    ]
                );
                $competenceAreas[$ca['name']] = $cArea;
            }

            // Seed Competency Subcategories (Competence Types)
            $competenceTypesData = [
                ['name' => 'Chromatography', 'level' => 1, 'description' => 'GC-MS, HPLC, and gas/liquid chromatographic separations.'],
                ['name' => 'Spectroscopy', 'level' => 2, 'description' => 'ICP-MS, FTIR, and atomic absorption spectroscopic techniques.'],
                ['name' => 'SOP Compliance', 'level' => 3, 'description' => 'Standard operating procedures execution and maintenance.']
            ];
            $competenceTypes = [];
            foreach ($competenceTypesData as $ct) {
                $cType = ModulePreConfigs::updateOrCreate(
                    [
                        'name' => $ct['name'],
                        'type' => 'Competence Type',
                        'module' => 'Skills-Matrix',
                        'inventory_location_id' => $locationId
                    ],
                    [
                        'level' => $ct['level'],
                        'description' => $ct['description'],
                        'active' => true
                    ]
                );
                $competenceTypes[$ct['name']] = $cType;
            }

            // Seed Competency Descriptions
            $competenceDescriptionsData = [
                ['name' => 'Ability to operate GC-MS system', 'level' => 1, 'description' => 'Independent sample preparation, column setup, and spectral analysis for GC-MS.'],
                ['name' => 'Ability to operate ICP-MS spectrometer', 'level' => 2, 'description' => 'Sample dissolution, tuning parameters, and heavy metal concentrations screening.'],
                ['name' => 'Ability to maintain ISO 17025 compliance', 'level' => 3, 'description' => 'Detailed understanding of audit trails, calibration validation, and error log handling.']
            ];
            $competenceDescriptions = [];
            foreach ($competenceDescriptionsData as $cd) {
                $cDesc = ModulePreConfigs::updateOrCreate(
                    [
                        'name' => $cd['name'],
                        'type' => 'Competence Description',
                        'module' => 'Skills-Matrix',
                        'inventory_location_id' => $locationId
                    ],
                    [
                        'level' => $cd['level'],
                        'description' => $cd['description'],
                        'active' => true
                    ]
                );
                $competenceDescriptions[$cd['name']] = $cDesc;
            }

            // Seed Training Type States
            $trainingTypesData = [
                ['name' => 'Needs Training', 'code' => 'NT', 'level' => 1, 'description' => 'Assigned staff needs structured training.'],
                ['name' => 'Trained & Certified', 'code' => 'TC', 'level' => 2, 'description' => 'Assigned staff is fully trained and certified.']
            ];
            foreach ($trainingTypesData as $tt) {
                ModulePreConfigs::updateOrCreate(
                    [
                        'name' => $tt['name'],
                        'type' => 'Training',
                        'module' => 'Skills-Matrix',
                        'inventory_location_id' => $locationId
                    ],
                    [
                        'code' => $tt['code'],
                        'level' => $tt['level'],
                        'description' => $tt['description'],
                        'active' => true
                    ]
                );
            }

            $otherLocationIds = array_values(array_filter($locationIds, function ($seedLocationId) use ($locationId) {
                return (string) $seedLocationId !== (string) $locationId;
            }));

            foreach ($otherLocationIds as $seedLocationId) {
                foreach ($proficienciesData as $p) {
                    ModulePreConfigs::updateOrCreate(
                        [
                            'name' => $p['name'],
                            'type' => 'Proficiency',
                            'module' => 'Skills-Matrix',
                            'inventory_location_id' => $seedLocationId
                        ],
                        [
                            'code' => $p['code'],
                            'level' => $p['level'],
                            'color' => $p['color'],
                            'description' => $p['description'],
                            'active' => true
                        ]
                    );
                }

                foreach ($jobDescriptionsData as $j) {
                    ModulePreConfigs::updateOrCreate(
                        [
                            'name' => $j['name'],
                            'type' => 'Job Description',
                            'module' => 'Personnel-Management',
                            'inventory_location_id' => $seedLocationId
                        ],
                        [
                            'description' => $j['description'],
                            'active' => true
                        ]
                    );
                }

                foreach ($competenceAreasData as $ca) {
                    ModulePreConfigs::updateOrCreate(
                        [
                            'name' => $ca['name'],
                            'type' => 'Competence',
                            'module' => 'Skills-Matrix',
                            'inventory_location_id' => $seedLocationId
                        ],
                        [
                            'level' => $ca['level'],
                            'description' => $ca['description'],
                            'active' => true
                        ]
                    );
                }

                foreach ($competenceTypesData as $ct) {
                    ModulePreConfigs::updateOrCreate(
                        [
                            'name' => $ct['name'],
                            'type' => 'Competence Type',
                            'module' => 'Skills-Matrix',
                            'inventory_location_id' => $seedLocationId
                        ],
                        [
                            'level' => $ct['level'],
                            'description' => $ct['description'],
                            'active' => true
                        ]
                    );
                }

                foreach ($competenceDescriptionsData as $cd) {
                    ModulePreConfigs::updateOrCreate(
                        [
                            'name' => $cd['name'],
                            'type' => 'Competence Description',
                            'module' => 'Skills-Matrix',
                            'inventory_location_id' => $seedLocationId
                        ],
                        [
                            'level' => $cd['level'],
                            'description' => $cd['description'],
                            'active' => true
                        ]
                    );
                }

                foreach ($trainingTypesData as $tt) {
                    ModulePreConfigs::updateOrCreate(
                        [
                            'name' => $tt['name'],
                            'type' => 'Training',
                            'module' => 'Skills-Matrix',
                            'inventory_location_id' => $seedLocationId
                        ],
                        [
                            'code' => $tt['code'],
                            'level' => $tt['level'],
                            'description' => $tt['description'],
                            'active' => true
                        ]
                    );
                }
            }

            $this->command?->info('Seeded all Module Pre-Configurations dictionary values.');

            // ----------------------------------------------------------------
            // 3. Retrieve or Create System Users
            // ----------------------------------------------------------------
            $users = User::all();
            if ($users->count() < 3) {
                $seedUsers = [
                    [
                        'name' => 'Sarah Jenkins',
                        'email' => AmSpecSeedData::seedUserEmail('sarah'),
                        'password' => bcrypt('password'),
                        'active' => 1,
                        'company_id' => $companyId,
                        'location_id' => $locationId,
                    ],
                    [
                        'name' => 'John Doe',
                        'email' => AmSpecSeedData::seedUserEmail('john'),
                        'password' => bcrypt('password'),
                        'active' => 1,
                        'company_id' => $companyId,
                        'location_id' => $locationId,
                    ],
                    [
                        'name' => 'Alice Smith',
                        'email' => AmSpecSeedData::seedUserEmail('alice'),
                        'password' => bcrypt('password'),
                        'active' => 1,
                        'company_id' => $companyId,
                        'location_id' => $locationId,
                    ],
                ];
                foreach ($seedUsers as $su) {
                    User::create($su);
                }
                $users = User::all();
            }

            // Assign position/job description to these users so relationships resolve beautifully
            $userSarah = $users->first();
            $userSarah->update(['position' => $jobDescriptions['Senior Analytical Chemist']->id, 'department_id' => $departmentId]);

            $userJohn = $users->skip(1)->first() ?? $users->first();
            $userJohn->update(['position' => $jobDescriptions['Laboratory Analyst']->id, 'department_id' => $departmentId]);

            $userAlice = $users->skip(2)->first() ?? $userJohn;
            $userAlice->update(['position' => $jobDescriptions['Quality Assurance Officer']->id, 'department_id' => $departmentId]);

            $this->command?->info('Seeded and linked 3 active laboratory section users.');

            // ----------------------------------------------------------------
            // 4. Create Skills Matrix Instance (`skillsmatrices` & `skills_matrix_role`)
            // ----------------------------------------------------------------
            $skillsMatrix = SkillsMatrix::create([
                'name' => 'Analytical Chemistry Skills Matrix',
                'department_id' => $departmentId,
                'matrix_role_ids' => json_encode(array_values(collect($jobDescriptions)->pluck('id')->toArray())),
                'status' => true,
            ]);

            // Add Skill Matrix Roles
            $smRoles = [];
            foreach ($jobDescriptions as $name => $jd) {
                $smRole = SkillMarixRole::create([
                    'skills_matrix_id' => $skillsMatrix->id,
                    'job_description_id' => $jd->id,
                ]);
                $smRoles[$name] = $smRole;
            }
            $this->command?->info('Seeded main Skills Matrix structure and mapped role bridges.');

            // ----------------------------------------------------------------
            // 5. Mapped Competencies (`skills_matrix_detail` & `skills_matrix_detail_role`)
            // ----------------------------------------------------------------
            // Map 3 competencies onto this matrix
            $competencyMappings = [
                [
                    'area' => 'Analytical Testing',
                    'type' => 'Chromatography',
                    'desc' => 'Ability to operate GC-MS system',
                    // Role required proficiencies:
                    'roles_req' => [
                        'Senior Analytical Chemist' => 'Advanced',
                        'Laboratory Analyst' => 'Advanced',
                        'Quality Assurance Officer' => 'Intermediate'
                    ]
                ],
                [
                    'area' => 'Instrument Operation',
                    'type' => 'Spectroscopy',
                    'desc' => 'Ability to operate ICP-MS spectrometer',
                    'roles_req' => [
                        'Senior Analytical Chemist' => 'Expert',
                        'Laboratory Analyst' => 'Advanced',
                        'Quality Assurance Officer' => 'Basic'
                    ]
                ],
                [
                    'area' => 'Quality & Compliance',
                    'type' => 'SOP Compliance',
                    'desc' => 'Ability to maintain ISO 17025 compliance',
                    'roles_req' => [
                        'Senior Analytical Chemist' => 'Advanced',
                        'Laboratory Analyst' => 'Intermediate',
                        'Quality Assurance Officer' => 'Expert'
                    ]
                ]
            ];

            // Seed the matrix configuration tree used by the configuration dashboard.
            foreach ($competencyMappings as $mapping) {
                $areaConfig = SkillsMatrixConfiguration::create([
                    'skills_matrix_id' => $skillsMatrix->id,
                    'competence_area_id' => $competenceAreas[$mapping['area']]->id,
                    'name' => $mapping['area'],
                    'parent' => null,
                    'level' => 1,
                    'active' => true,
                ]);

                $typeConfig = SkillsMatrixConfiguration::create([
                    'skills_matrix_id' => $skillsMatrix->id,
                    'competence_area_id' => $competenceAreas[$mapping['area']]->id,
                    'competence_type_id' => $competenceTypes[$mapping['type']]->id,
                    'name' => $mapping['type'],
                    'parent' => $areaConfig->id,
                    'level' => 2,
                    'active' => true,
                ]);

                $leafConfig = SkillsMatrixConfiguration::create([
                    'skills_matrix_id' => $skillsMatrix->id,
                    'competence_area_id' => $competenceAreas[$mapping['area']]->id,
                    'competence_type_id' => $competenceTypes[$mapping['type']]->id,
                    'competence_description_id' => $competenceDescriptions[$mapping['desc']]->id,
                    'name' => $mapping['desc'],
                    'parent' => $typeConfig->id,
                    'level' => 3,
                    'active' => true,
                ]);

                foreach ($mapping['roles_req'] as $roleName => $profName) {
                    SkillsMatrixRoleRequirment::create([
                        'skills_matrix_config_id' => $leafConfig->id,
                        'skills_matrix_id' => $skillsMatrix->id,
                        'role_id' => $jobDescriptions[$roleName]->id,
                        'role_name' => $jobDescriptions[$roleName]->name,
                        'color_code' => $proficiencies[$profName]->id,
                    ]);
                }
            }

            $this->command?->info('Seeded Skills Matrix configuration hierarchy and role requirements.');

            $matrixDetails = [];
            $detailRoles = [];

            foreach ($competencyMappings as $mapping) {
                $detail = SkillMatrixDetails::create([
                    'skill_matrix_id' => $skillsMatrix->id,
                    'competency_area_id' => $competenceAreas[$mapping['area']]->id,
                    'competency_type_id' => $competenceTypes[$mapping['type']]->id,
                    'competency_description_id' => $competenceDescriptions[$mapping['desc']]->id,
                ]);
                $matrixDetails[] = $detail;

                // Create target proficiency requirements for roles
                foreach ($mapping['roles_req'] as $roleName => $profName) {
                    $detailRole = SkillMatrixDetailRole::create([
                        'matrix_detail_id' => $detail->id,
                        'role_id' => $jobDescriptions[$roleName]->id,
                        'matrix_role_id' => $smRoles[$roleName]->id,
                        'proficiency_id' => $proficiencies[$profName]->id,
                    ]);
                    $detailRoles[$detail->id][$roleName] = $detailRole;
                }
            }
            $this->command?->info('Seeded Competency items and mapped target proficiency parameters for all roles.');

            // ----------------------------------------------------------------
            // 6. Capability Assessment Matrix (`skills_capability_matrix` & child user linkages)
            // ----------------------------------------------------------------
            $capabilityMatrix = CapabilityMatrix::create([
                'name' => 'Q2 2026 Lab Staff Capability Assessment',
                'matrix_id' => $skillsMatrix->id,
                'created_by' => $userSarah->id,
                'status' => true,
            ]);

            // Assign users as capability roles
            $capSarah = CapabilityMatrixRoles::create([
                'capability_id' => $capabilityMatrix->id,
                'skill_matrix_role_id' => $smRoles['Senior Analytical Chemist']->id,
                'role_id' => $jobDescriptions['Senior Analytical Chemist']->id,
                'user_id' => $userSarah->id,
                'code' => 'SJ',
            ]);

            $capJohn = CapabilityMatrixRoles::create([
                'capability_id' => $capabilityMatrix->id,
                'skill_matrix_role_id' => $smRoles['Laboratory Analyst']->id,
                'role_id' => $jobDescriptions['Laboratory Analyst']->id,
                'user_id' => $userJohn->id,
                'code' => 'JD',
            ]);

            $capAlice = CapabilityMatrixRoles::create([
                'capability_id' => $capabilityMatrix->id,
                'skill_matrix_role_id' => $smRoles['Quality Assurance Officer']->id,
                'role_id' => $jobDescriptions['Quality Assurance Officer']->id,
                'user_id' => $userAlice->id,
                'code' => 'AS',
            ]);
            $this->command?->info('Initialized Capability Assessment Matrix and bound system users.');

            // ----------------------------------------------------------------
            // 7. Seed User Actual Proficiencies (`skill_capability_detail`)
            // ----------------------------------------------------------------
            // For John Doe (Laboratory Analyst):
            // - GC-MS target: Advanced (code 3). We set John's actual: Intermediate (code 2) -> REQUIRES TRAINING!
            // - ICP-MS target: Advanced (code 3). We set John's actual: Intermediate (code 2) -> REQUIRES TRAINING!
            // - ISO 17025 target: Intermediate (code 2). We set John's actual: Intermediate (code 2) -> COMPETENT!
            // For Sarah Jenkins (Senior Analytical Chemist):
            // - All targets: Expert/Advanced. We set actuals: Expert/Advanced -> COMPETENT!
            // For Alice Smith (Quality Assurance Officer):
            // - ISO 17025 target: Expert (code 4). We set Alice's actual: Advanced (code 3) -> REQUIRES TRAINING!
            
            $johnActuals = [
                'Ability to operate GC-MS system' => 'Intermediate',
                'Ability to operate ICP-MS spectrometer' => 'Intermediate',
                'Ability to maintain ISO 17025 compliance' => 'Intermediate'
            ];

            $sarahActuals = [
                'Ability to operate GC-MS system' => 'Advanced',
                'Ability to operate ICP-MS spectrometer' => 'Expert',
                'Ability to maintain ISO 17025 compliance' => 'Advanced'
            ];

            $aliceActuals = [
                'Ability to operate GC-MS system' => 'Intermediate',
                'Ability to operate ICP-MS spectrometer' => 'Basic',
                'Ability to maintain ISO 17025 compliance' => 'Advanced'
            ];

            $capDetails = [];

            foreach ($matrixDetails as $detail) {
                $compDescName = $detail->competencydescription->name;

                // Sarah
                $capDetails[] = CapabilityMatrixDetail::create([
                    'capability_id' => $capabilityMatrix->id,
                    'competency_id' => $detail->id,
                    'user_id' => $userSarah->id,
                    'proficiency_id' => $proficiencies[$sarahActuals[$compDescName]]->id,
                    'skill_matrix_role_id' => $smRoles['Senior Analytical Chemist']->id,
                ]);

                // John
                $capDetails[] = CapabilityMatrixDetail::create([
                    'capability_id' => $capabilityMatrix->id,
                    'competency_id' => $detail->id,
                    'user_id' => $userJohn->id,
                    'proficiency_id' => $proficiencies[$johnActuals[$compDescName]]->id,
                    'skill_matrix_role_id' => $smRoles['Laboratory Analyst']->id,
                ]);

                // Alice
                $capDetails[] = CapabilityMatrixDetail::create([
                    'capability_id' => $capabilityMatrix->id,
                    'competency_id' => $detail->id,
                    'user_id' => $userAlice->id,
                    'proficiency_id' => $proficiencies[$aliceActuals[$compDescName]]->id,
                    'skill_matrix_role_id' => $smRoles['Quality Assurance Officer']->id,
                ]);
            }
            $this->command?->info('Seeded user actual current proficiency scores, creating structured gaps.');

            // ----------------------------------------------------------------
            // 8. Seed Training Needs (`skill_training_header` & `skills_training_detail`)
            // ----------------------------------------------------------------
            $trainingHeader = TrainingHeader::create([
                'name' => 'Spectroscopy & QA Protocols Training Need',
                'capability_id' => $capabilityMatrix->id,
                'created_by' => $userSarah->id,
            ]);

            // Add staffs to the training header
            TrainingHeaderStaff::create([
                'capability_matrix_role_id' => $capJohn->id,
                'training_header_id' => $trainingHeader->id,
            ]);
            TrainingHeaderStaff::create([
                'capability_matrix_role_id' => $capAlice->id,
                'training_header_id' => $trainingHeader->id,
            ]);

            // Calculate gaps and insert details
            // Gap 1: John Doe on GC-MS (Target: Advanced, Actual: Intermediate)
            $gcMsDetail = collect($matrixDetails)->first(function($d) {
                return $d->competencydescription->name === 'Ability to operate GC-MS system';
            });
            $gcMsCapDetailJohn = collect($capDetails)->first(function($d) use ($userJohn, $gcMsDetail) {
                return $d->user_id === $userJohn->id && $d->competency_id === $gcMsDetail->id;
            });
            $gcMsDetailRoleJohn = $detailRoles[$gcMsDetail->id]['Laboratory Analyst'];

            TrainingDetail::create([
                'training_header_id' => $trainingHeader->id,
                'capability_detail_id' => $gcMsCapDetailJohn->id,
                'skill_matrix_role_proficiency_id' => $gcMsDetailRoleJohn->id,
                'require_training' => 1,
            ]);

            // Gap 2: John Doe on ICP-MS (Target: Advanced, Actual: Intermediate)
            $icpMsDetail = collect($matrixDetails)->first(function($d) {
                return $d->competencydescription->name === 'Ability to operate ICP-MS spectrometer';
            });
            $icpMsCapDetailJohn = collect($capDetails)->first(function($d) use ($userJohn, $icpMsDetail) {
                return $d->user_id === $userJohn->id && $d->competency_id === $icpMsDetail->id;
            });
            $icpMsDetailRoleJohn = $detailRoles[$icpMsDetail->id]['Laboratory Analyst'];

            TrainingDetail::create([
                'training_header_id' => $trainingHeader->id,
                'capability_detail_id' => $icpMsCapDetailJohn->id,
                'skill_matrix_role_proficiency_id' => $icpMsDetailRoleJohn->id,
                'require_training' => 1,
            ]);

            // Gap 3: Alice Smith on ISO 17025 compliance (Target: Expert, Actual: Advanced)
            $isoDetail = collect($matrixDetails)->first(function($d) {
                return $d->competencydescription->name === 'Ability to maintain ISO 17025 compliance';
            });
            $isoCapDetailAlice = collect($capDetails)->first(function($d) use ($userAlice, $isoDetail) {
                return $d->user_id === $userAlice->id && $d->competency_id === $isoDetail->id;
            });
            $isoDetailRoleAlice = $detailRoles[$isoDetail->id]['Quality Assurance Officer'];

            TrainingDetail::create([
                'training_header_id' => $trainingHeader->id,
                'capability_detail_id' => $isoCapDetailAlice->id,
                'skill_matrix_role_proficiency_id' => $isoDetailRoleAlice->id,
                'require_training' => 1,
            ]);

            $this->command?->info('Evaluated skill proficiency gaps and populated Training Needs.');

            // ----------------------------------------------------------------
            // 9. Seed Training Planner (`skills_training_planner_header` & `detail`)
            // ----------------------------------------------------------------
            $plannerHeader = TrainingPlannerHeader::create([
                'name' => 'Q3 Analytical Training Plan',
                'training_need_header_id' => $trainingHeader->id,
                'is_complete' => false,
                'created_by' => $userSarah->id,
            ]);

            // Retrieve the training details we just created to schedule them in the planner
            $tDetails = TrainingDetail::query()->where('training_header_id', $trainingHeader->id)->get();

            $plannerDetails = [
                [
                    'training_need_detail_id' => $tDetails[0]->id,
                    'training_start_date' => '2026-06-08 09:00:00',
                    'training_end_date' => '2026-06-12 17:00:00',
                    'week_no' => 'Week 24',
                    'organizer_trainer' => 'Agilent Analytical Academy (Instructor: Dr. Alan Grant)',
                    'remark' => 'Advanced GC-MS instrumentation course covering chromatography calibration, tuning, and method optimization.',
                    'status' => 0, // Scheduled
                ],
                [
                    'training_need_detail_id' => $tDetails[1]->id,
                    'training_start_date' => '2026-06-22 09:00:00',
                    'training_end_date' => '2026-06-26 17:00:00',
                    'week_no' => 'Week 26',
                    'organizer_trainer' => 'PerkinElmer Spectroscopic Training Centre',
                    'remark' => 'High accuracy ICP-MS spectrometry analysis course with heavy focus on sample dilution and trace elements validation.',
                    'status' => 0, // Scheduled
                ],
                [
                    'training_need_detail_id' => $tDetails[2]->id,
                    'training_start_date' => '2026-07-06 09:00:00',
                    'training_end_date' => '2026-07-08 17:00:00',
                    'week_no' => 'Week 28',
                    'organizer_trainer' => 'International Quality Systems Inc.',
                    'remark' => 'Intensive audit readiness and laboratory ISO 17025 documentation protocols training.',
                    'status' => 0, // Scheduled
                ]
            ];

            foreach ($plannerDetails as $pd) {
                TrainingPlannerDetails::create([
                    'training_plan_header_id' => $plannerHeader->id,
                    'training_need_detail_id' => $pd['training_need_detail_id'],
                    'training_start_date' => $pd['training_start_date'],
                    'training_end_date' => $pd['training_end_date'],
                    'week_no' => $pd['week_no'],
                    'organizer_trainer' => $pd['organizer_trainer'],
                    'remark' => $pd['remark'],
                    'status' => $pd['status'],
                    'is_others' => false,
                ]);
            }

            $this->command?->info('Constructed detailed Q3 Training Plan schedule details.');

            $this->command?->info('====================================================');
            $this->command?->info('SUCCESS: SKILLS MATRIX MODULE COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });
    }
}

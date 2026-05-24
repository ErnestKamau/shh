<?php

namespace App\Services\SkillsMatrix;

/**
 * Demo skills-matrix content from docs/lab_skills_capability_system.html skillsData.
 */
final class SkillsMatrixDemoDataset
{
    public const MATRIX_NAME = 'GCLA Lab Skills Matrix';

    public const CAPABILITY_NAME = 'Lab Team Capability 2026';

    public const TRAINING_NEED_NAME = 'Lab Team Training Needs 2026';

    public const TRAINING_PLAN_NAME = 'Lab Training Plan 2026';

    /** @var array<int, string> */
    public const ROLE_KEYS = ['lab_lead', 'lab_tech', 'lab_asst_1', 'lab_asst_2', 'lab_gw'];

    /**
     * Maps each role to index in mockup req[] (4 columns: lead, tech, asst, gw).
     *
     * @var array<int, int>
     */
    public const REQ_INDEX_BY_ROLE = [0, 1, 2, 2, 3];

    /** @return array<int, array{key: string, name: string, user: array{name: string, email: string}}> */
    public static function roles(): array
    {
        return [
            ['key' => 'lab_lead', 'name' => 'Lab Lead', 'user' => ['name' => 'Judith Chumba', 'email' => 'judith.chumba@skills-demo.local']],
            ['key' => 'lab_tech', 'name' => 'Lab Technician', 'user' => ['name' => 'Naomi Omenge', 'email' => 'naomi.omenge@skills-demo.local']],
            ['key' => 'lab_asst_1', 'name' => 'Lab Assistant 1', 'user' => ['name' => 'Abraham Simiyu', 'email' => 'abraham.simiyu@skills-demo.local']],
            ['key' => 'lab_asst_2', 'name' => 'Lab Assistant 2', 'user' => ['name' => 'Sarah Ngotho', 'email' => 'sarah.ngotho@skills-demo.local']],
            ['key' => 'lab_gw', 'name' => 'Lab GW', 'user' => ['name' => 'John Nzioki', 'email' => 'john.nzioki@skills-demo.local']],
        ];
    }

    /**
     * @return array<int, array{area: string, rows: array<int, array{name: string, req: array<int, int>, cap: array<int, int>}>}>
     */
    public static function competencySections(): array
    {
        return [
            [
                'area' => 'Laboratory — General',
                'rows' => [
                    ['name' => 'Good lab practice', 'req' => [3, 3, 3, 3], 'cap' => [3, 3, 3, 3, 3]],
                    ['name' => 'PPE usage', 'req' => [3, 3, 2, 3], 'cap' => [3, 3, 3, 3, 3]],
                    ['name' => 'Waste & spill management', 'req' => [2, 2, 2, 2], 'cap' => [2, 2, 3, 3, 3]],
                    ['name' => 'Emergency & first aid', 'req' => [2, 3, 2, 2], 'cap' => [2, 2, 3, 2, 2]],
                    ['name' => 'Use of MSDS sheets', 'req' => [3, 3, 2, 2], 'cap' => [3, 2, 2, 2, 2]],
                ],
            ],
            [
                'area' => 'Administration',
                'rows' => [
                    ['name' => 'Phyto team space — common use', 'req' => [2, 2, 1, 1], 'cap' => [2, 1, 1, 1, 1]],
                    ['name' => 'Lab book administration', 'req' => [3, 2, 2, 2], 'cap' => [3, 1, 1, 1, 1]],
                    ['name' => 'Equipment & Chemicals admin', 'req' => [3, 3, 2, 2], 'cap' => [3, 3, 2, 1, 1]],
                    ['name' => 'Order management', 'req' => [3, 3, 2, 1], 'cap' => [3, 2, 2, 2, 1]],
                    ['name' => 'Sample handling in the lab', 'req' => [3, 3, 3, 3], 'cap' => [3, 2, 3, 3, 3]],
                ],
            ],
            [
                'area' => 'Basic Lab Skills',
                'rows' => [
                    ['name' => 'Pipetting', 'req' => [3, 3, 2, 2], 'cap' => [3, 3, 3, 3, 3]],
                    ['name' => 'Calculations', 'req' => [3, 3, 2, 1], 'cap' => [3, 2, 3, 2, 2]],
                    ['name' => 'Making buffers', 'req' => [3, 3, 2, 2], 'cap' => [3, 3, 3, 3, 3]],
                    ['name' => 'Making medium', 'req' => [3, 3, 2, 2], 'cap' => [3, 2, 3, 3, 3]],
                    ['name' => 'Handling chemicals', 'req' => [3, 2, 2, 2], 'cap' => [3, 3, 2, 2, 2]],
                    ['name' => 'Perform batch control & evaluate', 'req' => [2, 2, 1, 1], 'cap' => [2, 3, 1, 1, 1]],
                    ['name' => 'Bacterial dilutions and plating', 'req' => [3, 2, 2, 2], 'cap' => [3, 2, 3, 3, 3]],
                    ['name' => 'Preparing positive controls', 'req' => [2, 3, 1, 1], 'cap' => [2, 2, 3, 3, 2]],
                    ['name' => 'Maintenance of reference material', 'req' => [2, 2, 1, 1], 'cap' => [2, 1, 2, 2, 2]],
                    ['name' => 'Grinding samples', 'req' => [3, 3, 3, 3], 'cap' => [3, 3, 3, 3, 3]],
                ],
            ],
            [
                'area' => 'Management',
                'rows' => [
                    ['name' => 'Time management', 'req' => [3, 3, 2, 2], 'cap' => [2, 2, 2, 2, 2]],
                    ['name' => 'Problem solving skills', 'req' => [3, 3, 2, 2], 'cap' => [3, 2, 2, 2, 1]],
                    ['name' => 'Managing performance', 'req' => [3, 2, 2, 1], 'cap' => [2, 2, 1, 1, 1]],
                    ['name' => 'Complaint handling', 'req' => [3, 3, 2, 1], 'cap' => [3, 3, 2, 2, 2]],
                    ['name' => 'Planning and organizing', 'req' => [3, 2, 2, 2], 'cap' => [3, 2, 2, 2, 2]],
                    ['name' => 'Risk management', 'req' => [3, 3, 2, 2], 'cap' => [3, 1, 1, 1, 1]],
                    ['name' => 'Interpersonal skills', 'req' => [3, 3, 2, 2], 'cap' => [3, 3, 2, 2, 2]],
                    ['name' => 'Strategic thinking', 'req' => [3, 2, 2, 1], 'cap' => [3, 2, 1, 1, 1]],
                    ['name' => 'Negotiation skills', 'req' => [3, 2, 2, 1], 'cap' => [2, 2, 1, 1, 1]],
                ],
            ],
            [
                'area' => 'IT & Software',
                'rows' => [
                    ['name' => 'Microsoft Word', 'req' => [3, 2, 2, 2], 'cap' => [3, 3, 2, 2, 1]],
                    ['name' => 'Microsoft Excel', 'req' => [3, 2, 2, 2], 'cap' => [2, 2, 2, 2, 1]],
                    ['name' => 'Microsoft PowerPoint', 'req' => [3, 2, 2, 1], 'cap' => [3, 3, 2, 2, 1]],
                    ['name' => 'Microsoft Outlook', 'req' => [3, 2, 2, 1], 'cap' => [2, 3, 2, 2, 1]],
                    ['name' => 'Genstat', 'req' => [1, 1, 1, 1], 'cap' => [1, 1, 1, 1, 1]],
                    ['name' => 'SPSS', 'req' => [1, 1, 1, 1], 'cap' => [1, 1, 1, 2, 1]],
                    ['name' => 'Computer programming', 'req' => [2, 1, 1, 1], 'cap' => [2, 1, 1, 1, 1]],
                ],
            ],
        ];
    }

    /** @return array<int, array{level: int, code: int, description: string, color: string}> */
    public static function proficiencyLevels(): array
    {
        return [
            ['level' => 1, 'code' => 1, 'description' => 'Level 1 — No experience', 'color' => '#EF4444'],
            ['level' => 2, 'code' => 2, 'description' => 'Level 2 — Competent', 'color' => '#D97706'],
            ['level' => 3, 'code' => 3, 'description' => 'Level 3 — Highly proficient', 'color' => '#16A34A'],
        ];
    }
}

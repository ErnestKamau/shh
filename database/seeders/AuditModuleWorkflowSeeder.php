<?php

namespace Database\Seeders;

use App\Company;
use App\Models\AuditModule\Audit;
use App\Models\AuditModule\AuditChecklist;
use App\Models\AuditModule\AuditChecklistItem;
use App\Models\AuditModule\AuditChecklistItemResponse;
use App\Models\AuditModule\AuditModuleFinding;
use App\Models\AuditModule\AuditStatus;
use App\Models\AuditModule\AuditTeamMember;
use App\Models\AuditModule\AuditTeamRole;
use App\Models\AuditModule\AuditType;
use App\Models\AuditModule\AuditWorkflowApprover;
use App\Models\AuditModule\CapaActionType;
use App\Models\AuditModule\CapaCategory;
use App\Models\AuditModule\CapaPriority;
use App\Models\AuditModule\CapaStatus;
use App\Models\AuditModule\ComplianceStatus;
use App\Models\AuditModule\CorrectiveAction;
use App\Models\AuditModule\FindingCategory;
use App\Models\AuditModule\FindingStatus;
use App\Models\AuditModule\LikelihoodScale;
use App\Models\AuditModule\NcOrigin;
use App\Models\AuditModule\NcStatus;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\RcaStatus;
use App\Models\AuditModule\RiskLevel;
use App\Models\AuditModule\RootCauseAnalysis;
use App\Models\AuditModule\RootCauseMethod;
use App\Models\AuditModule\SeverityScale;
use App\Models\AuditModule\VerificationClosureStatus;
use App\Models\AuditModule\VerificationRecord;
use App\Models\AuditModule\VerificationResult;
use App\Models\AuditModule\WorkflowAction;
use App\Models\AuditModule\WorkflowActionRule;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuditModuleWorkflowSeeder extends Seeder
{
    private const DEMO_PREFIX = 'DEMO-AUD-';

    /** @var array<string, mixed> */
    private array $ctx = [];

    /** @var array<string, mixed> */
    private array $config = [];

    public function run(): void
    {
        Model::unguard();

        DB::transaction(function () {
            $this->resolveContext();
            $this->command?->info('Audit module seeder — company: '.$this->ctx['company_name'].' (id: '.$this->ctx['company_id'].')');

            $this->seedConfiguration();
            $this->seedAuditsAcrossWorkflows();

            $this->command?->info('Audit module workflow demo data seeded successfully.');
        });

        Model::reguard();
    }

    private function resolveContext(): void
    {
        $company = Company::query()->where('active', true)->orderBy('name')->first()
            ?? Company::query()->orderBy('name')->first();

        if (! $company) {
            throw new \RuntimeException('No company found. Run SystemSetupSeeder or create a company first.');
        }

        $user = User::query()->where('active', 1)->where('is_client', 0)->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();

        if (! $user) {
            throw new \RuntimeException('No user found. Create at least one user before seeding the audit module.');
        }

        $this->ctx = [
            'company' => $company,
            'company_id' => $company->id,
            'company_name' => $company->name,
            'user' => $user,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'now' => Carbon::now(),
        ];
    }

    private function seedConfiguration(): void
    {
        $companyId = $this->ctx['company_id'];

        $this->config['audit_types'] = $this->seedAuditTypes($companyId);
        $this->config['audit_statuses'] = $this->seedAuditStatuses($companyId);
        $this->config['finding_categories'] = $this->seedFindingCategories($companyId);
        $this->config['finding_statuses'] = $this->seedFindingStatuses($companyId);
        $this->config['risk_levels'] = $this->seedRiskLevels($companyId);
        $this->config['nc_origins'] = $this->seedNcOrigins($companyId);
        $this->config['nc_statuses'] = $this->seedNcStatuses($companyId);
        $this->config['rca_statuses'] = $this->seedRcaStatuses($companyId);
        $this->config['rca_methods'] = $this->seedRcaMethods($companyId);
        $this->config['capa_categories'] = $this->seedCapaCategories($companyId);
        $this->config['capa_action_types'] = $this->seedCapaActionTypes($companyId);
        $this->config['capa_priorities'] = $this->seedCapaPriorities($companyId);
        $this->config['capa_statuses'] = $this->seedCapaStatuses($companyId);
        $this->config['severity_scales'] = $this->seedSeverityScales($companyId);
        $this->config['likelihood_scales'] = $this->seedLikelihoodScales($companyId);
        $this->config['compliance_statuses'] = $this->seedComplianceStatuses($companyId);
        $this->config['verification_results'] = $this->seedVerificationResults($companyId);
        $this->config['verification_closure_statuses'] = $this->seedVerificationClosureStatuses($companyId);
        $this->config['team_roles'] = $this->seedTeamRoles($companyId);
        $this->config['checklist'] = $this->seedChecklist($companyId);
        $this->seedWorkflowActionsAndRules($companyId);
        $this->seedWorkflowApprovers($companyId);

        $this->command?->info('  → Configuration tables populated.');
    }

    private function seedAuditsAcrossWorkflows(): void
    {
        $workflowSteps = getAuditWorkflowSteps();
        $stepIndex = 0;

        foreach ($workflowSteps as $stepNum => $statusName) {
            $stepIndex++;
            $auditNumber = self::DEMO_PREFIX.'WF'.str_pad((string) $stepNum, 2, '0', STR_PAD_LEFT);

            $audit = $this->upsertAuditShell($auditNumber, $statusName, $stepNum, $stepIndex);
            $this->applyWorkflowStageData($audit, $stepNum, $statusName);

            $this->command?->info("  → Audit {$auditNumber} @ workflow: {$statusName}");
        }
    }

    /**
     * @return array<string, AuditType>
     */
    private function seedAuditTypes(string $companyId): array
    {
        $types = [
            ['code' => 'DEMO_INT', 'name' => 'Internal Audit (Demo)', 'description' => 'Planned internal ISO audit for laboratory processes.'],
            ['code' => 'DEMO_EXT', 'name' => 'External Audit (Demo)', 'description' => 'Customer or accreditation body audit.'],
            ['code' => 'DEMO_SUP', 'name' => 'Supplier Audit (Demo)', 'description' => 'Vendor / supplier quality audit.'],
        ];

        $result = [];
        foreach ($types as $i => $row) {
            $result[$row['code']] = AuditType::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'is_active' => true,
                    'company_id' => $companyId,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, AuditStatus>
     */
    private function seedAuditStatuses(string $companyId): array
    {
        $workflowSteps = getAuditWorkflowSteps();
        $colors = [
            1 => '#17a2b8',
            2 => '#007bff',
            3 => '#ffc107',
            4 => '#fd7e14',
            5 => '#6f42c1',
            6 => '#e83e8c',
            7 => '#20c997',
            8 => '#6610f2',
            9 => '#6c757d',
            10 => '#28a745',
        ];

        $result = [];
        foreach ($workflowSteps as $step => $name) {
            $code = 'DEMO_'.strtoupper(Str::slug($name, '_'));
            if ($step === 1) {
                $code = 'DEMO_SCHED';
            }
            if ($step === 10) {
                $code = 'DEMO_CLOSED';
            }

            $result[$name] = AuditStatus::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => "Workflow step {$step}: {$name}",
                    'color_code' => $colors[$step] ?? '#6c757d',
                    'order_index' => $step,
                    'workflow_step' => $step,
                    'is_active' => true,
                    'company_id' => $companyId,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, FindingCategory>
     */
    private function seedFindingCategories(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_OBS', 'name' => 'Observation (Demo)', 'severity' => 'Low', 'requires_capa' => false],
            ['code' => 'DEMO_NC', 'name' => 'Non-Conformance (Demo)', 'severity' => 'High', 'requires_capa' => true],
            ['code' => 'DEMO_MIN', 'name' => 'Minor Finding (Demo)', 'severity' => 'Medium', 'requires_capa' => true],
            ['code' => 'DEMO_MAJ', 'name' => 'Major Finding (Demo)', 'severity' => 'Critical', 'requires_capa' => true],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = FindingCategory::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => $row['name'].' for seeded demo audits.',
                    'severity' => $row['severity'],
                    'requires_capa' => $row['requires_capa'],
                    'is_active' => true,
                    'company_id' => $companyId,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, FindingStatus>
     */
    private function seedFindingStatuses(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_OPEN', 'name' => 'Open', 'order_index' => 1],
            ['code' => 'DEMO_REVIEW', 'name' => 'Under Review', 'order_index' => 2],
            ['code' => 'DEMO_CLOSED', 'name' => 'Closed', 'order_index' => 3],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = FindingStatus::updateOrCreate(
                ['code' => $row['code'], 'company_id' => $companyId],
                [
                    'name' => $row['name'],
                    'description' => null,
                    'color_code' => '#6c757d',
                    'order_index' => $row['order_index'],
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, RiskLevel>
     */
    private function seedRiskLevels(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_R_LOW', 'name' => 'Low', 'severity_score' => 1, 'color_code' => '#28a745'],
            ['code' => 'DEMO_R_MED', 'name' => 'Medium', 'severity_score' => 2, 'color_code' => '#ffc107'],
            ['code' => 'DEMO_R_HIGH', 'name' => 'High', 'severity_score' => 3, 'color_code' => '#fd7e14'],
            ['code' => 'DEMO_R_CRIT', 'name' => 'Critical', 'severity_score' => 4, 'color_code' => '#dc3545'],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = RiskLevel::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => 'Demo risk level '.$row['name'],
                    'severity_score' => $row['severity_score'],
                    'color_code' => $row['color_code'],
                    'is_active' => true,
                    'company_id' => $companyId,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, NcOrigin>
     */
    private function seedNcOrigins(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_AUDIT', 'name' => 'Audit Finding'],
            ['code' => 'DEMO_LAB', 'name' => 'Laboratory Process'],
            ['code' => 'DEMO_EQP', 'name' => 'Equipment'],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = NcOrigin::updateOrCreate(
                ['code' => $row['code'], 'company_id' => $companyId],
                [
                    'name' => $row['name'],
                    'description' => 'Demo NC origin: '.$row['name'],
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, NcStatus>
     */
    private function seedNcStatuses(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_IDENT', 'name' => 'Identified', 'order_index' => 1],
            ['code' => 'DEMO_RCA', 'name' => 'RCA In Progress', 'order_index' => 2],
            ['code' => 'DEMO_RCADONE', 'name' => 'RCA Complete', 'order_index' => 3],
            ['code' => 'DEMO_CAPA', 'name' => 'CAPA Assigned', 'order_index' => 4],
            ['code' => 'DEMO_VERIFY', 'name' => 'Verification Pending', 'order_index' => 5],
            ['code' => 'DEMO_NCCLOSED', 'name' => 'Closed', 'order_index' => 6],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = NcStatus::updateOrCreate(
                ['code' => $row['code'], 'company_id' => $companyId],
                [
                    'name' => $row['name'],
                    'description' => null,
                    'color_code' => '#6c757d',
                    'order_index' => $row['order_index'],
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, RcaStatus>
     */
    private function seedRcaStatuses(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_DRAFT', 'name' => 'Draft', 'order_index' => 1],
            ['code' => 'DEMO_SUBMIT', 'name' => 'Submitted', 'order_index' => 2],
            ['code' => 'DEMO_APPROVE', 'name' => 'Approved', 'order_index' => 3],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = RcaStatus::updateOrCreate(
                ['code' => $row['code'], 'company_id' => $companyId],
                [
                    'name' => $row['name'],
                    'description' => null,
                    'color_code' => '#6c757d',
                    'order_index' => $row['order_index'],
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, RootCauseMethod>
     */
    private function seedRcaMethods(string $companyId): array
    {
        $result = [];
        $result['5WHY'] = RootCauseMethod::updateOrCreate(
            ['code' => 'DEMO_5WHY', 'company_id' => $companyId],
            [
                'name' => '5 Whys (Demo)',
                'description' => 'Iterative why analysis.',
                'template' => ['why_1' => '', 'why_2' => '', 'why_3' => '', 'why_4' => '', 'why_5' => ''],
                'is_active' => true,
            ]
        );
        $result['FISHBONE'] = RootCauseMethod::updateOrCreate(
            ['code' => 'DEMO_FISH', 'company_id' => $companyId],
            [
                'name' => 'Fishbone / Ishikawa (Demo)',
                'description' => 'Cause-and-effect diagram.',
                'template' => ['man' => '', 'machine' => '', 'method' => '', 'material' => '', 'measurement' => '', 'environment' => ''],
                'is_active' => true,
            ]
        );

        return $result;
    }

    /**
     * @return array<string, CapaCategory>
     */
    private function seedCapaCategories(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_CORR', 'name' => 'Corrective Action'],
            ['code' => 'DEMO_PREV', 'name' => 'Preventive Action'],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = CapaCategory::updateOrCreate(
                ['code' => $row['code'], 'company_id' => $companyId],
                [
                    'name' => $row['name'],
                    'description' => 'Demo CAPA category',
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, CapaActionType>
     */
    private function seedCapaActionTypes(string $companyId): array
    {
        $result = [];
        $result['PROC'] = CapaActionType::updateOrCreate(
            ['code' => 'DEMO_PROC', 'company_id' => $companyId],
            ['name' => 'Process Change', 'description' => 'Update SOP or work instruction', 'is_active' => true]
        );
        $result['TRAIN'] = CapaActionType::updateOrCreate(
            ['code' => 'DEMO_TRAIN', 'company_id' => $companyId],
            ['name' => 'Training', 'description' => 'Staff training or competency', 'is_active' => true]
        );

        return $result;
    }

    /**
     * @return array<string, CapaPriority>
     */
    private function seedCapaPriorities(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_P_LOW', 'name' => 'Low', 'priority_level' => 1],
            ['code' => 'DEMO_P_HIGH', 'name' => 'High', 'priority_level' => 2],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = CapaPriority::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => null,
                    'color_code' => '#6c757d',
                    'priority_level' => $row['priority_level'],
                    'is_active' => true,
                    'company_id' => $companyId,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, CapaStatus>
     */
    private function seedCapaStatuses(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_C_OPEN', 'name' => 'Open', 'order_index' => 1],
            ['code' => 'DEMO_C_ASSIGNED', 'name' => 'Assigned', 'order_index' => 2],
            ['code' => 'DEMO_C_INPROG', 'name' => 'In Progress', 'order_index' => 3],
            ['code' => 'DEMO_C_IMPL', 'name' => 'Implemented', 'order_index' => 4],
            ['code' => 'DEMO_C_VERIFY', 'name' => 'Verification Pending', 'order_index' => 5],
            ['code' => 'DEMO_C_VERIFIED', 'name' => 'Verified', 'order_index' => 6],
            ['code' => 'DEMO_C_CLOSED', 'name' => 'Closed', 'order_index' => 7],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = CapaStatus::updateOrCreate(
                ['code' => $row['code'], 'company_id' => $companyId],
                [
                    'name' => $row['name'],
                    'description' => null,
                    'color_code' => '#6c757d',
                    'order_index' => $row['order_index'],
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, SeverityScale>
     */
    private function seedSeverityScales(string $companyId): array
    {
        $result = [];
        for ($score = 1; $score <= 5; $score++) {
            $code = 'DEMO_SEV_'.$score;
            $result[$code] = SeverityScale::updateOrCreate(
                ['code' => $code, 'company_id' => $companyId],
                [
                    'name' => 'Severity '.$score,
                    'description' => 'Demo severity scale level '.$score,
                    'score' => $score,
                    'color_code' => '#fd7e14',
                    'order_index' => $score,
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, LikelihoodScale>
     */
    private function seedLikelihoodScales(string $companyId): array
    {
        $result = [];
        for ($score = 1; $score <= 5; $score++) {
            $code = 'DEMO_LIK_'.$score;
            $result[$code] = LikelihoodScale::updateOrCreate(
                ['code' => $code, 'company_id' => $companyId],
                [
                    'name' => 'Likelihood '.$score,
                    'description' => 'Demo likelihood scale level '.$score,
                    'score' => $score,
                    'color_code' => '#17a2b8',
                    'order_index' => $score,
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, ComplianceStatus>
     */
    private function seedComplianceStatuses(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_COMPLIANT', 'name' => 'Compliant', 'badge_class' => 'success'],
            ['code' => 'DEMO_PARTIAL', 'name' => 'Partially Compliant', 'badge_class' => 'warning'],
            ['code' => 'DEMO_NC', 'name' => 'Non-Compliant', 'badge_class' => 'danger'],
        ];

        $result = [];
        foreach ($rows as $i => $row) {
            $result[$row['code']] = ComplianceStatus::updateOrCreate(
                ['code' => $row['code'], 'company_id' => $companyId],
                [
                    'name' => $row['name'],
                    'description' => null,
                    'color_code' => '#6c757d',
                    'badge_class' => $row['badge_class'],
                    'order_index' => $i + 1,
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, VerificationResult>
     */
    private function seedVerificationResults(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_EFF', 'name' => 'Effective', 'requires_reopen' => false],
            ['code' => 'DEMO_NOTEFF', 'name' => 'Not Effective', 'requires_reopen' => true],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = VerificationResult::updateOrCreate(
                ['code' => $row['code'], 'company_id' => $companyId],
                [
                    'name' => $row['name'],
                    'description' => 'Demo verification outcome',
                    'color_code' => $row['requires_reopen'] ? '#dc3545' : '#28a745',
                    'requires_reopen' => $row['requires_reopen'],
                    'next_workflow_step' => null,
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, VerificationClosureStatus>
     */
    private function seedVerificationClosureStatuses(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_V_PEND', 'name' => 'Pending'],
            ['code' => 'DEMO_V_CLOSED', 'name' => 'Closed'],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = VerificationClosureStatus::updateOrCreate(
                ['code' => $row['code'], 'company_id' => $companyId],
                [
                    'name' => $row['name'],
                    'description' => null,
                    'color_code' => '#6c757d',
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array<string, AuditTeamRole>
     */
    private function seedTeamRoles(string $companyId): array
    {
        $rows = [
            ['code' => 'DEMO_LEAD', 'name' => 'Lead Auditor'],
            ['code' => 'DEMO_AUD', 'name' => 'Auditor'],
            ['code' => 'DEMO_OBS', 'name' => 'Observer'],
        ];

        $result = [];
        foreach ($rows as $row) {
            $result[$row['code']] = AuditTeamRole::updateOrCreate(
                ['code' => $row['code'], 'company_id' => $companyId],
                [
                    'name' => $row['name'],
                    'description' => 'Demo team role',
                    'is_active' => true,
                ]
            );
        }

        return $result;
    }

    /**
     * @return array{checklist: AuditChecklist, items: \Illuminate\Support\Collection<int, AuditChecklistItem>}
     */
    private function seedChecklist(string $companyId): array
    {
        $auditType = $this->config['audit_types']['DEMO_INT'] ?? AuditType::where('code', 'DEMO_INT')->first();

        $checklist = AuditChecklist::updateOrCreate(
            ['code' => 'DEMO-CHK-ISO17025', 'company_id' => $companyId],
            [
                'name' => 'ISO/IEC 17025 Internal Audit Checklist (Demo)',
                'description' => 'Sample checklist for laboratory QMS internal audits.',
                'audit_type_id' => $auditType?->id,
                'iso_standard' => 'ISO/IEC 17025:2017',
                'is_active' => true,
                'created_by' => (string) $this->ctx['user_id'],
            ]
        );

        $items = collect();
        $checklistRows = [
            ['item_number' => '4.1', 'iso_clause' => '4.1', 'requirement' => 'Impartiality and confidentiality are addressed.', 'order_index' => 1],
            ['item_number' => '6.2', 'iso_clause' => '6.2', 'requirement' => 'Personnel are competent for assigned activities.', 'order_index' => 2],
            ['item_number' => '6.4', 'iso_clause' => '6.4', 'requirement' => 'Equipment is calibrated and maintained.', 'order_index' => 3],
            ['item_number' => '7.7', 'iso_clause' => '7.7', 'requirement' => 'Validity of results is ensured.', 'order_index' => 4],
            ['item_number' => '8.7', 'iso_clause' => '8.7', 'requirement' => 'Nonconforming work is controlled.', 'order_index' => 5],
        ];

        foreach ($checklistRows as $row) {
            $items->push(
                AuditChecklistItem::updateOrCreate(
                    [
                        'audit_checklist_id' => $checklist->id,
                        'item_number' => $row['item_number'],
                    ],
                    [
                        'iso_clause' => $row['iso_clause'],
                        'requirement' => $row['requirement'],
                        'guidance' => 'Review documented evidence and interview responsible personnel.',
                        'evidence_required' => 'Records, logs, or calibration certificates as applicable.',
                        'order_index' => $row['order_index'],
                        'is_mandatory' => true,
                        'is_active' => true,
                    ]
                )
            );
        }

        return ['checklist' => $checklist, 'items' => $items];
    }

    private function seedWorkflowActionsAndRules(string $companyId): void
    {
        $advance = WorkflowAction::updateOrCreate(
            ['code' => 'DEMO_ADVANCE', 'company_id' => $companyId],
            [
                'name' => 'Advance Workflow',
                'description' => 'Move audit to the next workflow step with mandatory remarks.',
                'icon' => 'mdi-arrow-right-bold',
                'color_code' => '#007bff',
                'badge_class' => 'primary',
                'requires_remarks' => true,
                'min_remarks_length' => 10,
                'requires_target_status' => false,
                'is_active' => true,
                'order_index' => 1,
            ]
        );

        $hold = WorkflowAction::updateOrCreate(
            ['code' => 'DEMO_HOLD', 'company_id' => $companyId],
            [
                'name' => 'Place On Hold',
                'description' => 'Pause workflow progression pending additional information.',
                'icon' => 'mdi-pause-circle',
                'color_code' => '#ffc107',
                'badge_class' => 'warning',
                'requires_remarks' => true,
                'min_remarks_length' => 10,
                'requires_target_status' => false,
                'is_active' => true,
                'order_index' => 2,
            ]
        );

        if (! Schema::hasTable('workflow_action_rules')) {
            return;
        }

        $statuses = $this->config['audit_statuses'] ?? [];
        $pairs = [
            ['Scheduled', 'In Progress'],
            ['In Progress', 'Record Findings & NC'],
            ['Record Findings & NC', 'Findings Review'],
            ['Findings Review', 'Root Cause Analysis'],
            ['Root Cause Analysis', 'CAPA Assigned'],
            ['CAPA Assigned', 'CAPA In Progress'],
            ['CAPA In Progress', 'CAPA Verification'],
            ['CAPA Verification', 'Pending Closure'],
            ['Pending Closure', 'Closed'],
        ];

        $order = 0;
        foreach ($pairs as [$fromName, $toName]) {
            $from = $statuses[$fromName] ?? null;
            $to = $statuses[$toName] ?? null;
            if (! $from || ! $to) {
                continue;
            }

            WorkflowActionRule::updateOrCreate(
                [
                    'workflow_action_id' => $advance->id,
                    'from_status_id' => $from->id,
                    'company_id' => $companyId,
                ],
                [
                    'from_status_name' => $from->name,
                    'target_status_id' => $to->id,
                    'target_status_name' => $to->name,
                    'target_type' => 'specific',
                    'validate_progression' => true,
                    'validation_rules' => null,
                    'conditions' => null,
                    'success_message' => "Advanced to {$to->name}.",
                    'error_message' => 'Cannot advance until required records are complete.',
                    'is_active' => true,
                    'order_index' => ++$order,
                ]
            );
        }

        if (isset($statuses['In Progress'])) {
            WorkflowActionRule::updateOrCreate(
                [
                    'workflow_action_id' => $hold->id,
                    'from_status_id' => $statuses['In Progress']->id,
                    'company_id' => $companyId,
                ],
                [
                    'from_status_name' => 'In Progress',
                    'target_status_id' => $statuses['In Progress']->id,
                    'target_status_name' => 'In Progress',
                    'target_type' => 'current',
                    'validate_progression' => false,
                    'is_active' => true,
                    'order_index' => 99,
                    'success_message' => 'Audit remains in progress (on hold).',
                ]
            );
        }
    }

    private function seedWorkflowApprovers(string $companyId): void
    {
        $userId = $this->ctx['user_id'];

        foreach ([3, 5, 7, 9] as $step) {
            AuditWorkflowApprover::updateOrCreate(
                [
                    'workflow_step' => $step,
                    'role_type' => 'approver',
                    'user_id' => $userId,
                    'module' => 'audit',
                    'company_id' => $companyId,
                ],
                [
                    'is_required' => true,
                    'approval_type' => 'sequential',
                ]
            );
        }
    }

    private function upsertAuditShell(string $auditNumber, string $statusName, int $stepNum, int $stepIndex): Audit
    {
        $auditType = $this->config['audit_types']['DEMO_INT'];
        $status = $this->config['audit_statuses'][$statusName] ?? null;
        $checklist = $this->config['checklist']['checklist'] ?? null;
        $now = $this->ctx['now'];

        return Audit::updateOrCreate(
            ['audit_number' => $auditNumber],
            [
                'revision_number' => 'REV. 00',
                'audit_type_id' => $auditType->id,
                'audit_type_name' => $auditType->name,
                'title' => "Demo audit — workflow step {$stepNum}: {$statusName}",
                'objective' => 'Demonstrate audit module workflow stages with realistic ISO 17025 laboratory data.',
                'scope' => 'Chemistry laboratory — sample receipt, analysis, and reporting.',
                'criteria' => 'ISO/IEC 17025:2017, internal QMS procedures, and customer requirements.',
                'checklist_id' => $checklist?->id,
                'lead_auditor_id' => null,
                'lead_auditor_name' => $this->ctx['user_name'],
                'auditee_name' => 'Laboratory Manager (Demo)',
                'auditee_department_name' => 'Quality Assurance',
                'scheduled_date' => $now->copy()->subDays(30 - $stepIndex),
                'start_date' => $stepNum >= 2 ? $now->copy()->subDays(28 - $stepIndex) : null,
                'end_date' => $stepNum >= 3 ? $now->copy()->subDays(20 - $stepIndex) : null,
                'report_date' => $stepNum >= 9 ? $now->copy()->subDays(5) : null,
                'closure_date' => $stepNum >= 10 ? $now->copy()->subDays(2) : null,
                'status_id' => $status?->id,
                'status_name' => $statusName,
                'executive_summary' => $stepNum >= 9
                    ? 'Internal audit completed. One major NC addressed via CAPA; overall QMS effective with minor observations.'
                    : null,
                'conclusions' => $stepNum >= 9 ? 'Laboratory generally conforms; corrective actions verified effective.' : null,
                'recommendations' => $stepNum >= 9 ? 'Continue annual internal audit cycle; reinforce training on sample chain of custody.' : null,
                'created_by' => (string) $this->ctx['user_id'],
                'updated_by' => null,
                'closed_by' => null,
                'company_id' => $this->ctx['company_id'],
            ]
        );
    }

    private function applyWorkflowStageData(Audit $audit, int $stepNum, string $statusName): void
    {
        $checklistId = $this->config['checklist']['checklist']->id ?? null;
        if ($checklistId && ! $audit->checklists()->where('audit_checklist_id', $checklistId)->exists()) {
            $audit->checklists()->attach($checklistId, [
                'id' => (string) Str::uuid(),
                'order_index' => 0,
            ]);
        }

        if ($stepNum >= 2) {
            $this->seedTeamMember($audit);
            $this->seedChecklistResponses($audit, $stepNum >= 3 ? 'Compliant' : 'Partially Compliant');
        }

        if ($stepNum >= 3) {
            $this->seedFindingsAndNcs($audit, $stepNum);
        }

        if ($stepNum >= 5) {
            $this->ensureRcasForAudit($audit, $stepNum >= 5);
        }

        if ($stepNum >= 6) {
            $this->ensureCapasForAudit($audit, $stepNum);
        }

        if ($stepNum >= 8) {
            $this->ensureVerificationsForAudit($audit);
        }
    }

    private function seedTeamMember(Audit $audit): void
    {
        $role = $this->config['team_roles']['DEMO_AUD'] ?? null;

        AuditTeamMember::updateOrCreate(
            [
                'audit_id' => $audit->id,
                'user_id' => (string) $this->ctx['user_id'],
            ],
            [
                'role_id' => $role?->id,
                'role_name' => $role?->name ?? 'Auditor',
                'responsibilities' => 'Conduct checklist review and document findings.',
            ]
        );
    }

    private function seedChecklistResponses(Audit $audit, string $complianceLabel): void
    {
        if (! Schema::hasTable('audit_checklist_item_responses')) {
            return;
        }

        $items = $this->config['checklist']['items'] ?? collect();

        foreach ($items as $item) {
            AuditChecklistItemResponse::updateOrCreate(
                [
                    'audit_id' => $audit->id,
                    'audit_checklist_item_id' => $item->id,
                ],
                [
                    'compliance_status' => $complianceLabel,
                    'audit_question' => $item->requirement,
                    'evidence_collected' => 'Procedure document PR-QA-001 rev. 3; interview notes.',
                    'observation' => $complianceLabel === 'Compliant'
                        ? 'Requirement met with objective evidence on file.'
                        : 'Minor gap noted — documented in finding register.',
                    'findings' => null,
                    'auditor_notes' => 'Seeded demo response for '.$item->item_number,
                    'audited_by' => null,
                    'audited_at' => $this->ctx['now'],
                    'requires_follow_up' => $complianceLabel !== 'Compliant',
                    'company_id' => $this->ctx['company_id'],
                ]
            );
        }
    }

    private function seedFindingsAndNcs(Audit $audit, int $stepNum): void
    {
        $categories = $this->config['finding_categories'];
        $risk = $this->config['risk_levels']['DEMO_R_HIGH'];
        $findingOpen = $this->config['finding_statuses']['DEMO_OPEN'];
        $findingReview = $this->config['finding_statuses']['DEMO_REVIEW'];
        $findingClosed = $this->config['finding_statuses']['DEMO_CLOSED'];

        $findingDefs = [
            [
                'suffix' => '01',
                'category' => $categories['DEMO_OBS'],
                'risk' => $this->config['risk_levels']['DEMO_R_LOW'],
                'status' => $stepNum >= 4 ? $findingReview : $findingOpen,
                'observation' => 'Sample login labels occasionally missing secondary identifier.',
                'raise_nc' => false,
            ],
            [
                'suffix' => '02',
                'category' => $categories['DEMO_MAJ'],
                'risk' => $risk,
                'status' => $stepNum >= 4 ? $findingClosed : $findingOpen,
                'observation' => 'Calibration certificate for balance BAL-04 expired; used for gravimetric prep.',
                'raise_nc' => true,
            ],
        ];

        foreach ($findingDefs as $index => $def) {
            $findingNumber = $audit->audit_number.'-F'.$def['suffix'];

            $finding = AuditModuleFinding::updateOrCreate(
                ['finding_number' => $findingNumber],
                [
                    'audit_id' => $audit->id,
                    'finding_category_id' => $def['category']->id,
                    'finding_category_name' => $def['category']->name,
                    'iso_clause' => '6.4.6',
                    'sop_reference' => 'SOP-EQP-012',
                    'requirement' => 'Equipment shall be calibrated before use.',
                    'observation' => $def['observation'],
                    'objective_evidence' => 'Equipment log export dated '.$this->ctx['now']->format('Y-m-d').'; photo of expired sticker.',
                    'risk_level_id' => $def['risk']->id,
                    'risk_level_name' => $def['risk']->name,
                    'responsible_person' => $this->ctx['user_name'],
                    'responsible_user_id' => null,
                    'response_due_date' => $this->ctx['now']->copy()->addDays(14),
                    'status_id' => $def['status']->id,
                    'status_name' => $def['status']->name,
                    'order_index' => $index,
                    'created_by' => (string) $this->ctx['user_id'],
                ]
            );

            if ($def['raise_nc'] && $stepNum >= 3) {
                $this->upsertNcForFinding($audit, $finding, $stepNum);
            }
        }
    }

    private function upsertNcForFinding(Audit $audit, AuditModuleFinding $finding, int $stepNum): NonConformance
    {
        $ncNumber = 'NC-DEMO-'.$audit->audit_number.'-'.$finding->order_index;
        $origin = $this->config['nc_origins']['DEMO_AUDIT'];
        $risk = $this->config['risk_levels']['DEMO_R_HIGH'];
        $severity = $this->config['severity_scales']['DEMO_SEV_4'] ?? null;
        $likelihood = $this->config['likelihood_scales']['DEMO_LIK_3'] ?? null;

        $ncStatusName = match (true) {
            $stepNum >= 9 => 'Closed',
            $stepNum >= 8 => 'Verification Pending',
            $stepNum >= 6 => 'CAPA Assigned',
            $stepNum >= 5 => 'RCA Complete',
            $stepNum >= 4 => 'RCA In Progress',
            default => 'Identified',
        };

        $ncStatus = collect($this->config['nc_statuses'])->first(fn ($s) => $s->name === $ncStatusName)
            ?? $this->config['nc_statuses']['DEMO_IDENT'];

        return NonConformance::updateOrCreate(
            ['nc_number' => $ncNumber],
            [
                'origin_id' => $origin->id,
                'origin_name' => $origin->name,
                'audit_id' => $audit->id,
                'audit_finding_id' => $finding->id,
                'title' => 'NC from '.$finding->finding_number,
                'description' => $finding->observation,
                'iso_clause_violated' => $finding->iso_clause,
                'date_identified' => $this->ctx['now']->copy()->subDays(10),
                'identified_by' => $this->ctx['user_name'],
                'identified_by_user_id' => null,
                'department' => 'Chemical Analysis Lab',
                'risk_level_id' => $risk->id,
                'risk_level_name' => $risk->name,
                'severity_score' => 4,
                'severity_scale_id' => $severity?->id,
                'likelihood_score' => 3,
                'likelihood_scale_id' => $likelihood?->id,
                'risk_assessment_notes' => 'Seeded risk assessment for demo workflow.',
                'immediate_correction' => 'Balance removed from service until recalibration.',
                'immediate_correction_date' => $this->ctx['now']->copy()->subDays(9),
                'immediate_correction_by' => $this->ctx['user_name'],
                'status_id' => $ncStatus->id,
                'status_name' => $ncStatus->name,
                'target_closure_date' => $this->ctx['now']->copy()->addDays(30),
                'actual_closure_date' => $stepNum >= 10 ? $this->ctx['now'] : null,
                'created_by' => (string) $this->ctx['user_id'],
                'company_id' => $this->ctx['company_id'],
            ]
        );
    }

    private function ensureRcasForAudit(Audit $audit, bool $approved): void
    {
        foreach ($audit->nonConformances as $nc) {
            $method = $this->config['rca_methods']['5WHY'] ?? reset($this->config['rca_methods']);
            $status = $approved
                ? $this->config['rca_statuses']['DEMO_APPROVE']
                : $this->config['rca_statuses']['DEMO_SUBMIT'];

            RootCauseAnalysis::updateOrCreate(
                ['non_conformance_id' => $nc->id],
                [
                    'root_cause_method_id' => $method->id,
                    'method_name' => $method->name,
                    'analysis_data' => [
                        'why_1' => 'Calibration schedule not triggered automatically.',
                        'why_2' => 'Reminder only in paper log.',
                        'why_3' => 'No integration with equipment module.',
                        'why_4' => 'Legacy process from pre-LIMS rollout.',
                        'why_5' => 'CAPA from previous audit not fully embedded.',
                    ],
                    'root_cause_description' => 'Lack of automated calibration due-date alerts in the equipment register.',
                    'contributing_factors' => 'Staff turnover; manual spreadsheet still used for one lab wing.',
                    'evidence_supporting_rca' => 'Maintenance log review; interview with lab supervisor.',
                    'status_id' => $status->id,
                    'status_name' => $status->name,
                    'approved_by' => null,
                    'approved_date' => $approved ? $this->ctx['now'] : null,
                    'approval_comments' => $approved ? 'RCA accepted — proceed to CAPA.' : null,
                    'created_by' => (string) $this->ctx['user_id'],
                ]
            );
        }
    }

    private function ensureCapasForAudit(Audit $audit, int $stepNum): void
    {
        $capaStatusName = match (true) {
            $stepNum >= 9 => 'Verified',
            $stepNum >= 8 => 'Verification Pending',
            $stepNum >= 7 => 'Implemented',
            default => 'Assigned',
        };

        $capaStatus = collect($this->config['capa_statuses'])->first(fn ($s) => $s->name === $capaStatusName)
            ?? $this->config['capa_statuses']['DEMO_C_ASSIGNED'];

        $category = $this->config['capa_categories']['DEMO_CORR'];
        $actionType = $this->config['capa_action_types']['PROC'];
        $priority = $this->config['capa_priorities']['DEMO_P_HIGH'];

        foreach ($audit->nonConformances as $nc) {
            $capaNumber = 'CAPA-DEMO-'.$nc->nc_number;

            CorrectiveAction::updateOrCreate(
                ['capa_number' => $capaNumber],
                [
                    'non_conformance_id' => $nc->id,
                    'capa_category_id' => $category->id,
                    'capa_category_name' => $category->name,
                    'action_type_id' => $actionType->id,
                    'action_type_name' => $actionType->name,
                    'title' => 'Recalibrate balance and enable automated due-date alerts',
                    'description' => 'Complete calibration of BAL-04; configure equipment module reminders 30/7/1 days before due.',
                    'expected_outcome' => 'No use of out-of-calibration equipment; alerts visible to technicians.',
                    'action_owner' => $this->ctx['user_name'],
                    'action_owner_id' => null,
                    'department' => 'Chemical Analysis Lab',
                    'due_date' => $this->ctx['now']->copy()->addDays(21),
                    'implementation_date' => $stepNum >= 7 ? $this->ctx['now']->copy()->subDays(3) : null,
                    'status_id' => $capaStatus->id,
                    'status_name' => $capaStatus->name,
                    'priority_id' => $priority->id,
                    'priority_name' => $priority->name,
                    'implementation_notes' => $stepNum >= 7 ? 'Calibration certificate CAL-2026-044 attached; alerts enabled.' : null,
                    'created_by' => (string) $this->ctx['user_id'],
                    'company_id' => $this->ctx['company_id'],
                ]
            );
        }
    }

    private function ensureVerificationsForAudit(Audit $audit): void
    {
        $effective = $this->config['verification_results']['DEMO_EFF'];
        $closure = $this->config['verification_closure_statuses']['DEMO_V_CLOSED'];

        foreach ($audit->nonConformances as $nc) {
            foreach ($nc->correctiveActions as $capa) {
                VerificationRecord::updateOrCreate(
                    [
                        'corrective_action_id' => $capa->id,
                        'verification_date' => $this->ctx['now']->copy()->subDays(1),
                    ],
                    [
                        'verified_by' => $this->ctx['user_name'],
                        'verified_by_user_id' => null,
                        'effectiveness_result_id' => $effective->id,
                        'effectiveness_result_name' => $effective->name,
                        'verification_method' => 'Review of calibration certificate and spot-check of alert configuration.',
                        'evidence_reviewed' => 'CAL-2026-044; screenshot of equipment dashboard alerts.',
                        'comments' => 'Corrective action effective — no recurrence observed in 30-day follow-up window.',
                        'requires_reopen' => false,
                        'closure_status_id' => $closure->id,
                        'closure_status_name' => $closure->name,
                        'closure_date' => $this->ctx['now'],
                        'closed_by' => null,
                        'created_by' => (string) $this->ctx['user_id'],
                    ]
                );
            }
        }
    }
}

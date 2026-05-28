<?php

namespace Database\Seeders;

use App\Company;
use App\Models\AuditModule\AuditWorkflowApprover;
use App\Models\AuditModule\LikelihoodScale;
use App\Models\AuditModule\SeverityScale;
use App\Models\RiskManagement\Risk;
use App\Models\RiskManagement\RiskAssessment;
use App\Models\RiskManagement\RiskCategory;
use App\Models\RiskManagement\RiskEvaluation;
use App\Models\RiskManagement\RiskReview;
use App\Models\RiskManagement\RiskSource;
use App\Models\RiskManagement\RiskStatus;
use App\Models\RiskManagement\RiskTreatmentPlan;
use App\Models\RiskManagement\TreatmentType;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiskModuleWorkflowSeeder extends Seeder
{
    private const DEMO_PREFIX = 'DEMO-RISK-WF';

    /** @var array<string, mixed> */
    private array $ctx = [];

    /** @var array<string, mixed> */
    private array $config = [];

    public function run(): void
    {
        Model::unguard();

        DB::transaction(function () {
            $this->resolveContext();
            $this->command?->info('Risk module seeder — company: '.$this->ctx['company_name'].' (id: '.$this->ctx['company_id'].')');

            $this->seedReferenceData();
            $this->seedConfiguration();
            $this->seedWorkflowApprovers();
            $this->seedRisksAcrossWorkflowStages();

            $this->command?->info('Risk module workflow demo data seeded successfully.');
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
            throw new \RuntimeException('No user found. Create at least one user before seeding the risk module.');
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

    private function seedReferenceData(): void
    {
        $this->call([
            RiskStatusesTableSeeder::class,
            RiskCategoriesTableSeeder::class,
            RiskSourcesTableSeeder::class,
            TreatmentTypesTableSeeder::class,
            RiskBusinessProcessSeeder::class,
        ]);

        $this->config['category'] = RiskCategory::query()
            ->where('code', 'QAL')
            ->first()
            ?? RiskCategory::query()->first();

        $this->config['source'] = RiskSource::query()
            ->where('code', 'RA')
            ->first()
            ?? RiskSource::query()->first();

        $this->config['treatment_type'] = TreatmentType::query()
            ->where('code', 'MITIGATE')
            ->first()
            ?? TreatmentType::query()->first();

        $this->command?->info('  → Reference data (statuses, categories, sources, treatment types).');
    }

    private function seedConfiguration(): void
    {
        $companyId = $this->ctx['company_id'];

        $this->call(RiskConfigurationSeeder::class, false, ['companyId' => $companyId]);

        $this->config['likelihood_scales'] = LikelihoodScale::query()
            ->where('company_id', $companyId)
            ->orderBy('score')
            ->get()
            ->keyBy('score');

        if ($this->config['likelihood_scales']->isEmpty()) {
            $this->config['likelihood_scales'] = LikelihoodScale::query()
                ->whereNull('company_id')
                ->orderBy('score')
                ->get()
                ->keyBy('score');
        }

        $this->config['severity_scales'] = SeverityScale::query()
            ->where('company_id', $companyId)
            ->orderBy('score')
            ->get()
            ->keyBy('score');

        if ($this->config['severity_scales']->isEmpty()) {
            $this->config['severity_scales'] = SeverityScale::query()
                ->whereNull('company_id')
                ->orderBy('score')
                ->get()
                ->keyBy('score');
        }

        $this->config['statuses_by_record_step'] = $this->loadStatusesByRiskRecordStep();

        $this->command?->info('  → Scoring, thresholds, scales, and configuration options.');
    }

    /**
     * Map risks.workflow_step (2–8) to risk_statuses rows (workflow_step 1–7).
     *
     * @return array<int, RiskStatus>
     */
    private function loadStatusesByRiskRecordStep(): array
    {
        $map = [];

        for ($recordStep = 2; $recordStep <= 8; $recordStep++) {
            $statusStep = mapRiskRecordWorkflowStepToStatusStep($recordStep);
            $status = RiskStatus::query()
                ->where('workflow_step', $statusStep)
                ->where(function ($q) {
                    $q->whereNull('company_id');
                })
                ->ordered()
                ->first();

            if ($status) {
                $map[$recordStep] = $status;
            }
        }

        return $map;
    }

    private function seedWorkflowApprovers(): void
    {
        $companyId = $this->ctx['company_id'];
        $userId = $this->ctx['user_id'];

        // workflow_step on approvers = risk_statuses.workflow_step (1–7)
        $approverSteps = [2, 3, 4, 7];

        foreach ($approverSteps as $step) {
            AuditWorkflowApprover::updateOrCreate(
                [
                    'workflow_step' => $step,
                    'role_type' => 'approver',
                    'user_id' => $userId,
                    'module' => 'risk',
                    'company_id' => $companyId,
                ],
                [
                    'is_required' => true,
                    'approval_type' => 'sequential',
                ]
            );
        }

        $this->command?->info('  → Workflow approvers for assessment, evaluation, treatment, and closure.');
    }

    private function seedRisksAcrossWorkflowStages(): void
    {
        for ($recordStep = 2; $recordStep <= 8; $recordStep++) {
            $suffix = str_pad((string) $recordStep, 2, '0', STR_PAD_LEFT);
            $riskNumber = self::DEMO_PREFIX.$suffix;
            $status = $this->config['statuses_by_record_step'][$recordStep] ?? null;
            $stepName = $status?->name ?? getWorkflowStepName($recordStep, 'risk');

            $risk = $this->upsertRiskShell($riskNumber, $recordStep, $status);
            $this->applyWorkflowStageData($risk, $recordStep);

            $this->command?->info("  → Risk {$riskNumber} @ {$stepName} (workflow step {$recordStep})");
        }
    }

    private function upsertRiskShell(string $riskNumber, int $recordStep, ?RiskStatus $status): Risk
    {
        $category = $this->config['category'];
        $source = $this->config['source'];

        return Risk::withTrashed()->updateOrCreate(
            ['risk_number' => $riskNumber],
            [
                'title' => 'Demo risk — '.($status?->name ?? "Step {$recordStep}"),
                'description' => 'Seeded demonstration risk at workflow stage '.$recordStep.'.',
                'category_id' => $category?->id,
                'category_name' => $category?->name,
                'other_source_id' => $source?->id,
                'other_source_name' => $source?->name,
                'risk_owner_name' => $this->ctx['user_name'],
                'date_identified' => $this->ctx['now']->copy()->subDays(14 - $recordStep),
                'identified_by' => $this->ctx['user_name'],
                'status_id' => $status?->id,
                'status_name' => $status?->name ?? 'Identified',
                'workflow_step' => $recordStep,
                'company_id' => $this->ctx['company_id'],
                'created_by' => $this->ctx['user_id'],
                'deleted_at' => null,
            ]
        );
    }

    private function applyWorkflowStageData(Risk $risk, int $recordStep): void
    {
        if ($recordStep >= 3) {
            $this->seedAssessment($risk, $recordStep >= 6 ? 2 : 4, $recordStep >= 6 ? 2 : 4);
        }

        if ($recordStep >= 4) {
            $this->seedEvaluation($risk, $recordStep >= 5 ? 'unacceptable' : 'tolerable');
        }

        if ($recordStep >= 5) {
            $this->seedTreatmentPlan($risk, $recordStep >= 6 ? 'completed' : 'planned');
        }

        if ($recordStep >= 7) {
            $this->seedReview($risk, $recordStep >= 8 ? 'close_risk' : 'continue_monitoring');
        }

        if ($recordStep >= 8) {
            $risk->update([
                'closure_type' => 'controlled',
                'closure_justification' => 'Residual risk reduced and verified through review.',
                'closure_date' => $this->ctx['now'],
                'residual_likelihood_score' => 2,
                'residual_severity_score' => 2,
                'residual_rpn' => 4,
                'residual_risk_level' => 'Low',
            ]);
        }
    }

    private function resolveRiskLevelFromRpn(int $rpn): string
    {
        if ($rpn >= 16) {
            return 'Critical';
        }
        if ($rpn >= 10) {
            return 'High';
        }
        if ($rpn >= 5) {
            return 'Medium';
        }

        return 'Low';
    }

    private function seedAssessment(Risk $risk, int $likelihoodScore, int $severityScore): void
    {
        $likelihood = $this->config['likelihood_scales'][$likelihoodScore] ?? null;
        $severity = $this->config['severity_scales'][$severityScore] ?? null;
        $rpn = $likelihoodScore * $severityScore;
        $riskLevel = $this->resolveRiskLevelFromRpn($rpn);

        $assessment = RiskAssessment::updateOrCreate(
            [
                'risk_id' => $risk->id,
                'assessment_number' => 'DEMO-ASM-'.$risk->risk_number,
            ],
            [
                'likelihood_scale_id' => $likelihood?->id,
                'likelihood_score' => $likelihoodScore,
                'severity_scale_id' => $severity?->id,
                'severity_score' => $severityScore,
                'rpn' => $rpn,
                'risk_level' => $riskLevel,
                'assessment_notes' => 'Seeded assessment for workflow demonstration.',
                'assessed_by' => $this->ctx['user_name'],
                'assessment_date' => $this->ctx['now'],
                'is_current' => true,
                'version' => 1,
                'created_by' => $this->ctx['user_id'],
                'company_id' => $this->ctx['company_id'],
            ]
        );

        $risk->update([
            'likelihood_scale_id' => $likelihood?->id,
            'likelihood_score' => $likelihoodScore,
            'severity_scale_id' => $severity?->id,
            'severity_score' => $severityScore,
            'rpn' => $rpn,
            'risk_level' => $riskLevel,
            'assessment_notes' => $assessment->assessment_notes,
            'assessment_date' => $this->ctx['now'],
            'current_assessment_id' => $assessment->id,
        ]);
    }

    private function seedEvaluation(Risk $risk, string $resultCode): void
    {
        $assessment = $risk->currentAssessment ?? RiskAssessment::query()
            ->where('risk_id', $risk->id)
            ->where('is_current', true)
            ->first();

        if (! $assessment) {
            return;
        }

        $rpn = $assessment->rpn ?? 12;
        $requiresTreatment = $resultCode === 'unacceptable';

        $evaluation = RiskEvaluation::updateOrCreate(
            [
                'risk_id' => $risk->id,
                'evaluation_number' => 'DEMO-EVL-'.$risk->risk_number,
            ],
            [
                'assessment_id' => $assessment->id,
                'risk_score' => $rpn,
                'evaluation_result' => $resultCode,
                'evaluation_notes' => 'Seeded evaluation ('.$resultCode.').',
                'evaluated_by' => $this->ctx['user_name'],
                'evaluation_date' => $this->ctx['now'],
                'is_current' => true,
                'created_by' => $this->ctx['user_id'],
                'company_id' => $this->ctx['company_id'],
            ]
        );

        $risk->update([
            'evaluation_result' => $resultCode,
            'evaluation_notes' => $evaluation->evaluation_notes,
            'evaluation_date' => $this->ctx['now'],
            'requires_treatment' => $requiresTreatment,
            'current_evaluation_id' => $evaluation->id,
        ]);
    }

    private function seedTreatmentPlan(Risk $risk, string $implementationStatus): void
    {
        $treatmentType = $this->config['treatment_type'];

        RiskTreatmentPlan::updateOrCreate(
            [
                'risk_id' => $risk->id,
                'title' => 'Demo treatment — '.$risk->risk_number,
            ],
            [
                'treatment_type_id' => $treatmentType?->id,
                'treatment_type_name' => $treatmentType?->name,
                'priority' => 'high',
                'description' => 'Seeded treatment plan for unacceptable / high risks.',
                'control_measures' => 'Implement additional QC checks and staff training.',
                'responsible_person' => $this->ctx['user_name'],
                'target_completion_date' => $this->ctx['now']->copy()->addDays(30),
                'implementation_status' => $implementationStatus === 'completed' ? 'completed' : 'planned',
                'actual_completion_date' => $implementationStatus === 'completed' ? $this->ctx['now'] : null,
                'created_by' => $this->ctx['user_id'],
            ]
        );
    }

    private function seedReview(Risk $risk, string $decisionCode): void
    {
        RiskReview::updateOrCreate(
            [
                'risk_id' => $risk->id,
                'review_number' => 'DEMO-REV-'.$risk->risk_number,
            ],
            [
                'review_date' => $this->ctx['now'],
                'reviewed_by' => $this->ctx['user_name'],
                'review_type' => 'Scheduled',
                'review_decision' => $decisionCode,
                'decision_justification' => 'Seeded periodic review.',
                'review_findings' => 'Controls operating as intended.',
                'review_likelihood_score' => $risk->residual_likelihood_score ?? $risk->likelihood_score,
                'review_severity_score' => $risk->residual_severity_score ?? $risk->severity_score,
                'review_rpn' => $risk->residual_rpn ?? $risk->rpn,
                'next_review_date' => $this->ctx['now']->copy()->addMonths(3),
                'created_by' => $this->ctx['user_id'],
            ]
        );

        $risk->update([
            'last_review_date' => $this->ctx['now'],
            'next_review_date' => $this->ctx['now']->copy()->addMonths(3),
        ]);
    }
}

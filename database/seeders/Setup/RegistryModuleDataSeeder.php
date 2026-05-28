<?php

namespace Database\Seeders\Setup;

use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestAction;
use App\Models\Registry\RegistryRequestAssignment;
use App\Models\Registry\RegistryRequestCategory;
use App\Models\Registry\RegistryRequestDocument;
use App\Models\Registry\RegistryRequestStatusLog;
use App\Models\Registry\WorkflowDefinition;
use App\Models\Registry\WorkflowStep;
use App\Support\Registry\RegistryReferenceGenerator;
use App\Support\Registry\RegistryStatusManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RegistryModuleDataSeeder extends Seeder
{
    /** @var list<string> */
    protected array $sampleSubmittingParties = [
        'Kenya Bureau of Standards',
        'Nairobi Water Services Ltd',
        'East Africa Pharma Ltd',
        'Coastal Environmental Consultants',
        'Ministry of Health — Public Health Dept',
    ];

    protected int $referenceSequence = 1;

    public function run(): void
    {
        $this->call(RegistryModuleSeeder::class);

        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function () {
            $this->command?->info('Seeding registry module demo data...');

            $this->clearOperationalData();
            $this->seedDemoRequests();

            $this->command?->info('Registry module demo data seeded (' . RegistryRequest::count() . ' requests).');
        });

        Model::reguard();
    }

    protected function clearOperationalData(): void
    {
        RegistryRequestStatusLog::query()->delete();
        RegistryRequestDocument::query()->delete();
        RegistryRequestAssignment::query()->delete();
        RegistryRequestAction::query()->delete();
        RegistryRequest::query()->forceDelete();

        $this->command?->info('Cleared existing registry requests and related records.');
    }

    protected function seedDemoRequests(): void
    {
        $statusManager = app(RegistryStatusManager::class);
        $definitions = WorkflowDefinition::query()
            ->with('steps')
            ->whereIn('code', ['amendment', 'complaint', 'inquiry', 'analysis'])
            ->get()
            ->keyBy('code');

        $categories = RegistryRequestCategory::query()
            ->with('workflowDefinition.steps')
            ->get()
            ->keyBy('code');

        $scenarios = [
            // --- Requests index: drafts & early stages ---
            [
                'category' => 'general',
                'status' => RegistryRequest::STATUS_DRAFT,
                'stage' => null,
                'direction' => 'incoming',
                'priority' => 'normal',
                'subject' => 'Draft correspondence — pending internal review',
                'days_ago' => 1,
                'section' => 'requests',
            ],
            [
                'category' => 'inquiry',
                'workflow' => 'inquiry',
                'status' => RegistryRequest::STATUS_OPEN,
                'stage' => 'registry',
                'direction' => 'incoming',
                'priority' => 'normal',
                'subject' => 'Customer inquiry on turnaround times',
                'days_ago' => 2,
                'section' => 'requests',
            ],
            [
                'category' => 'amendment',
                'workflow' => 'amendment',
                'status' => RegistryRequest::STATUS_RETURNED,
                'stage' => 'registry',
                'direction' => 'incoming',
                'priority' => 'high',
                'subject' => 'Certificate amendment — returned for missing documents',
                'days_ago' => 5,
                'last_action' => 'reject',
                'section' => 'requests',
            ],
            [
                'category' => 'service',
                'workflow' => 'inquiry',
                'status' => RegistryRequest::STATUS_CANCELLED,
                'stage' => 'registry',
                'direction' => 'outgoing',
                'priority' => 'low',
                'subject' => 'Service request withdrawn by client',
                'days_ago' => 10,
                'section' => 'requests',
            ],

            // --- Approval queue (pending_approval at mid-workflow stages) ---
            [
                'category' => 'complaint',
                'workflow' => 'complaint',
                'status' => RegistryRequest::STATUS_PENDING_APPROVAL,
                'stage' => 'investigation',
                'direction' => 'incoming',
                'priority' => 'urgent',
                'subject' => 'Complaint — sample handling at collection point',
                'days_ago' => 4,
                'assign' => true,
                'documents' => true,
                'section' => 'approvals',
            ],
            [
                'category' => 'analysis',
                'workflow' => 'analysis',
                'status' => RegistryRequest::STATUS_PENDING_APPROVAL,
                'stage' => 'director',
                'direction' => 'incoming',
                'priority' => 'high',
                'subject' => 'New analysis request — industrial effluent monitoring',
                'days_ago' => 6,
                'assign' => true,
                'documents' => true,
                'section' => 'approvals',
            ],
            [
                'category' => 'amendment',
                'workflow' => 'amendment',
                'status' => RegistryRequest::STATUS_PENDING_APPROVAL,
                'stage' => 'lab_manager',
                'direction' => 'incoming',
                'priority' => 'high',
                'subject' => 'Amendment to report REF-2025-8841 — unit correction',
                'days_ago' => 7,
                'assign' => true,
                'section' => 'approvals',
            ],
            [
                'category' => 'inquiry',
                'workflow' => 'inquiry',
                'status' => RegistryRequest::STATUS_PENDING_APPROVAL,
                'stage' => 'customer_service',
                'direction' => 'incoming',
                'priority' => 'normal',
                'subject' => 'Pricing inquiry for multi-site water testing',
                'days_ago' => 3,
                'assign' => true,
                'section' => 'approvals',
            ],
            [
                'category' => 'regulatory',
                'workflow' => 'inquiry',
                'status' => RegistryRequest::STATUS_PENDING_APPROVAL,
                'stage' => 'registry',
                'direction' => 'incoming',
                'priority' => 'urgent',
                'subject' => 'Regulatory notice — updated submission requirements',
                'days_ago' => 2,
                'assign' => true,
                'section' => 'approvals',
            ],
            [
                'category' => 'appeal',
                'workflow' => 'inquiry',
                'status' => RegistryRequest::STATUS_PENDING_APPROVAL,
                'stage' => 'customer_service',
                'direction' => 'incoming',
                'priority' => 'high',
                'subject' => 'Appeal against rejected sample acceptance',
                'days_ago' => 8,
                'assign' => true,
                'section' => 'approvals',
            ],

            // --- Approved / closed (completed workflows) ---
            [
                'category' => 'complaint',
                'workflow' => 'complaint',
                'status' => RegistryRequest::STATUS_CLOSED,
                'stage' => 'closed',
                'direction' => 'incoming',
                'priority' => 'normal',
                'subject' => 'Complaint resolved — delayed report delivery',
                'days_ago' => 30,
                'closed' => true,
                'documents' => true,
                'section' => 'approved',
            ],
            [
                'category' => 'analysis',
                'workflow' => 'analysis',
                'status' => RegistryRequest::STATUS_CLOSED,
                'stage' => 'closed',
                'direction' => 'incoming',
                'priority' => 'normal',
                'subject' => 'Analysis request completed — soil heavy metals panel',
                'days_ago' => 25,
                'closed' => true,
                'documents' => true,
                'section' => 'approved',
            ],
            [
                'category' => 'amendment',
                'workflow' => 'amendment',
                'status' => RegistryRequest::STATUS_CLOSED,
                'stage' => 'completed',
                'direction' => 'incoming',
                'priority' => 'normal',
                'subject' => 'Certificate amendment issued — client name update',
                'days_ago' => 20,
                'closed' => true,
                'section' => 'approved',
            ],
            [
                'category' => 'inquiry',
                'workflow' => 'inquiry',
                'status' => RegistryRequest::STATUS_CLOSED,
                'stage' => 'closed',
                'direction' => 'incoming',
                'priority' => 'low',
                'subject' => 'General inquiry closed — accreditation scope',
                'days_ago' => 18,
                'closed' => true,
                'section' => 'approved',
            ],
            [
                'category' => 'internal',
                'workflow' => 'inquiry',
                'status' => RegistryRequest::STATUS_CLOSED,
                'stage' => 'closed',
                'direction' => 'outgoing',
                'priority' => 'normal',
                'subject' => 'Internal memo — Q1 registry process review',
                'days_ago' => 15,
                'closed' => true,
                'section' => 'approved',
            ],
            [
                'category' => 'investigation',
                'workflow' => 'complaint',
                'status' => RegistryRequest::STATUS_CLOSED,
                'stage' => 'closed',
                'direction' => 'incoming',
                'priority' => 'high',
                'subject' => 'Investigation closed — equipment calibration dispute',
                'days_ago' => 40,
                'closed' => true,
                'section' => 'approved',
            ],

            // --- Correspondence register (incoming / outgoing mix) ---
            [
                'category' => 'general',
                'workflow' => 'inquiry',
                'status' => RegistryRequest::STATUS_OPEN,
                'stage' => 'registry',
                'direction' => 'incoming',
                'priority' => 'normal',
                'subject' => 'Incoming letter — partnership proposal',
                'days_ago' => 1,
                'received_today' => true,
                'section' => 'correspondence',
            ],
            [
                'category' => 'general',
                'workflow' => 'inquiry',
                'status' => RegistryRequest::STATUS_CLOSED,
                'stage' => 'closed',
                'direction' => 'outgoing',
                'priority' => 'normal',
                'subject' => 'Outgoing response — ISO 17025 audit schedule',
                'days_ago' => 12,
                'closed' => true,
                'section' => 'correspondence',
            ],
            [
                'category' => 'regulatory',
                'workflow' => 'inquiry',
                'status' => RegistryRequest::STATUS_OPEN,
                'stage' => 'registry',
                'direction' => 'outgoing',
                'priority' => 'high',
                'subject' => 'Outgoing submission — quarterly compliance return',
                'days_ago' => 0,
                'received_today' => true,
                'section' => 'correspondence',
            ],

            // --- Dashboard: received today, delayed, deep amendment pipeline ---
            [
                'category' => 'analysis',
                'workflow' => 'analysis',
                'status' => RegistryRequest::STATUS_PENDING_APPROVAL,
                'stage' => 'sro',
                'direction' => 'incoming',
                'priority' => 'normal',
                'subject' => 'Analysis request — drinking water bacteriological',
                'days_ago' => 0,
                'received_today' => true,
                'section' => 'dashboard',
            ],
            [
                'category' => 'amendment',
                'workflow' => 'amendment',
                'status' => RegistryRequest::STATUS_PENDING_APPROVAL,
                'stage' => 'analyst',
                'direction' => 'incoming',
                'priority' => 'normal',
                'subject' => 'Amendment in analyst review — result table correction',
                'days_ago' => 14,
                'delayed' => true,
                'assign' => true,
                'section' => 'dashboard',
            ],
            [
                'category' => 'amendment',
                'workflow' => 'amendment',
                'status' => RegistryRequest::STATUS_PENDING_APPROVAL,
                'stage' => 'qa',
                'direction' => 'incoming',
                'priority' => 'high',
                'subject' => 'Amendment awaiting QA sign-off',
                'days_ago' => 12,
                'delayed' => true,
                'assign' => true,
                'documents' => true,
                'section' => 'dashboard',
            ],
            [
                'category' => 'complaint',
                'workflow' => 'complaint',
                'status' => RegistryRequest::STATUS_PENDING_APPROVAL,
                'stage' => 'resolution',
                'direction' => 'incoming',
                'priority' => 'normal',
                'subject' => 'Complaint at resolution — billing discrepancy',
                'days_ago' => 9,
                'assign' => true,
                'section' => 'dashboard',
            ],
            [
                'category' => 'analysis',
                'workflow' => 'analysis',
                'status' => RegistryRequest::STATUS_PENDING_APPROVAL,
                'stage' => 'lab_receiving',
                'direction' => 'incoming',
                'priority' => 'normal',
                'subject' => 'Samples received at lab — awaiting closure',
                'days_ago' => 11,
                'assign' => true,
                'section' => 'dashboard',
            ],
        ];

        foreach ($scenarios as $scenario) {
            $category = $categories->get($scenario['category']);
            if ($category === null) {
                continue;
            }

            $workflow = isset($scenario['workflow'])
                ? $definitions->get($scenario['workflow'])
                : $category->workflowDefinition;

            $this->createScenarioRequest(
                $category,
                $workflow,
                $scenario,
                $statusManager
            );
        }
    }

    /**
     * @param  array<string, mixed>  $scenario
     */
    protected function createScenarioRequest(
        RegistryRequestCategory $category,
        ?WorkflowDefinition $workflow,
        array $scenario,
        RegistryStatusManager $statusManager
    ): RegistryRequest {
        $createdAt = now()->subDays($scenario['days_ago'] ?? 0);
        $receivedAt = ! empty($scenario['received_today'])
            ? now()->subHours(random_int(1, 8))
            : $createdAt->copy()->addHours(2);

        if (! empty($scenario['delayed'])) {
            $createdAt = now()->subDays(12);
        }

        $reference = $this->nextReference($category);
        $request = RegistryRequest::create([
            'reference_no' => $reference,
            'request_category_id' => $category->id,
            'workflow_definition_id' => $workflow?->id,
            'subject' => $scenario['subject'],
            'description' => 'Demo registry record seeded for ' . ($scenario['section'] ?? 'general') . ' section testing.',
            'metadata' => [
                'seed_section' => $scenario['section'] ?? 'general',
                'source' => 'RegistryModuleDataSeeder',
            ],
            'priority' => $scenario['priority'] ?? 'normal',
            'direction' => $scenario['direction'] ?? 'incoming',
            'current_stage' => $scenario['stage'] ?? null,
            'status' => $scenario['status'],
            'submitting_party' => $this->sampleSubmittingParties[$this->referenceSequence % count($this->sampleSubmittingParties)],
            'submitted_by' => null,
            'assigned_to' => null,
            'received_by' => null,
            'received_at' => $receivedAt,
            'closed_at' => ! empty($scenario['closed']) ? $createdAt->copy()->addDays(3) : null,
            'company_id' => null,
            'created_at' => $createdAt,
            'updated_at' => ! empty($scenario['delayed'])
                ? now()->subDays(9)
                : $createdAt->copy()->addDays(min($scenario['days_ago'] ?? 0, 3)),
        ]);

        if ($workflow !== null && $scenario['stage'] !== null) {
            $this->seedWorkflowHistory(
                $request,
                $workflow,
                $scenario['stage'],
                $scenario['status'],
                $statusManager,
                $scenario['last_action'] ?? 'approve'
            );
        }

        // Assignments skipped: registry_requests user FK columns are bigint; users table uses UUID ids.

        if (! empty($scenario['documents'])) {
            $this->seedDocuments($request, $createdAt);
        }

        RegistryRequestAction::create([
            'registry_request_id' => $request->id,
            'action_type' => 'created',
            'from_stage' => null,
            'to_stage' => $scenario['stage'],
            'comment' => 'Request registered in the system.',
            'performed_by' => null,
            'performed_at' => $createdAt,
        ]);

        return $request;
    }

    protected function seedWorkflowHistory(
        RegistryRequest $request,
        WorkflowDefinition $workflow,
        string $currentStageCode,
        string $status,
        RegistryStatusManager $statusManager,
        string $terminalAction = 'approve'
    ): void {
        $steps = $workflow->steps->sortBy('sequence')->values();
        $currentIndex = $steps->search(fn (WorkflowStep $step) => $step->step_code === $currentStageCode);

        if ($currentIndex === false) {
            return;
        }

        $cursor = $request->created_at ?? now();
        $performer = null;

        for ($i = 0; $i <= $currentIndex; $i++) {
            $step = $steps[$i];
            $isCurrent = $i === $currentIndex;
            $isFinal = (bool) $step->is_final;
            $enteredAt = $cursor->copy();
            $exitedAt = null;
            $durationSeconds = null;
            $slaBreached = false;

            if (! $isCurrent || $status === RegistryRequest::STATUS_CLOSED) {
                $hoursInStage = match ($step->step_code) {
                    'director' => 18,
                    'sro' => 8,
                    'investigation' => 36,
                    'qa' => 12,
                    default => 6,
                };
                $durationSeconds = $hoursInStage * 3600;
                $exitedAt = $enteredAt->copy()->addSeconds($durationSeconds);
                $slaBreached = $step->step_code === 'director' && $durationSeconds > 86400 * 2;
                $cursor = $exitedAt;
            }

            RegistryRequestStatusLog::create([
                'registry_request_id' => $request->id,
                'stage_code' => $step->step_code,
                'entered_at' => $enteredAt,
                'exited_at' => $exitedAt,
                'duration_seconds' => $durationSeconds,
                'sla_breached' => $slaBreached,
            ]);

            if ($i > 0) {
                $previous = $steps[$i - 1];
                $actionType = ($isCurrent && $status === RegistryRequest::STATUS_RETURNED)
                    ? 'reject'
                    : 'approve';

                RegistryRequestAction::create([
                    'registry_request_id' => $request->id,
                    'action_type' => $actionType,
                    'from_stage' => $previous->step_code,
                    'to_stage' => $step->step_code,
                    'comment' => $actionType === 'reject'
                        ? 'Returned to registry for additional information.'
                        : 'Advanced to ' . $step->step_name . '.',
                    'performed_by' => $performer,
                    'performed_at' => $enteredAt,
                ]);
            }

            if ($isCurrent && $status === RegistryRequest::STATUS_RETURNED && $terminalAction === 'reject') {
                break;
            }

            if ($isFinal && $status === RegistryRequest::STATUS_CLOSED) {
                RegistryRequestAction::create([
                    'registry_request_id' => $request->id,
                    'action_type' => 'close',
                    'from_stage' => $step->step_code,
                    'to_stage' => $step->step_code,
                    'comment' => 'Request completed and closed.',
                    'performed_by' => $performer,
                    'performed_at' => $exitedAt ?? $enteredAt,
                ]);
            }
        }

        if ($status === RegistryRequest::STATUS_PENDING_APPROVAL) {
            $currentStep = $steps[$currentIndex];
            $request->update([
                'status' => $statusManager->statusForStage($currentStep),
            ]);
        }
    }

    protected function seedDocuments(RegistryRequest $request, Carbon $baseTime): void
    {
        $files = [
            ['cover_letter.pdf', 'application/pdf', 245_000],
            ['supporting_evidence.pdf', 'application/pdf', 512_000],
            ['client_correspondence.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 88_000],
        ];

        $docCount = min(count($files), ($this->referenceSequence % 3) + 1);
        foreach (array_slice($files, 0, $docCount) as $index => [$name, $mime, $size]) {
            RegistryRequestDocument::create([
                'registry_request_id' => $request->id,
                'original_name' => $name,
                'stored_path' => 'registry/demo/' . $request->id . '/' . $name,
                'mime_type' => $mime,
                'file_size' => $size,
                'version' => $index + 1,
                'uploaded_by' => null,
                'created_at' => $baseTime->copy()->addHours($index + 1),
                'updated_at' => $baseTime->copy()->addHours($index + 1),
            ]);
        }
    }

    protected function nextReference(RegistryRequestCategory $category): string
    {
        $generator = app(RegistryReferenceGenerator::class);
        $reference = $generator->generate($category);

        while (RegistryRequest::query()->where('reference_no', $reference)->exists()) {
            $this->referenceSequence++;
            $prefix = strtoupper(substr($category->code, 0, 3));
            $reference = sprintf('%s-%s-%05d', $prefix, now()->format('Y'), $this->referenceSequence);
        }

        $this->referenceSequence++;

        return $reference;
    }

}

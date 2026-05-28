<?php

namespace Database\Seeders;

use App\Models\Auth\Role;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentAmendment;
use App\Models\DMS\DocumentApprovalWorkflow;
use App\Models\DMS\DocumentApprovalWorkflowStep;
use App\Models\DMS\DocumentAuditLog;
use App\Models\DMS\DocumentPermission;
use App\Models\DMS\DocumentType;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DmsModuleWorkflowSeeder extends Seeder
{
    private const DEMO_DOC_PREFIX = 'DEMO-DMS';

    private const DEMO_TYPE_CODES = ['DEMO-SOP', 'DEMO-POL', 'DEMO-FRM', 'DEMO-LAB-SOP'];

    /** @var array<string, mixed> */
    private array $ctx = [];

    /** @var array<string, DocumentType> */
    private array $types = [];

    public function run(): void
    {
        Model::unguard();

        DB::transaction(function () {
            $this->resolveContext();
            $this->command?->info('DMS module seeder — user: '.$this->ctx['user_name'].' (id: '.$this->ctx['user_id'].')');

            $this->removeExistingDemoData();
            $this->seedDocumentTypes();
            $this->seedApprovalWorkflows();
            $this->seedDocumentTypePermissions();
            $this->seedDocuments();
            $this->seedAmendments();
            $this->seedAuditLogsForReports();
            $this->ensureDemoFilesOnDisk();

            $this->command?->info('DMS module workflow demo data seeded successfully.');
        });

        Model::reguard();
    }

    private function resolveContext(): void
    {
        $user = User::query()->where('active', 1)->where('is_client', 0)->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();

        if (! $user) {
            throw new \RuntimeException('No user found. Create at least one user before seeding the DMS module.');
        }

        $secondUser = User::query()
            ->where('active', 1)
            ->where('id', '!=', $user->id)
            ->orderBy('id')
            ->first();

        $adminRole = Role::query()->where('name', 'admin')->first();

        $this->ctx = [
            'user' => $user,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'second_user' => $secondUser,
            'admin_role' => $adminRole,
            'now' => Carbon::now(),
        ];
    }

    private function removeExistingDemoData(): void
    {
        $demoDocumentIds = Document::withTrashed()
            ->where('document_number', 'like', self::DEMO_DOC_PREFIX.'%')
            ->pluck('id');

        if ($demoDocumentIds->isNotEmpty()) {
            DocumentAuditLog::query()
                ->where('auditable_type', Document::class)
                ->whereIn('auditable_id', $demoDocumentIds)
                ->delete();

            DocumentAmendment::query()->whereIn('document_id', $demoDocumentIds)->delete();
            Document::withTrashed()->whereIn('id', $demoDocumentIds)->forceDelete();
        }

        $demoTypeIds = DocumentType::query()
            ->whereIn('code', self::DEMO_TYPE_CODES)
            ->pluck('id');

        if ($demoTypeIds->isNotEmpty()) {
            $workflowIds = DocumentApprovalWorkflow::query()
                ->whereIn('document_type_id', $demoTypeIds)
                ->pluck('id');

            if ($workflowIds->isNotEmpty()) {
                DocumentApprovalWorkflowStep::query()->whereIn('workflow_id', $workflowIds)->delete();
                DocumentApprovalWorkflow::query()->whereIn('id', $workflowIds)->delete();
            }

            DocumentPermission::query()
                ->where('permissionable_type', DocumentType::class)
                ->whereIn('permissionable_id', $demoTypeIds)
                ->delete();

            DocumentType::query()->whereIn('id', $demoTypeIds)->delete();
        }

        DocumentAuditLog::query()
            ->where('description', 'like', self::DEMO_DOC_PREFIX.'%')
            ->delete();
    }

    private function seedDocumentTypes(): void
    {
        $userId = $this->ctx['user_id'];

        $sop = DocumentType::create([
            'name' => 'Demo Standard Operating Procedure',
            'code' => 'DEMO-SOP',
            'description' => 'Seeded SOP document type for workflow demos',
            'numbering_format' => '{TYPE_CODE}-{YEAR}-{SEQ}',
            'amendment_limit' => 5,
            'is_active' => true,
            'sort_order' => 10,
            'created_by' => $userId,
        ]);

        $policy = DocumentType::create([
            'name' => 'Demo Policy',
            'code' => 'DEMO-POL',
            'description' => 'Seeded policy document type',
            'numbering_format' => '{TYPE_CODE}-{YEAR}-{SEQ}',
            'amendment_limit' => 3,
            'is_active' => true,
            'sort_order' => 20,
            'created_by' => $userId,
        ]);

        $form = DocumentType::create([
            'name' => 'Demo Form / Record',
            'code' => 'DEMO-FRM',
            'description' => 'Seeded form document type',
            'numbering_format' => '{TYPE_CODE}-{YEAR}-{SEQ}',
            'amendment_limit' => 10,
            'is_active' => true,
            'sort_order' => 30,
            'created_by' => $userId,
        ]);

        $labSop = DocumentType::create([
            'name' => 'Demo Laboratory SOP (child)',
            'code' => 'DEMO-LAB-SOP',
            'description' => 'Child type under DEMO-SOP',
            'parent_id' => $sop->id,
            'numbering_format' => '{PARENT_CODE}-{TYPE_CODE}-{YEAR}-{SEQ}',
            'amendment_limit' => 5,
            'is_active' => true,
            'sort_order' => 11,
            'created_by' => $userId,
        ]);

        $this->types = [
            'sop' => $sop,
            'policy' => $policy,
            'form' => $form,
            'lab_sop' => $labSop,
        ];
    }

    private function seedApprovalWorkflows(): void
    {
        $userId = $this->ctx['user_id'];

        foreach ($this->types as $type) {
            $workflow = DocumentApprovalWorkflow::create([
                'document_type_id' => $type->id,
                'workflow_name' => $type->name.' approval',
                'description' => 'Seeded two-step authorization and approval workflow',
                'is_active' => true,
                'created_by' => $userId,
            ]);

            DocumentApprovalWorkflowStep::create([
                'workflow_id' => $workflow->id,
                'step_order' => 1,
                'step_name' => 'Authorize',
                'step_type' => 'authorization',
                'assignee_type' => User::class,
                'assignee_id' => $userId,
                'is_required' => true,
            ]);

            DocumentApprovalWorkflowStep::create([
                'workflow_id' => $workflow->id,
                'step_order' => 2,
                'step_name' => 'Approve',
                'step_type' => 'approval',
                'assignee_type' => User::class,
                'assignee_id' => $userId,
                'is_required' => true,
            ]);
        }
    }

    private function seedDocumentTypePermissions(): void
    {
        $role = $this->ctx['admin_role'];
        if (! $role) {
            return;
        }

        $grantedBy = $this->ctx['user_id'];
        $permissionTypes = ['view', 'add', 'edit', 'delete', 'amend', 'authorize_amendment', 'approve_amendment'];

        foreach ($this->types as $type) {
            foreach ($permissionTypes as $permissionType) {
                DocumentPermission::create([
                    'permissionable_type' => DocumentType::class,
                    'permissionable_id' => $type->id,
                    'subject_type' => Role::class,
                    'subject_id' => $role->id,
                    'permission_type' => $permissionType,
                    'granted_by' => $grantedBy,
                ]);
            }
        }
    }

    private function seedDocuments(): void
    {
        $userId = $this->ctx['user_id'];
        $now = $this->ctx['now'];

        $definitions = [
            [
                'number' => self::DEMO_DOC_PREFIX.'-DRAFT-01',
                'type' => 'sop',
                'title' => 'Demo draft — sample handling overview',
                'status' => 'draft',
                'approval_status' => null,
                'is_archived' => false,
            ],
            [
                'number' => self::DEMO_DOC_PREFIX.'-PENDING-01',
                'type' => 'policy',
                'title' => 'Demo pending approval — quality policy excerpt',
                'status' => 'pending_approval',
                'approval_status' => 'pending',
                'is_archived' => false,
            ],
            [
                'number' => self::DEMO_DOC_PREFIX.'-APPROVED-01',
                'type' => 'sop',
                'title' => 'Demo approved — laboratory safety protocol',
                'status' => 'approved',
                'approval_status' => 'approved',
                'is_archived' => false,
                'approved' => true,
            ],
            [
                'number' => self::DEMO_DOC_PREFIX.'-APPROVED-02',
                'type' => 'policy',
                'title' => 'Demo approved — document control procedure',
                'status' => 'approved',
                'approval_status' => 'approved',
                'is_archived' => false,
                'approved' => true,
                'amendment_count' => 2,
            ],
            [
                'number' => self::DEMO_DOC_PREFIX.'-APPROVED-03',
                'type' => 'form',
                'title' => 'Demo approved — equipment calibration log',
                'status' => 'approved',
                'approval_status' => 'approved',
                'is_archived' => false,
                'approved' => true,
                'expiry_date' => $now->copy()->addDays(14),
                'is_expiring' => true,
            ],
            [
                'number' => self::DEMO_DOC_PREFIX.'-LAB-01',
                'type' => 'lab_sop',
                'title' => 'Demo approved — microbiology plating SOP',
                'status' => 'approved',
                'approval_status' => 'approved',
                'is_archived' => false,
                'approved' => true,
            ],
            [
                'number' => self::DEMO_DOC_PREFIX.'-ARCHIVED-01',
                'type' => 'sop',
                'title' => 'Demo archived — superseded safety bulletin',
                'status' => 'archived',
                'approval_status' => 'approved',
                'is_archived' => true,
                'approved' => true,
                'archive_reason' => 'Superseded by '.self::DEMO_DOC_PREFIX.'-APPROVED-01',
            ],
            [
                'number' => self::DEMO_DOC_PREFIX.'-REJECTED-01',
                'type' => 'policy',
                'title' => 'Demo rejected — draft policy revision',
                'status' => 'draft',
                'approval_status' => 'rejected',
                'is_archived' => false,
            ],
        ];

        foreach ($definitions as $def) {
            $type = $this->types[$def['type']];
            $fileName = Str::slug($def['number']).'.pdf';
            $filePath = 'demo/'.$fileName;

            $attrs = [
                'document_type_id' => $type->id,
                'document_number' => $def['number'],
                'title' => $def['title'],
                'description' => 'Seeded document for DMS workflow and reporting demos.',
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => 1024,
                'mime_type' => 'application/pdf',
                'owner_id' => $userId,
                'created_by' => $userId,
                'version_number' => 1,
                'amendment_count' => $def['amendment_count'] ?? 0,
                'status' => $def['status'],
                'approval_status' => $def['approval_status'],
                'is_archived' => $def['is_archived'],
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now,
            ];

            if (! empty($def['approved'])) {
                $attrs['approved_by'] = $userId;
                $attrs['approved_at'] = $now->copy()->subDays(5);
            }

            if ($def['is_archived']) {
                $attrs['archived_by'] = $userId;
                $attrs['archived_at'] = $now->copy()->subDays(2);
                $attrs['archive_reason'] = $def['archive_reason'] ?? 'Archived for demo';
            }

            if (! empty($def['expiry_date'])) {
                $attrs['expiry_date'] = $def['expiry_date'];
                $attrs['is_expiring'] = (bool) ($def['is_expiring'] ?? false);
            }

            Document::create($attrs);
        }
    }

    private function seedAmendments(): void
    {
        $document = Document::query()
            ->where('document_number', self::DEMO_DOC_PREFIX.'-APPROVED-02')
            ->first();

        if (! $document) {
            return;
        }

        $userId = $this->ctx['user_id'];
        $now = $this->ctx['now'];

        $rows = [
            [
                'amendment_number' => 1,
                'status' => 'requested',
                'authorization_status' => 'pending',
                'approval_status' => null,
                'amendment_reason' => 'Update references to ISO 17025:2017 clause 8.3',
                'requested_at' => $now->copy()->subDays(4),
            ],
            [
                'amendment_number' => 2,
                'status' => 'authorized',
                'authorization_status' => 'approved',
                'approval_status' => 'pending',
                'amendment_reason' => 'Clarify retention period for controlled copies',
                'authorized_by' => $userId,
                'authorized_at' => $now->copy()->subDays(3),
                'requested_at' => $now->copy()->subDays(5),
            ],
            [
                'amendment_number' => 3,
                'status' => 'amended',
                'authorization_status' => 'approved',
                'approval_status' => 'pending',
                'amendment_reason' => 'Replace obsolete appendix A',
                'authorized_by' => $userId,
                'authorized_at' => $now->copy()->subDays(2),
                'amended_by' => $userId,
                'amended_at' => $now->copy()->subDay(),
                'file_before_path' => 'demo/'.Str::slug(self::DEMO_DOC_PREFIX.'-APPROVED-02').'-before.pdf',
                'file_after_path' => 'demo/'.Str::slug(self::DEMO_DOC_PREFIX.'-APPROVED-02').'-after.pdf',
                'requested_at' => $now->copy()->subDays(6),
            ],
            [
                'amendment_number' => 4,
                'status' => 'approved',
                'authorization_status' => 'approved',
                'approval_status' => 'approved',
                'amendment_reason' => 'Annual review — minor editorial changes',
                'authorized_by' => $userId,
                'authorized_at' => $now->copy()->subDays(8),
                'approved_by' => $userId,
                'approved_at' => $now->copy()->subDays(7),
                'requested_at' => $now->copy()->subDays(9),
            ],
            [
                'amendment_number' => 5,
                'status' => 'rejected',
                'authorization_status' => 'rejected',
                'approval_status' => null,
                'amendment_reason' => 'Proposed scope change without management review',
                'authorization_comment' => 'Rejected — requires management review record first.',
                'requested_at' => $now->copy()->subDays(1),
            ],
        ];

        foreach ($rows as $row) {
            DocumentAmendment::create(array_merge([
                'document_id' => $document->id,
                'amendment_description' => self::DEMO_DOC_PREFIX.' seeded amendment',
                'requested_by' => $userId,
            ], $row));
        }
    }

    private function seedAuditLogsForReports(): void
    {
        $primaryUserId = $this->ctx['user_id'];
        $secondaryUserId = $this->ctx['second_user']?->id ?? $primaryUserId;
        $now = $this->ctx['now'];

        $reportDocuments = Document::query()
            ->whereIn('document_number', [
                self::DEMO_DOC_PREFIX.'-APPROVED-01',
                self::DEMO_DOC_PREFIX.'-APPROVED-02',
                self::DEMO_DOC_PREFIX.'-APPROVED-03',
                self::DEMO_DOC_PREFIX.'-ARCHIVED-01',
            ])
            ->get()
            ->keyBy('document_number');

        $accessActions = [
            ['action' => 'viewed', 'user_id' => $primaryUserId, 'days_ago' => 2],
            ['action' => 'viewed', 'user_id' => $secondaryUserId, 'days_ago' => 3],
            ['action' => 'downloaded', 'user_id' => $primaryUserId, 'days_ago' => 4],
            ['action' => 'downloaded', 'user_id' => $secondaryUserId, 'days_ago' => 5],
            ['action' => 'viewed', 'user_id' => $primaryUserId, 'days_ago' => 7],
            ['action' => 'viewed', 'user_id' => $secondaryUserId, 'days_ago' => 10],
            ['action' => 'downloaded', 'user_id' => $primaryUserId, 'days_ago' => 12],
            ['action' => 'viewed', 'user_id' => $primaryUserId, 'days_ago' => 15],
        ];

        foreach ($reportDocuments as $number => $document) {
            foreach ($accessActions as $index => $entry) {
                $createdAt = $now->copy()->subDays($entry['days_ago'])->addMinutes($index);

                DocumentAuditLog::create([
                    'auditable_type' => Document::class,
                    'auditable_id' => $document->id,
                    'action' => $entry['action'],
                    'user_id' => $entry['user_id'],
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'DmsModuleWorkflowSeeder/1.0',
                    'description' => self::DEMO_DOC_PREFIX." {$entry['action']} on {$number}",
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            DocumentAuditLog::create([
                'auditable_type' => Document::class,
                'auditable_id' => $document->id,
                'action' => 'created',
                'user_id' => $primaryUserId,
                'description' => self::DEMO_DOC_PREFIX." created {$number}",
                'created_at' => $now->copy()->subDays(20),
                'updated_at' => $now->copy()->subDays(20),
            ]);
        }

        $archived = $reportDocuments->get(self::DEMO_DOC_PREFIX.'-ARCHIVED-01');
        if ($archived) {
            DocumentAuditLog::create([
                'auditable_type' => Document::class,
                'auditable_id' => $archived->id,
                'action' => 'archived',
                'user_id' => $primaryUserId,
                'description' => self::DEMO_DOC_PREFIX.' archived document',
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
            ]);
        }
    }

    private function ensureDemoFilesOnDisk(): void
    {
        $disk = Storage::disk('dms');
        $placeholder = "%PDF-1.4\n% Demo placeholder for DMS seeder\n";

        $paths = Document::query()
            ->where('document_number', 'like', self::DEMO_DOC_PREFIX.'%')
            ->pluck('file_path')
            ->filter()
            ->unique();

        $paths->push('demo/'.Str::slug(self::DEMO_DOC_PREFIX.'-APPROVED-02').'-before.pdf');
        $paths->push('demo/'.Str::slug(self::DEMO_DOC_PREFIX.'-APPROVED-02').'-after.pdf');

        foreach ($paths as $path) {
            if (! $disk->exists($path)) {
                $disk->put($path, $placeholder);
            }
        }
    }
}

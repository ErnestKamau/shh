<?php

namespace App\Livewire\SubmissionForms;

use App\ChainOfCustody;
use App\BatchAttachment;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceAttachment;
use App\Models\SubmissionFormInstanceNote;
use App\Services\SampleCreationService;
use App\Services\SubmissionFormBatchSyncService;
use App\Services\SubmissionForm\SubmissionFormInstanceNoteService;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
class RequestViewPage extends Component
{
    use WithFileUploads;

    public $newAttachment;

    public bool $showAttachmentModal = false;

    public string $newAttachmentTitle = '';

    public string $newAttachmentType = '';

    public string $newCustomAttachmentType = '';

    public string $newAttachmentDescription = '';

    /** @var list<string> */
    public array $availableAttachmentTypes = [];

    /** @var list<string> */
    protected array $defaultAttachmentTypes = [
        'Permit',
        'Invoice',
        'Packing List',
        'Report',
        'Certificate',
        'Authorization Letter',
    ];

    public string $submissionFormId;

    public string $instanceId;

    public string $activeTab = 'samples';

    public string $noteBody = '';

    public string $noteVisibility = SubmissionFormInstanceNote::VISIBILITY_INTERNAL;

    public SubmissionFormInstance $instance;

    public SubmissionForm $submissionForm;

    public bool $linkedBatchesOutOfSyncWithForm = false;

    public function mount(
        string $submissionFormId,
        string $instanceId,
        SubmissionFormBatchSyncService $batchSyncService
    ): void {
        $this->submissionFormId = $submissionFormId;
        $this->instanceId = $instanceId;
        $this->newAttachments = [
            ['file' => null, 'type' => '', 'heading' => ''],
        ];

        $this->submissionForm = SubmissionForm::query()->findOrFail($submissionFormId);
        $this->instance = SubmissionFormInstance::query()
            ->with([
                'submissionForm',
                'submittedBy',
                'reviewedBy',
                'crmCustomer',
                'batches',
                'notes.author',
                'analysisAcceptanceForms',
                'attachmentInstances.submissionForm',
            ])
            ->where('submission_form_id', $submissionFormId)
            ->findOrFail($instanceId);

        if ($this->instance->batches()->exists()) {
            $this->linkedBatchesOutOfSyncWithForm = $batchSyncService
                ->linkedBatchesOutOfSyncWithForm($this->instance);
        }

        $this->loadAvailableAttachmentTypes();
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['samples', 'notes', 'attachments', 'custody'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function addNote(SubmissionFormInstanceNoteService $noteService): void
    {
        $user = auth()->user();
        $this->authorizeFormAccess($user);

        $this->validate([
            'noteBody' => ['required', 'string', 'max:10000'],
            'noteVisibility' => ['required', 'in:internal,public'],
        ]);

        $noteService->addNote(
            $this->instance,
            $user,
            $this->noteBody,
            $this->noteVisibility
        );

        $this->reset(['noteBody']);
        $this->noteVisibility = SubmissionFormInstanceNote::VISIBILITY_INTERNAL;

        $this->instance->load(['notes.author']);

        session()->flash('request_view_message', 'Note saved successfully.');
    }

    public function openAttachmentModal(): void
    {
        $this->reset([
            'newAttachment',
            'newAttachmentTitle',
            'newAttachmentType',
            'newCustomAttachmentType',
            'newAttachmentDescription',
        ]);
        $this->resetValidation();
        $this->loadAvailableAttachmentTypes();
        $this->showAttachmentModal = true;
    }

    public function closeAttachmentModal(): void
    {
        $this->showAttachmentModal = false;
        $this->reset([
            'newAttachment',
            'newAttachmentTitle',
            'newAttachmentType',
            'newCustomAttachmentType',
            'newAttachmentDescription',
        ]);
        $this->resetValidation();
    }

    public function loadAvailableAttachmentTypes(): void
    {
        $dbTypes = SubmissionFormInstanceAttachment::query()
            ->whereNotNull('attachment_type')
            ->where('attachment_type', '!=', '')
            ->distinct()
            ->pluck('attachment_type')
            ->map(fn ($type) => $this->formatAttachmentTypeLabel((string) $type))
            ->all();

        $systemTypes = \App\Models\System\SystemConfiguration::query()
            ->where('key', 'attachment_type')
            ->pluck('value')
            ->map(fn ($type) => trim((string) $type))
            ->filter()
            ->all();

        $this->availableAttachmentTypes = collect(array_merge(
            $this->defaultAttachmentTypes,
            $dbTypes,
            $systemTypes
        ))
            ->map(fn ($type) => trim((string) $type))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function uploadAttachment(): void
    {
        $user = auth()->user();
        $this->authorizeFormAccess($user);

        $this->validate([
            'newAttachment' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp,txt'],
            'newAttachmentTitle' => ['required', 'string', 'max:255'],
            'newAttachmentType' => ['required', 'string', 'max:100'],
            'newCustomAttachmentType' => ['required_if:newAttachmentType,Other', 'nullable', 'string', 'max:100'],
            'newAttachmentDescription' => ['nullable', 'string', 'max:5000'],
        ], [
            'newAttachment.required' => 'Please attach a file.',
            'newAttachment.max' => 'The attachment must not be greater than 10MB.',
            'newAttachment.mimes' => 'The attachment must be a file of type: pdf, doc, docx, xls, xlsx, png, jpg, jpeg, webp, txt.',
            'newAttachmentTitle.required' => 'Please enter a title.',
            'newAttachmentType.required' => 'Please select an attachment type.',
            'newCustomAttachmentType.required_if' => 'Please enter the attachment type.',
        ]);

        $resolvedType = $this->newAttachmentType === 'Other'
            ? trim($this->newCustomAttachmentType)
            : $this->newAttachmentType;

        $originalName = $this->newAttachment->getClientOriginalName();
        $path = $this->newAttachment->store('request-attachments', 'public');

        $this->instance->customAttachments()->create([
            'file_path' => $path,
            'original_name' => $originalName,
            'uploaded_by' => $user->id,
            'attachment_type' => $resolvedType ?: null,
            'attachment_heading' => $this->newAttachmentTitle,
            'description' => $this->newAttachmentDescription ?: null,
        ]);

        $this->closeAttachmentModal();
        $this->loadAvailableAttachmentTypes();
        session()->flash('request_view_message', 'Attachment uploaded successfully.');
    }

    private function formatAttachmentTypeLabel(string $type): string
    {
        $normalized = str_replace('_', ' ', trim($type));

        return ucwords($normalized);
    }

    private function authorizeFormAccess(?\App\User $user): void
    {
        if (! $user) {
            abort(403, 'You are not authorized to access this form instance.');
        }

        if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
            return;
        }

        if ($user->can('laboratory.components.rft form.view') || $user->can('laboratory.permission')) {
            return;
        }

        abort(403, 'You are not authorized to access this form instance.');
    }

    /**
     * @return list<array{
     *     row_index: int,
     *     customer_sample_id: ?string,
     *     sample_type_id: ?string,
     *     sample_type_name: ?string,
     *     analysis_type_id: ?string,
     *     analysis_type_name: ?string,
     *     analysis_element_id: ?string,
     *     parameter_label: ?string
     * }>
     */
    public function getSampleLinesProperty(): array
    {
        return app(SubmissionRequestSampleLineService::class)->linesForInstance($this->instance);
    }

    /**
     * @return Collection<int, object{
     *     source: string,
     *     title: string,
     *     subtitle: ?string,
     *     user_name: ?string,
     *     occurred_at: \Carbon\Carbon,
     *     badge: ?string,
     *     comment: ?string
     * }>
     */
    public function getCustodyTimelineProperty(): Collection
    {
        $events = collect();

        $auditLogs = $this->instance->auditLogs()->with('user')->latest()->get();
        foreach ($auditLogs as $log) {
            $events->push((object) [
                'source' => 'audit',
                'title' => $log->getActionDisplayName(),
                'subtitle' => $log->action,
                'user_name' => $log->user?->name,
                'occurred_at' => $log->created_at,
                'badge' => $log->getActionBadgeColor(),
                'comment' => $log->notes,
            ]);
        }

        $intrays = $this->instance->intrays()
            ->with(['fromUser', 'toUser', 'assignedBy', 'completedByUser'])
            ->orderByDesc('created_at')
            ->get();

        foreach ($intrays as $intray) {
            $events->push((object) [
                'source' => 'intray',
                'title' => $intray->status === 'completed' ? 'Intray completed' : 'Intray assigned',
                'subtitle' => $intray->fromUser
                    ? 'From '.$intray->fromUser->name.' → '.$intray->toUser->name
                    : 'Assigned to '.$intray->toUser->name,
                'user_name' => $intray->assignedBy?->name,
                'occurred_at' => $intray->completed_at ?? $intray->created_at,
                'badge' => $intray->status === 'completed' ? 'success' : 'warning',
                'comment' => $intray->comment,
            ]);
        }

        foreach ($this->instance->batches as $batch) {
            $custodyRecords = ChainOfCustody::query()
                ->where('sample_header_id', $batch->id)
                ->orderByDesc('created_at')
                ->get();

            foreach ($custodyRecords as $custody) {
                $events->push((object) [
                    'source' => 'batch',
                    'title' => $custody->workflow_stage ?? 'Batch custody',
                    'subtitle' => 'Batch '.$batch->batch_code,
                    'user_name' => $custody->started_by?->name,
                    'occurred_at' => $custody->created_at,
                    'badge' => 'info',
                    'comment' => null,
                ]);
            }
        }

        return $events
            ->filter(fn ($event) => $event->occurred_at !== null)
            ->sortByDesc(fn ($event) => $event->occurred_at)
            ->values();
    }

    public function workflowBoardStatus(): string
    {
        $batch = $this->instance->batches->first();
        if ($batch && $batch->status === 'Samples Request Review') {
            return 'Samples Request Review';
        }

        if ($this->instance->analysisAcceptanceForms->isNotEmpty()) {
            return 'Samples Request Review';
        }

        return 'Samples Receiving';
    }

    public function render(): View
    {
        $formData = $this->instance->getFormDataForDisplay();

        $attachmentInstances = $this->instance->attachmentInstances()
            ->with('submissionForm')
            ->whereIn('status', ['submitted', 'in_review', 'approved', 'rejected', 'draft'])
            ->latest()
            ->get();

        $canCreateSamples = $this->instance->isSubmitted() && $this->instance->submissionForm->sections()
            ->whereHas('elements', fn ($query) => $query->where('is_mapped', true))
            ->exists();

        $sampleStatus = app(SampleCreationService::class)->getSampleCreationStatus($this->instance);

        $acceptanceForm = $this->instance->analysisAcceptanceForms->first();

        $batchIds = $this->instance->batches->pluck('id');
        $batchAttachments = BatchAttachment::query()
            ->whereIn('batch_id', $batchIds)
            ->where(function ($q) {
                $q->where('title', 'like', '%Laboratory Analysis Acceptance%')
                  ->orWhere('title', 'like', '%Sample Receipt Notification%');
            })
            ->latest()
            ->get();

        $customAttachments = $this->instance->customAttachments()->with('uploader')->get();

        $formMediaAttachments = [];
        $values = collect($formData['sections'] ?? [])
            ->flatMap(fn($s) => $s['element_holders'] ?? [])
            ->filter(fn($h) => $h['holder_type'] === 'field')
            ->flatMap(fn($h) => $h['elements'] ?? [])
            ->filter(fn($e) => in_array($e['element_type'], ['file', 'camera_photo', 'image_upload']))
            ->values();

        foreach ($values as $element) {
            $savedValue = $element['saved_values'][0] ?? null;
            if ($savedValue && !empty($savedValue['value']) && $savedValue['value'] !== 'N/A') {
                $candidate = trim((string) $savedValue['value']);
                $decoded = json_decode($candidate, true);
                if (is_array($decoded) && !empty($decoded)) {
                    $first = $decoded[0] ?? null;
                    if (is_string($first)) {
                        $candidate = trim($first);
                    } elseif (is_array($first)) {
                        foreach (['url', 'path', 'file_path', 'value'] as $key) {
                            if (!empty($first[$key]) && is_string($first[$key])) {
                                $candidate = trim($first[$key]);
                                break;
                            }
                        }
                    }
                }
                if ($candidate && $candidate !== 'N/A') {
                    $mediaUrl = $this->instance->resolveUploadedMediaUrl($candidate);
                    if ($mediaUrl) {
                        $formMediaAttachments[] = (object) [
                            'original_name' => $element['label'],
                            'file_url' => $mediaUrl,
                            'created_at' => $this->instance->created_at,
                        ];
                    }
                }
            }
        }

        return view('livewire.submission-forms.request-view-page', [
            'formData' => $formData,
            'attachmentInstances' => $attachmentInstances,
            'batchAttachments' => $batchAttachments,
            'customAttachments' => $customAttachments,
            'formMediaAttachments' => collect($formMediaAttachments),
            'canCreateSamples' => $canCreateSamples,
            'sampleStatus' => $sampleStatus,
            'acceptanceForm' => $acceptanceForm,
            'workflowForms' => $this->instance->workflowForms()->get(),
        ]);
    }
}

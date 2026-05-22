<?php

namespace App\Livewire\SubmissionForms;

use App\ChainOfCustody;
use App\BatchAttachment;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceNote;
use App\Services\SampleCreationService;
use App\Services\SubmissionFormBatchSyncService;
use App\Services\SubmissionForm\SubmissionFormInstanceNoteService;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class RequestViewPage extends Component
{
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

        return view('livewire.submission-forms.request-view-page', [
            'formData' => $formData,
            'attachmentInstances' => $attachmentInstances,
            'batchAttachments' => $batchAttachments,
            'canCreateSamples' => $canCreateSamples,
            'sampleStatus' => $sampleStatus,
            'acceptanceForm' => $acceptanceForm,
            'workflowForms' => $this->instance->workflowForms()->get(),
        ]);
    }
}

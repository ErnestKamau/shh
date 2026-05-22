<?php

namespace App\Livewire\Sampleworkflow;

use App\Jobs\Sampleworkflow\CreateSamplesFromAcceptanceFormJob;
use App\Livewire\Sampleworkflow\Concerns\ManagesManagerAssignments;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\SampleReceiptNotificationService;
use App\Services\Sampleworkflow\SampleReceivingDisclaimerService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class ManagerAcceptanceSignModal extends Component
{
    use ManagesManagerAssignments;

    public bool $showModal = false;

    public ?string $acceptanceFormId = null;

    public ?AnalysisAcceptanceForm $acceptanceForm = null;

    public string $managerSignerName = '';

    public string $managerSignature = '';

    public ?string $managerSignedAt = null;

    /** @var array<string, mixed> */
    public array $receiptNotificationForm = [];

    /** @var array<string, mixed> */
    public array $disclaimerForm = [];

    public bool $showSampleDisclaimer = false;

    #[On('open-manager-acceptance-sign')]
    public function openModal(string $acceptanceFormId): void
    {
        $this->authorizeLabAccess();

        $form = AnalysisAcceptanceForm::query()
            ->with([
                'lines.sampleType',
                'lines.analysisType',
                'customer',
                'pricelist',
                'submissionFormInstance',
                'sampleHeader',
            ])
            ->find($acceptanceFormId);

        if (! $form || $form->status !== AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN) {
            $this->dispatch('notify', type: 'error', message: 'This request is not awaiting laboratory manager approval.');

            return;
        }

        $form = $this->ensureSampleBatchExists($form);

        $this->resetModal();
        $this->acceptanceFormId = $form->id;
        $this->acceptanceForm = $form;
        $this->managerSignerName = (string) (Auth::user()->name ?? '');
        $this->managerSignedAt = now()->format('Y-m-d');
        $this->initializeManagerAssignmentFields($form);

        $this->receiptNotificationForm = app(SampleReceiptNotificationService::class)
            ->resolveFormStateForAcceptanceForm($form);

        $this->showSampleDisclaimer = (bool) $form->raises_sample_disclaimer;
        $this->disclaimerForm = $this->showSampleDisclaimer
            ? app(SampleReceivingDisclaimerService::class)->resolveFormStateForAcceptanceForm($form)
            : SampleReceivingDisclaimerService::emptyForm();

        $this->showModal = true;
        $this->dispatch('manager-acceptance-sign-opened');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetModal();
    }

    public function submitManagerSign(AcceptanceFormService $acceptanceFormService): void
    {
        $this->authorizeLabAccess();

        $form = $this->loadAcceptanceForm();

        if ($form->status !== AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN) {
            $this->dispatch('notify', type: 'error', message: 'This acceptance form is no longer awaiting manager approval.');
            $this->closeModal();

            return;
        }

        $this->validate(array_merge(
            $this->managerAssignmentValidationRules(),
            [
                'managerSignerName' => ['required', 'string', 'max:255'],
                'managerSignature' => ['required', 'string'],
                'managerSignedAt' => ['nullable', 'date'],
            ]
        ), $this->managerAssignmentValidationMessages());

        $receiptService = app(SampleReceiptNotificationService::class);
        $receiptService->mergePayloadIntoAcceptanceForm($form, $this->receiptNotificationForm);

        $batchHeader = $form->sampleHeader;
        if ($batchHeader === null && $form->sample_header_id) {
            $batchHeader = \App\SampleHeader::query()->find((string) $form->sample_header_id);
        }
        if ($batchHeader !== null) {
            $receiptService->persistForBatchLinkedAcceptance($form, $batchHeader, $this->receiptNotificationForm);
            $receiptWarn = '';
            $receiptService->tryFinalizePdfAttachment(
                $batchHeader,
                $this->receiptNotificationForm,
                Auth::id(),
                function (string $msg, array $errors) use (&$receiptWarn): void {
                    $receiptWarn = $msg;
                }
            );
            if ($receiptWarn !== '') {
                session()->flash('warning', 'Acceptance approved. Complete Sample Receipt Notification (GCLA 01) in Lab Acceptance tab (Part E)—required fields or signatures are missing.');
            }
        }

        $completedForm = $acceptanceFormService->recordManagerSignature(
            $form->fresh(['sampleHeader', 'lines.sampleType', 'lines.analysisType']),
            $this->managerSignerName,
            $this->managerSignature,
            $this->managerSignedAt,
            $this->leadAnalystId,
            $this->technicalSignatoryId,
            $this->assignedAnalystIds,
        );

        $batchId = (string) ($completedForm->sample_header_id ?? '');
        $redirectUrl = $batchId !== ''
            ? route('view-batch-details', [
                'batch' => $batchId,
                'client' => 0,
                'portal' => 0,
                'status' => 'Samples In Lab',
            ]) . '#laboratory-acceptance-part-5'
            : route('sample-workflow', ['status' => 'Samples In Lab']);

        session()->flash('success', 'Acceptance approved. Batch moved to Samples In Lab.');
        $this->closeModal();
        $this->dispatch('acceptance-form-completed', redirectUrl: $redirectUrl);
    }

    public function render(): View
    {
        return view('livewire.sampleworkflow.manager-acceptance-sign-modal');
    }

    private function loadAcceptanceForm(): AnalysisAcceptanceForm
    {
        if (! $this->acceptanceFormId) {
            throw ValidationException::withMessages([
                'managerSignerName' => ['Acceptance form not loaded.'],
            ]);
        }

        $form = AnalysisAcceptanceForm::query()
            ->with(['lines.sampleType', 'lines.analysisType', 'customer', 'sampleHeader'])
            ->find($this->acceptanceFormId);

        if (! $form) {
            throw ValidationException::withMessages([
                'managerSignerName' => ['Acceptance form not found.'],
            ]);
        }

        $this->acceptanceForm = $form;

        return $form;
    }

    private function authorizeLabAccess(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
            return;
        }

        if ($user->can('Laboratory.components.RFT Form.View') || $user->can('Laboratory.permission')) {
            return;
        }

        abort(403, 'You are not authorized to perform this action.');
    }

    private function resetModal(): void
    {
        $this->acceptanceFormId = null;
        $this->acceptanceForm = null;
        $this->managerSignerName = '';
        $this->managerSignature = '';
        $this->managerSignedAt = null;
        $this->resetManagerAssignmentFields();
        $this->receiptNotificationForm = SampleReceiptNotificationService::emptyForm();
        $this->disclaimerForm = SampleReceivingDisclaimerService::emptyForm();
        $this->showSampleDisclaimer = false;
    }

    private function ensureSampleBatchExists(AnalysisAcceptanceForm $form): AnalysisAcceptanceForm
    {
        if ($form->sample_header_id) {
            return $form->fresh([
                'lines.sampleType',
                'lines.analysisType',
                'customer',
                'submissionFormInstance',
                'sampleHeader',
            ]) ?? $form;
        }

        try {
            CreateSamplesFromAcceptanceFormJob::dispatchSync((string) $form->id);
        } catch (\Throwable) {
            // Job logs and stores processing_error on the form.
        }

        $refreshed = $form->fresh([
            'lines.sampleType',
            'lines.analysisType',
            'customer',
            'pricelist',
            'submissionFormInstance',
            'sampleHeader',
        ]);

        return $refreshed ?? $form;
    }
}

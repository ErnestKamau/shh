<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\CRM\CustomerContact;
use App\Models\CRM\CustomerNotification;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\SampleReceivingDisclaimerService;
use App\Services\Sampleworkflow\SampleReceiptNotificationService;
use App\Services\Sampleworkflow\CustomerContactVerificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class CustomerAcceptanceSignModal extends Component
{
    public bool $showModal = false;

    public int $currentStep = 1;

    public ?string $acceptanceFormId = null;

    public string $selectedContactId = '';

    public string $password = '';

    public ?string $verifiedContactId = null;

    public string $customerSignerName = '';

    public string $customerSignature = '';

    /** @var array<string, mixed> */
    public array $receiptNotificationForm = [];

    /** @var array<string, mixed> */
    public array $disclaimerForm = [];

    public bool $showDisclaimerClaimantSign = false;

    /** @var list<array{id: string, label: string}> */
    public array $contactOptions = [];

    public ?AnalysisAcceptanceForm $acceptanceForm = null;

    #[On('open-customer-acceptance-sign')]
    public function openModal(string $acceptanceFormId): void
    {
        $this->authorizeLabAccess();

        $form = AnalysisAcceptanceForm::query()
            ->with([
                'lines.sampleType',
                'lines.analysisType',
                'customer',
                'submissionFormInstance',
            ])
            ->find($acceptanceFormId);

        if (!$form || $form->status !== AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN) {
            $this->dispatch('notify', type: 'error', message: 'This request is not awaiting customer signature.');

            return;
        }

        if (!$form->crm_customer_id) {
            $this->dispatch('notify', type: 'error', message: 'No portal customer is linked to this request.');

            return;
        }

        $this->resetModal();
        $this->acceptanceFormId = $form->id;
        $this->acceptanceForm = $form;
        $this->contactOptions = app(CustomerContactVerificationService::class)
            ->portalContactsForCustomer((string) $form->crm_customer_id);

        if ($this->contactOptions === []) {
            $this->dispatch('notify', type: 'error', message: 'No portal contacts with login access are configured for this customer.');

            return;
        }

        $this->showModal = true;
        $this->currentStep = 1;
        $receiptService = app(SampleReceiptNotificationService::class);
        $this->receiptNotificationForm = $receiptService->resolveFormStateForAcceptanceForm($form);

        $disclaimerService = app(SampleReceivingDisclaimerService::class);
        $this->disclaimerForm = $disclaimerService->resolveFormStateForAcceptanceForm($form);
        $this->showDisclaimerClaimantSign = (bool) $form->raises_sample_disclaimer
            && $disclaimerService->claimantSignatureMissing($this->disclaimerForm);

        $this->dispatch('customer-acceptance-sign-opened');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetModal();
    }

    public function verifyIdentity(CustomerContactVerificationService $verificationService): void
    {
        $this->authorizeLabAccess();

        $form = $this->loadAcceptanceForm();

        $this->validate([
            'selectedContactId' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        try {
            $result = $verificationService->verify(
                (string) $form->crm_customer_id,
                $this->selectedContactId,
                $this->password,
                (string) $form->id
            );
        } catch (ValidationException $exception) {
            throw $exception;
        }

        $this->verifiedContactId = $result['contact_id'];
        $this->customerSignerName = $result['signer_name'];
        $this->password = '';
        $this->currentStep = 2;
        $this->dispatch('customer-acceptance-sign-step2');
    }

    public function continueToReceiptStep(): void
    {
        $this->authorizeLabAccess();

        if (!$this->verifiedContactId) {
            $this->currentStep = 1;

            return;
        }

        $this->validate([
            'customerSignature' => ['required', 'string'],
        ], [
            'customerSignature.required' => 'Please sign the Analysis Acceptance Form before continuing.',
        ]);

        if ($this->showDisclaimerClaimantSign) {
            $this->validate([
                'disclaimerForm.claimant_name' => ['required', 'string', 'max:255'],
                'disclaimerForm.claimant_signature' => ['required', 'string'],
                'disclaimerForm.claimant_signed_at' => ['nullable', 'date'],
            ]);
        }

        if (($this->receiptNotificationForm['submitter_name'] ?? '') === '') {
            $this->receiptNotificationForm['submitter_name'] = $this->customerSignerName;
        }

        if (trim((string) ($this->receiptNotificationForm['submitter_designation'] ?? '')) === '' && $this->verifiedContactId) {
            $contact = CustomerContact::query()->find($this->verifiedContactId);
            $designation = trim((string) ($contact?->job_occupation ?? ''));
            if ($designation !== '') {
                $this->receiptNotificationForm['submitter_designation'] = $designation;
            }
        }

        $this->receiptNotificationForm = app(SampleReceiptNotificationService::class)
            ->applyReceivingPersonFromAuth($this->receiptNotificationForm);

        $this->currentStep = 3;
        $this->dispatch('customer-acceptance-sign-step3');
    }

    public function goToStep(int $step): void
    {
        if ($step === 1) {
            $this->currentStep = 1;

            return;
        }

        if ($step === 2 && $this->verifiedContactId) {
            $this->currentStep = 2;
            $this->dispatch('customer-acceptance-sign-step2');

            return;
        }

        if ($step === 3 && $this->verifiedContactId && $this->customerSignature !== '') {
            $this->currentStep = 3;
            $this->dispatch('customer-acceptance-sign-step3');
        }
    }

    public function submitCustomerSign(AcceptanceFormService $acceptanceFormService): void
    {
        $this->authorizeLabAccess();

        if (!$this->verifiedContactId) {
            $this->currentStep = 1;

            return;
        }

        $form = $this->loadAcceptanceForm();

        $receiptService = app(SampleReceiptNotificationService::class);
        $disclaimerService = app(SampleReceivingDisclaimerService::class);
        $receiptService->mergePayloadIntoAcceptanceForm($form, $this->receiptNotificationForm);

        if ($this->showDisclaimerClaimantSign) {
            $this->validate([
                'disclaimerForm.claimant_name' => ['required', 'string', 'max:255'],
                'disclaimerForm.claimant_signature' => ['required', 'string'],
                'disclaimerForm.claimant_signed_at' => ['nullable', 'date'],
            ]);
            $claimantPatch = $disclaimerService->mergeFormPayloads($this->disclaimerForm, [
                'claimant_signature_source' => 'portal',
                'claimant_signed_at' => $this->disclaimerForm['claimant_signed_at'] ?? now()->format('Y-m-d'),
            ]);
            $disclaimerService->mergePayloadIntoAcceptanceForm($form, $claimantPatch);
        }

        if ($form->status !== AnalysisAcceptanceForm::STATUS_AWAITING_CUSTOMER_SIGN) {
            $this->dispatch('notify', type: 'error', message: 'This acceptance form is no longer awaiting customer signature.');
            $this->closeModal();

            return;
        }

        $this->receiptNotificationForm = $receiptService->applyReceivingPersonFromAuth($this->receiptNotificationForm);

        $this->validate(array_merge(
            [
                'customerSignerName' => ['required', 'string', 'max:255'],
                'customerSignature' => ['required', 'string'],
            ],
            $receiptService->labAssistedCustomerSignValidationRules()
        ), array_merge(
            $receiptService->labAssistedCustomerSignValidationMessages(),
            [
                'customerSignature.required' => 'Please sign the Analysis Acceptance Form.',
            ]
        ));

        $signedForm = $acceptanceFormService->recordCustomerSignature(
            $form,
            $this->customerSignerName,
            $this->customerSignature,
            now()->format('Y-m-d H:i:s')
        );

        app(SampleReceiptNotificationService::class)->applyBatchDefaultsAfterCustomerSign($signedForm);
        app(SampleReceivingDisclaimerService::class)->applyBatchDefaultsAfterCustomerSign($signedForm->fresh());
        $fresh = AnalysisAcceptanceForm::query()->find($signedForm->id);
        if ($fresh !== null) {
            $receiptService->mergePayloadIntoAcceptanceForm($fresh, $this->receiptNotificationForm);
        }

        CustomerNotification::query()
            ->where('customer_id', $form->crm_customer_id)
            ->where('entity_type', AnalysisAcceptanceForm::class)
            ->where('entity_id', $form->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $signedForm->refresh();

        if ($signedForm->processing_error) {
            session()->flash('error', 'Customer signed, but sample creation failed: ' . $signedForm->processing_error);
        } else {
            session()->flash('success', 'Customer signature recorded. Sample batch creation has been started.');
        }

        $this->closeModal();
        $this->dispatch('acceptance-form-created');
    }

    public function render(): View
    {
        return view('livewire.sampleworkflow.customer-acceptance-sign-modal');
    }

    private function loadAcceptanceForm(): AnalysisAcceptanceForm
    {
        if (!$this->acceptanceFormId) {
            throw ValidationException::withMessages([
                'password' => ['Acceptance form not loaded.'],
            ]);
        }

        $form = AnalysisAcceptanceForm::query()
            ->with(['lines.sampleType', 'lines.analysisType', 'customer'])
            ->find($this->acceptanceFormId);

        if (!$form) {
            throw ValidationException::withMessages([
                'password' => ['Acceptance form not found.'],
            ]);
        }

        $this->acceptanceForm = $form;

        return $form;
    }

    private function authorizeLabAccess(): void
    {
        $user = Auth::user();

        if (!$user) {
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
        $this->currentStep = 1;
        $this->acceptanceFormId = null;
        $this->acceptanceForm = null;
        $this->selectedContactId = '';
        $this->password = '';
        $this->verifiedContactId = null;
        $this->customerSignerName = '';
        $this->customerSignature = '';
        $this->receiptNotificationForm = SampleReceiptNotificationService::emptyForm();
        $this->disclaimerForm = SampleReceivingDisclaimerService::emptyForm();
        $this->showDisclaimerClaimantSign = false;
        $this->contactOptions = [];
    }
}

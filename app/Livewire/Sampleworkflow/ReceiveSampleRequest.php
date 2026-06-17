<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\SubmissionFormInstance;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use App\Models\Workflow\Approval;
use App\Services\Commercial\CommercialEnquiryFromFormService;
use App\Services\Sampleworkflow\SampleReceivingCheckInService;
use App\Services\SubmissionForm\PortalSubmissionFormAccess;
use App\Services\WorkflowService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ReceiveSampleRequest extends Component
{
    public const STAGE_NAME = 'Samples Receiving';

    public const APPROVAL_CODE = 'sro_receiving_sample';

    /** @var array<int, string> */
    public array $selectedFormInstanceIds = [];

    /** @var array<int, array{id: string, label: string, customer: string}> */
    public array $selectedFormSummaries = [];

    /** @var list<array<string, mixed>> */
    public array $checkInContexts = [];

    public array $responses = [];

    public string $remarks = '';

    public ?string $loadError = null;

    // New properties for dynamic TestRequestForms
    public ?string $selectedSampleTypeId = null;

    public array $formData = [];

    public $sampleTypes = [];

    /** @var array<int, string> */
    public array $lastGeneratedTrfiIds = [];

    public function mount(array $selectedFormInstanceIds = [], array $selectedFormSummaries = []): void
    {
        $this->selectedFormInstanceIds = array_values(array_filter($selectedFormInstanceIds));
        $this->selectedFormSummaries = $selectedFormSummaries;
        $this->syncLoadErrorFromApproval();
        $this->initializeResponses();

        // Self-healing seeding and load sample types
        TestRequestForm::seedDefaults();
        $this->sampleTypes = \App\SampleType::orderBy('name')->get();

        // Auto-load form data if viewing an existing request
        if (!empty($this->selectedFormInstanceIds)) {
            $this->loadExistingFormData();
        }

        $this->refreshCheckInContexts();
    }

    private function loadExistingFormData(): void
    {
        if (empty($this->selectedFormInstanceIds)) {
            return;
        }

        $firstInstance = \App\Models\SubmissionFormInstance::with(['crmCustomer', 'testRequestFormInstance', 'submissionForm.sampleTypes', 'batches.sampleType'])->find($this->selectedFormInstanceIds[0]);
        
        if (!$firstInstance) {
            return;
        }

        // Try to determine sample type from submission form
        $sampleType = null;
        if ($firstInstance->submissionForm && $firstInstance->submissionForm->sampleTypes->first()) {
            $sampleType = $firstInstance->submissionForm->sampleTypes->first();
        }

        // If no sample type from submission form, try from batches
        if (!$sampleType && $firstInstance->batches->first()) {
            $sampleType = $firstInstance->batches->first()->sampleType;
        }

        // If we found a sample type, set it and manually load the form data
        if ($sampleType) {
            $this->selectedSampleTypeId = $sampleType->id;
            $this->initializeFormDataForSampleType($sampleType->id);
            $this->loadFormDataFromInstance($firstInstance);
        }
    }

    private function initializeFormDataForSampleType(string $sampleTypeId): void
    {
        $this->formData = [];
        $form = TestRequestForm::where('sample_type_id', $sampleTypeId)->where('is_active', true)->first();
        if ($form) {
            $fields = $form->getFlatFields();
            foreach ($fields as $field) {
                if (empty($field['name'])) {
                    continue;
                }
                $isMulti = in_array($field['name'], ['sampling_apparatus', 'method_of_sampling', 'reason_of_collection', 'transport_condition', 'sampling_source', 'sample_types_ww', 'sampling_technique', 'field_data_requirements'], true);
                if ($isMulti) {
                    $this->formData[$field['name']] = [];
                } else {
                    $this->formData[$field['name']] = ($field['type'] ?? '') === 'checkbox' ? false : '';
                }
            }
        }

        if ($this->isFood || $this->isWater) {
            $this->formData['sample_rows'] = [$this->getDefaultSampleRow()];
        }
    }

    private function loadFormDataFromInstance(\App\Models\SubmissionFormInstance $instance): void
    {
        // Load existing form data from TestRequestFormInstance
        if ($instance->testRequestFormInstance) {
            $existingFormData = $instance->testRequestFormInstance->form_data ?? [];
            if (!empty($existingFormData)) {
                // Merge existing data, preserving structure
                foreach ($existingFormData as $key => $val) {
                    if (array_key_exists($key, $this->formData)) {
                        $this->formData[$key] = $val;
                    }
                }
                // Ensure sample_rows exists and has at least one row
                if (isset($existingFormData['sample_rows']) && !empty($existingFormData['sample_rows'])) {
                    $this->formData['sample_rows'] = $existingFormData['sample_rows'];
                }
            }
        } elseif ($instance->values) {
            // Try to load from submission form instance values as fallback
            foreach ($instance->values as $value) {
                $fieldName = $value->element?->name ?? $value->element_name ?? null;
                if ($fieldName && array_key_exists($fieldName, $this->formData)) {
                    $this->formData[$fieldName] = $value->value;
                }
            }
        }

        // Auto-prefill client/customer details if not already loaded
        if ($instance->crmCustomer) {
            $crmCustomer = $instance->crmCustomer;
            foreach ($this->formData as $key => $val) {
                // Only prefill if not already set from existing form data
                if (empty($val)) {
                    if (in_array($key, ['customer_name', 'client_name', 'customer', 'client'], true)) {
                        $this->formData[$key] = $crmCustomer->name;
                    }
                    if (in_array($key, ['phone', 'telephone', 'phone_number', 'mobile_number', 'telephone_number', 'tel_fax_no'], true)) {
                        $this->formData[$key] = $crmCustomer->telephone1 ?? $crmCustomer->telephone2 ?? '';
                    }
                    if (in_array($key, ['email', 'email_address'], true)) {
                        $this->formData[$key] = $crmCustomer->email ?? '';
                    }
                    if (in_array($key, ['address', 'physical_address', 'postal_address', 'customer_address'], true)) {
                        $this->formData[$key] = $crmCustomer->physical_address ?? $crmCustomer->postal_address ?? '';
                    }
                    if (in_array($key, ['contact_person', 'contact', 'contact_name'], true)) {
                        $contact = method_exists($crmCustomer, 'contacts') ? $crmCustomer->contacts()->first() : null;
                        $this->formData[$key] = $contact ? $contact->name : '';
                    }
                }
            }
        }
    }

    public function getSelectedSampleTypeProperty()
    {
        if (!$this->selectedSampleTypeId) {
            return null;
        }
        return \App\SampleType::find($this->selectedSampleTypeId);
    }

    public function getIsFoodProperty()
    {
        $st = $this->selectedSampleType;
        if (!$st) {
            return false;
        }
        return stripos($st->name, 'Food') !== false || stripos($st->code, 'FOOD') !== false;
    }

    public function getIsWaterProperty()
    {
        $st = $this->selectedSampleType;
        if (!$st) {
            return false;
        }
        $isWasteWater = stripos($st->name, 'Waste Water') !== false || stripos($st->code, 'WWTR') !== false;
        return !$isWasteWater && (stripos($st->name, 'Water') !== false || stripos($st->code, 'WTR') !== false);
    }

    public function getIsWasteWaterProperty()
    {
        $st = $this->selectedSampleType;
        if (!$st) {
            return false;
        }
        return stripos($st->name, 'Waste Water') !== false || stripos($st->code, 'WWTR') !== false;
    }

    public function getDefaultSampleRow()
    {
        if ($this->isFood) {
            return [
                'sample_no' => '',
                'sample_description' => '',
                'sampling_point' => '',
                'qty' => '',
                'sample_type' => '', // Raw, Cooked, Ready To Eat
                'sample_condition' => '', // Acceptable, Chilled, Chilled/Ambient
                'sample_temp' => '',
                'production_date' => '',
                'expiration_date' => '',
                'batch_number' => '',
                'parameters' => '',
                'state_of_sample' => '', // L, SS, S
            ];
        }

        if ($this->isWater) {
            return [
                'sample_no' => '',
                'sample_description' => '',
                'location' => '',
                'qty' => '',
                'sampling_point' => '', // Tap, Tank, Pool, Shower Head, Others
                'ph' => '',
                'appearance' => '',
                'residual_chlorine' => '',
                'odor' => '',
                'sample_temp' => '',
                'microbiology' => false,
                'legionella' => false,
                'chemical_analysis' => false,
            ];
        }

        return [];
    }

    public function addSampleRow(): void
    {
        if (!isset($this->formData['sample_rows'])) {
            $this->formData['sample_rows'] = [];
        }
        $this->formData['sample_rows'][] = $this->getDefaultSampleRow();
    }

    public function removeSampleRow(int $index): void
    {
        if (isset($this->formData['sample_rows'][$index])) {
            unset($this->formData['sample_rows'][$index]);
            $this->formData['sample_rows'] = array_values($this->formData['sample_rows']);
        }
    }

    public function updatedSelectedFormInstanceIds(): void
    {
        $this->initializeResponses();
        $this->refreshCheckInContexts();
    }

    public function updatedSelectedSampleTypeId($value): void
    {
        $this->formData = [];
        if ($value) {
            $form = TestRequestForm::where('sample_type_id', $value)->where('is_active', true)->first();
            if ($form) {
                $fields = $form->getFlatFields();
                foreach ($fields as $field) {
                    if (empty($field['name'])) {
                        continue;
                    }
                    $isMulti = in_array($field['name'], ['sampling_apparatus', 'method_of_sampling', 'reason_of_collection', 'transport_condition', 'sampling_source', 'sample_types_ww', 'sampling_technique', 'field_data_requirements'], true);
                    if ($isMulti) {
                        $this->formData[$field['name']] = [];
                    } else {
                        $this->formData[$field['name']] = ($field['type'] ?? '') === 'checkbox' ? false : '';
                    }
                }
            }

            if ($this->isFood || $this->isWater) {
                $this->formData['sample_rows'] = [$this->getDefaultSampleRow()];
            }

            // Load existing test request form data if available
            if (!empty($this->selectedFormInstanceIds)) {
                $firstInstance = \App\Models\SubmissionFormInstance::with(['crmCustomer', 'testRequestFormInstance', 'values'])->find($this->selectedFormInstanceIds[0]);
                
                // Load existing form data from TestRequestFormInstance
                if ($firstInstance && $firstInstance->testRequestFormInstance) {
                    $existingFormData = $firstInstance->testRequestFormInstance->form_data ?? [];
                    if (!empty($existingFormData)) {
                        // Merge existing data, preserving structure
                        foreach ($existingFormData as $key => $val) {
                            if (array_key_exists($key, $this->formData)) {
                                $this->formData[$key] = $val;
                            }
                        }
                        // Ensure sample_rows exists and has at least one row
                        if (isset($existingFormData['sample_rows']) && !empty($existingFormData['sample_rows'])) {
                            $this->formData['sample_rows'] = $existingFormData['sample_rows'];
                        }
                    }
                } elseif ($firstInstance && $firstInstance->values) {
                    // Try to load from submission form instance values as fallback
                    foreach ($firstInstance->values as $value) {
                        $fieldName = $value->element?->name ?? $value->element_name ?? null;
                        if ($fieldName && array_key_exists($fieldName, $this->formData)) {
                            $this->formData[$fieldName] = $value->value;
                        }
                    }
                }

                // Auto-prefill client/customer details if not already loaded
                if ($firstInstance && $firstInstance->crmCustomer) {
                    $crmCustomer = $firstInstance->crmCustomer;
                    foreach ($this->formData as $key => $val) {
                        // Only prefill if not already set from existing form data
                        if (empty($val)) {
                            if (in_array($key, ['customer_name', 'client_name', 'customer', 'client'], true)) {
                                $this->formData[$key] = $crmCustomer->name;
                            }
                            if (in_array($key, ['phone', 'telephone', 'phone_number', 'mobile_number', 'telephone_number', 'tel_fax_no'], true)) {
                                $this->formData[$key] = $crmCustomer->telephone1 ?? $crmCustomer->telephone2 ?? '';
                            }
                            if (in_array($key, ['email', 'email_address'], true)) {
                                $this->formData[$key] = $crmCustomer->email ?? '';
                            }
                            if (in_array($key, ['address', 'physical_address', 'postal_address', 'customer_address'], true)) {
                                $this->formData[$key] = $crmCustomer->physical_address ?? $crmCustomer->postal_address ?? '';
                            }
                            if (in_array($key, ['contact_person', 'contact', 'contact_name'], true)) {
                                $contact = method_exists($crmCustomer, 'contacts') ? $crmCustomer->contacts()->first() : null;
                                $this->formData[$key] = $contact ? $contact->name : '';
                            }
                        }
                    }
                }
            }
        }
    }

    public function updated($propertyName, $value): void
    {
        $fieldKey = str_replace('formData.', '', $propertyName);

        // Prefill customer details when customer is selected
        if (in_array($fieldKey, ['customer_name', 'client_name', 'customer', 'client'], true) && $value) {
            $customer = \App\Models\CRM\CRMCustomer::where('name', $value)->first();
            if ($customer) {
                foreach ($this->formData as $key => $val) {
                    if (in_array($key, ['phone', 'telephone', 'phone_number', 'mobile_number', 'telephone_number', 'tel_fax_no'], true)) {
                        $this->formData[$key] = $customer->telephone1 ?? $customer->telephone2 ?? '';
                    }
                    if (in_array($key, ['email', 'email_address'], true)) {
                        $this->formData[$key] = $customer->email ?? '';
                    }
                    if (in_array($key, ['address', 'physical_address', 'postal_address', 'customer_address'], true)) {
                        $this->formData[$key] = $customer->physical_address ?? $customer->postal_address ?? '';
                    }
                    if (in_array($key, ['contact_person', 'contact', 'contact_name'], true)) {
                        $contact = method_exists($customer, 'contacts') ? $customer->contacts()->first() : null;
                        $this->formData[$key] = $contact ? $contact->name : '';
                    }
                }
            }
        }

        // Clear parameter selection when analysis type changes
        if (in_array($fieldKey, ['analysis_type', 'analysis_types'], true)) {
            foreach (['parameter', 'parameters'] as $paramKey) {
                if (array_key_exists($paramKey, $this->formData)) {
                    $this->formData[$paramKey] = '';
                }
            }
        }
    }

    public function getCustomersProperty()
    {
        return \App\Models\CRM\CRMCustomer::where('active', 1)->orderBy('name')->get();
    }

    public function getAnalysisTypesProperty()
    {
        if (!$this->selectedSampleTypeId) {
            return collect();
        }
        return \App\AnalysisType::where('sample_type_id', $this->selectedSampleTypeId)->orderBy('name')->get();
    }

    public function getParametersProperty()
    {
        if (!$this->selectedSampleTypeId) {
            return collect();
        }
        $atName = null;
        foreach (['analysis_type', 'analysis_types'] as $key) {
            if (!empty($this->formData[$key])) {
                $atName = $this->formData[$key];
                break;
            }
        }
        if (!$atName) {
            return collect();
        }
        $at = \App\AnalysisType::where('sample_type_id', $this->selectedSampleTypeId)
            ->where('name', $atName)
            ->first();
        if (!$at) {
            return collect();
        }
        return \App\Analyte::whereHas('analysis_elements', function ($q) use ($at) {
            $q->where('analysis_type_id', $at->id)->where('active', 1);
        })->orderBy('name')->get();
    }

    /**
     * Receives data from the parent WorkflowBoard and shows the Bootstrap modal
     * once this component's state is fully updated in the same response cycle.
     *
     * @param  array<int, string>  $instanceIds
     * @param  array<int, array{id: string, label: string, customer: string}>  $summaries
     */
    public function handleReceiveModalOpen(array $instanceIds, array $summaries): void
    {
        $this->selectedFormInstanceIds = array_values(array_filter($instanceIds));
        $this->selectedFormSummaries = $summaries;
        $this->remarks = '';
        $this->selectedSampleTypeId = null;
        $this->formData = [];
        $this->resetValidation();
        $this->syncLoadErrorFromApproval();
        $this->responses = [];
        $this->initializeResponses();
        $this->refreshCheckInContexts();

        // Dispatched after state is set — JS listener shows the modal.
        $this->dispatch('show-receive-sample-modal');
    }

    protected function getListeners(): array
    {
        return [
            'receive-modal-open' => 'handleReceiveModalOpen',
        ];
    }

    public function openRejectWizard(string $instanceId): void
    {
        $this->dispatch('open-rejection-wizard', submissionFormInstanceId: $instanceId);
        $this->dispatch('hide-receive-sample-modal');
    }

    public function confirmReceive(): void
    {
        if ($this->isPhysicalCheckIn) {
            $this->confirmPhysicalCheckIn();

            return;
        }

        $this->confirmWalkInCapture();
    }

    private function confirmPhysicalCheckIn(): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            $this->addError('selection', 'You must be signed in to receive samples.');

            return;
        }

        $checkInService = app(SampleReceivingCheckInService::class);
        $processed = 0;
        $skipped = 0;
        $blockedReasons = [];

        DB::transaction(function () use ($user, $checkInService, &$processed, &$skipped, &$blockedReasons): void {
            $instances = SubmissionFormInstance::query()
                ->with(['batches', 'submissionForm', 'sampleSubmissionRequest'])
                ->whereIn('id', $this->selectedFormInstanceIds)
                ->get();

            foreach ($instances as $instance) {
                if (! $checkInService->canReceiveInstance($instance)) {
                    $skipped++;
                    $reason = $checkInService->receiveBlockReason($instance);
                    if ($reason !== null) {
                        $blockedReasons[] = $reason;
                    }

                    continue;
                }

                $instance->markAsReceived($user, $this->remarks !== '' ? $this->remarks : null);
                $processed++;
            }
        });

        if ($processed === 0) {
            $message = 'No eligible requests were checked in.';
            if ($blockedReasons !== []) {
                $message .= ' '.collect($blockedReasons)->unique()->implode(' ');
            }

            $this->addError('selection', $message);

            return;
        }

        $message = $processed === 1
            ? '1 request checked in at reception.'
            : "{$processed} requests checked in at reception.";

        if ($skipped > 0) {
            $message .= " ({$skipped} skipped.)";
        }

        session()->flash('success', $message);
        $this->dispatch('receive-completed');
        $this->dispatch('hide-receive-sample-modal');
    }

    private function confirmWalkInCapture(): void
    {
        $this->validate([
            'selectedSampleTypeId' => 'required|exists:sample_types,id',
        ], [
            'selectedSampleTypeId.required' => 'Please select a Sample Type.',
        ]);

        $form = TestRequestForm::where('sample_type_id', $this->selectedSampleTypeId)->where('is_active', true)->first();
        if (! $form) {
            $this->addError('selectedSampleTypeId', 'No active form template found for the selected sample type.');

            return;
        }

        // Build validation rules
        $rules = [];
        $messages = [];

        // Dynamic fields validation (walk-in TRF capture only)
        $fields = $form->getFlatFields();
        foreach ($fields as $field) {
            if (empty($field['name'])) {
                continue;
            }
            $key = 'formData.'.$field['name'];
            $fieldRules = [];
            if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            if (($field['type'] ?? '') === 'number') {
                $fieldRules[] = 'numeric';
            } elseif (($field['type'] ?? '') === 'date') {
                $fieldRules[] = 'date';
            }

            $rules[$key] = $fieldRules;
            $messages[$key.'.required'] = ($field['label'] ?? $field['name']).' is required.';
        }

        if ($rules !== []) {
            $this->validate($rules, $messages);
        }

        $this->prepareLabUseFields();

        $user = Auth::user();
        if (! $user instanceof User) {
            $this->addError('selection', 'You must be signed in to receive samples.');

            return;
        }

        $processed = 0;
        $generatedTrfiIds = [];

        // Standalone walk-in capture (no pre-selected requests)
        DB::transaction(function () use ($user, $form, &$processed, &$generatedTrfiIds): void {
                $submissionForm = app(PortalSubmissionFormAccess::class)
                    ->testRequestFormForSampleType((string) $this->selectedSampleTypeId);

                if (!$submissionForm) {
                    throw new \Exception('No active Test Request Form template found for this sample type. Please seed TRF templates first.');
                }

                // Try to resolve customer if selected
                $crmCustomerId = null;
                $custName = null;
                foreach (['customer_name', 'client_name', 'customer', 'client'] as $key) {
                    if (!empty($this->formData[$key])) {
                        $custName = $this->formData[$key];
                        break;
                    }
                }
                if ($custName) {
                    $crmCustomerId = \App\Models\CRM\CRMCustomer::where('name', $custName)->first()?->id;
                }

                $instance = SubmissionFormInstance::create([
                    'submission_form_id' => $submissionForm->id,
                    'form_number' => null,
                    'sequence_number' => null,
                    'title' => 'Test Request Form - ' . now()->format('Y-m-d H:i'),
                    'submitted_by' => $user->id,
                    'status' => 'draft',
                    'priority' => 'normal',
                    'crm_customer_id' => $crmCustomerId,
                ]);

                // Save the form values to the database using unified mapper
                $requestData = \App\Models\TestRequestFormInstance::mapToSubmissionFormRequestData($this->formData, $form);
                $submissionService = app(\App\Services\SubmissionForm\SubmissionFormSubmissionService::class);
                $elements = $submissionService->elementsForForm($submissionForm);
                $req = new \Illuminate\Http\Request();
                $req->merge($requestData);
                $submissionService->processFormData($instance, $req, $elements);

                $instance->logAction('created', $user);
                $instance->submit($user);
                $instance->refresh();

                $trfi = TestRequestFormInstance::updateOrCreate(
                    ['submission_form_instance_id' => $instance->id],
                    [
                        'test_request_form_id' => $form->id,
                        'form_data' => $this->formData,
                        'status' => 'submitted',
                        'created_by' => $user->id,
                    ]
                );
                $generatedTrfiIds[] = $trfi->id;

                app(CommercialEnquiryFromFormService::class)->syncFromSubmittedInstance(
                    $instance->fresh(['values.element', 'submissionForm', 'crmCustomer', 'testRequestFormInstance'])
                );
            });

        $message = 'Walk-in test request submitted successfully.';
        $processed = 1;

        $pdfService = app(\App\Services\Sampleworkflow\TestRequestFormPdfService::class);
        foreach (array_unique($generatedTrfiIds) as $trfiId) {
            try {
                $trfi = TestRequestFormInstance::query()->find($trfiId);
                if ($trfi) {
                    $pdfService->generateAndStore($trfi);
                }
            } catch (\Throwable) {
                // PDF failure should not block receiving.
            }
        }

        $this->lastGeneratedTrfiIds = array_values(array_unique($generatedTrfiIds));

        session()->flash('success', $message);
        $this->dispatch('receive-completed', trfiIds: $this->lastGeneratedTrfiIds);
        $this->dispatch('hide-receive-sample-modal');
    }

    public function getIsPhysicalCheckInProperty(): bool
    {
        return $this->selectedFormInstanceIds !== [];
    }

    public function previewDraft(): void
    {
        if (!$this->selectedSampleTypeId) {
            $this->addError('selectedSampleTypeId', 'Select a sample type to preview the test request form.');
            return;
        }

        $submission = null;
        if (!empty($this->selectedFormInstanceIds)) {
            $submission = SubmissionFormInstance::with('crmCustomer')->find($this->selectedFormInstanceIds[0]);
        }

        session([
            'test_request_form_preview_draft' => [
                'form_data' => $this->formData,
                'sample_type_id' => $this->selectedSampleTypeId,
                'submission_form_instance_id' => $submission?->id,
            ],
        ]);

        $this->dispatch('open-test-request-preview', url: route('test-request-form.preview-draft'));
    }

    private function prepareLabUseFields(): void
    {
        $user = Auth::user();

        if (empty($this->formData['lab_received_datetime'])) {
            $this->formData['lab_received_datetime'] = now()->format('Y-m-d\TH:i');
        }

        if (empty($this->formData['lab_received_by']) && $user instanceof User) {
            $this->formData['lab_received_by'] = $user->name;
        }
    }

    public function render()
    {
        $formTemplate = null;
        if ($this->selectedSampleTypeId) {
            $formTemplate = TestRequestForm::where('sample_type_id', $this->selectedSampleTypeId)->where('is_active', true)->first();
        }

        return view('livewire.sampleworkflow.receive-sample-request', [
            'approval' => $this->resolveApproval(),
            'formTemplate' => $formTemplate,
        ]);
    }

    private function refreshCheckInContexts(): void
    {
        if ($this->selectedFormInstanceIds === []) {
            $this->checkInContexts = [];

            return;
        }

        $this->checkInContexts = app(SampleReceivingCheckInService::class)
            ->buildCheckInContexts($this->selectedFormInstanceIds);
    }

    private function resolveApproval(): ?Approval
    {
        $approval = $this->workflowService()->getApprovalByCode(self::STAGE_NAME, self::APPROVAL_CODE);
        $this->syncLoadErrorFromApproval($approval);

        return $approval;
    }

    private function syncLoadErrorFromApproval(?Approval $approval = null): void
    {
        $approval ??= $this->workflowService()->getApprovalByCode(self::STAGE_NAME, self::APPROVAL_CODE);

        $this->loadError = $approval === null
            ? 'Receiving checklist is not configured. Add approval code "sro_receiving_sample" for Samples Receiving.'
            : null;
    }

    private function initializeResponses(): void
    {
        $approval = $this->workflowService()->getApprovalByCode(self::STAGE_NAME, self::APPROVAL_CODE);
        if ($approval === null) {
            return;
        }

        foreach ($approval->checklistItems as $item) {
            if (!array_key_exists($item->id, $this->responses) && $item->type === 'checkbox') {
                $this->responses[$item->id] = false;
            }
        }
    }

    private function canReceiveInstance(SubmissionFormInstance $instance): bool
    {
        if ($instance->status !== 'submitted') {
            return false;
        }

        if ($instance->batches->isNotEmpty()) {
            return false;
        }

        return $instance->submissionForm !== null
            && ($instance->submissionForm->form_type ?? '') === 'template';
    }

    private function rulesForApproval(Approval $approval): array
    {
        $rules = [];

        foreach ($approval->checklistItems as $item) {
            $key = 'responses.' . $item->id;

            if ($item->type === 'checkbox') {
                $rules[$key] = $item->is_required ? ['accepted'] : ['nullable', 'boolean'];
                continue;
            }

            if ($item->type === 'select') {
                $selectRules = [$item->is_required ? 'required' : 'nullable'];
                $selectRules[] = Rule::in($item->options ?? []);
                $rules[$key] = $selectRules;
                continue;
            }

            $rules[$key] = $item->is_required
                ? ['required', 'string']
                : ['nullable', 'string'];
        }

        return $rules;
    }

    private function messagesForApproval(Approval $approval): array
    {
        $messages = [];

        foreach ($approval->checklistItems as $item) {
            $key = 'responses.' . $item->id;
            $messages[$key . '.required'] = $item->label . ' is required.';
            $messages[$key . '.accepted'] = $item->label . ' must be checked.';
            $messages[$key . '.in'] = 'Select a valid option for ' . $item->label . '.';
        }

        return $messages;
    }

    private function approvalResponses(Approval $approval): array
    {
        $payload = [];

        foreach ($approval->checklistItems as $item) {
            $payload[$item->id] = $this->responses[$item->id] ?? null;
        }

        return $payload;
    }

    private function workflowService(): WorkflowService
    {
        return app(WorkflowService::class);
    }
}

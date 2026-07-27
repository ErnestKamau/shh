<?php

namespace App\Livewire\Sampleworkflow;

use App\Lab;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\SubcontractingAssignmentService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;

class DispatchSubcontractRequest extends Component
{
    /** @var array<int, string> */
    public array $selectedFormInstanceIds = [];

    /** @var array<int, array{id: string, label: string, customer: string}> */
    public array $selectedFormSummaries = [];

    public string $barcode = '';

    public ?string $labelUrl = null;

    public ?string $selectedEnquiryId = null;

    public ?string $selectedFormInstanceId = null;

    /** @var array<int, array{id: string, label: string}> */
    public array $availableLabs = [];

    /** @var array<int, string> */
    public array $selectedLabIds = [];

    /** @var array<int, array{id: string, label: string, analysis_type: string}> */
    public array $subcontractedTests = [];

    /**
     * lab_id => list of analysis_element_ids assigned to that lab.
     *
     * @var array<string, array<int, string>>
     */
    public array $labTestIds = [];

    public function mount(): void
    {
        $this->availableLabs = Lab::query()
            ->where('active', 1)
            ->where('is_external', 1)
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(fn (Lab $lab): array => [
                'id' => (string) $lab->id,
                'label' => trim(((string) ($lab->code ?? '')).' - '.((string) ($lab->name ?? ''))),
            ])
            ->values()
            ->all();
    }

    public function handleSubcontractDispatchModalOpen(array $instanceIds, array $summaries): void
    {
        $this->selectedFormInstanceIds = array_values(array_filter($instanceIds));
        $this->selectedFormSummaries = $summaries;
        $this->barcode = '';
        $this->labelUrl = null;
        $this->selectedEnquiryId = null;
        $this->selectedFormInstanceId = null;
        $this->selectedLabIds = [];
        $this->subcontractedTests = [];
        $this->labTestIds = [];
        $this->resetValidation();

        if (count($this->selectedFormInstanceIds) === 1) {
            $instance = SubmissionFormInstance::query()
                ->with('sampleSubmissionRequest')
                ->find($this->selectedFormInstanceIds[0]);

            if ($instance !== null) {
                $this->selectedFormInstanceId = (string) $instance->id;
                $this->selectedEnquiryId = (string) ($instance->sampleSubmissionRequest?->id ?? '');
                $this->labelUrl = route('submission-forms.instances.sample-collection-label', ['instance' => $instance->id]);

                if ($instance->sampleSubmissionRequest !== null) {
                    $enquiry = $instance->sampleSubmissionRequest;
                    $assignmentService = app(SubcontractingAssignmentService::class);
                    $this->subcontractedTests = $assignmentService->resolveSubcontractedTests($enquiry);
                    $existing = $assignmentService->labByElementIdForRequest($enquiry);

                    $existingLabIds = collect($existing)
                        ->values()
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();

                    $storedLabIds = collect(explode(',', (string) ($enquiry->subcontracting_dispatch_lab_ids ?? '')))
                        ->map(fn ($id) => trim((string) $id))
                        ->filter()
                        ->values()
                        ->all();

                    $this->selectedLabIds = array_values(array_unique(array_merge($existingLabIds, $storedLabIds)));
                    $this->syncLabTestBucketsFromExisting($existing);
                }
            }
        }

        $this->dispatch('show-subcontract-dispatch-modal');
    }

    public function updatedSelectedLabIds(): void
    {
        $selected = $this->normalizedSelectedLabIds();
        $previousBuckets = $this->labTestIds;
        $this->labTestIds = [];

        foreach ($selected as $labId) {
            $this->labTestIds[$labId] = array_values(array_map(
                'strval',
                $previousBuckets[$labId] ?? []
            ));
        }

        // One receiving lab: default all subcontracted tests to that lab.
        if (count($selected) === 1 && $this->subcontractedTests !== []) {
            $onlyLabId = $selected[0];
            $current = $this->labTestIds[$onlyLabId] ?? [];
            if ($current === []) {
                $this->labTestIds[$onlyLabId] = collect($this->subcontractedTests)
                    ->pluck('id')
                    ->map(fn ($id) => (string) $id)
                    ->values()
                    ->all();
            }
        }
    }

    public function updated($propertyName, $value = null): void
    {
        if ($propertyName === 'selectedLabIds') {
            return;
        }

        if (! is_string($propertyName) || ! str_starts_with($propertyName, 'labTestIds.')) {
            return;
        }

        $changedLabId = substr($propertyName, strlen('labTestIds.'));
        if ($changedLabId === '' || str_contains($changedLabId, '.')) {
            // Ignore deeper nested updates; exclusivity runs on the lab bucket itself.
            $changedLabId = explode('.', $changedLabId)[0] ?? '';
        }

        if ($changedLabId === '') {
            return;
        }

        $assignedOnChangedLab = collect($this->labTestIds[$changedLabId] ?? [])
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values()
            ->all();

        foreach ($this->labTestIds as $labId => $elementIds) {
            if ((string) $labId === (string) $changedLabId) {
                $this->labTestIds[$labId] = $assignedOnChangedLab;

                continue;
            }

            $this->labTestIds[$labId] = collect($elementIds)
                ->map(fn ($id) => (string) $id)
                ->reject(fn (string $elementId) => in_array($elementId, $assignedOnChangedLab, true))
                ->values()
                ->all();
        }
    }

    /**
     * @return array<int, array{id: string, label: string}>
     */
    public function getSelectedLabOptionsProperty(): array
    {
        $selected = $this->normalizedSelectedLabIds();

        return collect($this->availableLabs)
            ->filter(fn (array $lab): bool => in_array((string) $lab['id'], $selected, true))
            ->values()
            ->all();
    }

    protected function getListeners(): array
    {
        return [
            'subcontract-dispatch-modal-open' => 'handleSubcontractDispatchModalOpen',
        ];
    }

    public function openLabelInNewTab(): void
    {
        if ($this->labelUrl === null || $this->labelUrl === '') {
            $this->addError('barcode', 'A label could not be prepared for this request.');

            return;
        }

        $this->dispatch('open-subcontract-label-tab', url: $this->labelUrl);
    }

    public function confirmDispatch(): void
    {
        if (count($this->selectedFormInstanceIds) !== 1 || empty($this->selectedFormInstanceId) || empty($this->selectedEnquiryId)) {
            $this->addError('selection', 'Select exactly one subcontracting request before dispatching.');

            return;
        }

        $this->validate([
            'barcode' => ['required', 'string', 'max:255'],
            'selectedLabIds' => ['required', 'array', 'min:1'],
            'selectedLabIds.*' => [
                'string',
                Rule::exists('labs', 'id')->where(fn ($query) => $query->where('active', 1)->where('is_external', 1)),
            ],
            'labTestIds' => ['nullable', 'array'],
        ], [
            'barcode.required' => 'Scan or enter the request barcode before dispatching.',
            'selectedLabIds.required' => 'Select at least one subcontracted lab to receive the sample(s).',
            'selectedLabIds.min' => 'Select at least one subcontracted lab to receive the sample(s).',
            'selectedLabIds.*.exists' => 'One or more selected labs are invalid or inactive.',
        ]);

        $selectedLabIds = collect($this->normalizedSelectedLabIds());
        $assignments = $this->assignmentsFromLabTestIds($selectedLabIds->all());
        $requiredTestIds = collect($this->subcontractedTests)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values();

        if ($requiredTestIds->isNotEmpty()) {
            $assignedTestIds = collect($assignments)->keys()->map(fn ($id) => (string) $id)->values();
            $missing = $requiredTestIds->diff($assignedTestIds)->values();

            if ($missing->isNotEmpty()) {
                $this->addError(
                    'labTestIds',
                    'Assign every subcontracted test to one of the selected labs before dispatching.'
                );

                return;
            }
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            $this->addError('selection', 'You must be signed in to dispatch a subcontracting request.');

            return;
        }

        $instance = SubmissionFormInstance::query()
            ->with('sampleSubmissionRequest')
            ->find($this->selectedFormInstanceId);

        if ($instance === null || $instance->sampleSubmissionRequest === null) {
            $this->addError('selection', 'The selected subcontracting request could not be found.');

            return;
        }

        $expectedCodes = array_values(array_unique(array_filter([
            trim((string) ($instance->id ?? '')),
            trim((string) ($instance->form_number ?? '')),
            trim((string) ($instance->sampleSubmissionRequest->unique_identification ?? '')),
            trim((string) ($instance->sampleSubmissionRequest->id ?? '')),
        ])));

        $inputBarcode = trim($this->barcode);
        if (! in_array($inputBarcode, $expectedCodes, true)) {
            $this->addError('barcode', 'The scanned barcode does not match the selected request label. Generate/print the request label and scan it again.');

            return;
        }

        $assignmentService = app(SubcontractingAssignmentService::class);

        DB::transaction(function () use ($instance, $user, $assignmentService, $selectedLabIds, $assignments): void {
            $enquiry = $instance->sampleSubmissionRequest;

            $selectedLabNames = Lab::query()
                ->whereIn('id', $selectedLabIds->all())
                ->where('active', 1)
                ->where('is_external', 1)
                ->orderBy('name')
                ->get(['code', 'name'])
                ->map(function (Lab $lab): string {
                    $code = trim((string) ($lab->code ?? ''));
                    $name = trim((string) ($lab->name ?? ''));

                    return $code !== '' ? ($code.' - '.$name) : $name;
                })
                ->filter()
                ->values();

            $assignmentService->persistAssignments($enquiry, $assignments);

            if (Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_status')) {
                $enquiry->subcontracting_dispatch_status = SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED;
            }

            if (Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_date')) {
                $enquiry->subcontracting_dispatch_date = now();
            }

            if (Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_lab_ids')) {
                $enquiry->subcontracting_dispatch_lab_ids = $selectedLabIds->implode(',');
            }

            if (Schema::hasColumn('sample_submission_requests', 'subcontracting_dispatch_lab_names')) {
                $enquiry->subcontracting_dispatch_lab_names = $selectedLabNames->implode(', ');
            }

            $enquiry->save();

            if ($instance->status !== 'approved') {
                $instance->update([
                    'status' => 'approved',
                    'reviewed_at' => $instance->reviewed_at ?? now(),
                    'reviewed_by' => $user->id,
                    'review_notes' => 'Subcontracting dispatch confirmed from Samples Receiving queue.',
                ]);

                $instance->logAction('subcontract_dispatched', $user, [
                    'status' => ['from' => $instance->getOriginal('status') ?: $instance->status, 'to' => 'approved'],
                    'labs' => $selectedLabIds->all(),
                    'assignments' => $assignments,
                ], 'Subcontracting dispatch confirmed from Samples Receiving queue.');
            }

            $hasExistingJob = ! empty($enquiry->sample_header_id)
                || ! empty($instance->analysisAcceptanceForms()->value('sample_header_id'));

            if (! $hasExistingJob) {
                $prefill = app(AcceptanceFormPricingService::class)->buildPrefillFromSelection(
                    (string) $enquiry->id,
                    (string) $instance->id,
                );

                $lines = $prefill['lines'] ?? [];
                if (is_array($lines) && $lines !== []) {
                    app(AcceptanceFormService::class)->acceptWithStaffSignature(
                        (string) $instance->id,
                        (string) $enquiry->id,
                        [
                            'crm_customer_id' => $prefill['customer_id'] ?? $enquiry->crm_customer_id,
                            'customer_name' => $prefill['customer_name'] ?? ($instance->crmCustomer?->name ?? ''),
                            'request_date' => $prefill['request_date'] ?? now()->format('Y-m-d'),
                            'number_of_samples' => (int) ($prefill['number_of_samples'] ?? max(1, (int) ($enquiry->number_of_samples ?? 1))),
                            'mode_of_work' => $prefill['mode_of_work'] ?? 'Normal',
                            'date_of_sampling' => $prefill['date_of_sampling'] ?? null,
                        ],
                        $lines,
                        (string) ($user->name ?? 'System Dispatch'),
                        'subcontract-dispatch-staff-signature',
                        now()->toDateString(),
                        (string) $user->id,
                    );
                }
            }

            $enquiry->refresh();
            $sampleHeaderId = trim((string) ($enquiry->sample_header_id ?? ''));
            if ($sampleHeaderId === '') {
                $sampleHeaderId = trim((string) ($instance->analysisAcceptanceForms()->value('sample_header_id') ?? ''));
            }

            if ($sampleHeaderId !== '') {
                $assignmentService->syncAssignmentsToSampleHeader($enquiry, $sampleHeaderId);
            }
        });

        session()->flash('success', 'Subcontracting request dispatched successfully.');
        $this->dispatch('subcontract-dispatch-completed');
    }

    /**
     * @return list<string>
     */
    private function normalizedSelectedLabIds(): array
    {
        return collect($this->selectedLabIds)
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $existing element_id => lab_id
     */
    private function syncLabTestBucketsFromExisting(array $existing): void
    {
        $this->labTestIds = [];

        foreach ($this->normalizedSelectedLabIds() as $labId) {
            $this->labTestIds[$labId] = [];
        }

        foreach ($existing as $elementId => $labId) {
            $elementId = trim((string) $elementId);
            $labId = trim((string) $labId);
            if ($elementId === '' || $labId === '') {
                continue;
            }

            if (! in_array($labId, $this->normalizedSelectedLabIds(), true)) {
                continue;
            }

            $this->labTestIds[$labId] ??= [];
            $this->labTestIds[$labId][] = $elementId;
        }

        foreach ($this->labTestIds as $labId => $elementIds) {
            $this->labTestIds[$labId] = array_values(array_unique(array_map('strval', $elementIds)));
        }
    }

    /**
     * @param  list<string>  $selectedLabIds
     * @return array<string, string> analysis_element_id => lab_id
     */
    private function assignmentsFromLabTestIds(array $selectedLabIds): array
    {
        $allowedElementIds = collect($this->subcontractedTests)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $assignments = [];

        foreach ($this->labTestIds as $labId => $elementIds) {
            $labId = trim((string) $labId);
            if ($labId === '' || ! in_array($labId, $selectedLabIds, true)) {
                continue;
            }

            foreach ((array) $elementIds as $elementId) {
                $elementId = trim((string) $elementId);
                if ($elementId === '' || ! in_array($elementId, $allowedElementIds, true)) {
                    continue;
                }

                $assignments[$elementId] = $labId;
            }
        }

        return $assignments;
    }

    public function render()
    {
        return view('livewire.sampleworkflow.dispatch-subcontract-request');
    }
}

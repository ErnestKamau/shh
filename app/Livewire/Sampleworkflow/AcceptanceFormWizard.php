<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class AcceptanceFormWizard extends Component
{
    public bool $showModal = false;

    public int $currentStep = 1;

    public ?string $submissionFormInstanceId = null;

    public ?string $submissionRequestId = null;

    public ?string $acceptanceFormId = null;

    public string $status = '';

    public string $customerName = '';

    public ?string $requestDate = null;

    public int $numberOfSamples = 1;

    public string $modeOfWork = 'Normal';

    public ?string $dateOfSampling = null;

    /** @var list<array<string, mixed>> */
    public array $lines = [];

    public ?string $crmCustomerId = null;

    public string $managerSignerName = '';

    public string $managerSignature = '';

    public ?string $managerSignedAt = null;

    public bool $showAddLineModal = false;

    public ?string $addLineSampleTypeId = null;

    public ?string $addLineAnalysisTypeId = null;

    public ?string $addLineParameterKey = null;

    /** @var list<array{id: string, name: string}> */
    public array $addLineSampleTypes = [];

    /** @var list<array{id: string, name: string}> */
    public array $addLineAnalysisTypes = [];

    /** @var list<array<string, mixed>> */
    public array $addLineParameters = [];

    public bool $addLineAllParametersSelected = false;

    public bool $addLineCanAddWholeAnalysisType = false;

    public function mount(): void
    {
        $this->managerSignerName = (string) (Auth::user()->name ?? '');
        $this->requestDate = now()->format('Y-m-d');
    }

    #[On('open-acceptance-wizard')]
    public function openWizard(?string $submissionFormInstanceId = null, ?string $submissionRequestId = null): void
    {
        $this->resetWizard();
        $this->submissionFormInstanceId = $submissionFormInstanceId ?: null;
        $this->submissionRequestId = $submissionRequestId ?: null;

        if (!$this->submissionFormInstanceId && !$this->submissionRequestId) {
            $this->dispatch('notify', type: 'error', message: 'Select exactly one request or form row before accepting.');

            return;
        }

        $prefill = app(AcceptanceFormPricingService::class)->buildPrefillFromSelection(
            $this->submissionRequestId,
            $this->submissionFormInstanceId
        );

        $this->crmCustomerId = $prefill['customer_id'];
        $this->customerName = $prefill['customer_name'];
        $this->requestDate = $prefill['request_date'] ?? now()->format('Y-m-d');
        $this->numberOfSamples = (int) ($prefill['number_of_samples'] ?? 1);
        $this->modeOfWork = (string) ($prefill['mode_of_work'] ?? 'Normal');
        $this->dateOfSampling = $prefill['date_of_sampling'];
        $pricingService = app(AcceptanceFormPricingService::class);

        $this->lines = collect($pricingService->deduplicateRedundantAnalysisTypeLines($prefill['lines']))
            ->map(function (array $line, int $index) {
                return [
                    'line_no' => $index + 1,
                    'sample_type_id' => $line['sample_type_id'] ?? null,
                    'sample_type_name' => $line['sample_type_id'] ? optional(\App\SampleType::find($line['sample_type_id']))->name : '',
                    'analysis_type_id' => $line['analysis_type_id'] ?? null,
                    'analysis_type_name' => $line['analysis_type_id'] ? optional(\App\AnalysisType::find($line['analysis_type_id']))->name : '',
                    'analysis_element_id' => $line['analysis_element_id'] ?? null,
                    'parameter_label' => $line['parameter_label'] ?? '',
                    'unit_amount' => (float) ($line['unit_amount'] ?? 0),
                    'number_of_samples' => (int) ($line['number_of_samples'] ?? 1),
                    'is_approved' => (bool) ($line['is_approved'] ?? true),
                    'sort_order' => $index,
                ];
            })->values()->all();

        $this->showModal = true;
        $this->currentStep = 1;
    }

    public function closeWizard(): void
    {
        $this->showModal = false;
        $this->resetWizard();
    }

    public function goToStep(int $step): void
    {
        if ($step === 2 && $this->acceptanceFormId === null) {
            return;
        }

        if ($step === 3 && $this->status !== AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN
            && $this->status !== AnalysisAcceptanceForm::STATUS_COMPLETED) {
            return;
        }

        $this->currentStep = max(1, min(3, $step));
    }

    public function submitStep1(): void
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'requestDate' => 'required|date',
            'numberOfSamples' => 'required|integer|min:1',
            'modeOfWork' => 'required|in:Normal,Express',
            'dateOfSampling' => 'nullable|date',
            'lines' => 'required|array|min:1',
        ]);

        $form = app(AcceptanceFormService::class)->createFromStep1(
            $this->submissionFormInstanceId,
            $this->submissionRequestId,
            [
                'crm_customer_id' => $this->crmCustomerId,
                'customer_name' => $this->customerName,
                'request_date' => $this->requestDate,
                'number_of_samples' => $this->numberOfSamples,
                'mode_of_work' => $this->modeOfWork,
                'date_of_sampling' => $this->dateOfSampling,
            ],
            $this->lines,
            (string) Auth::id()
        );

        $this->acceptanceFormId = $form->id;
        $this->status = $form->status;
        $this->currentStep = 2;

        $this->dispatch('acceptance-form-created');
        session()->flash('success', 'Acceptance form sent to the customer for signing.');
    }

    public function submitManagerSign(): void
    {
        $this->validate([
            'managerSignerName' => 'required|string|max:255',
            'managerSignature' => 'required|string',
        ]);

        $form = AnalysisAcceptanceForm::query()->findOrFail($this->acceptanceFormId);

        $completedForm = app(AcceptanceFormService::class)->recordManagerSignature(
            $form,
            $this->managerSignerName,
            $this->managerSignature,
            $this->managerSignedAt
        );

        $this->status = AnalysisAcceptanceForm::STATUS_COMPLETED;
        $this->closeWizard();

        $batchId = (string) ($completedForm->sample_header_id ?? '');
        $redirectUrl = $batchId !== ''
            ? route('view-batch-details', [
                'batch' => $batchId,
                'client' => 0,
                'portal' => 0,
                'status' => 'Samples In Lab',
            ]) . '#sample-receipt-notification'
            : route('sample-workflow', ['status' => 'Samples In Lab']);

        $this->dispatch('acceptance-form-completed', redirectUrl: $redirectUrl);
        session()->flash('success', 'Acceptance form completed and batch moved to Samples In Lab.');
    }

    public function removeLine(int $index): void
    {
        if (!isset($this->lines[$index])) {
            return;
        }

        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
        $this->reindexLines();

        if ($this->showAddLineModal && $this->addLineAnalysisTypeId) {
            $this->refreshAddLineParameterOptions();
        }
    }

    public function openAddLineModal(): void
    {
        if (!$this->crmCustomerId) {
            return;
        }

        $pricing = app(AcceptanceFormPricingService::class);
        $this->addLineSampleTypes = $pricing->pricelistSampleTypesForCustomer($this->crmCustomerId);
        $this->addLineAnalysisTypes = [];
        $this->addLineParameters = [];
        $this->addLineSampleTypeId = null;
        $this->addLineAnalysisTypeId = null;
        $this->addLineParameterKey = null;
        $this->addLineAllParametersSelected = false;
        $this->addLineCanAddWholeAnalysisType = false;
        $this->showAddLineModal = true;
    }

    public function updatedAddLineSampleTypeId(): void
    {
        if (!$this->crmCustomerId || !$this->addLineSampleTypeId) {
            $this->addLineAnalysisTypes = [];
            $this->addLineParameters = [];

            return;
        }

        $this->addLineAnalysisTypes = app(AcceptanceFormPricingService::class)
            ->pricelistAnalysisTypesForCustomer($this->crmCustomerId, $this->addLineSampleTypeId);
        $this->addLineAnalysisTypeId = null;
        $this->addLineParameters = [];
        $this->addLineAllParametersSelected = false;
        $this->addLineCanAddWholeAnalysisType = false;
    }

    public function updatedAddLineAnalysisTypeId(): void
    {
        $this->refreshAddLineParameterOptions();
    }

    public function confirmAddLine(): void
    {
        if ($this->addLineAllParametersSelected) {
            $this->dispatch(
                'notify',
                type: 'warning',
                message: 'All parameters for this sample type and analysis type are already in the list below.'
            );

            return;
        }

        $isWholeAnalysisType = $this->addLineParameterKey === null || $this->addLineParameterKey === '';

        if ($isWholeAnalysisType && !$this->addLineCanAddWholeAnalysisType) {
            $this->dispatch(
                'notify',
                type: 'warning',
                message: 'This analysis type is already represented in the list below.'
            );

            return;
        }

        $parameter = collect($this->addLineParameters)->firstWhere('id', $this->addLineParameterKey);
        if (!$parameter && $this->addLineAnalysisTypeId && $isWholeAnalysisType) {
            $analysis = \App\AnalysisType::query()->find($this->addLineAnalysisTypeId);
            $parameter = [
                'sample_type_id' => $this->addLineSampleTypeId,
                'sample_type_name' => optional(\App\SampleType::find($this->addLineSampleTypeId))->name,
                'analysis_type_id' => $this->addLineAnalysisTypeId,
                'analysis_type_name' => $analysis?->name,
                'analysis_element_id' => null,
                'label' => $analysis?->name ?? 'Analysis',
                'unit_amount' => app(AcceptanceFormPricingService::class)->resolveLinePrice(
                    app(AcceptanceFormPricingService::class)->resolvePricelist($this->crmCustomerId),
                    $this->addLineSampleTypeId,
                    $this->addLineAnalysisTypeId
                ),
            ];
        }

        if (!$parameter) {
            return;
        }

        if ($this->isParameterAlreadyInTable(
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId,
            $isWholeAnalysisType ? null : (string) $this->addLineParameterKey,
            $parameter['analysis_element_id'] ?? null
        )) {
            $this->dispatch(
                'notify',
                type: 'warning',
                message: 'That parameter is already in the list below.'
            );
            $this->refreshAddLineParameterOptions();

            return;
        }

        $this->lines[] = [
            'line_no' => count($this->lines) + 1,
            'sample_type_id' => $parameter['sample_type_id'] ?? $this->addLineSampleTypeId,
            'sample_type_name' => $parameter['sample_type_name'] ?? '',
            'analysis_type_id' => $parameter['analysis_type_id'] ?? $this->addLineAnalysisTypeId,
            'analysis_type_name' => $parameter['analysis_type_name'] ?? '',
            'analysis_element_id' => $parameter['analysis_element_id'] ?? null,
            'parameter_label' => $parameter['label'] ?? '',
            'unit_amount' => (float) ($parameter['unit_amount'] ?? 0),
            'number_of_samples' => 1,
            'is_approved' => true,
            'sort_order' => count($this->lines),
        ];

        $this->lines = app(AcceptanceFormPricingService::class)
            ->deduplicateRedundantAnalysisTypeLines($this->lines);
        $this->reindexLines();
        $this->refreshAddLineParameterOptions();

        if ($this->addLineAllParametersSelected) {
            $this->showAddLineModal = false;
        }
    }

    public function getTotalAmountProperty(): float
    {
        return collect($this->lines)
            ->filter(fn (array $line) => !empty($line['is_approved']))
            ->sum(fn (array $line) => (float) ($line['unit_amount'] ?? 0) * (int) ($line['number_of_samples'] ?? 1));
    }

    /**
     * Lines grouped for display: sample type → analysis type → parameters.
     *
     * @return list<array{
     *     sample_type_id: ?string,
     *     sample_type_name: string,
     *     analysis_groups: list<array{
     *         analysis_type_id: ?string,
     *         analysis_type_name: string,
     *         items: list<array{index: int, line: array<string, mixed>}>,
     *         header_line_index: ?int
     *     }>
     * }>
     */
    public function getGroupedLinesProperty(): array
    {
        $sampleBuckets = [];
        $sampleOrder = [];
        $analysisOrder = [];

        foreach ($this->lines as $index => $line) {
            $sampleKey = (string) ($line['sample_type_id'] ?? '__ungrouped_sample__');
            $analysisKey = $sampleKey . '::' . (string) ($line['analysis_type_id'] ?? '__ungrouped_analysis__');

            if (!isset($sampleBuckets[$sampleKey])) {
                $sampleBuckets[$sampleKey] = [
                    'sample_type_id' => $line['sample_type_id'] ?? null,
                    'sample_type_name' => trim((string) ($line['sample_type_name'] ?? '')) !== ''
                        ? (string) $line['sample_type_name']
                        : 'Unspecified sample type',
                    'analysis_map' => [],
                ];
                $sampleOrder[] = $sampleKey;
                $analysisOrder[$sampleKey] = [];
            }

            if (!isset($sampleBuckets[$sampleKey]['analysis_map'][$analysisKey])) {
                $sampleBuckets[$sampleKey]['analysis_map'][$analysisKey] = [
                    'analysis_type_id' => $line['analysis_type_id'] ?? null,
                    'analysis_type_name' => trim((string) ($line['analysis_type_name'] ?? '')) !== ''
                        ? (string) $line['analysis_type_name']
                        : 'Unspecified analysis',
                    'items' => [],
                    'header_line_index' => null,
                ];
                $analysisOrder[$sampleKey][] = $analysisKey;
            }

            $item = ['index' => $index, 'line' => $line];

            if ($this->shouldDisplayLineAsParameter($line)) {
                $sampleBuckets[$sampleKey]['analysis_map'][$analysisKey]['items'][] = $item;
            } elseif ($this->isAnalysisTypeOnlyLine($line)) {
                $sampleBuckets[$sampleKey]['analysis_map'][$analysisKey]['header_line_index'] = $index;
            }
        }

        $grouped = [];
        foreach ($sampleOrder as $sampleKey) {
            $bucket = $sampleBuckets[$sampleKey];
            $analysisGroups = [];
            foreach ($analysisOrder[$sampleKey] as $analysisKey) {
                $analysisGroups[] = $bucket['analysis_map'][$analysisKey];
            }

            $grouped[] = [
                'sample_type_id' => $bucket['sample_type_id'],
                'sample_type_name' => $bucket['sample_type_name'],
                'analysis_groups' => $analysisGroups,
            ];
        }

        return $grouped;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function isAnalysisTypeOnlyLine(array $line): bool
    {
        return empty($line['analysis_element_id']);
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function shouldDisplayLineAsParameter(array $line): bool
    {
        return !$this->isAnalysisTypeOnlyLine($line);
    }

    public function refreshAcceptanceStatus(): void
    {
        if (!$this->acceptanceFormId) {
            return;
        }

        $form = AnalysisAcceptanceForm::query()->find($this->acceptanceFormId);
        if (!$form) {
            return;
        }

        $this->status = $form->status;

        if ($form->status === AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN) {
            $this->currentStep = 3;
        }
    }

    public function render()
    {
        return view('livewire.sampleworkflow.acceptance-form-wizard');
    }

    private function reindexLines(): void
    {
        foreach ($this->lines as $index => &$line) {
            $line['line_no'] = $index + 1;
            $line['sort_order'] = $index;
        }
    }

    private function refreshAddLineParameterOptions(): void
    {
        $this->addLineParameters = [];
        $this->addLineParameterKey = null;
        $this->addLineAllParametersSelected = false;
        $this->addLineCanAddWholeAnalysisType = false;

        if (!$this->crmCustomerId || !$this->addLineSampleTypeId || !$this->addLineAnalysisTypeId) {
            return;
        }

        $allParameters = app(AcceptanceFormPricingService::class)->pricelistParametersForCustomer(
            $this->crmCustomerId,
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId
        );

        $this->addLineCanAddWholeAnalysisType = $this->canAddWholeAnalysisType(
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId
        );

        $this->addLineParameters = $this->filterParametersNotInTable(
            $allParameters,
            $this->addLineSampleTypeId,
            $this->addLineAnalysisTypeId
        );

        $this->addLineAllParametersSelected = empty($this->addLineParameters)
            && !$this->addLineCanAddWholeAnalysisType;
    }

    /**
     * @param  list<array<string, mixed>>  $parameters
     * @return list<array<string, mixed>>
     */
    private function filterParametersNotInTable(
        array $parameters,
        ?string $sampleTypeId,
        ?string $analysisTypeId
    ): array {
        return array_values(array_filter(
            $parameters,
            fn (array $param) => !$this->isParameterAlreadyInTable(
                $sampleTypeId,
                $analysisTypeId,
                !empty($param['analysis_element_id']) ? (string) $param['id'] : null,
                $param['analysis_element_id'] ?? null
            )
        ));
    }

    private function canAddWholeAnalysisType(?string $sampleTypeId, ?string $analysisTypeId): bool
    {
        if ($analysisTypeId === null || $analysisTypeId === '') {
            return false;
        }

        if ($this->hasElementLinesForAnalysis($sampleTypeId, $analysisTypeId)) {
            return false;
        }

        return !$this->isWholeAnalysisTypeInTable($sampleTypeId, $analysisTypeId);
    }

    private function isWholeAnalysisTypeInTable(?string $sampleTypeId, ?string $analysisTypeId): bool
    {
        return collect($this->lines)->contains(
            fn (array $line) => $this->lineMatchesSampleAndAnalysis($line, $sampleTypeId, $analysisTypeId)
                && empty($line['analysis_element_id'])
        );
    }

    private function hasElementLinesForAnalysis(?string $sampleTypeId, ?string $analysisTypeId): bool
    {
        return collect($this->lines)->contains(
            fn (array $line) => $this->lineMatchesSampleAndAnalysis($line, $sampleTypeId, $analysisTypeId)
                && !empty($line['analysis_element_id'])
        );
    }

    private function isParameterAlreadyInTable(
        ?string $sampleTypeId,
        ?string $analysisTypeId,
        ?string $parameterKey,
        ?string $analysisElementId = null
    ): bool {
        if ($parameterKey === null || $parameterKey === '') {
            return $this->isWholeAnalysisTypeInTable($sampleTypeId, $analysisTypeId)
                || $this->hasElementLinesForAnalysis($sampleTypeId, $analysisTypeId);
        }

        $elementId = $analysisElementId ?? $parameterKey;

        return collect($this->lines)->contains(
            fn (array $line) => $this->lineMatchesSampleAndAnalysis($line, $sampleTypeId, $analysisTypeId)
                && (string) ($line['analysis_element_id'] ?? '') === (string) $elementId
        );
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function lineMatchesSampleAndAnalysis(
        array $line,
        ?string $sampleTypeId,
        ?string $analysisTypeId
    ): bool {
        return (string) ($line['sample_type_id'] ?? '') === (string) ($sampleTypeId ?? '')
            && (string) ($line['analysis_type_id'] ?? '') === (string) ($analysisTypeId ?? '');
    }

    private function resetWizard(): void
    {
        $this->currentStep = 1;
        $this->submissionFormInstanceId = null;
        $this->submissionRequestId = null;
        $this->acceptanceFormId = null;
        $this->status = '';
        $this->lines = [];
        $this->crmCustomerId = null;
        $this->showAddLineModal = false;
        $this->managerSignature = '';
        $this->managerSignedAt = now()->format('Y-m-d');
    }
}

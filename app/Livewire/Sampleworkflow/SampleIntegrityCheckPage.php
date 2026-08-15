<?php

namespace App\Livewire\Sampleworkflow;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\CustomerAnalysisTypeStandardService;
use App\Services\Sampleworkflow\RequestTestWorksheetPdfService;
use App\Services\Sampleworkflow\SampleIntegrityCheckService;
use App\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SampleIntegrityCheckPage extends Component
{
    use WithToastNotifications;

    public string $submissionFormId;

    public string $instanceId;

    public SubmissionFormInstance $instance;

    public SubmissionForm $submissionForm;

    public ?SampleSubmissionRequest $enquiry = null;

    /** @var list<array<string, mixed>> */
    public array $testRows = [];

    public string $flashMessage = '';

    public string $selectedSampleKey = '';

    public string $testSearch = '';

    /** @var 'all'|'incomplete'|'subcontracted' */
    public string $testFilter = 'all';

    /** @var list<string> */
    public array $selectedRowKeys = [];

    public string $editingRowKey = '';

    public bool $showAcceptConfirmModal = false;

    public bool $isAccepting = false;

    /** @var list<string> */
    public array $bulkLabSectionIds = [];

    /** @var list<string> */
    public array $bulkAnalystLabSectionIds = [];

    /**
     * Analyst IDs keyed by lab section ID for bulk apply.
     *
     * @var array<string, list<string>>
     */
    public array $bulkAnalystIdsBySection = [];

    public function mount(string $submissionFormId, string $instanceId): void
    {
        $this->submissionFormId = $submissionFormId;
        $this->instanceId = $instanceId;

        $this->submissionForm = SubmissionForm::query()->findOrFail($submissionFormId);
        $this->instance = SubmissionFormInstance::query()
            ->with([
                'sampleSubmissionRequest.currentQuotation.details',
                'sampleSubmissionRequest.acceptedQuotation.details',
                'sampleSubmissionRequest.customer',
                'sampleSubmissionRequest.contact',
                'crmCustomer',
                'batches',
                'submissionForm',
            ])
            ->where('submission_form_id', $submissionFormId)
            ->findOrFail($instanceId);

        $this->enquiry = $this->instance->sampleSubmissionRequest
            ?? SampleSubmissionRequest::query()
                ->with(['currentQuotation.details', 'acceptedQuotation.details', 'customer', 'contact'])
                ->where('submission_form_instance_id', $this->instance->id)
                ->first();

        $this->reloadRows();
    }

    public function updatedSelectedSampleKey(): void
    {
        $this->selectedRowKeys = [];
        $this->editingRowKey = '';
        $this->bulkLabSectionIds = [];
        $this->resetBulkAnalystState();
    }

    public function updatedSelectedRowKeys(): void
    {
        $this->resetBulkAnalystState();
    }

    public function updatedBulkAnalystLabSectionIds(): void
    {
        $allowed = array_flip(array_map('strval', $this->bulkAnalystLabSectionIds));
        $pruned = [];
        foreach ($this->bulkAnalystIdsBySection as $sectionId => $analystIds) {
            $sectionId = (string) $sectionId;
            if (! isset($allowed[$sectionId])) {
                continue;
            }
            $pruned[$sectionId] = array_values(array_map(
                'strval',
                is_array($analystIds) ? $analystIds : []
            ));
        }

        $selectedRows = array_flip(array_map('strval', $this->selectedRowKeys));
        foreach (array_keys($allowed) as $sectionId) {
            if (array_key_exists($sectionId, $pruned)) {
                continue;
            }

            $assigned = [];
            foreach ($this->testRows as $row) {
                if (! isset($selectedRows[(string) ($row['row_key'] ?? '')])) {
                    continue;
                }

                $rowAssignments = is_array($row['analysts_by_lab_section'] ?? null)
                    ? $row['analysts_by_lab_section']
                    : [];
                $assigned = [
                    ...$assigned,
                    ...(is_array($rowAssignments[$sectionId] ?? null) ? $rowAssignments[$sectionId] : []),
                ];
            }

            $pruned[$sectionId] = array_values(array_unique(array_filter(array_map('strval', $assigned))));
        }
        $this->bulkAnalystIdsBySection = $pruned;
    }

    public function toggleBulkAnalyst(string $labSectionId, string $userId): void
    {
        $labSectionId = trim($labSectionId);
        $userId = trim($userId);
        if ($labSectionId === '' || $userId === '') {
            return;
        }

        if (! in_array($labSectionId, array_map('strval', $this->bulkAnalystLabSectionIds), true)) {
            $this->bulkAnalystLabSectionIds[] = $labSectionId;
        }

        $assigned = array_values(array_map(
            'strval',
            is_array($this->bulkAnalystIdsBySection[$labSectionId] ?? null)
                ? $this->bulkAnalystIdsBySection[$labSectionId]
                : []
        ));

        if (in_array($userId, $assigned, true)) {
            $assigned = array_values(array_filter($assigned, fn (string $id) => $id !== $userId));
        } else {
            $assigned[] = $userId;
        }

        $this->bulkAnalystIdsBySection[$labSectionId] = array_values(array_unique($assigned));
    }

    public function updatedTestSearch(): void
    {
        $this->selectedRowKeys = [];
        $this->resetBulkAnalystState();
    }

    public function updatedTestFilter(): void
    {
        $this->selectedRowKeys = [];
        $this->resetBulkAnalystState();
    }

    public function selectSample(string $sampleKey): void
    {
        $this->selectedSampleKey = $sampleKey;
        $this->selectedRowKeys = [];
        $this->editingRowKey = '';
        $this->bulkLabSectionIds = [];
        $this->resetBulkAnalystState();
    }

    public function setTestFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'incomplete', 'subcontracted'], true)) {
            return;
        }

        $this->testFilter = $filter;
        $this->selectedRowKeys = [];
    }

    public function toggleRowSelection(string $rowKey): void
    {
        if (in_array($rowKey, $this->selectedRowKeys, true)) {
            $this->selectedRowKeys = array_values(array_filter(
                $this->selectedRowKeys,
                fn (string $key) => $key !== $rowKey
            ));

            return;
        }

        $this->selectedRowKeys[] = $rowKey;
    }

    public function selectAllVisibleRows(): void
    {
        $this->selectedRowKeys = array_values(array_map(
            fn (array $row) => (string) ($row['row_key'] ?? ''),
            $this->visibleTestRows
        ));
    }

    public function clearRowSelection(): void
    {
        $this->selectedRowKeys = [];
        $this->bulkLabSectionIds = [];
        $this->resetBulkAnalystState();
    }

    public function startEditingRow(string $rowKey): void
    {
        $this->editingRowKey = $this->editingRowKey === $rowKey ? '' : $rowKey;
    }

    public function stopEditingRow(): void
    {
        $this->editingRowKey = '';
    }

    public function saveAssignments(): void
    {
        if ($this->enquiry === null) {
            $this->setFlashMessage('No commercial enquiry is linked to this request.', 'warning');

            return;
        }

        $this->enquiry = app(SampleIntegrityCheckService::class)
            ->persistIntegrityAssignments($this->enquiry, $this->testRows);

        $this->reloadRows();
        $this->setFlashMessage('Integrity assignments saved.', 'success');
    }

    public function generateWorksheetPdf(): void
    {
        $analystNamesById = [];
        foreach ($this->analystsBySection as $sectionAnalysts) {
            foreach ($sectionAnalysts as $analyst) {
                $analystNamesById[(string) ($analyst['id'] ?? '')] = (string) ($analyst['name'] ?? '');
            }
        }

        $url = app(RequestTestWorksheetPdfService::class)->storeIntegrityPdf(
            $this->instance,
            $this->enquiry,
            $this->testRows,
            $this->labSectionNames,
            $analystNamesById,
        );

        $this->dispatch('open-integrity-worksheet-pdf', url: $url.'?v='.now()->timestamp);
        $this->setFlashMessage('PDF generated and opened in a new tab.', 'success');
    }

    public function toggleSubcontracted(string $rowKey): void
    {
        foreach ($this->testRows as $index => $row) {
            if ((string) ($row['row_key'] ?? '') !== $rowKey) {
                continue;
            }

            $this->testRows[$index]['subcontracted'] = ! (bool) ($row['subcontracted'] ?? false);
            break;
        }
    }

    /**
     * @param  list<string>|string  $labSectionIds
     */
    public function setRowLabSections(string $rowKey, array|string $labSectionIds): void
    {
        $ids = app(AcceptanceFormSampleConfigService::class)->normalizeLabSectionIds($labSectionIds);

        foreach ($this->testRows as $index => $row) {
            if ((string) ($row['row_key'] ?? '') !== $rowKey) {
                continue;
            }

            $this->applyLabSectionsToIndex($index, $ids);
            break;
        }
    }

    public function toggleRowAnalyst(string $rowKey, string $labSectionId, string $userId): void
    {
        foreach ($this->testRows as $index => $row) {
            if ((string) ($row['row_key'] ?? '') !== $rowKey) {
                continue;
            }

            $bySection = is_array($row['analysts_by_lab_section'] ?? null)
                ? $row['analysts_by_lab_section']
                : [];
            $assigned = array_values(array_map(
                'strval',
                is_array($bySection[$labSectionId] ?? null) ? $bySection[$labSectionId] : []
            ));

            if (in_array($userId, $assigned, true)) {
                $assigned = array_values(array_filter($assigned, fn (string $id) => $id !== $userId));
            } else {
                $assigned[] = $userId;
            }

            $bySection[$labSectionId] = array_values(array_unique($assigned));
            $this->testRows[$index]['analysts_by_lab_section'] = $bySection;
            break;
        }
    }

    public function applyBulkLabSections(array $labSectionIds = []): void
    {
        if ($this->selectedRowKeys === []) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $ids = $configService->normalizeLabSectionIds(
            $labSectionIds !== [] ? $labSectionIds : $this->bulkLabSectionIds
        );

        if ($ids === []) {
            $this->setFlashMessage('Select at least one lab section to apply.', 'warning');

            return;
        }

        $selected = array_flip($this->selectedRowKeys);
        $updated = 0;
        foreach ($this->testRows as $index => $row) {
            $rowKey = (string) ($row['row_key'] ?? '');
            if (! isset($selected[$rowKey])) {
                continue;
            }

            $merged = $configService->normalizeLabSectionIds(array_merge(
                is_array($row['lab_section_ids'] ?? null) ? $row['lab_section_ids'] : [],
                $ids
            ));
            $this->applyLabSectionsToIndex($index, $merged);
            $updated++;
        }

        $this->setFlashMessage($updated.' test(s) updated with lab section(s).', 'success');
    }

    public function applyBulkSubcontracted(bool $subcontracted = true): void
    {
        if ($this->selectedRowKeys === []) {
            return;
        }

        $selected = array_flip($this->selectedRowKeys);
        $updated = 0;
        foreach ($this->testRows as $index => $row) {
            $rowKey = (string) ($row['row_key'] ?? '');
            if (! isset($selected[$rowKey])) {
                continue;
            }

            $this->testRows[$index]['subcontracted'] = $subcontracted;
            $updated++;
        }

        $this->clearRowSelection();
        $this->setFlashMessage($subcontracted
            ? $updated.' test(s) marked subcontracted.'
            : $updated.' test(s) unmarked as subcontracted.', 'success');
    }

    /**
     * @param  array<string, list<string>>|null  $analystIdsBySection
     */
    public function applyBulkAnalysts(?array $analystIdsBySection = null): void
    {
        if ($this->selectedRowKeys === []) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $payload = is_array($analystIdsBySection) ? $analystIdsBySection : $this->bulkAnalystIdsBySection;

        $bySection = [];
        foreach ($payload as $sectionId => $analystIds) {
            $normalizedSections = $configService->normalizeLabSectionIds([(string) $sectionId]);
            $sectionId = $normalizedSections[0] ?? '';
            if ($sectionId === '') {
                continue;
            }

            $ids = array_values(array_unique(array_filter(
                array_map('strval', is_array($analystIds) ? $analystIds : []),
                static fn (string $id): bool => $id !== ''
            )));
            if ($ids === []) {
                continue;
            }

            $bySection[$sectionId] = $ids;
        }

        if ($bySection === []) {
            $this->setFlashMessage('Select at least one analyst for a lab section already on the selected test(s).', 'warning');

            return;
        }

        $selected = array_flip($this->selectedRowKeys);
        $updated = 0;
        foreach ($this->testRows as $index => $row) {
            $rowKey = (string) ($row['row_key'] ?? '');
            if (! isset($selected[$rowKey])) {
                continue;
            }

            $rowSectionIds = $configService->normalizeLabSectionIds($row['lab_section_ids'] ?? []);
            $rowSectionFlip = array_flip($rowSectionIds);
            $existing = is_array($this->testRows[$index]['analysts_by_lab_section'] ?? null)
                ? $this->testRows[$index]['analysts_by_lab_section']
                : [];
            $changed = false;

            foreach ($bySection as $sectionId => $analystIds) {
                if (! isset($rowSectionFlip[$sectionId])) {
                    continue;
                }

                $existing[$sectionId] = $analystIds;
                $changed = true;
            }

            if (! $changed) {
                continue;
            }

            $this->testRows[$index]['analysts_by_lab_section'] = $existing;
            $updated++;
        }

        if ($updated === 0) {
            $this->setFlashMessage('No selected tests include the chosen lab section(s). Assign sections first.', 'warning');

            return;
        }

        $this->clearRowSelection();
        $this->setFlashMessage($updated.' test(s) updated with analyst(s).', 'success');
    }

    public function copyAssignmentsFromFirstSelected(): void
    {
        if (count($this->selectedRowKeys) < 2) {
            $this->setFlashMessage('Select at least two tests to copy assignments.', 'warning');

            return;
        }

        $sourceKey = $this->selectedRowKeys[0];
        $source = null;
        foreach ($this->testRows as $row) {
            if ((string) ($row['row_key'] ?? '') === $sourceKey) {
                $source = $row;
                break;
            }
        }

        if ($source === null) {
            return;
        }

        $sectionIds = app(AcceptanceFormSampleConfigService::class)
            ->normalizeLabSectionIds($source['lab_section_ids'] ?? []);
        $analysts = is_array($source['analysts_by_lab_section'] ?? null)
            ? $source['analysts_by_lab_section']
            : [];
        $subcontracted = (bool) ($source['subcontracted'] ?? false);

        $selected = array_flip($this->selectedRowKeys);
        $updated = 0;
        foreach ($this->testRows as $index => $row) {
            $rowKey = (string) ($row['row_key'] ?? '');
            if (! isset($selected[$rowKey]) || $rowKey === $sourceKey) {
                continue;
            }

            $this->applyLabSectionsToIndex($index, $sectionIds);
            $pruned = [];
            foreach ($sectionIds as $sectionId) {
                $pruned[$sectionId] = array_values(array_map(
                    'strval',
                    is_array($analysts[$sectionId] ?? null) ? $analysts[$sectionId] : []
                ));
            }
            $this->testRows[$index]['analysts_by_lab_section'] = $pruned;
            $this->testRows[$index]['subcontracted'] = $subcontracted;
            $updated++;
        }

        $this->clearRowSelection();
        $this->setFlashMessage("Copied assignments from the first selected test to {$updated} other test(s).", 'success');
    }

    public function openAcceptConfirm(): void
    {
        if ($this->enquiry === null) {
            $this->setFlashMessage('No commercial enquiry is linked to this request.', 'warning');

            return;
        }

        $this->enquiry = app(SampleIntegrityCheckService::class)
            ->persistIntegrityAssignments($this->enquiry, $this->testRows);

        $readiness = app(EnquiryReceptionReadinessService::class);
        if (! $readiness->isEligibleForSampleAcceptance($this->enquiry, $this->instance)) {
            $this->setFlashMessage('This request is not ready for sample acceptance yet.', 'warning');

            return;
        }

        $this->showAcceptConfirmModal = true;
    }

    public function closeAcceptConfirm(): void
    {
        $this->showAcceptConfirmModal = false;
        $this->isAccepting = false;
    }

    public function confirmAcceptSamples(): void
    {
        if ($this->enquiry === null || $this->isAccepting) {
            return;
        }

        $this->isAccepting = true;

        $this->enquiry = app(SampleIntegrityCheckService::class)
            ->persistIntegrityAssignments($this->enquiry, $this->testRows);

        $readiness = app(EnquiryReceptionReadinessService::class);
        if (! $readiness->isEligibleForSampleAcceptance($this->enquiry, $this->instance)) {
            $this->isAccepting = false;
            $this->showAcceptConfirmModal = false;
            $this->setFlashMessage('This request is not ready for sample acceptance yet.', 'warning');

            return;
        }

        try {
            $form = app(AcceptanceFormService::class)->acceptFromIntegrityCheck(
                (string) $this->instance->id,
                (string) $this->enquiry->id,
                auth()->id() ? (string) auth()->id() : null,
            );
        } catch (\Throwable $exception) {
            report($exception);
            $this->isAccepting = false;
            $this->setFlashMessage(
                $exception->getMessage() !== ''
                    ? $exception->getMessage()
                    : 'Could not accept samples. Check the application log for details.',
                'error'
            );

            return;
        }

        $configs = is_array($form->sample_configuration_payload)
            ? $form->sample_configuration_payload
            : [];
        app(CustomerAnalysisTypeStandardService::class)->syncPreferencesFromConfigs(
            $form->crm_customer_id !== null ? (string) $form->crm_customer_id : null,
            $configs,
        );

        $batchId = (string) ($form->sample_header_id ?? '');
        $this->showAcceptConfirmModal = false;
        $this->isAccepting = false;

        if ($batchId === '') {
            $this->setFlashMessage('Samples were accepted but the job number could not be created.', 'error');

            return;
        }

        $batch = \App\SampleHeader::query()->find($batchId);
        $batchCode = (string) ($batch?->batch_code ?? '');
        $isShelfLife = (bool) ($batch?->is_shelf_life ?? false);

        if ($isShelfLife) {
            $studyId = \App\Models\ShelfLife\ShelfLifeStudy::query()
                ->where('sample_header_id', $batchId)
                ->value('id');

            $redirectUrl = $studyId
                ? route('shelf-life.studies.show', ['study' => $studyId])
                : route('shelf-life.studies.index');

            $this->dispatch('acceptance-form-completed', redirectUrl: $redirectUrl);
            session()->flash('success', "Samples accepted as shelf-life testing. Job number {$batchCode} sent to Shelf Life Studies.");

            return;
        }

        $redirectUrl = route('view-batch-details', [
            'batch' => $batchId,
            'client' => 0,
            'portal' => 0,
            'status' => 'Samples In Lab',
        ]).'#samples';

        $this->dispatch('acceptance-form-completed', redirectUrl: $redirectUrl);
        session()->flash('success', "Samples accepted. Job number {$batchCode} created and moved to Samples In Lab.");
    }

    public function getCanAcceptProperty(): bool
    {
        if ($this->enquiry === null) {
            return false;
        }

        return app(EnquiryReceptionReadinessService::class)
            ->isEligibleForSampleAcceptance($this->enquiry, $this->instance);
    }

    public function getCollectionLabelUrlProperty(): string
    {
        return route('submission-forms.instances.sample-collection-label', [
            'instance' => $this->instance->id,
        ]);
    }

    protected function setFlashMessage(string $message, string $type = 'info'): void
    {
        $this->flashMessage = $message;
        if ($message !== '') {
            $this->toast($type, $message);
        }
    }

    public function getLabSectionsProperty(): array
    {
        return app(AcceptanceFormSampleConfigService::class)->labSectionsForPicker();
    }

    /**
     * @return array<string, list<array{id: string, name: string}>>
     */
    public function getAnalystsBySectionProperty(): array
    {
        $configService = app(AcceptanceFormSampleConfigService::class);
        $bySection = [];
        $sectionIds = [];

        foreach ($this->testRows as $row) {
            foreach (($row['lab_section_ids'] ?? []) as $sectionId) {
                $sectionIds[(string) $sectionId] = true;
            }
        }

        foreach ($this->bulkLabSectionIds as $sectionId) {
            $sectionIds[(string) $sectionId] = true;
        }

        foreach ($this->bulkAnalystLabSectionIds as $sectionId) {
            $sectionIds[(string) $sectionId] = true;
        }

        foreach (array_keys($sectionIds) as $sectionId) {
            if ($sectionId === '') {
                continue;
            }
            $bySection[$sectionId] = $configService->analystsForLabSectionPicker($sectionId);
        }

        $extraIdsBySection = [];
        foreach ($this->testRows as $row) {
            foreach ((array) ($row['analysts_by_lab_section'] ?? []) as $sectionId => $analystIds) {
                foreach ((array) $analystIds as $analystId) {
                    $analystId = (string) $analystId;
                    if ($analystId !== '') {
                        $extraIdsBySection[(string) $sectionId][$analystId] = true;
                    }
                }
            }
        }

        $extraIds = collect($extraIdsBySection)
            ->flatMap(fn (array $ids): array => array_keys($ids))
            ->unique()
            ->values()
            ->all();
        $extraUsers = User::query()
            ->whereIn('id', $extraIds)
            ->get(['id', 'name'])
            ->keyBy(fn (User $user): string => (string) $user->id);

        foreach ($extraIdsBySection as $sectionId => $analystIds) {
            $existing = collect($bySection[$sectionId] ?? [])->keyBy('id');
            foreach (array_keys($analystIds) as $analystId) {
                $user = $extraUsers->get($analystId);
                if ($user !== null && ! $existing->has($analystId)) {
                    $bySection[$sectionId][] = [
                        'id' => (string) $user->id,
                        'name' => (string) $user->name,
                    ];
                }
            }
        }

        return $bySection;
    }

    /**
     * Lab sections already assigned to the currently selected tests (deduped by ID).
     *
     * @return list<array{id: string, name: string}>
     */
    public function getBulkAnalystSectionOptionsProperty(): array
    {
        if ($this->selectedRowKeys === []) {
            return [];
        }

        $selected = array_flip($this->selectedRowKeys);
        $sectionIds = [];
        foreach ($this->testRows as $row) {
            $rowKey = (string) ($row['row_key'] ?? '');
            if (! isset($selected[$rowKey])) {
                continue;
            }

            foreach (($row['lab_section_ids'] ?? []) as $sectionId) {
                $sectionId = (string) $sectionId;
                if ($sectionId !== '') {
                    $sectionIds[$sectionId] = true;
                }
            }
        }

        $names = $this->labSectionNames;
        $options = [];
        foreach (array_keys($sectionIds) as $sectionId) {
            $options[] = [
                'id' => $sectionId,
                'name' => $names[$sectionId] ?? 'Lab section',
            ];
        }

        usort($options, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));

        return $options;
    }

    public function getHasBulkAnalystPicksProperty(): bool
    {
        foreach ($this->bulkAnalystIdsBySection as $analystIds) {
            if (is_array($analystIds) && $analystIds !== []) {
                return true;
            }
        }

        return false;
    }

    public function getRequestInfoCardProperty(): array
    {
        return app(SampleIntegrityCheckService::class)
            ->requestInfoCard($this->instance, $this->enquiry);
    }

    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     total: int,
     *     complete: int,
     *     incomplete: int,
     *     subcontracted: int
     * }>
     */
    public function getSampleSummariesProperty(): array
    {
        $groups = [];

        foreach ($this->testRows as $row) {
            $key = (string) ($row['config_id'] ?? '');
            if ($key === '') {
                continue;
            }

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'key' => $key,
                    'label' => (string) ($row['sample_label'] ?? 'Sample'),
                    'total' => 0,
                    'complete' => 0,
                    'incomplete' => 0,
                    'subcontracted' => 0,
                ];
            }

            $groups[$key]['total']++;
            if ($this->rowIsComplete($row)) {
                $groups[$key]['complete']++;
            } else {
                $groups[$key]['incomplete']++;
            }
            if (! empty($row['subcontracted'])) {
                $groups[$key]['subcontracted']++;
            }
        }

        return array_values($groups);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getVisibleTestRowsProperty(): array
    {
        $needle = mb_strtolower(trim($this->testSearch));
        $rows = [];

        foreach ($this->testRows as $row) {
            if ((string) ($row['config_id'] ?? '') !== $this->selectedSampleKey) {
                continue;
            }

            $complete = $this->rowIsComplete($row);

            if ($this->testFilter === 'incomplete' && $complete) {
                continue;
            }

            if ($this->testFilter === 'subcontracted' && empty($row['subcontracted'])) {
                continue;
            }

            if ($needle !== '') {
                $haystack = mb_strtolower((string) ($row['test_label'] ?? ''));
                if (! str_contains($haystack, $needle)) {
                    continue;
                }
            }

            $analystIds = [];
            foreach (($row['analysts_by_lab_section'] ?? []) as $assigned) {
                foreach ((array) $assigned as $analystId) {
                    $analystIds[(string) $analystId] = true;
                }
            }

            $row['is_complete'] = $complete;
            $row['analyst_count'] = count($analystIds);
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    public function getLabSectionNamesProperty(): array
    {
        $names = [];
        foreach ($this->labSections as $section) {
            $names[(string) ($section['id'] ?? '')] = (string) ($section['name'] ?? 'Lab section');
        }

        return $names;
    }

    public function render(): View
    {
        return view('livewire.sampleworkflow.sample-integrity-check-page');
    }

    /**
     * @param  list<string>  $ids
     */
    private function applyLabSectionsToIndex(int $index, array $ids): void
    {
        $existing = is_array($this->testRows[$index]['analysts_by_lab_section'] ?? null)
            ? $this->testRows[$index]['analysts_by_lab_section']
            : [];

        $this->testRows[$index]['lab_section_ids'] = $ids;
        $pruned = [];
        foreach ($ids as $sectionId) {
            $pruned[$sectionId] = array_values(array_map(
                'strval',
                is_array($existing[$sectionId] ?? null) ? $existing[$sectionId] : []
            ));
        }

        $hasAnyAssignedAnalyst = false;
        foreach ($pruned as $assigned) {
            if ($assigned !== []) {
                $hasAnyAssignedAnalyst = true;
                break;
            }
        }

        $defaultOperatorId = trim((string) ($this->testRows[$index]['default_operator_id'] ?? ''));
        if (! $hasAnyAssignedAnalyst && $defaultOperatorId !== '' && $ids !== []) {
            $pruned[$ids[0]] = [$defaultOperatorId];
        }

        $this->testRows[$index]['analysts_by_lab_section'] = $pruned;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowIsComplete(array $row): bool
    {
        $sectionIds = array_values(array_filter(array_map(
            'strval',
            is_array($row['lab_section_ids'] ?? null) ? $row['lab_section_ids'] : []
        )));

        if ($sectionIds === []) {
            return false;
        }

        if (! empty($row['subcontracted'])) {
            return true;
        }

        // Analysts are assigned to the test; any non-empty assignment completes the row.
        $bySection = is_array($row['analysts_by_lab_section'] ?? null)
            ? $row['analysts_by_lab_section']
            : [];

        foreach ($bySection as $assigned) {
            if (is_array($assigned) && $assigned !== []) {
                return true;
            }
        }

        return false;
    }

    private function resetBulkAnalystState(): void
    {
        $this->bulkAnalystLabSectionIds = [];
        $this->bulkAnalystIdsBySection = [];
    }

    private function pruneBulkAnalystStateToSelection(): void
    {
        $allowedIds = array_flip(array_map(
            static fn (array $section): string => (string) ($section['id'] ?? ''),
            $this->bulkAnalystSectionOptions
        ));

        $this->bulkAnalystLabSectionIds = array_values(array_filter(
            array_map('strval', $this->bulkAnalystLabSectionIds),
            static fn (string $id): bool => $id !== '' && isset($allowedIds[$id])
        ));

        $pruned = [];
        foreach ($this->bulkAnalystIdsBySection as $sectionId => $analystIds) {
            $sectionId = (string) $sectionId;
            if (! isset($allowedIds[$sectionId])) {
                continue;
            }
            $pruned[$sectionId] = array_values(array_map('strval', is_array($analystIds) ? $analystIds : []));
        }
        $this->bulkAnalystIdsBySection = $pruned;
    }

    private function reloadRows(): void
    {
        if ($this->enquiry === null) {
            $this->testRows = [];
            $this->selectedSampleKey = '';

            return;
        }

        $this->testRows = app(SampleIntegrityCheckService::class)
            ->buildTestRows($this->enquiry, $this->instance);

        $summaries = $this->sampleSummaries;
        $keys = array_column($summaries, 'key');
        if ($this->selectedSampleKey === '' || ! in_array($this->selectedSampleKey, $keys, true)) {
            $this->selectedSampleKey = (string) ($keys[0] ?? '');
        }
    }
}

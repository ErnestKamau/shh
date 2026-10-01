<?php

namespace App\Livewire\Sampleworkflow;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Commercial\JobPurchaseOrderCoverageService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\BatchResultsExcelImportService;
use App\Services\Sampleworkflow\CustomerAnalysisTypeStandardService;
use App\Services\Sampleworkflow\LabSectionWorksheetIssueService;
use App\Services\Sampleworkflow\RequestTestExportDataService;
use App\Services\Sampleworkflow\RequestTestWorksheetPdfService;
use App\Services\Sampleworkflow\SampleIntegrityCheckService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\Sampleworkflow\TrfLabUseFieldsService;
use App\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public string $flashMessageType = 'info';

    public string $selectedSampleKey = '';

    public string $testSearch = '';

    /** @var 'all'|'incomplete'|'subcontracted' */
    public string $testFilter = 'all';

    public bool $testFiltersOpen = false;

    public string $filterLabSectionId = '';

    public string $filterAnalysisType = '';

    public string $filterSampleType = '';

    public int $testsPage = 1;

    public int $testsPerPage = 10;

    /** @var list<string> */
    public array $selectedRowKeys = [];

    public string $editingRowKey = '';

    public bool $showAcceptConfirmModal = false;

    public bool $isAccepting = false;

    public string $acceptError = '';

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

    public bool $showWorksheetModal = false;

    /** pdf|excel — which Actions entry opened the modal */
    public string $worksheetModalMode = 'pdf';

    /** @var list<string> */
    public array $selectedWorksheetSectionIds = [];

    public bool $showTestInfoModal = false;

    public string $viewingTestInfoRowKey = '';

    public bool $showSampleInfoModal = false;

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
        $this->syncBulkAnalystSectionsFromSelection();
        $this->bulkLabSectionIds = $this->selectedTestsLabSectionIds();
    }

    /**
     * Apply the bulk lab-section Select2 values to every selected test (exact set).
     *
     * @param  list<string|int>  $labSectionIds
     */
    public function syncBulkLabSections(array $labSectionIds = []): void
    {
        if ($this->selectedRowKeys === []) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $nextIds = $configService->normalizeLabSectionIds($labSectionIds);
        $selected = array_flip($this->selectedRowKeys);
        $updated = 0;

        foreach ($this->testRows as $index => $row) {
            $rowKey = (string) ($row['row_key'] ?? '');
            if (! isset($selected[$rowKey])) {
                continue;
            }

            $existing = $configService->normalizeLabSectionIds(
                is_array($row['lab_section_ids'] ?? null) ? $row['lab_section_ids'] : []
            );

            $existingSorted = $existing;
            $nextSorted = $nextIds;
            sort($existingSorted);
            sort($nextSorted);

            if ($existingSorted === $nextSorted) {
                continue;
            }

            $this->applyLabSectionsToIndex($index, $nextIds);
            $updated++;
        }

        $this->bulkLabSectionIds = $nextIds;
        $this->persistAssignments(reload: false);
        $this->syncBulkAnalystSectionsFromSelection();

        if ($updated > 0) {
            $this->setFlashMessage($updated.' test(s) updated with lab section(s).', 'success');
        }
    }

    /**
     * Union of lab section IDs already assigned on the currently selected tests.
     *
     * @return list<string>
     */
    private function selectedTestsLabSectionIds(): array
    {
        return array_values(array_filter(array_map(
            static fn (array $section): string => (string) ($section['id'] ?? ''),
            $this->bulkAnalystSectionOptions
        )));
    }

    public function updatedBulkAnalystLabSectionIds(): void
    {
        $this->hydrateBulkAnalystIdsFromSelection();
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
        $this->applyBulkAnalystsToSelection($labSectionId, silent: true);
    }

    public function selectSample(string $sampleKey): void
    {
        $this->selectedSampleKey = $sampleKey;
        $this->selectedRowKeys = [];
        $this->editingRowKey = '';
        $this->bulkLabSectionIds = [];
        $this->resetBulkAnalystState();
        $this->testsPage = 1;
        $this->testFiltersOpen = false;
    }

    public function setTestFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'incomplete', 'subcontracted'], true)) {
            return;
        }

        $this->testFilter = $filter;
        $this->selectedRowKeys = [];
        $this->testsPage = 1;
    }

    public function toggleTestFilters(): void
    {
        $this->testFiltersOpen = ! $this->testFiltersOpen;
    }

    public function closeTestFilters(): void
    {
        $this->testFiltersOpen = false;
    }

    public function clearTestFilters(): void
    {
        $this->testSearch = '';
        $this->testFilter = 'all';
        $this->filterLabSectionId = '';
        $this->filterAnalysisType = '';
        $this->filterSampleType = '';
        $this->selectedRowKeys = [];
        $this->testsPage = 1;
    }

    public function updatedTestSearch(): void
    {
        $this->testsPage = 1;
        $this->selectedRowKeys = [];
        $this->resetBulkAnalystState();
    }

    public function updatedTestFilter(): void
    {
        $this->testsPage = 1;
        $this->selectedRowKeys = [];
        $this->resetBulkAnalystState();
    }

    public function updatedFilterLabSectionId(): void
    {
        $this->testsPage = 1;
        $this->selectedRowKeys = [];
    }

    public function updatedFilterAnalysisType(): void
    {
        $this->testsPage = 1;
        $this->selectedRowKeys = [];
    }

    public function updatedFilterSampleType(): void
    {
        $this->testsPage = 1;
        $this->selectedRowKeys = [];
    }

    public function goToTestsPage(int $page): void
    {
        $lastPage = max(1, (int) ($this->testsPagination['last_page'] ?? 1));
        $this->testsPage = max(1, min($page, $lastPage));
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

        $this->persistAssignments();
        $this->setFlashMessage('Integrity assignments saved.', 'success');
    }

    public function generateWorksheetPdf(): void
    {
        $url = app(RequestTestWorksheetPdfService::class)->storeIntegrityPdf(
            $this->instance,
            $this->enquiry,
            $this->testRows,
            $this->labSectionNames,
            $this->analystNamesById(),
        );

        $this->dispatch('open-integrity-worksheet-pdf', url: $url.'?v='.now()->timestamp);
        $this->setFlashMessage('PDF generated and opened in a new tab.', 'success');
    }

    public function downloadExcel(): BinaryFileResponse
    {
        $payload = app(RequestTestExportDataService::class)->buildFromIntegrityRows(
            $this->instance,
            $this->enquiry,
            $this->testRows,
            $this->labSectionNames,
            $this->analystNamesById(),
        );

        $reference = trim((string) ($this->enquiry?->formatted_number
            ?? $this->enquiry?->request_number
            ?? $this->instance->code
            ?? $this->instance->id));

        return app(BatchResultsExcelImportService::class)->downloadTemplateFromIntegrityRows(
            $payload['flat_rows'],
            'integrity-results-'.$this->safeExcelFilename($reference).'.xlsx',
        );
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

        $this->persistAssignments(reload: false);
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

        $this->persistAssignments(reload: false);
    }

    public function removeLabSectionChip(string $rowKey, string $sectionId): void
    {
        $sectionId = trim($sectionId);
        if ($sectionId === '') {
            return;
        }

        if ($this->selectedRowKeys !== []) {
            $this->removeBulkLabSections([$sectionId], silent: false);

            return;
        }

        $remaining = [];
        foreach ($this->testRows as $index => $row) {
            if ((string) ($row['row_key'] ?? '') !== $rowKey) {
                continue;
            }

            $existing = app(AcceptanceFormSampleConfigService::class)->normalizeLabSectionIds(
                is_array($row['lab_section_ids'] ?? null) ? $row['lab_section_ids'] : []
            );
            $remaining = array_values(array_filter(
                $existing,
                static fn (string $id): bool => $id !== $sectionId
            ));
            $this->applyLabSectionsToIndex($index, $remaining);
            break;
        }

        $this->persistAssignments(reload: false);
        $this->syncBulkAnalystSectionsFromSelection();
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

        $this->persistAssignments(reload: false);
    }

    public function applyBulkLabSections(array $labSectionIds = [], bool $silent = false): void
    {
        if ($this->selectedRowKeys === []) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $ids = $configService->normalizeLabSectionIds(
            $labSectionIds !== [] ? $labSectionIds : $this->bulkLabSectionIds
        );

        if ($ids === []) {
            if (! $silent) {
                $this->setFlashMessage('Select at least one lab section to apply.', 'warning');
            }

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

        $this->persistAssignments(reload: ! $silent);
        $this->syncBulkAnalystSectionsFromSelection();

        if (! $silent && $updated > 0) {
            $this->setFlashMessage($updated.' test(s) updated with lab section(s).', 'success');
        }
    }

    public function removeBulkLabSections(array $labSectionIds = [], bool $silent = false): void
    {
        if ($this->selectedRowKeys === []) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $ids = $configService->normalizeLabSectionIds(
            $labSectionIds !== [] ? $labSectionIds : $this->bulkLabSectionIds
        );

        if ($ids === []) {
            if (! $silent) {
                $this->setFlashMessage('Select at least one lab section to remove.', 'warning');
            }

            return;
        }

        $removeFlip = array_flip($ids);
        $selected = array_flip($this->selectedRowKeys);
        $updated = 0;

        foreach ($this->testRows as $index => $row) {
            $rowKey = (string) ($row['row_key'] ?? '');
            if (! isset($selected[$rowKey])) {
                continue;
            }

            $existing = $configService->normalizeLabSectionIds(
                is_array($row['lab_section_ids'] ?? null) ? $row['lab_section_ids'] : []
            );
            $remaining = array_values(array_filter(
                $existing,
                static fn (string $sectionId): bool => ! isset($removeFlip[$sectionId])
            ));

            if ($remaining === $existing) {
                continue;
            }

            $this->applyLabSectionsToIndex($index, $remaining);
            $updated++;
        }

        $this->persistAssignments(reload: ! $silent);
        $this->syncBulkAnalystSectionsFromSelection();

        if ($silent) {
            return;
        }

        if ($updated > 0) {
            $this->setFlashMessage($updated.' test(s) updated — lab section(s) removed.', 'success');

            return;
        }

        $this->setFlashMessage('None of the selected tests had those lab section(s).', 'warning');
    }

    public function toggleBulkSubcontractedOnSelected(): void
    {
        if ($this->selectedRowKeys === []) {
            return;
        }

        $this->applyBulkSubcontracted(! $this->bulkSubcontractAllSelected);
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
        $this->persistAssignments(reload: false);

        if ($updated > 0) {
            $this->setFlashMessage($subcontracted
                ? $updated.' test(s) marked subcontracted.'
                : $updated.' test(s) unmarked as subcontracted.', 'success');
        }
    }

    /**
     * @param  array<string, list<string>>|null  $analystIdsBySection
     */
    public function applyBulkAnalysts(?array $analystIdsBySection = null): void
    {
        $this->applyBulkAnalystsToSelection(null, $analystIdsBySection, silent: false);
    }

    /**
     * @param  array<string, list<string>>|null  $analystIdsBySection
     */
    private function applyBulkAnalystsToSelection(
        ?string $onlySectionId = null,
        ?array $analystIdsBySection = null,
        bool $silent = false,
    ): void {
        if ($this->selectedRowKeys === []) {
            return;
        }

        $configService = app(AcceptanceFormSampleConfigService::class);
        $payload = is_array($analystIdsBySection) ? $analystIdsBySection : $this->bulkAnalystIdsBySection;

        if ($onlySectionId !== null && $onlySectionId !== '') {
            $sectionAnalystIds = is_array($payload[$onlySectionId] ?? null) ? $payload[$onlySectionId] : [];
            $payload = [$onlySectionId => $sectionAnalystIds];
        }

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
            if ($ids === [] && $onlySectionId === null) {
                continue;
            }

            $bySection[$sectionId] = $ids;
        }

        if ($bySection === []) {
            if ($silent && $onlySectionId !== null) {
                $normalized = $configService->normalizeLabSectionIds([(string) $onlySectionId]);
                $sectionId = $normalized[0] ?? '';
                if ($sectionId !== '') {
                    $bySection[$sectionId] = [];
                }
            }

            if ($bySection === []) {
                if (! $silent) {
                    $this->setFlashMessage('Select at least one analyst for a lab section already on the selected test(s).', 'warning');
                }

                return;
            }
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
            if ($silent) {
                $this->persistAssignments(reload: false);
            } else {
                $this->setFlashMessage('No selected tests include the chosen lab section(s). Assign sections first.', 'warning');
            }

            return;
        }

            if ($silent) {
                $this->persistAssignments(reload: false);

                return;
            }

        $this->clearRowSelection();
        $this->persistAssignments();
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
        $this->persistAssignments(reload: false);
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
        $this->acceptError = '';
    }

    public function closeAcceptConfirm(): void
    {
        $this->showAcceptConfirmModal = false;
        $this->isAccepting = false;
        $this->acceptError = '';
    }

    public function confirmAcceptSamples(): void
    {
        if ($this->enquiry === null || $this->isAccepting) {
            return;
        }

        $this->isAccepting = true;
        $this->acceptError = '';

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
            $this->acceptError = $this->userFacingAcceptFailureMessage($exception);

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
            $this->acceptError = 'Samples were accepted but the job number could not be created.';

            return;
        }

        $batch = \App\SampleHeader::query()->find($batchId);
        $batchCode = (string) ($batch?->batch_code ?? '');

        $redirectUrl = route('view-batch-details', [
            'batch' => $batchId,
            'client' => 0,
            'portal' => 0,
            'status' => (string) ($batch?->status ?: 'Samples In Lab'),
        ]).'#samples';

        $coverageSummary = $batch !== null
            ? app(JobPurchaseOrderCoverageService::class)->acceptanceSummary($batch)
            : null;

        $this->dispatch('acceptance-form-completed', redirectUrl: $redirectUrl);
        session()->flash('success', $coverageSummary !== null
            ? 'Samples accepted. '.$coverageSummary
            : "Samples accepted. Job number {$batchCode} created and moved to Samples In Lab.");
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

    public function getRegistrationLabelUrlProperty(): string
    {
        return route('submission-forms.instances.sample-collection-label', [
            'instance' => $this->instance->id,
            'type' => 'registration',
        ]);
    }

    public function getTrfPdfUrlProperty(): string
    {
        return route('test-request-form.pdf', $this->instance->id);
    }

    public function getTrfViewUrlProperty(): string
    {
        return route('submission-forms.instances.show', [
            'submissionForm' => $this->submissionFormId,
            'instance' => $this->instanceId,
        ]);
    }

    public function getQuotationPdfUrlProperty(): ?string
    {
        $quotation = $this->enquiry?->currentQuotation ?? $this->enquiry?->acceptedQuotation;
        if ($quotation === null) {
            return null;
        }

        return route('quotation.preview.pdf', ['id' => $quotation->id]);
    }

    public function openSampleInfoModal(?string $sampleKey = null): void
    {
        if (is_string($sampleKey) && $sampleKey !== '') {
            $this->selectSample($sampleKey);
        }

        $this->showSampleInfoModal = true;
    }

    public function closeSampleInfoModal(): void
    {
        $this->showSampleInfoModal = false;
    }

    public function openTestInfoModal(string $rowKey): void
    {
        $this->viewingTestInfoRowKey = $rowKey;
        $this->showTestInfoModal = true;
    }

    public function closeTestInfoModal(): void
    {
        $this->showTestInfoModal = false;
        $this->viewingTestInfoRowKey = '';
    }

    /**
     * @return array{test_label: string, fields: array<string, string>}
     */
    public function getViewingTestInfoProperty(): array
    {
        if ($this->viewingTestInfoRowKey === '') {
            return ['test_label' => '', 'fields' => []];
        }

        foreach ($this->testRows as $row) {
            if ((string) ($row['row_key'] ?? '') !== $this->viewingTestInfoRowKey) {
                continue;
            }

            $fields = is_array($row['test_info'] ?? null) ? $row['test_info'] : [];

            return [
                'test_label' => (string) ($row['test_label'] ?? 'Test'),
                'fields' => $fields,
            ];
        }

        return ['test_label' => '', 'fields' => []];
    }

    public function sampleCollectionLabelUrlForConfig(string $configKey): string
    {
        return route('submission-forms.instances.sample-collection-label', [
            'instance' => $this->instance->id,
            'type' => 'collection',
            'config_id' => $configKey,
        ]);
    }

    public function sampleRegistrationLabelUrlForConfig(string $configKey): string
    {
        return route('submission-forms.instances.sample-collection-label', [
            'instance' => $this->instance->id,
            'type' => 'registration',
            'config_id' => $configKey,
        ]);
    }

    public function openWorksheetModal(string $mode = 'pdf'): void
    {
        $this->worksheetModalMode = in_array($mode, ['pdf', 'excel'], true) ? $mode : 'pdf';
        $this->selectedWorksheetSectionIds = array_values(array_map(
            static fn (array $option): string => (string) ($option['id'] ?? ''),
            $this->worksheetSectionOptions,
        ));
        $this->showWorksheetModal = true;
    }

    public function closeWorksheetModal(): void
    {
        $this->showWorksheetModal = false;
        $this->worksheetModalMode = 'pdf';
    }

    /**
     * @return list<array{id: string, name: string, code: string, test_count: int, preview_number: string}>
     */
    public function getWorksheetSectionOptionsProperty(): array
    {
        $counts = [];
        foreach ($this->testRows as $row) {
            if (! empty($row['subcontracted'])) {
                continue;
            }
            foreach (($row['lab_section_ids'] ?? []) as $sectionId) {
                $sectionId = (string) $sectionId;
                if ($sectionId === '') {
                    continue;
                }
                $counts[$sectionId] = ($counts[$sectionId] ?? 0) + 1;
            }
        }

        if ($counts === []) {
            return [];
        }

        $sections = collect($this->labSections)
            ->filter(fn (array $section): bool => isset($counts[(string) ($section['id'] ?? '')]))
            ->values();

        $sequenceService = app(\App\Services\Sampleworkflow\LabSectionWorksheetSequenceService::class);

        return $sections->map(function (array $section) use ($counts, $sequenceService): array {
            $id = (string) ($section['id'] ?? '');
            $code = trim((string) ($section['code'] ?? ''));

            return [
                'id' => $id,
                'name' => (string) ($section['name'] ?? 'Lab section'),
                'code' => $code,
                'test_count' => (int) ($counts[$id] ?? 0),
                'preview_number' => $code !== '' ? $sequenceService->previewNextNumber($code) : '—',
            ];
        })->all();
    }

    public function issueWorksheetPdf(): void
    {
        $this->issueWorksheets('pdf');
    }

    public function issueWorksheetExcel(): void
    {
        $this->issueWorksheets('excel');
    }

    public function issueAndNotifyAnalysts(): void
    {
        $this->issueWorksheets('notify');
    }

    private function issueWorksheets(string $mode): void
    {
        $sectionIds = array_values(array_filter(array_map(
            static fn (mixed $id): string => trim((string) $id),
            $this->selectedWorksheetSectionIds,
        )));

        if ($sectionIds === []) {
            $this->setFlashMessage('Select at least one lab section.', 'warning');

            return;
        }

        if ($this->enquiry === null) {
            $this->setFlashMessage('No commercial enquiry is linked to this request.', 'warning');

            return;
        }

        $actingUser = Auth::user();
        if ($actingUser === null) {
            $this->setFlashMessage('You must be signed in to issue worksheets.', 'error');

            return;
        }

        try {
            $issued = app(LabSectionWorksheetIssueService::class)->issueForIntegrityCheck(
                $this->instance,
                $this->enquiry,
                $this->testRows,
                $sectionIds,
                $this->labSectionNames,
                $this->analystNamesById(),
                $actingUser,
                $mode,
            );
        } catch (\Throwable $exception) {
            $this->setFlashMessage(
                'Could not issue worksheets. '.$this->userFacingWorksheetFailureMessage($exception),
                'error',
            );

            return;
        }

        $this->closeWorksheetModal();

        if ($issued === []) {
            $this->setFlashMessage('No worksheets were issued for the selected lab sections.', 'warning');

            return;
        }

        if ($mode === 'pdf' || $mode === 'notify') {
            foreach ($issued as $item) {
                $pdfUrl = trim((string) ($item['pdf_url'] ?? ''));
                if ($pdfUrl !== '') {
                    $this->dispatch('open-integrity-worksheet-pdf', url: $pdfUrl);
                }
            }

            $count = count($issued);
            $this->setFlashMessage(
                $mode === 'notify'
                    ? "{$count} worksheet(s) issued and assigned analysts notified."
                    : "{$count} worksheet PDF(s) opened in new tab(s).",
                'success',
            );

            return;
        }

        foreach ($issued as $item) {
            $excelUrl = trim((string) ($item['excel_url'] ?? ''));
            if ($excelUrl !== '') {
                $this->dispatch('download-integrity-worksheet-excel', url: $excelUrl);
            }
        }

        $count = count($issued);
        $this->setFlashMessage("{$count} worksheet Excel file(s) ready to download.", 'success');
    }

    private function userFacingWorksheetFailureMessage(\Throwable $exception): string
    {
        $message = trim($exception->getMessage());
        $lower = strtolower($message);

        if (
            $message !== ''
            && (
                str_contains($lower, 'lab_section_worksheets')
                || str_contains($lower, 'does not exist')
                || str_contains($lower, 'relation')
            )
        ) {
            return 'Database tables are not ready. Run migrations locally, then try again.';
        }

        if (
            $message !== ''
            && ! str_contains($lower, 'sqlstate')
            && ! str_contains($lower, 'pgsql')
            && ! str_contains($lower, 'connection:')
        ) {
            return $message;
        }

        return 'An unexpected error occurred. Check lab section assignments and try again.';
    }

    protected function setFlashMessage(string $message, string $type = 'info'): void
    {
        $this->flashMessage = $message;
        $this->flashMessageType = $type;
        if ($message !== '' && $type !== 'error') {
            $this->toast($type, $message);
        }
    }

    private function userFacingAcceptFailureMessage(\Throwable $exception): string
    {
        $message = trim($exception->getMessage());
        $lower = strtolower($message);

        if (
            $message !== ''
            && ! str_contains($lower, 'sqlstate')
            && ! str_contains($lower, 'pgsql')
            && ! str_contains($lower, 'connection:')
        ) {
            return $message;
        }

        return 'Could not accept samples. Check that each sample has a sample type and tests, then try again.';
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

    public function getBulkSubcontractAllSelectedProperty(): bool
    {
        if ($this->selectedRowKeys === []) {
            return false;
        }

        $selected = array_flip($this->selectedRowKeys);
        foreach ($this->testRows as $row) {
            $rowKey = (string) ($row['row_key'] ?? '');
            if (! isset($selected[$rowKey])) {
                continue;
            }

            if (empty($row['subcontracted'])) {
                return false;
            }
        }

        return true;
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
     *     display_label: string,
     *     customer_sample_id: string,
     *     sample_condition_name: string,
     *     condition_not_acceptable: bool,
     *     total: int,
     *     complete: int,
     *     incomplete: int,
     *     subcontracted: int
     * }>
     */
    public function getSampleSummariesProperty(): array
    {
        $groups = [];
        $conditionByConfigId = [];

        if ($this->enquiry !== null) {
            $configs = app(SampleIntegrityCheckService::class)
                ->resolveConfigs($this->enquiry, $this->instance);
            $conditionByConfigId = TrfLabUseFieldsService::conditionNameByConfigId($configs);
        }

        foreach ($this->testRows as $row) {
            $key = (string) ($row['config_id'] ?? '');
            if ($key === '') {
                continue;
            }

            if (! isset($groups[$key])) {
                $conditionName = trim((string) ($conditionByConfigId[$key] ?? ''));
                $sampleDescription = trim((string) ($row['sample_description'] ?? ''));
                $sampleIndex = (int) ($row['sample_index'] ?? 0);
                $groups[$key] = [
                    'key' => $key,
                    'sample_index' => $sampleIndex,
                    'sample_description' => $sampleDescription,
                    'label' => $sampleDescription !== '' ? $sampleDescription : ('Sample '.$sampleIndex),
                    'display_label' => $sampleDescription,
                    'customer_sample_id' => (string) ($row['customer_sample_id'] ?? ''),
                    'sample_condition_name' => $conditionName,
                    'condition_not_acceptable' => TrfLabUseFieldsService::isNotAcceptableConditionName($conditionName),
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
     * @return array{
     *     form_number: string,
     *     request_number: string,
     *     client_name: string,
     *     form_name: string
     * }
     */
    public function getPageHeaderProperty(): array
    {
        $formNumber = trim((string) (
            $this->instance->getDocumentControlNumber()
            ?? $this->instance->form_number
            ?? ''
        ));

        $requestNumber = trim((string) (
            $this->enquiry?->formatted_number
            ?? $this->enquiry?->request_number
            ?? ''
        ));

        $clientName = trim((string) (
            $this->enquiry?->customer?->name
            ?? $this->instance->crmCustomer?->name
            ?? ''
        ));

        return [
            'form_number' => $formNumber,
            'request_number' => $requestNumber,
            'client_name' => $clientName,
            'form_name' => trim((string) ($this->submissionForm->name ?? '')),
        ];
    }

    /**
     * @return array{
     *     display_label: string,
     *     export_label: string,
     *     customer_sample_id: string,
     *     condition_name: string,
     *     condition_not_acceptable: bool,
     *     identity: list<array{label: string, value: string}>,
     *     tests: list<array<string, mixed>>
     * }
     */
    public function getActiveSampleDossierProperty(): array
    {
        if ($this->selectedSampleKey === '' || $this->enquiry === null) {
            return [
                'display_label' => '',
                'export_label' => '',
                'customer_sample_id' => '',
                'condition_name' => '',
                'condition_not_acceptable' => false,
                'client_title' => 'Client',
                'customer' => [],
                'customer_card' => [
                    'client_name' => '',
                    'contact_person' => '',
                    'email' => '',
                    'mobile' => '',
                    'address' => '',
                ],
                'collection' => [],
                'sample_info' => [],
                'sample_tests' => [],
                'identity' => [],
                'tests' => [],
            ];
        }

        return app(SampleIntegrityCheckService::class)->sampleDossierForConfig(
            $this->selectedSampleKey,
            $this->instance,
            $this->enquiry,
            $this->testRows,
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getFilteredTestRowsProperty(): array
    {
        $needle = mb_strtolower(trim($this->testSearch));
        $labSectionFilter = trim($this->filterLabSectionId);
        $analysisTypeFilter = trim($this->filterAnalysisType);
        $sampleTypeFilter = trim($this->filterSampleType);
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

            if ($labSectionFilter !== '') {
                $assignedSections = array_map('strval', $row['lab_section_ids'] ?? []);
                if (! in_array($labSectionFilter, $assignedSections, true)) {
                    continue;
                }
            }

            if ($analysisTypeFilter !== '') {
                if (trim((string) ($row['analysis_type'] ?? '')) !== $analysisTypeFilter) {
                    continue;
                }
            }

            if ($sampleTypeFilter !== '') {
                if (trim((string) ($row['sample_type'] ?? '')) !== $sampleTypeFilter) {
                    continue;
                }
            }

            if ($needle !== '') {
                $haystack = mb_strtolower(implode(' ', array_filter([
                    (string) ($row['test_label'] ?? ''),
                    (string) ($row['analysis_type'] ?? ''),
                    (string) ($row['sample_type'] ?? ''),
                    (string) ($row['method'] ?? ''),
                ])));
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
     * @return list<array<string, mixed>>
     */
    public function getVisibleTestRowsProperty(): array
    {
        $filtered = $this->filteredTestRows;
        $perPage = max(1, $this->testsPerPage);
        $lastPage = max(1, (int) ceil(count($filtered) / $perPage));
        $page = max(1, min($this->testsPage, $lastPage));
        $offset = ($page - 1) * $perPage;

        return array_values(array_slice($filtered, $offset, $perPage));
    }

    /**
     * @return array{
     *     total: int,
     *     from: int,
     *     to: int,
     *     current_page: int,
     *     last_page: int,
     *     per_page: int
     * }
     */
    public function getTestsPaginationProperty(): array
    {
        $total = count($this->filteredTestRows);
        $perPage = max(1, $this->testsPerPage);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, min($this->testsPage, $lastPage));
        $from = $total === 0 ? 0 : (($currentPage - 1) * $perPage) + 1;
        $to = $total === 0 ? 0 : min($total, $currentPage * $perPage);

        return [
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'per_page' => $perPage,
        ];
    }

    public function getActiveTestFilterCountProperty(): int
    {
        $count = 0;
        if ($this->testFilter !== 'all') {
            $count++;
        }
        if (trim($this->filterLabSectionId) !== '') {
            $count++;
        }
        if (trim($this->filterAnalysisType) !== '') {
            $count++;
        }
        if (trim($this->filterSampleType) !== '') {
            $count++;
        }

        return $count;
    }

    /**
     * @return list<string>
     */
    public function getFilterAnalysisTypeOptionsProperty(): array
    {
        $options = [];
        foreach ($this->testRows as $row) {
            if ((string) ($row['config_id'] ?? '') !== $this->selectedSampleKey) {
                continue;
            }
            $value = trim((string) ($row['analysis_type'] ?? ''));
            if ($value !== '') {
                $options[$value] = $value;
            }
        }
        $values = array_values($options);
        sort($values, SORT_NATURAL | SORT_FLAG_CASE);

        return $values;
    }

    /**
     * @return list<string>
     */
    public function getFilterSampleTypeOptionsProperty(): array
    {
        $options = [];
        foreach ($this->testRows as $row) {
            if ((string) ($row['config_id'] ?? '') !== $this->selectedSampleKey) {
                continue;
            }
            $value = trim((string) ($row['sample_type'] ?? ''));
            if ($value !== '') {
                $options[$value] = $value;
            }
        }
        $values = array_values($options);
        sort($values, SORT_NATURAL | SORT_FLAG_CASE);

        return $values;
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

    private function syncBulkAnalystSectionsFromSelection(): void
    {
        $options = $this->bulkAnalystSectionOptions;
        if ($options === []) {
            return;
        }

        $this->bulkAnalystLabSectionIds = array_values(array_filter(array_map(
            static fn (array $section): string => (string) ($section['id'] ?? ''),
            $options
        )));

        $this->hydrateBulkAnalystIdsFromSelection();
    }

    private function hydrateBulkAnalystIdsFromSelection(): void
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

    private function persistAssignments(bool $reload = true): void
    {
        if ($this->enquiry === null) {
            return;
        }

        $this->enquiry = app(SampleIntegrityCheckService::class)
            ->persistIntegrityAssignments($this->enquiry, $this->testRows);

        if ($reload) {
            $this->reloadRows();
        }
    }

    /**
     * @return array<string, string>
     */
    private function analystNamesById(): array
    {
        $analystNamesById = [];
        foreach ($this->analystsBySection as $sectionAnalysts) {
            foreach ($sectionAnalysts as $analyst) {
                $analystNamesById[(string) ($analyst['id'] ?? '')] = (string) ($analyst['name'] ?? '');
            }
        }

        return $analystNamesById;
    }

    private function safeExcelFilename(string $value): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_-]+/', '-', $value) ?? 'export';
        $safe = trim($safe, '-');

        return $safe !== '' ? $safe : 'export';
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

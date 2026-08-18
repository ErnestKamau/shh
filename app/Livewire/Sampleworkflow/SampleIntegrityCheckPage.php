<?php

namespace App\Livewire\Sampleworkflow;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\BatchResultsExcelImportService;
use App\Services\Sampleworkflow\CustomerAnalysisTypeStandardService;
use App\Services\Sampleworkflow\RequestTestExportDataService;
use App\Services\Sampleworkflow\RequestTestWorksheetPdfService;
use App\Services\Sampleworkflow\SampleIntegrityCheckService;
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

    /** @var array<string, array{label: string, sample_details: string, details: list<array{label: string, value: string}>, tests: list<string>}> */
    public array $sampleInfoByKey = [];

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

    public function getRegistrationLabelUrlProperty(): string
    {
        return route('submission-forms.instances.sample-collection-label', [
            'instance' => $this->instance->id,
            'type' => 'registration',
        ]);
    }

    /**
     * @return array{
     *     label: string,
     *     sample_details: string,
     *     details: list<array{label: string, value: string}>,
     *     tests: list<string>
     * }
     */
    public function sampleInfoForKey(string $configKey): array
    {
        if ($configKey === '' || $this->enquiry === null) {
            return [
                'label' => 'Sample',
                'sample_details' => '',
                'details' => [],
                'tests' => [],
            ];
        }

        if (! isset($this->sampleInfoByKey[$configKey])) {
            $this->sampleInfoByKey = app(SampleIntegrityCheckService::class)
                ->sampleInfoByConfigKey($this->instance, $this->enquiry, $this->testRows);
        }

        return $this->sampleInfoByKey[$configKey] ?? [
            'label' => 'Sample',
            'sample_details' => '',
            'details' => [],
            'tests' => [],
        ];
    }

    public function openSampleInfo(string $configKey, string $label): void
    {
        $info = $this->sampleInfoForKey($configKey);

        $this->dispatch(
            'integrity-sample-info-open',
            title: $label.' — Test & sample information',
            details: $info['sample_details'],
            fields: $info['details'],
            tests: $info['tests'],
        );
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
     *     customer_sample_id: string,
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
                    'customer_sample_id' => (string) ($row['customer_sample_id'] ?? ''),
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
            $this->sampleInfoByKey = [];

            return;
        }

        $this->testRows = app(SampleIntegrityCheckService::class)
            ->buildTestRows($this->enquiry, $this->instance);

        $this->sampleInfoByKey = [];

        $summaries = $this->sampleSummaries;
        $keys = array_column($summaries, 'key');
        if ($this->selectedSampleKey === '' || ! in_array($this->selectedSampleKey, $keys, true)) {
            $this->selectedSampleKey = (string) ($keys[0] ?? '');
        }
    }
}

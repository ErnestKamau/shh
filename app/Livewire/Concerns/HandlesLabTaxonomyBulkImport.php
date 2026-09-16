<?php

namespace App\Livewire\Concerns;

use App\Models\BulkImportBatch;
use App\Models\DataImportVersion;
use App\Services\BulkImportService;
use App\Services\ImportVersioning\DataImportVersionService;
use Illuminate\Support\Facades\Log;

/**
 * Requires the host Livewire component to use Livewire\WithFileUploads
 * and expose $message / $messageType properties.
 */
trait HandlesLabTaxonomyBulkImport
{
    public bool $showBulkImportModal = false;

    public bool $showBulkImportVersionsModal = false;

    /** @var mixed */
    public $bulkFile = null;

    /** @var list<array{id: string, version: int, status: string, applied_at: ?string, user_name: ?string, summary: string, notes: ?string}> */
    public array $bulkImportVersions = [];

    /**
     * Bulk import form type key used by BulkImportService / template factory.
     * One of: analyte, sample_type, analysis_type.
     */
    abstract protected function labTaxonomyBulkImportFormType(): string;

    /**
     * Human-readable entity label for modal copy and result messages.
     */
    abstract protected function labTaxonomyBulkImportEntityLabel(): string;

    /**
     * Optional default sample type when importing analysis types from a scoped page.
     */
    protected function labTaxonomyBulkImportDefaultSampleTypeId(): ?string
    {
        return null;
    }

    /**
     * Hook after a successful import attempt (refresh lists, reset page, etc.).
     */
    protected function afterLabTaxonomyBulkImport(): void
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function openBulkImportModal(): void
    {
        $this->showBulkImportModal = true;
        $this->bulkFile = null;
        $this->resetValidation('bulkFile');
    }

    public function closeBulkImportModal(): void
    {
        $this->showBulkImportModal = false;
        $this->bulkFile = null;
        $this->resetValidation('bulkFile');
    }

    public function downloadBulkImportTemplate()
    {
        $formType = $this->labTaxonomyBulkImportFormType();

        return app(BulkImportService::class)->generateTemplate('lab', $formType);
    }

    public function downloadBulkImportCurrentData()
    {
        $formType = $this->labTaxonomyBulkImportFormType();

        return app(BulkImportService::class)->generateCurrentDataExport('lab', $formType);
    }

    public function openBulkImportVersionsModal(): void
    {
        $this->refreshBulkImportVersions();
        $this->showBulkImportVersionsModal = true;
    }

    public function closeBulkImportVersionsModal(): void
    {
        $this->showBulkImportVersionsModal = false;
    }

    public function refreshBulkImportVersions(): void
    {
        $formType = $this->labTaxonomyBulkImportFormType();
        $scopeKey = DataImportVersion::labMasterScopeKey('lab', $formType);

        $this->bulkImportVersions = app(DataImportVersionService::class)
            ->listVersions(DataImportVersion::SCOPE_LAB_MASTER, $scopeKey)
            ->map(static function (DataImportVersion $version): array {
                $summary = $version->change_summary ?? [];
                $bits = [];
                if (isset($summary['imported'])) {
                    $bits[] = (int) $summary['imported'].' imported';
                }
                if (isset($summary['errors'])) {
                    $bits[] = (int) $summary['errors'].' errors';
                }

                return [
                    'id' => (string) $version->id,
                    'version' => (int) $version->version,
                    'status' => (string) $version->status,
                    'applied_at' => optional($version->applied_at)?->format('Y-m-d H:i'),
                    'user_name' => $version->user?->name,
                    'summary' => $bits !== [] ? implode(', ', $bits) : '—',
                    'notes' => $version->notes,
                ];
            })
            ->values()
            ->all();
    }

    public function downloadBulkImportVersion(string $versionId)
    {
        $version = DataImportVersion::query()->findOrFail($versionId);

        return app(DataImportVersionService::class)->downloadVersionFile(
            $version,
            'lab-'.$this->labTaxonomyBulkImportFormType().'-v'.$version->version.'.xlsx',
        );
    }

    public function restoreBulkImportVersion(string $versionId): void
    {
        $version = DataImportVersion::query()->findOrFail($versionId);
        $entityLabel = $this->labTaxonomyBulkImportEntityLabel();

        try {
            $result = app(BulkImportService::class)->rollbackLabMasterVersion($version);
        } catch (\Throwable $e) {
            Log::error('Lab taxonomy import version restore failed: '.$e->getMessage());
            $this->message = 'Failed to restore version: '.$e->getMessage();
            $this->messageType = 'danger';

            return;
        }

        $this->refreshBulkImportVersions();
        $this->afterLabTaxonomyBulkImport();

        if (! ($result['success'] ?? false)) {
            $this->message = (string) ($result['message'] ?? 'Restore failed.');
            $this->messageType = 'danger';

            return;
        }

        $newVersion = $result['version']->version ?? null;
        $this->message = "Restored {$entityLabel} data from version {$version->version}"
            .($newVersion ? " (saved as version {$newVersion})" : '')
            .'.';
        $this->messageType = 'success';
    }

    public function processBulkImport(): void
    {
        $this->validate([
            'bulkFile' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        $formType = $this->labTaxonomyBulkImportFormType();
        $entityLabel = $this->labTaxonomyBulkImportEntityLabel();
        $batch = null;

        try {
            $service = app(BulkImportService::class);
            $batch = $service->createBatch('lab', $formType);
            $results = $service->processImport($batch, $this->bulkFile, null, false);

            $this->closeBulkImportModal();
            $this->afterLabTaxonomyBulkImport();

            if (! ($results['success'] ?? false)) {
                $this->message = 'Error processing file: '.($results['message'] ?? 'Unknown error');
                $this->messageType = 'danger';

                return;
            }

            $batch = $results['batch'] ?? $batch;
            $imported = (int) ($batch->imported_rows ?? 0);
            $errors = (int) ($batch->error_rows ?? 0);
            $versionSuffix = ! empty($results['version'])
                ? ' Saved as version '.$results['version']->version.'.'
                : '';

            if ($errors > 0) {
                $summary = $batch->getErrorSummary();
                $errorMessage = implode(' | ', array_map(
                    fn (array $error): string => (string) ($error['message'] ?? 'Unknown error'),
                    array_slice($summary, 0, 5)
                ));
                if (count($summary) > 5) {
                    $errorMessage .= ' ... and more';
                }
                $this->message = "{$imported} {$entityLabel}(s) imported. {$errors} row(s) failed. Errors: {$errorMessage}{$versionSuffix}";
                $this->messageType = 'warning';
            } else {
                $this->message = "{$imported} {$entityLabel}(s) imported successfully.{$versionSuffix}";
                $this->messageType = 'success';
            }
        } catch (\Throwable $e) {
            Log::error('Lab taxonomy bulk import failed: '.$e->getMessage(), [
                'form_type' => $formType,
                'exception' => $e,
            ]);

            if ($batch instanceof BulkImportBatch) {
                try {
                    $batch->markAsFailed($e->getMessage());
                } catch (\Throwable) {
                }
            }

            $this->closeBulkImportModal();
            $this->message = 'Error processing file: '.$e->getMessage();
            $this->messageType = 'danger';
        }
    }
}

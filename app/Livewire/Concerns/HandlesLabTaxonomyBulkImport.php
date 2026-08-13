<?php

namespace App\Livewire\Concerns;

use App\Imports\Lab\AnalysisTypeImporter;
use App\Models\BulkImportBatch;
use App\Services\BulkImportService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Requires the host Livewire component to use Livewire\WithFileUploads
 * and expose $message / $messageType properties.
 */
trait HandlesLabTaxonomyBulkImport
{
    public bool $showBulkImportModal = false;

    /** @var mixed */
    public $bulkFile = null;

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
            $batch->status = 'processing';
            $batch->started_at = now();
            $batch->save();

            $importerClass = $service->getImporterClass('lab', $formType);
            if (! class_exists($importerClass)) {
                throw new \RuntimeException("Importer class {$importerClass} not found");
            }

            $defaultSampleTypeId = $this->labTaxonomyBulkImportDefaultSampleTypeId();
            if ($formType === 'analysis_type' && $importerClass === AnalysisTypeImporter::class) {
                $importer = new AnalysisTypeImporter($batch, null, $defaultSampleTypeId);
            } else {
                $importer = new $importerClass($batch, null);
            }

            if (method_exists($importer, 'resetBatchCounters')) {
                $importer->resetBatchCounters();
            }

            $sheets = Excel::toCollection($importer, $this->bulkFile);
            foreach ($sheets as $sheet) {
                if ($sheet->isEmpty()) {
                    continue;
                }
                $importer->collection($sheet);
            }

            $batch->refresh();
            $batch->markAsCompleted();

            $this->closeBulkImportModal();
            $this->afterLabTaxonomyBulkImport();

            $imported = (int) ($batch->imported_rows ?? 0);
            $errors = (int) ($batch->error_rows ?? 0);

            if ($errors > 0) {
                $summary = $batch->getErrorSummary();
                $errorMessage = implode(' | ', array_map(
                    fn (array $error): string => (string) ($error['message'] ?? 'Unknown error'),
                    array_slice($summary, 0, 5)
                ));
                if (count($summary) > 5) {
                    $errorMessage .= ' ... and more';
                }
                $this->message = "{$imported} {$entityLabel}(s) imported. {$errors} row(s) failed. Errors: {$errorMessage}";
                $this->messageType = 'warning';
            } else {
                $this->message = "{$imported} {$entityLabel}(s) imported successfully.";
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

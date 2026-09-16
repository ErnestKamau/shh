<?php

namespace App\Services;

use App\Models\BulkImportBatch;
use App\Models\DataImportVersion;
use App\Services\ImportVersioning\DataImportVersionService;
use App\Services\ImportVersioning\LabMasterCurrentDataExporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImportService
{
    /**
     * Get all available modules for bulk import.
     */
    public function getAvailableModules(): array
    {
        return [
            'lab' => [
                'name' => 'Lab Management',
                'forms' => [
                    'sample_type_category' => 'Sample Type Category',
                    'sample_type' => 'Sample Type',
                    'analysis_type' => 'Analysis Type',
                    'analyte' => 'Analyte',
                    'sample_condition' => 'Sample Condition (requires Sample Type)',
                    'standard' => 'Standard & Analytes',
                    'pricelist' => 'Pricelist & Items',
                    'analysis_method' => 'Analysis Methods (Reference + Laboratory Test)',
                    'amspec_parameters' => 'Amspec Parameters (Sample Types → Analysis Types → Parameters)',
                ],
            ],
            'equipment' => [
                'name' => 'Equipment Management',
                'forms' => [
                    'asset_type' => 'Asset Type',
                    'asset_location' => 'Asset Location',
                    'equipment' => 'Equipment',
                ],
            ],
            'personnel' => [
                'name' => 'Personnel Management',
                'forms' => [
                    'department' => 'Department',
                    'user' => 'Personnel (User)',
                ],
            ],
            'crm' => [
                'name' => 'CRM Management',
                'forms' => [
                    'customer' => 'Customer',
                ],
            ],
            'inventory' => [
                'name' => 'Inventory Management',
                'forms' => [
                    'inventory' => 'Inventory Categories & Items',
                ],
            ],
        ];
    }

    /**
     * Generate template for a given form type.
     */
    public function generateTemplate(string $module, string $formType): StreamedResponse|BinaryFileResponse
    {
        $templateFactory = app(\App\Factories\BulkImportTemplateFactory::class);
        $templateGenerator = $templateFactory->createTemplateGenerator($module, $formType);
        
        $filename = "{$module}_{$formType}_template_" . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download($templateGenerator, $filename);
    }

    /**
     * Download current database rows for a lab master-data form.
     */
    public function generateCurrentDataExport(string $module, string $formType): StreamedResponse|BinaryFileResponse
    {
        $companyId = null;
        if (function_exists('getUserCompany')) {
            $companyId = getUserCompany();
        }
        $companyId ??= Auth::user()?->company_id;

        return app(LabMasterCurrentDataExporter::class)->download($module, $formType, $companyId ? (string) $companyId : null);
    }

    /**
     * Create a new import batch.
     */
    public function createBatch(string $module, string $formType): BulkImportBatch
    {
        $resolvedCompanyId = null;
        if (function_exists('getUserCompany')) {
            $resolvedCompanyId = getUserCompany();
        }

        $resolvedCompanyId ??= Auth::user()?->company_id;

        if (empty($resolvedCompanyId)) {
            throw new \RuntimeException('Unable to resolve company context for bulk import.');
        }

        $batch = new BulkImportBatch();
        $batch->company_id = (string) $resolvedCompanyId;
        $batch->user_id = Auth::id();
        $batch->module = $module;
        $batch->form_type = $formType;
        $batch->status = 'pending';
        $batch->save();

        return $batch;
    }

    /**
     * Process an uploaded file for a given batch.
     */
    public function processImport(BulkImportBatch $batch, $uploadedFile, ?string $zoneId = null, bool $replaceExisting = false)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $versionService = app(DataImportVersionService::class);
        $scopeKey = DataImportVersion::labMasterScopeKey($batch->module, $batch->form_type);
        $nextVersion = $versionService->nextVersionNumber(DataImportVersion::SCOPE_LAB_MASTER, $scopeKey);
        $preSnapshotPath = null;
        $storedUploadPath = null;

        try {
            $batch->status = 'processing';
            $batch->started_at = now();
            $batch->save();

            if ($batch->module === 'lab') {
                $preRelative = 'import-versions/'.DataImportVersion::SCOPE_LAB_MASTER.'/'
                    .preg_replace('/[^A-Za-z0-9_-]+/', '-', $scopeKey)."/v{$nextVersion}/pre-apply-snapshot.xlsx";
                try {
                    app(LabMasterCurrentDataExporter::class)->storeToPath(
                        $batch->module,
                        $batch->form_type,
                        (string) $batch->company_id,
                        $preRelative,
                    );
                    $preSnapshotPath = $preRelative;
                } catch (\Throwable $e) {
                    \Log::warning('Failed to store pre-apply snapshot for bulk import', [
                        'batch_id' => $batch->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                if ($uploadedFile instanceof UploadedFile) {
                    $storedUploadPath = $versionService->storeUploadedFile(
                        DataImportVersion::SCOPE_LAB_MASTER,
                        $scopeKey,
                        $uploadedFile,
                        $nextVersion,
                        'upload.xlsx',
                    );
                    $batch->file_path = $storedUploadPath;
                    $batch->save();
                }
            }

            $purgeSummary = null;
            if ($replaceExisting && $batch->module === 'lab' && in_array($batch->form_type, ['lab_hierarchy', 'amspec_parameters'], true)) {
                $purgeSummary = app(\App\Services\Lab\LabHierarchyPurgeService::class)
                    ->purgeForCompany((string) $batch->company_id);
            }

            if ($replaceExisting && $batch->module === 'lab' && $batch->form_type === 'analysis_method') {
                $purgeSummary = app(\App\Services\Lab\AnalysisMethodPurgeService::class)
                    ->purgeForCompany((string) $batch->company_id);
            }

            if ($replaceExisting && $batch->module === 'lab' && $batch->form_type === 'analysis_type') {
                $purgeSummary = app(\App\Services\Lab\LabHierarchyPurgeService::class)
                    ->purgeAnalysisTypesForCompany((string) $batch->company_id);
            }

            if ($replaceExisting && $batch->module === 'lab' && $batch->form_type === 'analyte') {
                $purgeSummary = app(\App\Services\Lab\LabHierarchyPurgeService::class)
                    ->purgeAnalytesForCompany((string) $batch->company_id);
            }

            if ($replaceExisting && $batch->module === 'equipment' && $batch->form_type === 'equipment') {
                $purgeSummary = app(\App\Services\Equipment\EquipmentPurgeService::class)
                    ->purgeForCompany((string) $batch->company_id);
            }

            if ($replaceExisting && $batch->module === 'crm' && $batch->form_type === 'customer') {
                $purgeSummary = app(\App\Services\CRM\CustomerPurgeService::class)
                    ->purgeAll();
            }

            $importerClass = $this->getImporterClass($batch->module, $batch->form_type);
            
            if (!class_exists($importerClass)) {
                throw new \Exception("Importer class {$importerClass} not found");
            }

            $importer = new $importerClass($batch, $zoneId);
            
            // Reset counters before starting multi-sheet import
            if (method_exists($importer, 'resetBatchCounters')) {
                $importer->resetBatchCounters();
            }

            // Use toCollection to get all sheets
            $sheets = Excel::toCollection($importer, $uploadedFile);
            
            foreach ($sheets as $sheet) {
                // Skip empty sheets or sheets that don't have the required headers
                // (shouldSkipRow inside collection() handles detailed row skipping)
                if ($sheet->isEmpty()) {
                    continue;
                }
                
                $importer->collection($sheet);
            }

            $batch->markAsCompleted();

            $version = null;
            if ($batch->module === 'lab' && $storedUploadPath !== null && in_array($batch->status, ['completed', 'completed_with_errors'], true)) {
                $upserted = $batch->getUpsertSummary();
                $version = $versionService->recordAppliedVersion(
                    DataImportVersion::SCOPE_LAB_MASTER,
                    $scopeKey,
                    $storedUploadPath,
                    $preSnapshotPath,
                    [
                        'imported' => (int) $batch->imported_rows,
                        'errors' => (int) $batch->error_rows,
                        'total' => (int) $batch->total_rows,
                        'upserted' => $upserted,
                    ],
                    [
                        'company_id' => (string) $batch->company_id,
                        'bulk_import_batch_id' => (string) $batch->id,
                    ],
                    Auth::user(),
                );
            }

            return [
                'success' => true,
                'batch' => $batch,
                'version' => $version,
                'summary' => [
                    'total_rows' => $batch->total_rows,
                    'imported_rows' => $batch->imported_rows,
                    'error_rows' => $batch->error_rows,
                    'errors' => $batch->getErrorSummary(),
                    'warnings' => $batch->getWarningSummary(),
                    'upserted' => $batch->getUpsertSummary(),
                    'purge_summary' => $purgeSummary,
                ],
            ];
        } catch (\Exception $e) {
            $batch->markAsFailed($e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'batch' => $batch,
            ];
        }
    }

    /**
     * Re-apply a stored lab master import version (append-only history).
     *
     * @return array{success: bool, batch?: BulkImportBatch, version?: DataImportVersion|null, message?: string, summary?: array}
     */
    public function rollbackLabMasterVersion(DataImportVersion $version, ?string $zoneId = null): array
    {
        if ($version->scope !== DataImportVersion::SCOPE_LAB_MASTER) {
            return ['success' => false, 'message' => 'Version is not a lab master-data import.'];
        }

        if (! $version->fileExists()) {
            return ['success' => false, 'message' => 'Version file is missing from storage.'];
        }

        [$module, $formType] = array_pad(explode(':', $version->scope_key, 2), 2, null);
        if ($module === null || $formType === null) {
            return ['success' => false, 'message' => 'Invalid version scope key.'];
        }

        $batch = $this->createBatch($module, $formType);
        $absolute = app(DataImportVersionService::class)->absolutePath($version->file_path);
        $uploaded = new UploadedFile(
            $absolute,
            'restore-v'.$version->version.'.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $result = $this->processImport($batch, $uploaded, $zoneId, false);
        if (($result['success'] ?? false) && $version->status !== DataImportVersion::STATUS_APPLIED) {
            app(DataImportVersionService::class)->markSupersededAsRolledBack($version);
        }

        return $result;
    }

    /**
     * Get the importer class for a given module and form type.
     */
    public function getImporterClass(string $module, string $formType): string
    {
        $formTypeMap = [
            'lab' => [
                'pricelist' => 'App\Imports\Lab\PricelistImporter',
                'analyte' => 'App\Imports\Lab\AnalyteImporter',
                'lab' => 'App\Imports\Lab\LabImporter',
                'sample_type_category' => 'App\Imports\Lab\SampleTypeCategoryImporter',
                'sample_type' => 'App\Imports\Lab\SampleTypeImporter',
                'analysis_type' => 'App\Imports\Lab\AnalysisTypeImporter',
                'analysis_elements' => 'App\Imports\Lab\AnalysisElementsImporter',
                'standard' => 'App\Imports\Lab\StandardImporter',
                'sample_condition' => 'App\Imports\Lab\SampleConditionImporter',
                'lab_hierarchy' => 'App\Imports\Lab\UnifiedLabHierarchyImporter',
                'amspec_parameters' => 'App\Imports\Lab\AmspecParametersImporter',
                'analysis_method' => 'App\Imports\Lab\AnalysisMethodImporter',
            ],
            'equipment' => [
                'asset_type' => 'App\Imports\Equipment\AssetTypeImporter',
                'asset_location' => 'App\Imports\Equipment\AssetLocationImporter',
                'equipment' => 'App\Imports\Equipment\EquipmentImporter',
            ],
            'personnel' => [
                'department' => 'App\Imports\Personnel\DepartmentImporter',
                'user' => 'App\Imports\Personnel\UserImporter',
            ],
            'crm' => [
                'customer' => 'App\Imports\CRM\CRMCustomerImporter',
            ],
            'inventory' => [
                'inventory' => 'App\Imports\Inventory\InventoryItemImporter',
            ],
        ];

        return $formTypeMap[$module][$formType] ?? 'App\Imports\BaseImporter';
    }

    /**
     * Decrypt field value if needed for template generation.
     */
    public function decryptFieldIfNeeded($value, string $fieldName): string
    {
        $encryptedFields = ['code', 'email', 'phone', 'phone1', 'phone2', 'postal_address'];
        
        if (in_array($fieldName, $encryptedFields) && $value) {
            try {
                return decrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }
        
        return $value;
    }

    /**
     * Encrypt field value for storage if needed.
     */
    public function encryptFieldIfNeeded($value, string $fieldName): mixed
    {
        $encryptedFields = ['code', 'email', 'phone', 'phone1', 'phone2', 'postal_address'];
        
        if (in_array($fieldName, $encryptedFields) && $value) {
            try {
                return encrypt($value);
            } catch (\Exception $e) {
                return $value;
            }
        }
        
        return $value;
    }

    /**
     * Get batch results for display.
     */
    public function getBatchResults(BulkImportBatch $batch): array
    {
        return [
            'batch_id' => $batch->id,
            'module' => $batch->module,
            'form_type' => $batch->form_type,
            'status' => $batch->status,
            'total_rows' => $batch->total_rows,
            'imported_rows' => $batch->imported_rows,
            'error_rows' => $batch->error_rows,
            'completed_at' => $batch->completed_at,
            'errors' => $batch->getErrorSummary(),
            'upserted_summary' => $batch->getUpsertSummary(),
        ];
    }
}

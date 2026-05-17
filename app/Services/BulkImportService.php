<?php

namespace App\Services;

use App\Models\BulkImportBatch;
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
                    'pricelist' => 'Pricelist & Items',
                    'analyte' => 'Analyte',
                    'lab' => 'Lab (with Zone Hierarchy)',
                    'sample_type' => 'Sample Type',
                    'analysis_type' => 'Analysis Type',
                    'analysis_elements' => 'Analysis Elements',
                    'standard' => 'Standard & Analytes',
                    'sample_condition' => 'Sample Condition',
                    'lab_hierarchy' => 'Unified Lab Hierarchy (Sample Type -> Analysis Type -> Analysis Elements -> Analytes & Standards)',
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
     * Create a new import batch.
     */
    public function createBatch(string $module, string $formType): BulkImportBatch
    {
        $batch = new BulkImportBatch();
        $batch->company_id = Auth::user()->company_id ?? Auth::user()->inventory_location_id;
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
    public function processImport(BulkImportBatch $batch, $uploadedFile, ?string $zoneId = null)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        try {
            $batch->status = 'processing';
            $batch->started_at = now();
            $batch->save();

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

            return [
                'success' => true,
                'batch' => $batch,
                'summary' => [
                    'total_rows' => $batch->total_rows,
                    'imported_rows' => $batch->imported_rows,
                    'error_rows' => $batch->error_rows,
                    'errors' => $batch->getErrorSummary(),
                    'upserted' => $batch->getUpsertSummary(),
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
     * Get the importer class for a given module and form type.
     */
    public function getImporterClass(string $module, string $formType): string
    {
        $formTypeMap = [
            'lab' => [
                'pricelist' => 'App\Imports\Lab\PricelistImporter',
                'analyte' => 'App\Imports\Lab\UnifiedLabHierarchyImporter',
                'lab' => 'App\Imports\Lab\LabImporter',
                'sample_type' => 'App\Imports\Lab\UnifiedLabHierarchyImporter',
                'analysis_type' => 'App\Imports\Lab\AnalysisTypeImporter',
                'analysis_elements' => 'App\Imports\Lab\AnalysisElementsImporter',
                'standard' => 'App\Imports\Lab\UnifiedLabHierarchyImporter',
                'sample_condition' => 'App\Imports\Lab\SampleConditionImporter',
                'lab_hierarchy' => 'App\Imports\Lab\UnifiedLabHierarchyImporter',
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

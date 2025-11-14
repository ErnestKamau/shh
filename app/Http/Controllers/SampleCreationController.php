<?php

namespace App\Http\Controllers;

use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use App\Services\SampleCreationService;
use App\SampleDate;
use App\AnalysisType;
use App\AnalysisElements;
use App\SampleAnalysisTypeRelationView;
use App\Lab;
use App\ReportingUnit;
use App\StandardAnalytes;
use App\SampleAnalysisTypeRelation;
use App\SampleDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SampleCreationController extends Controller
{
    protected $sampleCreationService;

    public function __construct(SampleCreationService $sampleCreationService)
    {
        $this->middleware('auth');
        $this->sampleCreationService = $sampleCreationService;
    }

    /**
     * Create samples from a submitted form instance
     */
    public function createFromForm(Request $request, SubmissionFormInstance $instance)
    {
        try {
            // Get sample batches from form 
            
            // dd($request->all());

            $sampleBatches = $instance->getAllFieldsWithValues();

            // Debug: Log the structure of sampleBatches to see what's available
            Log::info('Sample batches structure', [
                'total_batches' => count($sampleBatches),
                'first_batch_sample_header' => $sampleBatches[0]['sample_header'] ?? 'No sample header',
                'first_batch_sample_details_count' => count($sampleBatches[0]['sample_details'] ?? []),
                'available_keys_sample_header' => array_keys($sampleBatches[0]['sample_header'] ?? [])
            ]);
            
            if (empty($sampleBatches)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sample data found in the form instance'
                ], 400);
            }

            $createdBatches = [];
            
            // Count total batches for smart code generation
            $totalBatchCount = count($sampleBatches);

            foreach ($sampleBatches as $batchIndex => $batch) {
                // Log the batch data for debugging
                Log::info('Processing sample batch', [
                    'batch_index' => $batchIndex,
                    'sample_header' => $batch['sample_header'],
                    'sample_details_count' => count($batch['sample_details']),
                    'total_batch_count' => $totalBatchCount
                ]);


                // Create sample header using our custom method with batch count
                $sampleHeader = $this->createSampleHeader($batch['sample_header'], $instance->id, $totalBatchCount);
                
                // Update the sample header with the instance ID
                $sampleHeader->submission_form_instance_id = $instance->id;
                $sampleHeader->save();
                
                $analysisTypeIds = $this->extractAnalysisTypeIds($batch['sample_details']);
                $this->applyLabMetadataAndDates($sampleHeader, $analysisTypeIds);
                $this->createStagingEntry($sampleHeader, $batch, $analysisTypeIds);

                $createdBatches[] = [
                    'batch_id' => $sampleHeader->id,
                    'batch_code' => $sampleHeader->batch_code,
                    'sample_type_id' => $batch['sample_header']['sample_type_id'] ?? 'Unknown',
                    'sample_count' => count($batch['sample_details']),
                    'staged' => true
                ];

            }

            return response()->json([
                'success' => true,
                'message' => 'Samples created successfully',
                'data' => [
                    'total_batches' => count($createdBatches),
                    'batches' => $createdBatches
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating samples from form instance', [
                'instance_id' => $instance->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating samples: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create request object for batch header creation
     */
    private function createBatchHeaderRequest(array $sampleHeader)
    {
        $request = new \Illuminate\Http\Request();
        
        // Log the sample header data for debugging
        Log::info('Sample header data received', [
            'sample_header' => $sampleHeader,
            'available_keys' => array_keys($sampleHeader)
        ]);
        
        // Helper function to get single value from array or return the value itself
        $getSingleValue = function($value) {
            if (is_array($value)) {
                return !empty($value) ? $value[0] : null;
            }
            return $value;
        };
        
        // Helper function to ensure integer values
        $getIntegerValue = function($value) use ($getSingleValue) {
            $singleValue = $getSingleValue($value);
            return is_numeric($singleValue) ? (int) $singleValue : null;
        };
        
        // Helper function to safely get array value
        $getArrayValue = function($key, $default = []) use ($sampleHeader) {
            return isset($sampleHeader[$key]) && is_array($sampleHeader[$key]) ? $sampleHeader[$key] : $default;
        };
        
        // Map form data to expected fields, ensuring single values
        // Map form data to expected fields, ensuring single values and correct mapping to sampleheader table columns
        // Reference: sampleheader table columns from ddl.sql
        $requestData = [
            'crm_customer_id'      => $getIntegerValue($sampleHeader['crm_customer_id'] ?? null), // maps to crm_customer_id
            'sample_type_id'       => $getIntegerValue($sampleHeader['sample_type_id'] ?? null),   // maps to sample_type_id
            'receipt_date'         => $getSingleValue($sampleHeader['receipt_date'] ?? now()->format('Y-m-d')), // maps to receipt_date
            'date_collected'       => $getSingleValue($sampleHeader['date_collected'] ?? now()->format('Y-m-d')), // maps to date_collected
            'batch_scope'          => $getSingleValue($sampleHeader['batch_scope'] ?? 'General Analysis'), // maps to batch_scope
            'customer_survey'      => $getSingleValue($sampleHeader['customer_survey'] ?? ''), // maps to customer_survey
            'quote_no'             => $getSingleValue($sampleHeader['quote_no'] ?? ''), // maps to quote_no
            'batch_instructions'   => $getSingleValue($sampleHeader['batch_instructions'] ?? ''), // maps to batch_instructions
            'sampling_method_id'   => $getIntegerValue($sampleHeader['sampling_method_id'] ?? null), // maps to sampling_method_id
            'require_mu'           => $getIntegerValue($sampleHeader['require_mu'] ?? 0), // maps to require_mu
            'payment_done_by'      => $getSingleValue($sampleHeader['payment_done_by'] ?? 'Client'), // maps to payment_done_by
            'condition_quality_sample' => $getSingleValue($sampleHeader['condition_quality_sample'] ?? 'Good'), // maps to condition_quality_sample
            'crm_contact_id'       => $getIntegerValue($sampleHeader['crm_contact_id'] ?? null), // maps to crm_contact_id
            'customer_email'       => $getSingleValue($sampleHeader['customer_email'] ?? ''), // maps to customer_email
            'description'          => $getSingleValue($sampleHeader['description'] ?? ''), // maps to description
            'document_number'      => $getSingleValue($sampleHeader['document_number'] ?? ''), // maps to document_number
            'importer_address'     => $getSingleValue($sampleHeader['importer_address'] ?? ''), // maps to importer_address
            'date_expected'        => $getSingleValue($sampleHeader['date_expected'] ?? now()->addDays(7)->format('Y-m-d')), // maps to date_expected
            'quote_id'             => $getIntegerValue($sampleHeader['quote_id'] ?? null), // maps to quote_id
            'radio_active_levels'  => $getSingleValue($sampleHeader['radio_active_levels'] ?? ''), // maps to radio_active_levels
            'receive_by'           => $getSingleValue($sampleHeader['receive_by'] ?? (auth()->user()->name ?? 'System')), // maps to receive_by
            'sample_by'            => $getSingleValue($sampleHeader['sample_by'] ?? (auth()->user()->name ?? 'System')), // maps to sample_by
            'reference_number'     => $getSingleValue($sampleHeader['reference_number'] ?? 'n/a'), // maps to reference_number
            'is_routine'           => $getIntegerValue($sampleHeader['is_routine'] ?? 0), // maps to is_routine
            'routine_frequency'    => $getIntegerValue($sampleHeader['routine_frequency'] ?? 0), // maps to routine_frequency
            'is_client_order'      => $getIntegerValue($sampleHeader['is_client_order'] ?? 0), // maps to is_client_order (should be int/tinyint)
            'lab_section_ids'      => $getArrayValue('lab_section_ids', []), // not a direct column, but may be used for relations
            'crm_unit_name'        => $getSingleValue($sampleHeader['crm_unit_name'] ?? null), // maps to crm_unit_name
        ];
        
        // Log the processed request data
        Log::info('Processed request data', [
            'request_data' => $requestData
        ]);
        
        $request->merge($requestData);

        return $request;
    }

    /**
     * Create a sample header directly
     */
    private function createSampleHeader(array $sampleHeaderData, $submissionFormInstanceId = null, $batchCount = 1)
    {
        // Helper function to get single value from array or return the value itself
        $getSingleValue = function($value) {
            if (is_array($value)) {
                return !empty($value) ? $value[0] : null;
            }
            return $value;
        };
        
        // Helper function to ensure integer values
        $getIntegerValue = function($value) use ($getSingleValue) {
            $singleValue = $getSingleValue($value);
            return is_numeric($singleValue) ? (int) $singleValue : null;
        };

        $formatDate = function($value, $default = null) use ($getSingleValue) {
            $singleValue = $getSingleValue($value);
            if (!$singleValue) {
                return $default;
            }

            try {
                return Carbon::parse($singleValue)->format('Y-m-d');
            } catch (\Throwable $th) {
                return $default;
            }
        };

        // Handle missing CRM unit - get first unit for the customer
        $crmCustomerId = $getIntegerValue($sampleHeaderData['crm_customer_id'] ?? null);
        $crmUnit = $getSingleValue($sampleHeaderData['crm_unit_name'] ?? null);
        $crmUnitId = '';
        $crmUnitName = '';
        
        if ($crmCustomerId && !$crmUnit) {
            $firstUnit = \App\Models\CRM\CRMCompanyUnit::where('crm_customer_id', $crmCustomerId)->first();
            if ($firstUnit) {
                $crmUnitName = $firstUnit->name;
                $crmUnitId = $firstUnit->id;
                Log::info('Auto-selected first CRM unit for customer', [
                    'customer_id' => $crmCustomerId,
                    'unit_name' => $crmUnitName,
                    'unit_id' => $firstUnit->id
                ]);
            }
        }else{
            $unit = \App\Models\CRM\CRMCompanyUnit::find(intval($crmUnit));
            $crmUnitId = isset($unit) ? $unit->id : '';
            $crmUnitName = isset($unit) ? $unit->name : '';
            Log::info('Auto-selected CRM unit for customer', [
                'customer_id' => $crmCustomerId,
                'unit_name' => $crmUnitName,
                'unit_id' => $crmUnitId
            ]);
        }

        // Generate batch code with smart logic
        $batchCode = $this->generateBatchCode($sampleHeaderData, $submissionFormInstanceId, $batchCount);
        
        // Create the sample header
        $sampleHeader = new SampleHeader();
        Log::info('Sample header data', [
            'sample_header_data' => $sampleHeaderData
        ]);
        $sampleHeader->fill([
            'crm_customer_id' => $getIntegerValue($sampleHeaderData['crm_customer_id'] ?? null),
            'sample_type_id' => $getIntegerValue($sampleHeaderData['sample_type_id'] ?? null),
            'batch_code' => $batchCode,
            'receipt_date' => $formatDate($sampleHeaderData['receipt_date'] ?? null, now()->toDateString()),
            'date_collected' => $formatDate($sampleHeaderData['date_collected'] ?? null, now()->toDateString()),
            'batch_scope' => $getSingleValue($sampleHeaderData['batch_scope'] ?? 'General Analysis'),
            'customer_survey' => $getSingleValue($sampleHeaderData['customer_survey'] ?? ''),
            'quote_no' => $getSingleValue($sampleHeaderData['quote_no'] ?? ''),
            'batch_instructions' => $getSingleValue($sampleHeaderData['batch_instructions'] ?? ''),
            'sampling_method_id' => $getIntegerValue($sampleHeaderData['sampling_method_id'] ?? null),
            'require_mu' => $getIntegerValue($sampleHeaderData['require_mu'] ?? 0),
            'payment_done_by' => $getSingleValue($sampleHeaderData['payment_done_by'] ?? 'Client'),
            'condition_quality_sample' => $getSingleValue($sampleHeaderData['condition_quality_sample'] ?? 'Good'),
            'crm_contact_id' => $getIntegerValue($sampleHeaderData['crm_contact_id'] ?? null),
            'customer_email' => $getSingleValue($sampleHeaderData['customer_email'] ?? ''),
            'description' => $getSingleValue($sampleHeaderData['description'] ?? ''),
            'document_number' => $getSingleValue($sampleHeaderData['document_number'] ?? ''),
            'importer_address' => $getSingleValue($sampleHeaderData['importer_address'] ?? ''),
            'date_expected' => $formatDate($sampleHeaderData['date_expected'] ?? null, now()->addDays(7)->toDateString()),
            'quote_id' => $getIntegerValue($sampleHeaderData['quote_id'] ?? null),
            'radio_active_levels' => $getSingleValue($sampleHeaderData['radio_active_levels'] ?? ''),
            'receiving_officer_name' => $getSingleValue($sampleHeaderData['receiving_officer']) ? getUserById($getSingleValue($sampleHeaderData['receiving_officer']))->name : auth()->user()->name ?? 'System',
            'receiving_officer' => $getSingleValue($sampleHeaderData['receiving_officer']),
            'sampling_officer_name' => $getSingleValue($sampleHeaderData['sampling_officer_name'] ),
            'reference_number' => $getSingleValue($sampleHeaderData['reference_number'] ?? 'n/a'),
            'is_routine' => $getIntegerValue($sampleHeaderData['is_routine'] ?? 0),
            'routine_frequency' => $getIntegerValue($sampleHeaderData['routine_frequency'] ?? 0),
            'is_client_order' => $getIntegerValue($sampleHeaderData['is_client_order'] ?? 0),
            'submit_by' => $getSingleValue($sampleHeaderData['submit_by']),
            'crm_unit_name' => $crmUnitName,
            'crm_unit_id' => $crmUnitId,
            'lab_capable' => 1,
            'client_instruction_clear' => 1,
            
            'status' => 'Samples In Lab',
            'radio_active_levels' => date('H:i:s', strtotime($getSingleValue($sampleHeaderData['radio_active_levels'] ?? now()->format('Y-m-d H:i:s')) ?? '')),
            'submission_form_instance_id' => null, // Will be set by the calling method
        ]);
        
        $sampleHeader->save();
        
        // Create chain of custody for sample creation
        $this->createChainOfCustody($sampleHeader, 'Sample Creation');
        
        Log::info('Created sample header', [
            'header_id' => $sampleHeader->id,
            'batch_code' => $sampleHeader->batch_code,
            'sample_type_id' => $sampleHeader->sample_type_id,
            'crm_customer_id' => $sampleHeader->crm_customer_id,
            'crm_unit_name' => $sampleHeader->crm_unit_name,
            'batch_count' => $batchCount
        ]);
        
        return $sampleHeader;
    }
    
    /**
     * Create chain of custody entry for sample creation
     */
    private function createChainOfCustody($sampleHeader, $action = 'Sample Creation')
    {
        $custody = new \App\ChainOfCustody();
        $custody->workflow_stage = $sampleHeader->status;
        $custody->tracking_stage_id = $sampleHeader->sample_tracking_stage ?? 1;
        $custody->moved_in_by = auth()->user()->id;
        $custody->sample_header_id = $sampleHeader->id;
        $custody->comments = $action . ' - Created from submission form';
        $custody->save();
        
        Log::info('Created chain of custody', [
            'sample_header_id' => $sampleHeader->id,
            'action' => $action,
            'workflow_stage' => $sampleHeader->status
        ]);
        
        return $custody;
    }

    /**
     * Create sample details for a sample header
     */
    private function createSampleDetails(array $sampleDetailsData, $sampleHeaderId, $index, $batchCode = null, $sampleCount = 1, $totalBatchCount = 1)
    {
        $createdDetails = [];

        $detailData = $sampleDetailsData;
        // Helper function to get single value from array or return the value itself
        $getSingleValue = function($value) {
            if (is_array($value)) {
                return !empty($value) ? $value[0] : null;
            }
            return $value;
        };
        
        // Helper function to ensure integer values
        $getIntegerValue = function($value) use ($getSingleValue) {
            $singleValue = $getSingleValue($value);
            return is_numeric($singleValue) ? (int) $singleValue : $singleValue;
        };

        // Generate sample code with smart logic
        $sampleCode = $this->generateSampleCode($sampleHeaderId, $index, $batchCode, $sampleCount, $totalBatchCount);

        
        // Get sample header to access sample_type_id
        $sampleHeader = SampleHeader::with('sample_type')->find($sampleHeaderId);

        $disposal_count = $sampleHeader->sample_type->disposal_count;
        if ($disposal_count) {
            $disposal_date = \Carbon\Carbon::parse($sampleHeader->receipt_date)->addDays($disposal_count)->format('Y-m-d');
        }else{
            $disposal_date = null;
            Log::info('Disposal count not found for sample type', [
                'sample_type_id' => $sampleHeader->sample_type_id
            ]);
        }
        // If company_product_id is not provided, get it from sample type's default product
        $companyProductId = $getIntegerValue($detailData['company_product_id'] ?? null);
        if (!$companyProductId && $sampleHeader && $sampleHeader->sample_type_id) {
            $sampleType = \App\SampleType::find($sampleHeader->sample_type_id);
            if ($sampleType && $sampleType->default_product_id) {
                $companyProductId = $sampleType->default_product_id;
                Log::info('Auto-filled company_product_id from sample type default', [
                    'sample_type_id' => $sampleType->id,
                    'default_product_id' => $companyProductId
                ]);
            }
        }
        
        // If sample_condition_id is not provided, get it from system configuration
        $sampleConditionId = $getIntegerValue($detailData['sample_condition_id'] ?? null);
        if (!$sampleConditionId) {
            $sampleConditionConfig = \App\Models\System\SystemConfiguration::where('key', 'sample_condition_ok')->first();
            if ($sampleConditionConfig && $sampleConditionConfig->value) {
                $sampleConditionId = (int) $sampleConditionConfig->value;
                Log::info('Auto-filled sample_condition_id from system configuration', [
                    'sample_condition_id' => $sampleConditionId,
                    'config_key' => 'sample_condition_ok'
                ]);
            }
        }
        
        // Create the sample detail
        $sampleDetail = new \App\SampleDetails();
        $sampleData = [
            'sample_header_id' => $sampleHeaderId,
            'sample_code' => $sampleCode['sample_code'],
            'sample_no' => $sampleCode['sample_no'],
            'report_number' => $sampleCode['report_number'],
            'sample_point_id' => $getIntegerValue($detailData['sample_point_id'] ?? null),
            'analysis_type_id' => $getSingleValue($detailData['analysis_type_id'] ?? ''),
            'sample_condition_id' => $sampleConditionId,
            'company_product_id' => $companyProductId,
            'barcode' => $sampleHeader->date_collected ? date('H:i:s', strtotime($sampleHeader->date_collected)) : null,
            'standard_id' => $getIntegerValue($detailData['standard_id'] ?? null),
            'lab_id' => $getIntegerValue($detailData['lab_id'] ?? 1), // Default lab
            'disposal_date' => $disposal_date,
            'main_standard' => $getIntegerValue($detailData['main_standard'] ?? null),
            'secondary_standard' => $getIntegerValue($detailData['secondary_standard'] ?? null),
            'third_standard_id' => $getIntegerValue($detailData['third_standard_id'] ?? null),
            'comments' => $getSingleValue($detailData['comments'] ?? ''),
            'is_duplicate' => $getIntegerValue($detailData['is_duplicate'] ?? 0),
            'sample_store' => $getSingleValue($detailData['sample_store'] ?? ''),
            'sample_store_slot' => $getSingleValue($detailData['sample_store_slot'] ?? ''),
            'sample_quantity' => $getSingleValue($detailData['sample_quantity'] ?? ''),
            'sample_reporting_unit' => $getSingleValue($detailData['sample_reporting_unit'] ?? ''),
        ];

        // Merge additional detail data

        // dd($sampleData, $detailData);

        // $sampleData = array_merge($sampleData, $detailData);

        $sampleDetail->fill($sampleData);
        
        $sampleDetail->save();
        
        // Create analysis type relations and captured results
        $this->createAnalysisRelationsAndResults($sampleHeaderId, $sampleDetail->id, $sampleDetail->analysis_type_id, $sampleDetail->sample_code);
        
        $createdDetails[] = $sampleDetail;
        Log::info('Created sample detail', [
            'detail_id' => $sampleDetail->id,
            'sample_code' => $sampleDetail->sample_code,
            'header_id' => $sampleHeaderId,
            'analysis_type_id' => $sampleDetail->analysis_type_id,
            'sample_count' => $sampleCount
        ]);
       
        return $createdDetails;
    }

    /**
     * Create analysis type relations and captured results for a sample detail
     */
    private function createAnalysisRelationsAndResults($batchId, $sampleDetailId, $analysisTypeIds, $sampleCode)
    {
        if (empty($analysisTypeIds)) {
            Log::warning('No analysis type IDs provided for sample detail', [
                'sample_detail_id' => $sampleDetailId,
                'sample_code' => $sampleCode
            ]);
            return;
        }


        // Parse analysis type IDs (can be comma-separated string or array)
        $analysisTypes = is_string($analysisTypeIds) ? explode(',', $analysisTypeIds) : $analysisTypeIds;
        $analysisTypes = array_filter(array_map('trim', $analysisTypes));

        if (empty($analysisTypes)) {
            Log::warning('No valid analysis type IDs found', [
                'sample_detail_id' => $sampleDetailId,
                'analysis_type_ids' => $analysisTypeIds
            ]);
            return;
        }
        
        // Create analysis type relations first
        $this->createDetailAnalysisRelation($batchId, $sampleDetailId, $analysisTypes);
        
        // Create captured results and results for each analysis type
        foreach ($analysisTypes as $analysisTypeId) {
            $this->createCapturedResultsForAnalysisType($batchId, $sampleDetailId, $analysisTypeId, $sampleCode);
        }
    }

    /**
     * Create analysis type relations for a sample detail
     */
    private function createDetailAnalysisRelation($batchId, $sampleId, $analysisTypes)
    {
        $data = [];
        
        // Delete existing relations that are not in the new list
        SampleAnalysisTypeRelation::where('batch_id', $batchId)
            ->where('sample_detail_id', $sampleId)
            ->whereNotIn('analysis_type_id', $analysisTypes)
            ->delete();
            
        // Get existing relations
        $existing = SampleAnalysisTypeRelation::where('batch_id', $batchId)
            ->where('sample_detail_id', $sampleId)
            ->pluck('analysis_type_id')
            ->toArray();
            
        // Create new relations
        foreach ($analysisTypes as $analysisTypeId) {
            if (!in_array($analysisTypeId, $existing)) {
                $data[] = [
                    'analysis_type_id' => $analysisTypeId,
                    'batch_id' => $batchId,
                    'sample_detail_id' => $sampleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        if (count($data) > 0) {
            SampleAnalysisTypeRelation::insert($data);
        }
        
        return 'success';
    }

    

    /**
     * Create captured results and results for a specific analysis type
     */
    private function createCapturedResultsForAnalysisType($batchId, $sampleDetailId, $analysisTypeId, $sampleCode)
    {
        // Get analysis elements for this analysis type
        $analysisElements = \App\AnalysisElements::where('analysis_type_id', $analysisTypeId)
            ->where('active', 1)
            ->get();

        if ($analysisElements->isEmpty()) {
            Log::warning('No analysis elements found for analysis type', [
                'analysis_type_id' => $analysisTypeId,
                'sample_detail_id' => $sampleDetailId
            ]);
            return;
        }

        // Get lab information for contracted status
        $sampleHeader = \App\SampleHeader::find($batchId);
        $lab = null;
        if ($sampleHeader) {
            $labs = $sampleHeader->labs(true);
            if (!empty($labs)) {
                $labstr = implode(',', $labs);
                $labarr = explode(' - ', $labstr);
                if (count($labarr) >= 2) {
                    $lab = Lab::where('code', $labarr[0])->where('name', $labarr[1])->first();
                }
            }
        }

        // Get sample detail to access its standards
        $sampleDetail = \App\SampleDetails::find($sampleDetailId);
        
        // Get analysis type to access lab_section_id
        $analysisType = \App\AnalysisType::find($analysisTypeId);
        $labSectionIdFromAnalysisType = $analysisType ? $analysisType->lab_section_id : null;
        
        foreach ($analysisElements as $element) {
            // Get the analyte code from the related analyte
            $analyteCode = $element->analyte->code ?? 'UNKNOWN';
            
            // Get standards from sample detail, not analysis element
            $standardID = null;
            $secondaryStandardID = null;
            $thirdStandardID = null;
            
            if ($sampleDetail) {
                if ($sampleDetail->main_standard) {
                    $standardID = StandardAnalytes::where('analyte_id', $element->analyte_id)
                        ->where('standard_id', $sampleDetail->main_standard)->first();
                }
                

                if ($sampleDetail->secondary_standard) {
                    $secondaryStandardID = StandardAnalytes::where('analyte_id', $element->analyte_id)
                        ->where('standard_id', $sampleDetail->secondary_standard)->first();
                }
                
                if ($sampleDetail->third_standard_id) {
                    $thirdStandardID = StandardAnalytes::where('analyte_id', $element->analyte_id)
                        ->where('standard_id', $sampleDetail->third_standard_id)->first();
                }
            }


            // Create captured result with all fields from SampleWorkFlowController
            $capturedResult = new \App\CapturedResult();

            $reportingUnit = ReportingUnit::where('id', $element->reporting_unit)
                ->orWhere('name', $element->reporting_unit)->first();
            
            // Use lab_section_id from analysis type, fallback to element's lab_section_id
            $labSectionId = $labSectionIdFromAnalysisType ?? $element->lab_section_id;

            $capturedResult->fill([
                'sample_detail_code' => $sampleCode,
                'sample_detail_id' => $sampleDetailId,
                'sample_header_id' => $batchId,
                'analyte_id' => $element->analyte_id,
                'analyte_code' => $analyteCode,
                'equipment_id' => $element->equipment_id ?? 0,
                'result' => null, // Will be filled when results are captured
                'user_id' => auth()->id() ?? 1,
                'analysis_type_id' => $analysisTypeId,
                'operator_id' => $element->operator_id,
                'method_id' => $element->method,
                'reporting_unit_id' => $reportingUnit->name,
                'ltm_method_id' => $element->ltm_method_id,
                'analyte_accredited' => $element->non_accredited ? 0 : 1,
                'analyte_status_contracted' => $lab->is_external ?? 0,
                'lab_section_id' => $labSectionId,
                'parameters_order' => $element->level ?? 0,
                'remark_is_manual' => $element->remark_is_manual,
                'remark' => null,
                'main_standard_id' => $standardID ? $standardID->id : null,
                'secondary_standard_id' => $secondaryStandardID ? $secondaryStandardID->id : null,
                'third_standard_id' => $thirdStandardID ? $thirdStandardID->id : null,
                'analysis_type_order' => $element->analysis_type_order ?? 0,
                'remark_colour' => null,
                'repeat_captured_id' => null,
            ]);
            
            $capturedResult->save();
            
            Log::info('Created captured result with lab_section_id from analysis type', [
                'captured_result_id' => $capturedResult->id,
                'analysis_type_id' => $analysisTypeId,
                'lab_section_id' => $labSectionId,
                'source' => $labSectionIdFromAnalysisType ? 'analysis_type' : 'element'
            ]);


            // dd($capturedResult, ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");

            // Create corresponding result record
            $result = new \App\Result();
            $result->fill([
                'captured_result_id' => $capturedResult->id,
                'sample_detail_code' => $sampleCode,
                'sample_detail_id' => $sampleDetailId,
                'sample_header_id' => $batchId,
                'analyte_id' => $element->analyte_id,
                'analyte_code' => $analyteCode,
                'analysis_type_id' => $analysisTypeId,
                'result' => null, // Will be filled when results are processed
                'guide' => null,
                'comments' => null,
                'recheck' => 0,
                'guide_low' => null,
                'guide_high' => null,
                'unit_code' => $element->reporting_unit,
                'reporting_unit_id' => $reportingUnit->name,
                'status_code' => null,
                'reporting_symbol' => $element->reporting_symbol,
                'qc' => 0,
                'correct_target' => null,
                'standard_target' => null,
                'recommendations' => null,
                'analyte_status_contracted' => $lab->is_external ?? 0,
                'lab_section_id' => $labSectionId,
                'parameters_order' => $element->level ?? 0,
                'remark_is_manual' => $element->remark_is_manual,
            ]);
            $result->save();

        }
    }

    /**
     * Generate batch code using smart format
     * If only 1 batch: reuse form_number
     * If multiple batches: {form_number}-{batch_seq_no}
     */
    private function generateBatchCode(array $sampleHeaderData, $submissionFormInstanceId = null, $totalBatchCount = 1)
    {
        // If no submission form instance ID provided, fall back to old method
        if (!$submissionFormInstanceId) {
            return $this->generateLegacyBatchCode($sampleHeaderData);
        }

        try {
            // Get submission form instance and its prefix
            $instance = \App\Models\SubmissionFormInstance::find($submissionFormInstanceId);
            if (!$instance) {
                throw new \Exception('Submission form instance not found');
            }

            // SMART LOGIC: If only 1 batch, reuse the form_number
            if ($totalBatchCount === 1) {
                $batch_code = $instance->form_number;
                
                Log::info('Smart batch code generation: Reusing form_number for single batch', [
                    'form_number' => $batch_code,
                    'total_batch_count' => $totalBatchCount
                ]);
                
                return $batch_code;
            }

            // Multiple batches: Use sequential batch codes
            $batch_count = SampleHeader::where('submission_form_instance_id', $submissionFormInstanceId)->count();
            $batch_count = $batch_count ? $batch_count + 1 : 1;                
            
            $batch_code = $instance->form_number.'-'.$batch_count;
            
            Log::info('Standard batch code generation for multiple batches', [
                'batch_code' => $batch_code,
                'total_batch_count' => $totalBatchCount
            ]);
            
            return $batch_code;
            
        } catch (\Exception $e) {
            Log::error('Error generating new batch code: ' . $e->getMessage());
            // Fall back to legacy method
            return $this->generateLegacyBatchCode($sampleHeaderData);
        }
    }

    /**
     * Legacy batch code generation (fallback)
     */
    private function generateLegacyBatchCode(array $sampleHeaderData)
    {
        // Get customer and sample type for code generation
        $customerId = is_array($sampleHeaderData['crm_customer_id'] ?? null) 
            ? $sampleHeaderData['crm_customer_id'][0] 
            : $sampleHeaderData['crm_customer_id'] ?? null;
            
        $sampleTypeId = is_array($sampleHeaderData['sample_type_id'] ?? null) 
            ? $sampleHeaderData['sample_type_id'][0] 
            : $sampleHeaderData['sample_type_id'] ?? null;

        // Get customer and sample type
        $customer = \App\Models\CRM\CRMCustomer::find($customerId);
        $sampleType = \App\SampleType::find($sampleTypeId);
        
        if (!$customer || !$sampleType) {
            // Fallback code generation
            return 'BA' . date('Y') . '0001' . ($sampleType ? $sampleType->code : 'XX');
        }

        // Get batch configuration
        $batchConfig = \App\Models\System\SystemConfiguration::where('key', 'batch_code_config')->first();
        $configValue = $batchConfig ? $batchConfig->value : '001';

        // Generate customer code part
        $custCode = str_split($customer->code);
        $code = [];
        $loop = 0;
        $cont = [];
        
        foreach ($custCode as $cc) {
            if ((int) $cc > 0) {
                array_push($cont, $loop);
            } elseif (is_string($cc) && $cc != '0') {
                array_push($code, $cc);
            }
            ++$loop;
        }
        
        $tt = sizeof($custCode) - 1;
        $ranges = range($cont[0], $tt);
        $values = [];
        
        if (sizeof($cont) < 2) {
            array_push($values, '0');
            array_push($values, $custCode[$cont[0]]);
        } else {
            foreach ($ranges as $r) {
                array_push($values, $custCode[$r]);
            }
        }

        $cP = 'BA' . $configValue . implode('', $values) . $sampleType->code;
        
        // Get batch number
        $configBatchNo = \App\Models\System\SystemConfiguration::where('key', 'batch_start_no')->first();
        $lastId = SampleHeader::latest('id')->first()->id ?? 0;
        $batchNoS = ($configBatchNo ? $configBatchNo->value : 1) + $lastId + 1;
        
        $finalNo = '';
        if (strlen(strval($batchNoS)) < 4) {
            $zerosss = str_repeat('0', 4 - strlen(strval($batchNoS)));
            $finalNo = $zerosss . '' . strval($batchNoS);
        } else {
            $finalNo = strval($batchNoS);
        }
        
        return $cP . '' . $finalNo;
    }

    /**
     * Generate sample code using smart format
     * If only 1 sample: reuse batch_code
     * If multiple samples: {batch_code}-{sample_no_seq_no}
     */
    private function generateSampleCode($sampleHeaderId, $index, $batchCode = null, $totalSampleCount = 1, $totalBatchCount = 1)
    {
        // Get sample header to get sample type
        $sampleHeader = SampleHeader::find($sampleHeaderId);
        if (!$sampleHeader) {
            throw new \Exception('Sample header not found');
        }

        // If batch code is provided, use new format
        if ($batchCode) {
            try {
                // SMART LOGIC: Only reuse batch_code if this is truly a single sample in a single batch
                if ($totalSampleCount === 1 && $totalBatchCount === 1) {
                    $sampleCode = $batchCode;
                    $sampleNo = '01';
                    $reportNumber = $batchCode;
                    
                    Log::info('Smart sample code generation: Reusing batch_code for single sample in single batch', [
                        'batch_code' => $batchCode,
                        'total_sample_count' => $totalSampleCount,
                        'total_batch_count' => $totalBatchCount
                    ]);
                    
                    return [
                        'sample_code' => $sampleCode,
                        'sample_no' => $sampleNo,
                        'report_number' => $reportNumber
                    ];
                }

                // Multiple samples: Use sequential sample codes with suffix
                // Get next sample sequence for this batch
                $sampleSeqNo = SampleDetails::where('sample_header_id', $sampleHeaderId)->count();

                $sampleSeqNo = $sampleSeqNo ? $sampleSeqNo + 1 : 1;
                // Generate sample code: {batch_code}-{sample_no_seq_no}
                $sampleCode = $batchCode . '-' . sprintf('%02d', $sampleSeqNo);
                $sampleNo = sprintf('%02d', $sampleSeqNo);
                
                // Generate report number (keeping existing format for now)
                $reportNumber = $batchCode;

                Log::info('Standard sample code generation for multiple samples', [
                    'sample_code' => $sampleCode,
                    'total_sample_count' => $totalSampleCount,
                    'total_batch_count' => $totalBatchCount
                ]);

                return [
                    'sample_code' => $sampleCode,
                    'sample_no' => $sampleNo,
                    'report_number' => $reportNumber
                ];
                
            } catch (\Exception $e) {
                Log::error('Error generating new sample code: ' . $e->getMessage());
                // Fall back to legacy method
                return $this->generateLegacySampleCode($sampleHeaderId, $index);
            }
        }

        // Fall back to legacy method if no batch code provided
        return $this->generateLegacySampleCode($sampleHeaderId, $index);
    }

    /**
     * Legacy sample code generation (fallback)
     */
    private function generateLegacySampleCode($sampleHeaderId, $index)
    {
        // Get sample header to get sample type
        $sampleHeader = SampleHeader::find($sampleHeaderId);
        if (!$sampleHeader) {
            throw new \Exception('Sample header not found');
        }

        $sampleType = \App\SampleType::find($sampleHeader->sample_type_id);
        if (!$sampleType) {
            throw new \Exception('Sample type not found');
        }

        // Get lab (default to lab 1)
        $lab = \App\Lab::find(1);
        if (!$lab) {
            throw new \Exception('Default lab not found');
        }

        // Get last sample number
        $lastSample = \App\SampleDetails::orderBy('id', 'DESC')->first();
        $lastSampleNo = $lastSample ? $lastSample->sample_no : $lab->start_sample_no;
        
        $sampleNumber = intval($lastSampleNo) + 1;
        $sampleCode = 'S' . date('Y') . $lab->code . $sampleType->code . sprintf('%04d', $sampleNumber);
        $sampleNo = sprintf('%04d', $sampleNumber);
        $reportNumber = 'LR/' . $sampleType->code . '/' . date('Y') . '/' . $lab->code . '/' . sprintf('%04d', $sampleNumber);

        return [
            'sample_code' => $sampleCode,
            'sample_no' => $sampleNo,
            'report_number' => $reportNumber
        ];
    }

    /**
     * Create request object for batch details creation
     */
    private function createBatchDetailsRequest(array $sampleDetails, $batchId)
    {
        $request = new \Illuminate\Http\Request();
        
        $detailsArray = [
            'sample_code' => [],
            'detail_header' => [],
            'lab_id' => [],
            'sample_analysis' => [],
            'sample_condition' => [],
            'sample_point' => [],
            'product' => [],
            'barcode' => [],
            'standard_id' => [],
            'analysis_type_id' => [],
            'sample_condition_id' => [],
            'sample_point_id' => [],
            'company_product_id' => [],
            'standard_analyte_id' => [],
        ];

        foreach ($sampleDetails as $index => $detail) {
            $detailsArray['sample_code'][] = $detail['sample_code'] ?? '';
            $detailsArray['detail_header'][] = $detail['detail_header'] ?? '';
            $detailsArray['lab_id'][] = $detail['lab_id'] ?? 1; // Default lab
            $detailsArray['sample_analysis'][] = $detail['sample_analysis'] ?? [];
            $detailsArray['sample_condition'][] = $detail['sample_condition'] ?? null;
            $detailsArray['sample_point'][] = $detail['sample_point'] ?? null;
            $detailsArray['product'][] = $detail['product'] ?? null;
            $detailsArray['barcode'][] = $detail['barcode'] ?? '';
            $detailsArray['standard_id'][] = $detail['standard_id'] ?? null;
            $detailsArray['analysis_type_id'][] = $detail['analysis_type_id'] ?? '';
            $detailsArray['sample_condition_id'][] = $detail['sample_condition_id'] ?? null;
            $detailsArray['sample_point_id'][] = $detail['sample_point_id'] ?? null;
            $detailsArray['company_product_id'][] = $detail['company_product_id'] ?? null;
            $detailsArray['standard_analyte_id'][] = $detail['standard_analyte_id'] ?? null;
        }

        $request->merge([
            'sample_details' => $detailsArray
        ]);

        return $request;
    }

    public function getFieldsAndValues(SubmissionFormInstance $instance)
    {
        $fields = $instance->submissionForm->elements;
        $values = $instance->values;
        return response()->json([
            'fields' => $fields,
            'values' => $values
        ]);
    }

    /**
     * Get sample creation status for a form instance
     */
    public function getStatus(SubmissionFormInstance $instance)
    {
        try {
            $status = $this->sampleCreationService->getSampleCreationStatus($instance);
            
            return response()->json([
                'success' => true,
                'data' => $status
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting sample creation status', [
                'instance_id' => $instance->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while checking status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create samples from multiple form instances
     */
    public function bulkCreate(Request $request)
    {
        $request->validate([
            'instance_ids' => 'required|array',
            'instance_ids.*' => 'exists:submission_form_instances,id'
        ]);

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($request->instance_ids as $instanceId) {
            try {
                $instance = SubmissionFormInstance::findOrFail($instanceId);
                
                if ($this->canUserCreateSamples($instance)) {
                    $result = $this->sampleCreationService->createSamplesFromForm($instance);
                    
                    if ($result) {
                        $results[] = [
                            'instance_id' => $instanceId,
                            'status' => 'success',
                            'sample_header_id' => $result['sample_header']->id,
                            'batch_code' => $result['sample_header']->batch_code
                        ];
                        $successCount++;
                    } else {
                        $results[] = [
                            'instance_id' => $instanceId,
                            'status' => 'failed',
                            'message' => 'Failed to create samples'
                        ];
                        $errorCount++;
                    }
                } else {
                    $results[] = [
                        'instance_id' => $instanceId,
                        'status' => 'unauthorized',
                        'message' => 'Not authorized to create samples'
                    ];
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $results[] = [
                    'instance_id' => $instanceId,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
                $errorCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Bulk creation completed. Success: {$successCount}, Errors: {$errorCount}",
            'data' => [
                'results' => $results,
                'success_count' => $successCount,
                'error_count' => $errorCount
            ]
        ]);
    }

    /**
     * Check if user can create samples from this form instance
     */
    private function canUserCreateSamples(SubmissionFormInstance $instance)
    {
        // Check if user is admin (assuming admin role check)
        if (auth()->user()->role === 'admin' || auth()->user()->is_admin) {
            return true;
        }

        // Check if user submitted this form
        if ($instance->submitted_by === auth()->id()) {
            return true;
        }

        // Add other authorization logic as needed
        return false;
    }

    /**
     * Create sample dates for a sample header
     */
    private function createSampleDates($sampleHeaderId, $sampleDetails)
    {
        // Create Login Date (current date)
        $this->createSampleDate($sampleHeaderId, 'Login Date', now());
        
        // Create Target Date (calculated based on analysis types)
        $targetDate = $this->calculateTargetDate($sampleHeaderId, $sampleDetails);
        $this->createSampleDate($sampleHeaderId, 'Target Date', $targetDate);
    }

    /**
     * Calculate target date based on analysis types and reporting time
     */
    private function calculateTargetDate($sampleHeaderId, $sampleDetails)
    {
        // Get analysis type IDs from sample details
        $analysisTypeIds = $this->getAnalysisTypeIdsFromSampleDetails($sampleDetails);
        
        if (empty($analysisTypeIds)) {
            // If no analysis types found, use default reporting time of 0
            $maxReportingTime = 0;
        } else {
            // Get maximum reporting time from analysis types
            $analysisMaxReportingTime = AnalysisType::whereIn('id', $analysisTypeIds)->max('reporting_time') ?? 0;
            
            // Get maximum reporting time from analysis elements
            $elementsMaxReportingTime = AnalysisElements::whereIn('analysis_type_id', $analysisTypeIds)->max('reporting_time') ?? 0;
            
            // Use the maximum of both
            $maxReportingTime = max($analysisMaxReportingTime, $elementsMaxReportingTime);
        }
        
        // Get sample header to access receipt_date
        $sampleHeader = SampleHeader::find($sampleHeaderId);
        
        if (!$sampleHeader || !$sampleHeader->receipt_date) {
            // Fallback to current date if no receipt date
            return now()->addDays($maxReportingTime);
        }
        
        // Calculate target date = receipt_date + max_reporting_time
        return \Carbon\Carbon::parse($sampleHeader->receipt_date)->addDays($maxReportingTime);
    }

    /**
     * Get analysis type IDs from sample details
     */
    private function getAnalysisTypeIdsFromSampleDetails($sampleDetails)
    {
        $sampleDetailIds = collect($sampleDetails)->pluck('id')->filter()->toArray();
        
        if (empty($sampleDetailIds)) {
            return [];
        }
        
        // Get analysis type IDs from sample analysis type relations
        return SampleAnalysisTypeRelationView::whereIn('sample_detail_id', $sampleDetailIds)
            ->pluck('analysis_type_id')
            ->unique()
            ->toArray();
    }

    /**
     * Create or update a sample date record
     */
    private function createSampleDate($sampleHeaderId, $dateName, $dateValue)
    {
        $sampleDate = SampleDate::where('sample_header_id', $sampleHeaderId)
            ->where('name', $dateName)
            ->first() ?? new SampleDate();
        
        $sampleDate->name = $dateName;
        $sampleDate->sample_header_id = $sampleHeaderId;
        $sampleDate->date = $dateValue;
        $sampleDate->save();
        
        return $sampleDate;
    }

    /**
     * Update sample header with unique lab_section_ids from all analysis types in the batch
     */
    private function updateSampleHeaderLabSections($sampleHeaderId)
    {
        try {
            // Get all analysis type IDs from sample analysis type relations for this batch
            $analysisTypeIds = SampleAnalysisTypeRelation::where('batch_id', $sampleHeaderId)
                ->pluck('analysis_type_id')
                ->unique()
                ->filter()
                ->toArray();

            if (empty($analysisTypeIds)) {
                Log::info('No analysis types found for batch', ['sample_header_id' => $sampleHeaderId]);
                return;
            }

            // Get unique lab_section_ids from these analysis types
            $labSectionIds = \App\AnalysisType::whereIn('id', $analysisTypeIds)
                ->whereNotNull('lab_section_id')
                ->pluck('lab_section_id')
                ->unique()
                ->filter()
                ->sort()
                ->values()
                ->toArray();

            if (empty($labSectionIds)) {
                Log::info('No lab_section_ids found in analysis types', [
                    'sample_header_id' => $sampleHeaderId,
                    'analysis_type_ids' => $analysisTypeIds
                ]);
                return;
            }

            // Convert to comma-separated string
            $labSectionIdsString = implode(',', $labSectionIds);

            // Update sample header
            $sampleHeader = SampleHeader::find($sampleHeaderId);
            if ($sampleHeader) {
                $sampleHeader->lab_section_ids = $labSectionIdsString;
                $sampleHeader->save();

                Log::info('Updated sample header with lab_section_ids', [
                    'sample_header_id' => $sampleHeaderId,
                    'lab_section_ids' => $labSectionIdsString,
                    'unique_count' => count($labSectionIds)
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error updating sample header lab_section_ids', [
                'sample_header_id' => $sampleHeaderId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function extractAnalysisTypeIds(array $sampleDetails): array
    {
        $analysisTypeIds = [];

        foreach ($sampleDetails as $detail) {
            if (empty($detail['analysis_type_id'])) {
                continue;
            }

            $rawValue = $detail['analysis_type_id'];
            if (is_array($rawValue)) {
                $values = $rawValue;
            } else {
                $values = explode(',', (string) $rawValue);
            }

            foreach ($values as $id) {
                $trimmed = trim((string) $id);
                if ($trimmed !== '') {
                    $analysisTypeIds[] = (int) $trimmed;
                }
            }
        }

        return array_values(array_unique(array_filter($analysisTypeIds)));
    }

    private function applyLabMetadataAndDates(SampleHeader $sampleHeader, array $analysisTypeIds): void
    {
        // $sampleHeader->receiving_officer = auth()->user()->id;

        if (empty($analysisTypeIds)) {
            $sampleHeader->save();
            $this->createSampleDateRecord($sampleHeader->id, 'Login Date', Carbon::now());
            return;
        }

        $labSectionIds = AnalysisType::whereIn('id', $analysisTypeIds)
            ->pluck('lab_section_id')
            ->filter()
            ->unique()
            ->toArray();

        if (!empty($labSectionIds)) {
            $sampleHeader->lab_section_ids = implode(',', $labSectionIds);
        }

        $sampleHeader->sample_detail_processed = 0;
        $sampleHeader->save();

        $this->createSampleDateRecord($sampleHeader->id, 'Login Date', Carbon::now());

        $maxReportingTime = AnalysisType::whereIn('id', $analysisTypeIds)->max('reporting_time') ?? 0;
        $baseDate = $sampleHeader->receipt_date
            ? Carbon::parse($sampleHeader->receipt_date)
            : Carbon::now();
        $targetDate = (clone $baseDate)->addDays($maxReportingTime);

        $this->createSampleDateRecord($sampleHeader->id, 'Target Date', $targetDate);
    }

    private function createStagingEntry(SampleHeader $sampleHeader, array $batch, array $analysisTypeIds): void
    {
        $getIntegerValue = function ($value) {
            if (is_array($value)) {
                $value = reset($value);
            }

            return is_numeric($value) ? (int) $value : null;
        };

        $getStringValue = function ($value) {
            if (is_array($value)) {
                $value = reset($value);
            }

            return $value ?? '';
        };

        $sampleDetails = $batch['sample_details'] ?? [];
        $firstDetail = $sampleDetails[0] ?? [];

        $stagingData = [
            'analysis_type_ids' => implode(',', $analysisTypeIds),
            'analysis_type_names' => $this->resolveAnalysisTypeNames($analysisTypeIds),
            'company_sub_unit_id' => $getIntegerValue($batch['sample_header']['company_sub_unit_id'] ?? $firstDetail['company_sub_unit_id'] ?? null),
            'quantity' => $getIntegerValue($firstDetail['quantity'] ?? 1),
            'sample_details' => $sampleDetails,
            'lab_id' => $getIntegerValue($firstDetail['lab_id'] ?? 1),
            'sample_condition_id' => $getIntegerValue($firstDetail['sample_condition_id'] ?? null),
            'company_product_id' => $getIntegerValue($firstDetail['company_product_id'] ?? null),
            'description' => $getStringValue($firstDetail['description'] ?? ''),
            'sample_type_id' => $getIntegerValue($batch['sample_header']['sample_type_id'] ?? null),
        ];

        // foreach ($sampleDetails as $detail) {
        //     $stagingData['quantity'] += $getIntegerValue($detail['quantity'] ?? 1) ?? 0;
        // }

        if ($stagingData['company_sub_unit_id']) {
            $companySubUnit = \App\Models\CRM\CRMCompanySubUnit::find($stagingData['company_sub_unit_id']);
            $stagingData['company_sub_unit_name'] = $companySubUnit->name ?? 'N/A';
            $stagingData['company_sub_unit_code'] = $companySubUnit->code ?? 'N/A';
        }

        \App\Models\SampleDetailStaging::create([
            'sample_header_id' => $sampleHeader->id,
            'data_json' => $stagingData,
            'is_processed' => 0,
        ]);

        Log::info('Created staging entry for batch', [
            'batch_id' => $sampleHeader->id,
            'analysis_type_ids' => $analysisTypeIds,
            'quantity' => $stagingData['quantity'],
        ]);
    }

    private function resolveAnalysisTypeNames(array $analysisTypeIds): string
    {
        if (empty($analysisTypeIds)) {
            return 'N/A';
        }

        $names = AnalysisType::whereIn('id', $analysisTypeIds)->pluck('name')->filter()->toArray();
        return !empty($names) ? implode(', ', $names) : 'N/A';
    }

    private function createSampleDateRecord(int $sampleHeaderId, string $name, Carbon $date): void
    {
        SampleDate::updateOrCreate(
            [
                'sample_header_id' => $sampleHeaderId,
                'name' => $name,
            ],
            [
                'date' => $date,
            ]
        );
    }

    /**
     * Load assignment data for staging record
     */
    public function loadAssignmentData($stagingId)
    {
        $staging = \App\Models\SampleDetailStaging::with('sampleHeader.sample_type', 'sampleHeader.client')
            ->findOrFail($stagingId);
        
        $dataJson = $staging->data_json;
        $subUnitId = $dataJson['company_sub_unit_id'] ?? null;
        
        if (!$subUnitId) {
            return response()->json(['error' => 'No company sub unit specified'], 400);
        }
        
        // Get sub unit
        $subUnit = \App\Models\CRM\CRMCompanySubUnit::with('companyUnit')->findOrFail($subUnitId);
        
        // Get all sample point areas tied to this sub unit
        $areas = \App\Models\SamplePointArea::with([
            'crmArea',
            'samplePoints' => function($q) {
                $q->where('active', 1)->with('crmSamplePoint');
            }
        ])
        ->where('crm_company_sub_unit_id', $subUnitId)
        ->where('active', 1)
        ->get();
        
        // Format areas for frontend
        $formattedAreas = $areas->map(function($area) {
            return [
                'id' => $area->id,
                'name' => $area->crmArea->name ?? 'N/A',
                'sample_points' => $area->samplePoints->map(function($point) {
                    return [
                        'id' => $point->id,
                        'name' => $point->crmSamplePoint->name ?? $point->name,
                    ];
                })
            ];
        });
        
        return response()->json([
            'batch_code' => $staging->sampleHeader->batch_code,
            'sample_type' => $staging->sampleHeader->sample_type->name ?? 'N/A',
            'customer' => $staging->sampleHeader->client->name ?? 'N/A',
            'customer_id' => $staging->sampleHeader->client->id ?? null,
            'company_unit' => $subUnit->companyUnit->name ?? 'N/A',
            'areas' => $formattedAreas,
        ]);
    }

    /**
     * Assign samples from staging to sample points
     */
    public function assignSamples(Request $request)
    {
        $validated = $request->validate([
            'sample_header_id' => 'required|exists:sample_headers,id',
            'sample_detail_stage_id' => 'required|exists:sample_detail_staging,id',
            'selections' => 'required|array|min:1',
            'selections.*.sample_point_id' => 'required|exists:sample_points,id',
            'selections.*.quantity' => 'nullable|integer|min:1',
        ]);
        
        DB::beginTransaction();
        try {
            $sampleHeader = \App\SampleHeader::findOrFail($validated['sample_header_id']);
            $staging = \App\Models\SampleDetailStaging::findOrFail($validated['sample_detail_stage_id']);
            
            $dataJson = $staging->data_json;
            $analysisTypeIds = $dataJson['analysis_type_ids'] ?? '';
            
            $createdSamples = [];
            $sampleIndex = 0;
            
            // Count total samples that will be created (one per selected sample point)
            $totalSamples = count($validated['selections']);
            
            // Loop through selected sample points - create one sample per point
            foreach ($validated['selections'] as $selection) {
                $samplePointId = $selection['sample_point_id'];
                
                // Create one sample for this point (ignore quantity)
                $sampleDetail = $this->createSampleDetailsFromStaging(
                    $sampleHeader,
                    $staging,
                    $samplePointId,
                    $sampleIndex,
                    $totalSamples
                );
                
                $createdSamples[] = $sampleDetail;
                $sampleIndex++;
            }
            
            // Mark staging as processed
            $staging->is_processed = 1;
            $staging->save();
            
            // Create chain of custody for sample assignment
            $this->createChainOfCustody($sampleHeader, 'Sample Assignment');
            
            // Check if all staging records for this header are processed
            $unprocessedCount = \App\Models\SampleDetailStaging::where('sample_header_id', $sampleHeader->id)
                ->where('is_processed', 0)
                ->count();
            
            if ($unprocessedCount === 0) {
                $sampleHeader->sample_detail_processed = 1;
                $sampleHeader->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => count($createdSamples) . ' samples assigned successfully!',
                'sample_codes' => array_column($createdSamples, 'sample_code')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning samples: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create sample details from staging data
     */
    private function createSampleDetailsFromStaging($sampleHeader, $staging, $samplePointId, $index, $totalSamples = 1)
    {
        $dataJson = $staging->data_json;
        
        // Generate sample code
        $sampleCode = $this->generateSampleCode(
            $sampleHeader->id, 
            $index, 
            $sampleHeader->batch_code, 
            $totalSamples, 
            1
        );
        
        // Get sample header to access sample_type_id
        $sampleHeaderFull = \App\SampleHeader::with('sample_type')->find($sampleHeader->id);

        $disposal_count = $sampleHeaderFull->sample_type->disposal_count ?? null;
        if ($disposal_count) {
            $disposal_date = \Carbon\Carbon::parse($sampleHeaderFull->receipt_date)->addDays($disposal_count)->format('Y-m-d');
        } else {
            $disposal_date = null;
        }

        // Get company_product_id
        $companyProductId = $dataJson['company_product_id'] ?? null;
        if (!$companyProductId && $sampleHeaderFull->sample_type_id) {
            $sampleType = \App\SampleType::find($sampleHeaderFull->sample_type_id);
            if ($sampleType && $sampleType->default_product_id) {
                $companyProductId = $sampleType->default_product_id;
            }
        }
        
        // Get sample_condition_id
        $sampleConditionId = $dataJson['sample_condition_id'] ?? null;
        if (!$sampleConditionId) {
            $sampleConditionConfig = \App\Models\System\SystemConfiguration::where('key', 'sample_condition_ok')->first();
            if ($sampleConditionConfig && $sampleConditionConfig->value) {
                $sampleConditionId = (int) $sampleConditionConfig->value;
            }
        }
        
        // Create sample detail
        $sampleDetail = new \App\SampleDetails();
        $sampleDetail->fill([
            'sample_header_id' => $sampleHeader->id,
            'sample_code' => $sampleCode['sample_code'],
            'sample_no' => $sampleCode['sample_no'],
            'report_number' => $sampleCode['report_number'],
            'sample_point_id' => $samplePointId,
            'analysis_type_id' => $dataJson['analysis_type_ids'] ?? '',
            'company_product_id' => $companyProductId,
            'sample_condition_id' => $sampleConditionId,
            'lab_id' => $dataJson['lab_id'] ?? 1,
            'barcode' => $sampleHeaderFull->date_collected ? date('H:i:s', strtotime($sampleHeaderFull->date_collected)) : null,
            'disposal_date' => $disposal_date,
        ]);
        $sampleDetail->save();
        
        // Create analysis relations and results
        if (!empty($dataJson['analysis_type_ids'])) {
            $this->createAnalysisRelationsAndResults(
                $sampleHeader->id,
                $sampleDetail->id,
                $dataJson['analysis_type_ids'],
                $sampleDetail->sample_code
            );
        }
        
        // Create sample dates
        $this->createSampleDates($sampleHeader->id, $sampleDetail->id);
        
        Log::info('Created sample detail from staging', [
            'sample_detail_id' => $sampleDetail->id,
            'sample_code' => $sampleDetail->sample_code
        ]);
        
        return [
            'id' => $sampleDetail->id,
            'sample_code' => $sampleDetail->sample_code
        ];
    }
}

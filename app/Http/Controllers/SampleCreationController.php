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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

            // dd("sampleBatches",$sampleBatches);
            
            if (empty($sampleBatches)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sample data found in the form instance'
                ], 400);
            }

            $createdBatches = [];

            foreach ($sampleBatches as $batchIndex => $batch) {
                // Log the batch data for debugging
                Log::info('Processing sample batch', [
                    'batch_index' => $batchIndex,
                    'sample_header' => $batch['sample_header'],
                    'sample_details_count' => count($batch['sample_details'])
                ]);


                $sampleDetails = [];

                echo ">>>>>>>>>>>>>>>>>>>> BATCH INDEX :: ".$batchIndex."\n";
                
                // Create sample header using our custom method
                $sampleHeader = $this->createSampleHeader($batch['sample_header'], $instance->id);
                
                // Update the sample header with the instance ID
                $sampleHeader->submission_form_instance_id = $instance->id;
                $sampleHeader->save();
                // Merge all sample details arrays into one flat array

                foreach($batch['sample_details'] as $i=>$sampleDetail){
                    echo ">>>>>>>>>>>>>>>>>>>> SAMPLE DETAIL INDEX :: ".$i."\n";
                    $samplePoints = explode(',', $sampleDetail['sample_point_id']);
                    $newSampleDetailsInfo = $sampleDetail;

                    foreach($samplePoints as $j=>$samplePoint){
                        echo ">>>>>>>>>>>>>>>>>>>> SAMPLE POINT INDEX :: ".$j."\n";
                        $newSampleDetailsInfo['sample_point_id'] = $samplePoint;
                        $sampleDetails[$i] = $this->createSampleDetails($newSampleDetailsInfo, $sampleHeader->id, $i, $sampleHeader->batch_code);
                        $this->createSampleDates($sampleHeader->id, $sampleDetails[$i]);
                    }
                }
                
                $createdBatches[] = [
                    'batch_id' => $sampleHeader->id,
                    'batch_code' => $sampleHeader->batch_code,
                    'sample_type_id' => $batch['sample_header']['sample_type_id'] ?? 'Unknown',
                    'sample_count' => count(array_keys($sampleDetails))
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
    private function createSampleHeader(array $sampleHeaderData, $submissionFormInstanceId = null)
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

        // Handle missing CRM unit - get first unit for the customer
        $crmCustomerId = $getIntegerValue($sampleHeaderData['crm_customer_id'] ?? null);
        $crmUnitName = $getSingleValue($sampleHeaderData['crm_unit_name'] ?? null);
        
        if ($crmCustomerId && !$crmUnitName) {
            $firstUnit = \App\Models\CRM\CRMCompanyUnit::where('crm_customer_id', $crmCustomerId)->first();
            if ($firstUnit) {
                $crmUnitName = $firstUnit->name;
                Log::info('Auto-selected first CRM unit for customer', [
                    'customer_id' => $crmCustomerId,
                    'unit_name' => $crmUnitName,
                    'unit_id' => $firstUnit->id
                ]);
            }
        }

        // Generate batch code
        $batchCode = $this->generateBatchCode($sampleHeaderData, $submissionFormInstanceId);
        
        // Create the sample header
        $sampleHeader = new SampleHeader();
        $sampleHeader->fill([
            'crm_customer_id' => $getIntegerValue($sampleHeaderData['crm_customer_id'] ?? null),
            'sample_type_id' => $getIntegerValue($sampleHeaderData['sample_type_id'] ?? null),
            'batch_code' => $batchCode,
            'receipt_date' => $getSingleValue($sampleHeaderData['receipt_date'] ?? now()->format('Y-m-d')),
            'date_collected' => $getSingleValue($sampleHeaderData['date_collected'] ?? now()->format('Y-m-d')),
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
            'date_expected' => $getSingleValue($sampleHeaderData['date_expected'] ?? now()->addDays(7)->format('Y-m-d')),
            'quote_id' => $getIntegerValue($sampleHeaderData['quote_id'] ?? null),
            'radio_active_levels' => $getSingleValue($sampleHeaderData['radio_active_levels'] ?? ''),
            'receiving_officer_name' => $getSingleValue($sampleHeaderData['receive_by'] ?? auth()->user()->name ?? 'System'),
            'receiving_officer' => $getSingleValue($sampleHeaderData['receive_by'] ?? auth()->user()->name ?? 'System'),
            'sampling_officer_name' => $getSingleValue($sampleHeaderData['sample_by'] ?? auth()->user()->name ?? 'System'),
            'reference_number' => $getSingleValue($sampleHeaderData['reference_number'] ?? 'n/a'),
            'is_routine' => $getIntegerValue($sampleHeaderData['is_routine'] ?? 0),
            'routine_frequency' => $getIntegerValue($sampleHeaderData['routine_frequency'] ?? 0),
            'is_client_order' => $getIntegerValue($sampleHeaderData['is_client_order'] ?? 0),
            'crm_unit_name' => $crmUnitName,
            'status' => 'Samples Reception',
            'submission_form_instance_id' => null, // Will be set by the calling method
        ]);
        
        $sampleHeader->save();
        
        Log::info('Created sample header', [
            'header_id' => $sampleHeader->id,
            'batch_code' => $sampleHeader->batch_code,
            'sample_type_id' => $sampleHeader->sample_type_id,
            'crm_customer_id' => $sampleHeader->crm_customer_id,
            'crm_unit_name' => $sampleHeader->crm_unit_name
        ]);
        
        return $sampleHeader;
    }

    /**
     * Create sample details for a sample header
     */
    private function createSampleDetails(array $sampleDetailsData, $sampleHeaderId, $index, $batchCode = null)
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

        // Generate sample code
        $sampleCode = $this->generateSampleCode($sampleHeaderId, $index, $batchCode);

        
        // Create the sample detail
        $sampleDetail = new \App\SampleDetails();
        $sampleData = [
            'sample_header_id' => $sampleHeaderId,
            'sample_code' => $sampleCode['sample_code'],
            'sample_no' => $sampleCode['sample_no'],
            'report_number' => $sampleCode['report_number'],
            'sample_point_id' => $getIntegerValue($detailData['sample_point_id'] ?? null),
            'analysis_type_id' => $getSingleValue($detailData['analysis_type_id'] ?? ''),
            'sample_condition_id' => $getIntegerValue($detailData['sample_condition_id'] ?? null),
            'company_product_id' => $getIntegerValue($detailData['company_product_id'] ?? null),
            'barcode' => $getSingleValue($detailData['barcode'] ?? ''),
            'standard_id' => $getIntegerValue($detailData['standard_id'] ?? null),
            'lab_id' => $getIntegerValue($detailData['lab_id'] ?? 1), // Default lab
            'disposal_date' => $getSingleValue($detailData['disposal_date'] ?? null),
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
            'analysis_type_id' => $sampleDetail->analysis_type_id
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
                'lab_section_id' => $element->lab_section_id,
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
                'lab_section_id' => $element->lab_section_id,
                'parameters_order' => $element->level ?? 0,
                'remark_is_manual' => $element->remark_is_manual,
            ]);
            $result->save();

        }
    }

    /**
     * Generate batch code using new format: {Submission-Form_instance_prefix}{batch_seq_no}/{YY}
     */
    private function generateBatchCode(array $sampleHeaderData, $submissionFormInstanceId = null)
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

            $submissionForm = $instance->submissionForm;
            if (!$submissionForm) {
                throw new \Exception('Submission form not found');
            }

            $prefix = $submissionForm->naming_convention_prefix ?? 'SF';
            $currentYear = date('Y');
            
            // Get next batch sequence for this form instance and year
            $batchSeqNo = \App\Models\BatchSequence::getNextBatchSequence($submissionFormInstanceId, $currentYear);
            
            // Generate batch code: {prefix}{batch_seq_no}/{YY}
            $batchCode = $prefix . sprintf('%03d', $batchSeqNo) . '/' . date('y');
            
            return $batchCode;
            
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
     * Generate sample code using new format: {Submission-Form_instance_prefix}{batch_seq_no}/{YY}-{sample_no_seq_no}
     */
    private function generateSampleCode($sampleHeaderId, $index, $batchCode = null)
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

        // If batch code is provided, use new format
        if ($batchCode) {
            try {
                // Get next sample sequence for this batch
                $sampleSeqNo = \App\Models\SampleSequence::getNextSampleSequence($batchCode);
                
                // Generate sample code: {batch_code}-{sample_no_seq_no}
                $sampleCode = $batchCode . '-' . sprintf('%03d', $sampleSeqNo);
                $sampleNo = sprintf('%03d', $sampleSeqNo);
                
                // Generate report number (keeping existing format for now)
                $lab = \App\Lab::find(1);
                $reportNumber = 'LR/' . $sampleType->code . '/' . date('Y') . '/' . ($lab ? $lab->code : 'XX') . '/' . sprintf('%03d', $sampleSeqNo);

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
}

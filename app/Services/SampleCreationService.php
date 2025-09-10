<?php

namespace App\Services;

use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstanceValue;
use App\SampleHeader;
use App\SampleDetails;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCustomerUnit;
use App\SampleType;
use App\Lab;
use App\Models\System\SystemConfiguration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SampleCreationService
{
    /**
     * Create sample header and details from a submitted form instance
     */
    public function createSamplesFromForm(SubmissionFormInstance $instance)
    {
        Log::info('Creating samples from form instance', [
            'instance_id' => $instance->id,
            'form_id' => $instance->submission_form_id
        ]);

        DB::beginTransaction();
        
        try {
            // Get all mapped elements for this form
            $mappedElements = $this->getMappedElements($instance);
            
            if (empty($mappedElements)) {
                Log::warning('No mapped elements found for form instance', ['instance_id' => $instance->id]);
                DB::rollBack();
                return null;
            }

            // Extract form data
            $formData = $this->extractFormData($instance, $mappedElements);
            
            // Create sample header
            $sampleHeader = $this->createSampleHeader($formData, $instance);
            
            // Create sample details
            $sampleDetails = $this->createSampleDetails($sampleHeader, $formData, $instance);
            
            DB::commit();
            
            Log::info('Successfully created samples from form instance', [
                'instance_id' => $instance->id,
                'sample_header_id' => $sampleHeader->id,
                'sample_details_count' => count($sampleDetails)
            ]);
            
            return [
                'sample_header' => $sampleHeader,
                'sample_details' => $sampleDetails
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create samples from form instance', [
                'instance_id' => $instance->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get all mapped elements for the form
     */
    private function getMappedElements(SubmissionFormInstance $instance)
    {
        return SubmissionFormElement::whereHas('holder.section', function($query) use ($instance) {
            $query->where('submission_form_id', $instance->submission_form_id);
        })
        ->where('is_mapped', true)
        ->whereNotNull('mapping_table')
        ->whereNotNull('mapping_field')
        ->with('values')
        ->get();
    }

    /**
     * Extract form data from mapped elements
     */
    private function extractFormData(SubmissionFormInstance $instance, $mappedElements)
    {
        $formData = [
            'sample_headers' => [],
            'sample_details' => []
        ];

        foreach ($mappedElements as $element) {
            $value = $this->getElementValue($instance, $element);
            
            if ($value !== null) {
                $mappingConfig = $element->getMappingConfig();
                $table = $mappingConfig['table'];
                $field = $mappingConfig['field'];
                
                // Handle array values (from rows sections)
                if (is_array($value)) {
                    foreach ($value as $index => $itemValue) {
                        if (!isset($formData[$table][$index])) {
                            $formData[$table][$index] = [];
                        }
                        $formData[$table][$index][$field] = $itemValue;
                    }
                } else {
                    $formData[$table][$field] = $value;
                }
            }
        }

        return $formData;
    }

    /**
     * Get element value from form instance
     */
    private function getElementValue(SubmissionFormInstance $instance, SubmissionFormElement $element)
    {
        $instanceValue = $instance->values()
            ->where('submission_form_element_id', $element->id)
            ->first();

        if (!$instanceValue) {
            return null;
        }

        // Handle different element types
        switch ($element->element_type) {
            case 'client_select':
            case 'sample_type_select':
            case 'client_unit_select':
            case 'client_contact_select':
            case 'analysis_type_select':
            case 'sample_condition_select':
            case 'store_select':
            case 'store_slot_select':
            case 'standard_select':
            case 'sample_point_select':
                // For custom select fields, return the selected value (ID)
                return $instanceValue->value;
                
            case 'date':
            case 'datetime':
                return $instanceValue->value ? Carbon::parse($instanceValue->value)->format('Y-m-d') : null;
                
            case 'checkbox':
                return $instanceValue->value ? 1 : 0;
                
            case 'file':
                // For file fields, we might want to store the file path or handle differently
                return $instanceValue->file_path;
                
            default:
                return $instanceValue->value;
        }
    }

    /**
     * Create sample header
     */
    private function createSampleHeader($formData, SubmissionFormInstance $instance)
    {
        $headerData = $formData['sample_headers'] ?? [];
        
        // Generate batch code if not provided
        if (empty($headerData['batch_code'])) {
            $headerData['batch_code'] = $this->generateBatchCode($headerData);
        }
        
        // Set default values
        $headerData['status'] = $headerData['status'] ?? 'Samples Reception';
        $headerData['priority'] = $headerData['priority'] ?? 'Normal';
        $headerData['is_routine'] = $headerData['is_routine'] ?? 0;
        $headerData['routine_frequency'] = $headerData['routine_frequency'] ?? 0;
        $headerData['is_amendment'] = $headerData['is_amendment'] ?? 1;
        $headerData['isactive'] = 1;
        $headerData['submission_form_instance_id'] = $instance->id;
        $headerData['created_at'] = now();
        $headerData['updated_at'] = now();
        
        // Set receipt date if not provided
        if (empty($headerData['receipt_date'])) {
            $headerData['receipt_date'] = now()->format('Y-m-d');
        }
        
        // Set date collected if not provided
        if (empty($headerData['date_collected'])) {
            $headerData['date_collected'] = now()->format('Y-m-d');
        }
        
        // Get CRM unit name from client unit ID
        if (!empty($headerData['crm_unit_id']) && empty($headerData['crm_unit_name'])) {
            $crmUnit = CRMCustomerUnit::find($headerData['crm_unit_id']);
            if ($crmUnit) {
                $headerData['crm_unit_name'] = $crmUnit->name;
            }
        }
        
        $sampleHeader = SampleHeader::create($headerData);
        
        Log::info('Created sample header', [
            'sample_header_id' => $sampleHeader->id,
            'batch_code' => $sampleHeader->batch_code
        ]);
        
        return $sampleHeader;
    }

    /**
     * Create sample details
     */
    private function createSampleDetails(SampleHeader $sampleHeader, $formData, SubmissionFormInstance $instance)
    {
        $sampleDetails = [];
        $detailsData = $formData['sample_details'] ?? [];
        
        // If no sample details data, create a default sample
        if (empty($detailsData)) {
            $detailsData = [0 => []]; // Create one default sample
        }
        
        foreach ($detailsData as $index => $detailData) {
            // Generate sample code if not provided
            if (empty($detailData['sample_code'])) {
                $detailData['sample_code'] = $this->generateSampleCode($sampleHeader, $detailData);
            }
            
            // Set required fields
            $detailData['sample_header_id'] = $sampleHeader->id;
            $detailData['created_at'] = now();
            $detailData['updated_at'] = now();
            
            // Set default values
            $detailData['is_ammendment'] = $detailData['is_ammendment'] ?? 0;
            $detailData['ammendment_number'] = $detailData['ammendment_number'] ?? 1;
            $detailData['is_disposed'] = $detailData['is_disposed'] ?? 0;
            
            $sampleDetail = SampleDetails::create($detailData);
            $sampleDetails[] = $sampleDetail;
            
            // Create analysis relations if analysis types are specified
            if (!empty($detailData['analysis_type_id'])) {
                $this->createDetailAnalysisRelation(
                    $sampleHeader->id, 
                    $sampleDetail->id, 
                    explode(',', $detailData['analysis_type_id'])
                );
            }
            
            Log::info('Created sample detail', [
                'sample_detail_id' => $sampleDetail->id,
                'sample_code' => $sampleDetail->sample_code
            ]);
        }
        
        return $sampleDetails;
    }

    /**
     * Generate batch code
     */
    private function generateBatchCode($headerData)
    {
        // Get customer
        $customer = null;
        if (!empty($headerData['crm_customer_id'])) {
            $customer = CRMCustomer::find($headerData['crm_customer_id']);
        }
        
        if (!$customer) {
            throw new \Exception('Customer is required to generate batch code');
        }
        
        // Get sample type
        $sampleType = null;
        if (!empty($headerData['sample_type_id'])) {
            $sampleType = SampleType::find($headerData['sample_type_id']);
        }
        
        if (!$sampleType) {
            throw new \Exception('Sample type is required to generate batch code');
        }
        
        // Get batch configuration
        $batchConfig = SystemConfiguration::where('key', 'batch_code_config')->first();
        if (!$batchConfig) {
            throw new \Exception('Batch code configuration not found');
        }
        
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
        
        $cP = 'BA' . $batchConfig->value . implode('', $values) . $sampleType->code;
        
        // Get batch number
        $configBatchNo = SystemConfiguration::where('key', 'batch_start_no')->first();
        if (!$configBatchNo) {
            throw new \Exception('Batch start number configuration not found');
        }
        
        $lastId = SampleHeader::latest('id')->first()->id ?? 0;
        $batchNoS = $configBatchNo->value + $lastId + 1;
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
     * Generate sample code
     */
    private function generateSampleCode(SampleHeader $sampleHeader, $detailData)
    {
        // Get lab
        $lab = null;
        if (!empty($detailData['lab_id'])) {
            $lab = Lab::find($detailData['lab_id']);
        }
        
        if (!$lab) {
            // Get default lab or first available lab
            $lab = Lab::where('active', 1)->first();
        }
        
        if (!$lab) {
            throw new \Exception('Lab is required to generate sample code');
        }
        
        // Get sample type
        $sampleType = SampleType::find($sampleHeader->sample_type_id);
        if (!$sampleType) {
            throw new \Exception('Sample type not found');
        }
        
        // Get last sample number
        $lastSample = SampleDetails::orderBy('id', 'DESC')->first();
        $lastSampleNo = $lastSample ? $lastSample->sample_no : $lab->start_sample_no;
        
        $sampleNumber = intval($lastSampleNo) + 1;
        
        // Generate sample code
        $sampleCode = 'S' . date('Y') . $lab->code . $sampleType->code . sprintf('%04d', $sampleNumber);
        
        return $sampleCode;
    }

    /**
     * Create detail analysis relation
     */
    private function createDetailAnalysisRelation($sampleHeaderId, $sampleDetailId, $analysisTypeIds)
    {
        foreach ($analysisTypeIds as $analysisTypeId) {
            if (empty($analysisTypeId)) continue;
            
            // Create the analysis relation
            // This would typically create a record in a sample_analysis_relations table
            // The exact implementation depends on your database structure
            DB::table('sample_analysis_relations')->insert([
                'sample_header_id' => $sampleHeaderId,
                'sample_detail_id' => $sampleDetailId,
                'analysis_type_id' => $analysisTypeId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Check if a form instance can create samples
     */
    public function canCreateSamples(SubmissionFormInstance $instance)
    {
        // Check if form is submitted
        if (!$instance->isSubmitted()) {
            return false;
        }
        
        // Check if there are mapped elements
        $mappedElements = $this->getMappedElements($instance);
        return $mappedElements->count() > 0;
    }

    /**
     * Get sample creation status for a form instance
     */
    public function getSampleCreationStatus(SubmissionFormInstance $instance)
    {
        // Check if samples already exist for this instance
        $existingSamples = SampleHeader::where('submission_form_instance_id', $instance->id)->get();
        
        if ($existingSamples->count() > 0) {
            return [
                'status' => 'created',
                'sample_headers' => $existingSamples,
                'message' => 'Samples already created for this form instance'
            ];
        }
        
        if (!$this->canCreateSamples($instance)) {
            return [
                'status' => 'cannot_create',
                'message' => 'Cannot create samples from this form instance'
            ];
        }
        
        return [
            'status' => 'ready',
            'message' => 'Ready to create samples'
        ];
    }
}

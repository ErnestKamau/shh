<?php

namespace App\Services;

use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstanceValue;
use App\SampleHeader;
use App\SampleDetails;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanyUnit;
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

            // Count distinct sample_type_id values to determine batch count
            $batchCount = $this->countDistinctSampleTypes($instance);

            // Create sample header
            $sampleHeader = $this->createSampleHeader($formData, $instance, $batchCount);

            // Create sample details
            $sampleDetails = $this->createSampleDetails($sampleHeader, $formData, $instance, $batchCount);

            DB::commit();

            // Auto-create runs/formulas for worksheets
            try {
                $autoRunService = app(\App\Services\Worksheets\AutoRunCreationService::class);
                $autoRunService->createRunsForBatch($sampleHeader->id);
            } catch (\Exception $e) {
                Log::error('Error triggering auto run creation in createSamplesFromForm: ' . $e->getMessage());
            }

            Log::info('Successfully created samples from form instance', [
                'instance_id' => $instance->id,
                'sample_header_id' => $sampleHeader->id,
                'sample_details_count' => count($sampleDetails),
                'batch_count' => $batchCount
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
        return SubmissionFormElement::whereHas('holder.section', function ($query) use ($instance) {
            $query->where('submission_form_id', $instance->submission_form_id);
        })
            ->where('is_mapped', true)
            ->whereNotNull('mapping_table')
            ->whereNotNull('mapping_field')
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
        // Check if this is a multi-select field
        $multiSelectTypes = ['sample_point_select', 'analysis_type_select', 'analysis_elements_select'];

        if (in_array($element->element_type, $multiSelectTypes)) {
            // For multi-select fields, get all values
            $instanceValues = $instance->values()
                ->where('submission_form_element_id', $element->id)
                ->orderBy('array_index')
                ->get();

            if ($instanceValues->isEmpty()) {
                return null;
            }

            // Return comma-separated values
            return $instanceValues->pluck('value')->filter()->implode(',');
        }

        // For single-select fields, get first value
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
            case 'sample_condition_select':
            case 'store_select':
            case 'store_slot_select':
            case 'standard_select':
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
    private function createSampleHeader($formData, SubmissionFormInstance $instance, $batchCount = 1)
    {
        $headerData = $formData['sample_headers'] ?? [];

        // Generate batch code if not provided
        if (empty($headerData['batch_code'])) {
            $headerData['batch_code'] = $this->generateBatchCode($headerData, $instance->id, $batchCount, $instance);
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
            $crmUnit = CRMCompanyUnit::find($headerData['crm_unit_id']);
            if ($crmUnit) {
                $headerData['crm_unit_name'] = $crmUnit->name;
            }
        }

        $sampleHeader = SampleHeader::create($headerData);

        Log::info('Created sample header', [
            'sample_header_id' => $sampleHeader->id,
            'batch_code' => $sampleHeader->batch_code,
            'batch_count' => $batchCount
        ]);

        return $sampleHeader;
    }

    /**
     * Create sample details
     */
    private function createSampleDetails(SampleHeader $sampleHeader, $formData, SubmissionFormInstance $instance, $batchCount = 1)
    {
        $sampleDetails = [];
        $detailsData = $formData['sample_details'] ?? [];

        // If no sample details data, create a default sample
        if (empty($detailsData)) {
            $detailsData = [0 => []]; // Create one default sample
        }

        // Determine total sample count for smart code generation
        $sampleCount = $this->determineSampleCount($detailsData, $formData);

        foreach ($detailsData as $index => $detailData) {
            // Generate sample code if not provided
            if (empty($detailData['sample_code'])) {
                $detailData['sample_code'] = $this->generateSampleCode($sampleHeader, $detailData, $sampleHeader->batch_code, $sampleCount, $batchCount);
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
                'sample_code' => $sampleDetail->sample_code,
                'sample_count' => $sampleCount
            ]);
        }

        return $sampleDetails;
    }

    /**
     * Generate batch code using new format: {Submission-Form_instance_prefix}{batch_seq_no}/{YY}
     * Smart logic: If only 1 batch, reuse form_number; if multiple batches, use sequential codes
     */
    private function generateBatchCode($headerData, $submissionFormInstanceId = null, $batchCount = 1, $instance = null)
    {
        // If no submission form instance ID provided, fall back to old method
        if (!$submissionFormInstanceId) {
            return $this->generateLegacyBatchCode($headerData);
        }

        try {
            // Get submission form instance and its prefix
            if (!$instance) {
                $instance = \App\Models\SubmissionFormInstance::find($submissionFormInstanceId);
            }

            if (!$instance) {
                throw new \Exception('Submission form instance not found');
            }

            $submissionForm = $instance->submissionForm;
            if (!$submissionForm) {
                throw new \Exception('Submission form not found');
            }

            // SMART LOGIC: If only 1 batch, reuse the form_number
            if ($batchCount === 1) {
                $batchCode = $instance->form_number;

                Log::info('Smart batch code generation: Reusing form_number for single batch', [
                    'form_number' => $batchCode,
                    'batch_count' => $batchCount
                ]);

                return $batchCode;
            }

            // Multiple batches: Use sequential batch codes
            $prefix = $submissionForm->naming_convention_prefix ?? 'SF';
            $currentYear = date('Y');

            // Get next batch sequence for this form instance and year
            $batchSeqNo = \App\Models\BatchSequence::getNextBatchSequence($submissionFormInstanceId, $currentYear);

            // Generate batch code: {prefix}{batch_seq_no}/{YY}
            $batchCode = $prefix . sprintf('%03d', $batchSeqNo) . '/' . date('y');

            Log::info('Standard batch code generation for multiple batches', [
                'batch_code' => $batchCode,
                'batch_count' => $batchCount
            ]);

            return $batchCode;

        } catch (\Exception $e) {
            Log::error('Error generating new batch code: ' . $e->getMessage());
            // Fall back to legacy method
            return $this->generateLegacyBatchCode($headerData);
        }
    }

    /**
     * Legacy batch code generation (fallback)
     */
    private function generateLegacyBatchCode($headerData)
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
     * Generate sample code using new format: {Submission-Form_instance_prefix}{batch_seq_no}/{YY}-{sample_no_seq_no}
     * Smart logic: If only 1 sample, reuse batch_code; if multiple samples, use sequential codes
     */
    private function generateSampleCode(SampleHeader $sampleHeader, $detailData, $batchCode = null, $sampleCount = 1, $batchCount = 1)
    {
        // If batch code is provided, use new format
        if ($batchCode) {
            try {
                // SMART LOGIC: Only reuse batch_code if this is truly a single sample in a single batch
                if ($sampleCount === 1 && $batchCount === 1) {
                    $sampleCode = $batchCode;

                    Log::info('Smart sample code generation: Reusing batch_code for single sample in single batch', [
                        'batch_code' => $batchCode,
                        'sample_count' => $sampleCount,
                        'batch_count' => $batchCount
                    ]);

                    return $sampleCode;
                }

                // Multiple samples: Use sequential sample codes
                // Get next sample sequence for this batch
                $sampleSeqNo = \App\Models\SampleSequence::getNextSampleSequence($batchCode);

                // Generate sample code: {batch_code}-{sample_no_seq_no}
                $sampleCode = $batchCode . '-' . sprintf('%03d', $sampleSeqNo);

                Log::info('Standard sample code generation for multiple samples', [
                    'sample_code' => $sampleCode,
                    'sample_count' => $sampleCount,
                    'batch_count' => $batchCount
                ]);

                return $sampleCode;

            } catch (\Exception $e) {
                Log::error('Error generating new sample code: ' . $e->getMessage());
                // Fall back to legacy method
                return $this->generateLegacySampleCode($sampleHeader, $detailData);
            }
        }

        // Fall back to legacy method if no batch code provided
        return $this->generateLegacySampleCode($sampleHeader, $detailData);
    }

    /**
     * Legacy sample code generation (fallback)
     */
    private function generateLegacySampleCode(SampleHeader $sampleHeader, $detailData)
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
            if (empty($analysisTypeId))
                continue;

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

    /**
     * Count distinct sample_type_id values to determine batch count
     */
    private function countDistinctSampleTypes(SubmissionFormInstance $instance)
    {
        // Find sample_type_id element
        $sampleTypeElement = SubmissionFormElement::whereHas('holder.section', function ($query) use ($instance) {
            $query->where('submission_form_id', $instance->submission_form_id);
        })
            ->where('is_mapped', true)
            ->where('mapping_table', 'sample_headers')
            ->where('mapping_field', 'sample_type_id')
            ->first();

        if (!$sampleTypeElement) {
            // No sample type element found, default to 1 batch
            return 1;
        }

        // Get all distinct sample_type_id values from the form instance
        $distinctSampleTypes = $instance->values()
            ->where('submission_form_element_id', $sampleTypeElement->id)
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->pluck('value')
            ->unique()
            ->count();

        return $distinctSampleTypes > 0 ? $distinctSampleTypes : 1;
    }

    /**
     * Determine total sample count for a batch
     * Checks if data is split by sample_points, otherwise counts array rows
     */
    private function determineSampleCount($detailsData, $formData)
    {
        // If details data is empty, there's 1 default sample
        if (empty($detailsData)) {
            return 1;
        }

        // Check if sample_point_id exists in the details data
        $hasSamplePoints = false;
        $samplePointIds = [];

        foreach ($detailsData as $detailRow) {
            if (isset($detailRow['sample_point_id']) && !empty($detailRow['sample_point_id'])) {
                $hasSamplePoints = true;
                $samplePointIds[] = $detailRow['sample_point_id'];
            }
        }

        // If sample points exist, count unique sample points
        if ($hasSamplePoints) {
            $uniqueSamplePoints = array_unique($samplePointIds);
            return count($uniqueSamplePoints);
        }

        // Otherwise, count the number of detail rows
        return count($detailsData);
    }

    /**
     * Check if sample codes need regeneration when adding new samples to a batch
     */
    public function shouldRegenerateCodes(SampleHeader $sampleHeader)
    {
        // Check if batch has samples without sequential suffix
        // (i.e., sample_code equals batch_code, indicating 1:1 scenario)
        $samplesWithoutSuffix = $sampleHeader->samples()
            ->where('sample_code', $sampleHeader->batch_code)
            ->exists();

        return $samplesWithoutSuffix;
    }

    /**
     * Regenerate sample codes when adding samples to a batch that originally had 1 sample
     */
    public function regenerateSampleCodes(SampleHeader $sampleHeader)
    {
        DB::beginTransaction();

        try {
            $samples = $sampleHeader->samples()->orderBy('id', 'asc')->get();
            $batchCode = $sampleHeader->batch_code;

            Log::info('Regenerating sample codes for batch', [
                'batch_id' => $sampleHeader->id,
                'batch_code' => $batchCode,
                'sample_count' => $samples->count()
            ]);

            // Reset the sample sequence for this batch
            $sampleSequence = \App\Models\SampleSequence::where('batch_code', $batchCode)->first();
            if ($sampleSequence) {
                $sampleSequence->update(['sample_sequence' => 0]);
            } else {
                \App\Models\SampleSequence::create([
                    'batch_code' => $batchCode,
                    'sample_sequence' => 0
                ]);
            }

            $updatedCodes = [];

            // Regenerate codes for all samples with sequential suffixes
            foreach ($samples as $index => $sample) {
                $sequenceNo = $index + 1;
                $newSampleCode = $batchCode . '-' . sprintf('%03d', $sequenceNo);

                $oldCode = $sample->sample_code;
                $sample->update(['sample_code' => $newSampleCode]);

                $updatedCodes[] = [
                    'sample_id' => $sample->id,
                    'old_code' => $oldCode,
                    'new_code' => $newSampleCode
                ];

                Log::info('Regenerated sample code', [
                    'sample_id' => $sample->id,
                    'old_code' => $oldCode,
                    'new_code' => $newSampleCode
                ]);
            }

            // Update the sequence counter
            if ($sampleSequence) {
                $sampleSequence->update(['sample_sequence' => $samples->count()]);
            }

            DB::commit();

            return [
                'success' => true,
                'updated_codes' => $updatedCodes,
                'message' => 'Sample codes regenerated successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to regenerate sample codes', [
                'batch_id' => $sampleHeader->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to regenerate sample codes: ' . $e->getMessage()
            ];
        }
    }
}

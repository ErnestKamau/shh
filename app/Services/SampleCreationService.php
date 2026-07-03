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
use App\Zone;
use App\User;
use App\Models\SampleSubmissionRequest;
use App\Models\System\SystemConfiguration;
use App\Services\Sampleworkflow\JobSampleNumberingService;
use App\Services\Sampleworkflow\SampleDetailCreationService;
use App\Services\Sampleworkflow\TrfSampleFieldMapper;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SampleCreationService
{
    public function __construct(
        private readonly JobSampleNumberingService $numberingService,
        private readonly SampleDetailCreationService $sampleDetailCreationService,
    ) {}

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

            // Link the SampleSubmissionRequest to the created batch
            $requestId = $instance->getAttribute('sample_submission_request_id')
                ?? ($instance->getAttribute('target_record_type') === SampleSubmissionRequest::class
                    ? $instance->getAttribute('target_record_id')
                    : null)
                ?? $instance->getAttribute('portal_request_id');

            if ($requestId) {
                $submissionRequest = SampleSubmissionRequest::find($requestId);
                if ($submissionRequest) {
                    $submissionRequest->update([
                        'sample_header_id' => $sampleHeader->id,
                        'status' => 'received_at_lab',
                    ]);
                }
            }

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
                    if ($table === 'sample_details') {
                        $formData[$table][0][$field] = $value;
                    } else {
                        $formData[$table][$field] = $value;
                    }
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
            case 'customer_sample_point_select':
            case 'sample_condition_select':
            case 'store_select':
            case 'store_slot_select':
            case 'standard_select':
            case 'zone_select':
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

            case 'camera_photo':
            case 'image_upload':
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

        $requestId = $instance->getAttribute('sample_submission_request_id')
            ?? ($instance->getAttribute('target_record_type') === SampleSubmissionRequest::class
                ? $instance->getAttribute('target_record_id')
                : null)
            ?? $instance->getAttribute('portal_request_id');

        $submissionRequest = $requestId ? SampleSubmissionRequest::find($requestId) : null;

        if ($submissionRequest) {
            $headerData['crm_contact_id'] = $headerData['crm_contact_id'] ?? $submissionRequest->crm_contact_id;
            $headerData['receiving_officer_name'] = $headerData['receiving_officer_name'] ?? $submissionRequest->received_by_full_name;
            if (empty($headerData['receipt_date']) && ($submissionRequest->received_by_date || $submissionRequest->submission_date)) {
                $headerData['receipt_date'] = ($submissionRequest->received_by_date?->format('Y-m-d') ?? $submissionRequest->submission_date?->format('Y-m-d'));
            }
            $headerData['submit_by'] = $headerData['submit_by'] ?? ($submissionRequest->submitting_officer_full_name ?? $submissionRequest->submitted_by_full_name);
            $headerData['description'] = $headerData['description'] ?? $submissionRequest->description_of_samples;
            $headerData['batch_scope'] = $headerData['batch_scope'] ?? $submissionRequest->group_of_samples;
            $headerData['reference_number'] = $headerData['reference_number'] ?? $submissionRequest->gcla_file_reference_number;
            $headerData['schedule_customer_email'] = $headerData['schedule_customer_email'] ?? $submissionRequest->email;
            $headerData['case_id'] = $headerData['case_id'] ?? ($submissionRequest->is_police_sample ? ($submissionRequest->ir_number ?? $submissionRequest->case_no) : null);
        }

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

        // Recovery logic for CRM Unit
        if (empty($headerData['crm_unit_id']) || !$this->isValidUuid($headerData['crm_unit_id'])) {
            $headerData['crm_unit_id'] = $this->resolveUnitId($instance, $headerData['crm_customer_id'] ?? null);
            
            if ($this->isValidUuid($headerData['crm_unit_id'])) {
                Log::info('Recovered missing or invalid crm_unit_id for sample header', [
                    'instance_id' => $instance->id,
                    'recovered_id' => $headerData['crm_unit_id']
                ]);
            }
        }

        // Get CRM unit name from client unit ID
        if (!empty($headerData['crm_unit_id']) && empty($headerData['crm_unit_name'])) {
            $crmUnit = CRMCompanyUnit::find($headerData['crm_unit_id']);
            if ($crmUnit) {
                $headerData['crm_unit_name'] = $crmUnit->name;
            }
        }

        // Fallback for crm_unit_name if still missing (mandatory field)
        if (empty($headerData['crm_unit_name'])) {
            $headerData['crm_unit_name'] = 'N/A';
        }

        // Recovery logic for missing or invalid mandatory fields
        if (empty($headerData['crm_customer_id']) || !$this->isValidUuid($headerData['crm_customer_id'])) {
            $recoveredId = $instance->crm_customer_id ?? $this->resolveCustomerIdFromLinkedRequest($instance);
            
            if ($this->isValidUuid($recoveredId)) {
                $headerData['crm_customer_id'] = $recoveredId;
                Log::info('Recovered missing or invalid crm_customer_id for sample header', [
                    'instance_id' => $instance->id,
                    'recovered_id' => $headerData['crm_customer_id']
                ]);
            }
        }

        if (empty($headerData['sample_type_id']) || !$this->isValidUuid($headerData['sample_type_id'])) {
            $recoveredId = $this->resolveSampleTypeId($instance);
            
            if ($this->isValidUuid($recoveredId)) {
                $headerData['sample_type_id'] = $recoveredId;
                Log::info('Recovered missing or invalid sample_type_id for sample header', [
                    'instance_id' => $instance->id,
                    'recovered_id' => $headerData['sample_type_id']
                ]);
            }
        }

        if (empty($headerData['crm_customer_id']) || !$this->isValidUuid($headerData['crm_customer_id'])) {
            throw new \RuntimeException("Mandatory 'crm_customer_id' is missing or invalid and could not be recovered for form instance {$instance->id}");
        }

        if (empty($headerData['sample_type_id']) || !$this->isValidUuid($headerData['sample_type_id'])) {
            throw new \RuntimeException("Mandatory 'sample_type_id' is missing or invalid and could not be recovered for form instance {$instance->id}");
        }

        $instance->loadMissing(['values.element', 'crmCustomer']);
        $formData = app(SubmissionFormValueNormalizer::class)->valuesMapFromInstance($instance);
        $trfMapped = app(TrfSampleFieldMapper::class)->mapToSampleHeader($formData, [
            'crm_customer_id' => $headerData['crm_customer_id'] ?? $instance->crm_customer_id,
            'crm_contact_id' => $headerData['crm_contact_id'] ?? null,
            'email' => $submissionRequest?->email,
        ]);
        $headerData = app(TrfSampleFieldMapper::class)->mergeFillGaps($headerData, $trfMapped);

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

        $requestId = $instance->getAttribute('sample_submission_request_id')
            ?? ($instance->getAttribute('target_record_type') === SampleSubmissionRequest::class
                ? $instance->getAttribute('target_record_id')
                : null)
            ?? $instance->getAttribute('portal_request_id');

        $submissionRequest = $requestId ? SampleSubmissionRequest::find($requestId) : null;
        $exhibits = $submissionRequest
            ? $submissionRequest->exhibits()->orderBy('serial_number')->orderBy('id')->get()
            : collect();

        foreach ($detailsData as $index => $detailData) {
            $analysisTypeIdsStr = $detailData['analysis_type_id'] ?? null;

            $uuidColumns = ['lab_id', 'sample_condition_id', 'sample_point_id', 'company_product_id'];
            foreach ($uuidColumns as $col) {
                if (isset($detailData[$col])) {
                    if (empty($detailData[$col]) || ! $this->isValidUuid($detailData[$col])) {
                        $detailData[$col] = null;
                    }
                }
            }

            if (! empty($detailData['analysis_type_id']) && ! $this->isValidUuid($detailData['analysis_type_id'])) {
                unset($detailData['analysis_type_id']);
                $analysisTypeIdsStr = null;
            }

            unset($detailData['sample_code'], $detailData['sample_no'], $detailData['report_number']);

            $detailData['is_ammendment'] = $detailData['is_ammendment'] ?? 0;
            $detailData['ammendment_number'] = $detailData['ammendment_number'] ?? 1;
            $detailData['is_disposed'] = $detailData['is_disposed'] ?? 0;
            $detailData['created_at'] = now();
            $detailData['updated_at'] = now();

            if ($this->numberingService->isJobNumberFormat((string) $sampleHeader->batch_code)) {
                $prefix = (string) ($detailData['sample_code_prefix'] ?? '');
                if ($prefix === '' && ! empty($detailData['test_category'])) {
                    $prefix = $this->numberingService->resolveCategoryPrefixFromRow($detailData);
                }
                if ($prefix === '') {
                    $prefix = JobSampleNumberingService::PREFIX_CHEMISTRY;
                }

                $sampleDetail = $this->sampleDetailCreationService->create(
                    $sampleHeader,
                    $prefix,
                    $detailData,
                    $detailData['customer_sample_id'] ?? null,
                );
            } else {
                if (empty($detailData['sample_code'])) {
                    $detailData['sample_code'] = $this->generateLegacySampleCode($sampleHeader, $detailData);
                }

                $detailData['sample_header_id'] = $sampleHeader->id;
                $sampleDetail = SampleDetails::create($detailData);
            }

            $sampleDetails[] = $sampleDetail;

            // Link exhibit sequentially to this sample detail if available
            if ($submissionRequest && isset($exhibits[$index])) {
                $exhibit = $exhibits[$index];
                $exhibit->update(['sample_detail_id' => $sampleDetail->id]);

                $updates = [];
                if (empty($sampleDetail->comments) && !empty($exhibit->item_description)) {
                    $updates['comments'] = $exhibit->item_description;
                }
                if (empty($sampleDetail->barcode) && !empty($exhibit->serial_number)) {
                    $updates['barcode'] = $exhibit->serial_number;
                    $updates['customer_sample_id'] = $exhibit->serial_number;
                }
                if ($updates !== []) {
                    $sampleDetail->update($updates);
                }
            }

            // Create analysis relations if analysis types are specified
            if (!empty($analysisTypeIdsStr)) {
                $this->createDetailAnalysisRelation(
                    $sampleHeader->id,
                    $sampleDetail->id,
                    explode(',', $analysisTypeIdsStr)
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
     * Generate batch code using required format: [ZoneCode][YY]-[NNNNN]
     * Example: LZ26-00015
     */
    private function generateBatchCode($headerData, $submissionFormInstanceId = null, $batchCount = 1, $instance = null)
    {
        try {
            if (! $instance) {
                $instance = \App\Models\SubmissionFormInstance::find($submissionFormInstanceId);
            }

            $jobNumber = $this->numberingService->generateJobNumber();

            if ($instance !== null) {
                $this->numberingService->persistJobNumberOnSubmissionInstance($instance, $jobNumber);
            }

            Log::info('Generated job/batch code', [
                'batch_code' => $jobNumber,
                'batch_count' => $batchCount,
                'form_instance_id' => $instance?->id,
            ]);

            return $jobNumber;
        } catch (\Exception $e) {
            Log::error('Error generating job/batch code: ' . $e->getMessage());

            return $this->generateLegacyBatchCode($headerData);
        }
    }

    private function resolveZoneCodeForBatch(?SubmissionFormInstance $instance, array $formData = []): string
    {
        if ($instance) {
            $zoneFromRequest = $this->resolveZoneCodeFromLinkedSubmissionRequest($instance);
            if ($zoneFromRequest !== null) {
                return $zoneFromRequest;
            }

            $zoneFromInstance = $this->normalizeZoneCode($instance->getAttribute('zone_code'))
                ?? $this->resolveZoneCodeFromZoneId($instance->getAttribute('zone_id'))
                ?? $this->resolveZoneCodeFromFormValues($instance)
                ?? $this->resolveZoneCodeFromSubmittingUser($instance);

            if ($zoneFromInstance !== null) {
                return $zoneFromInstance;
            }
        }

        $zonesFromLabs = $this->resolveZoneCodesFromMappedLabs($formData);

        if ($zonesFromLabs->count() === 1) {
            return (string) $zonesFromLabs->first();
        }

        if ($zonesFromLabs->count() > 1) {
            throw new \RuntimeException(
                'Samples belong to multiple zones (' . $zonesFromLabs->implode(', ') . '). Split submission by zone or provide zone explicitly on submission request.'
            );
        }

        throw new \RuntimeException('Unable to resolve zone code for batch generation.');
    }

    private function resolveZoneCodeFromLinkedSubmissionRequest(SubmissionFormInstance $instance): ?string
    {
        $candidateIds = collect([
            $instance->getAttribute('sample_submission_request_id'),
            $instance->getAttribute('target_record_type') === SampleSubmissionRequest::class
                ? $instance->getAttribute('target_record_id')
                : null,
            $instance->getAttribute('portal_request_id'),
        ])
            ->filter(fn ($id) => !empty($id))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        foreach ($candidateIds as $requestId) {
            $submissionRequest = SampleSubmissionRequest::query()->find($requestId);
            if (! $submissionRequest) {
                continue;
            }

            $zoneCode = $this->normalizeZoneCode($submissionRequest->getAttribute('zone_code'))
                ?? $this->resolveZoneCodeFromZoneId($submissionRequest->getAttribute('zone_id'))
                ?? $this->normalizeZoneCode($submissionRequest->getAttribute('zone'));

            if ($zoneCode !== null) {
                return $zoneCode;
            }
        }

        return null;
    }

    private function resolveZoneCodeFromFormValues(SubmissionFormInstance $instance): ?string
    {
        $values = $instance->values()->with('element')->get();

        foreach ($values as $value) {
            $element = $value->element;
            if (! $element) {
                continue;
            }

            $name = Str::lower(trim(((string) $element->name) . ' ' . ((string) $element->label) . ' ' . ((string) $element->mapping_field)));
            if (! Str::contains($name, ['zone', 'submission zone'])) {
                continue;
            }

            $zoneCode = $this->normalizeZoneCode($value->value)
                ?? $this->resolveZoneCodeFromZoneId($value->value);

            if ($zoneCode !== null) {
                return $zoneCode;
            }
        }

        return null;
    }

    private function resolveZoneCodeFromSubmittingUser(SubmissionFormInstance $instance): ?string
    {
        if (empty($instance->submitted_by)) {
            return null;
        }

        $user = User::query()->select('id', 'zone_id')->find((string) $instance->submitted_by);
        if (! $user) {
            return null;
        }

        return $this->resolveZoneCodeFromZoneId($user->zone_id);
    }

    private function resolveZoneCodesFromMappedLabs(array $formData): \Illuminate\Support\Collection
    {
        $detailRows = $formData['sample_details'] ?? [];
        if (! is_array($detailRows)) {
            return collect();
        }

        $labIds = collect($detailRows)
            ->map(function ($row) {
                if (! is_array($row)) {
                    return null;
                }

                $labId = $row['lab_id'] ?? null;
                return !empty($labId) ? (string) $labId : null;
            })
            ->filter()
            ->unique()
            ->values();

        if ($labIds->isEmpty()) {
            return collect();
        }

        $zoneIds = Lab::query()
            ->whereIn('id', $labIds)
            ->whereNotNull('zone_id')
            ->pluck('zone_id')
            ->filter()
            ->unique()
            ->values();

        return Zone::query()
            ->whereIn('id', $zoneIds)
            ->pluck('key')
            ->map(fn ($key) => $this->normalizeZoneCode($key))
            ->filter()
            ->unique()
            ->values();
    }

    private function resolveZoneCodeFromZoneId($zoneId): ?string
    {
        if (empty($zoneId)) {
            return null;
        }

        $zone = Zone::query()->select('id', 'key')->find((string) $zoneId);
        if (! $zone) {
            return null;
        }

        return $this->normalizeZoneCode($zone->key);
    }

    private function normalizeZoneCode($zone): ?string
    {
        $value = strtoupper(trim((string) $zone));
        if ($value === '') {
            return null;
        }

        // Keep only alphanumeric zone tokens (e.g., LZ, MZ, CZ)
        $normalized = preg_replace('/[^A-Z0-9]/', '', $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function generateZoneYearBatchCode(string $zoneCode): string
    {
        $yy = date('y');
        $prefix = strtoupper($zoneCode) . $yy . '-';

        $existingCodes = SampleHeader::query()
            ->where('batch_code', 'like', $prefix . '%')
            ->pluck('batch_code');

        $maxSeq = 0;
        foreach ($existingCodes as $code) {
            $code = (string) $code;
            if (!str_starts_with($code, $prefix)) {
                continue;
            }

            $suffix = substr($code, strlen($prefix));
            if (ctype_digit($suffix)) {
                $maxSeq = max($maxSeq, (int) $suffix);
            }
        }

        $nextSeq = $maxSeq + 1;

        return $prefix . sprintf('%05d', $nextSeq);
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

            // Validate UUID if table expects it
            if (!$this->isValidUuid($analysisTypeId)) {
                Log::warning('Skipping invalid analysis_type_id for relation creation', [
                    'sample_detail_id' => $sampleDetailId,
                    'invalid_id' => $analysisTypeId
                ]);
                continue;
            }

            // Create the analysis relation
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
        if (! $this->numberingService->isJobNumberFormat((string) $sampleHeader->batch_code)) {
            return [
                'success' => false,
                'message' => 'Sample code regeneration is only supported for job-number batches.',
            ];
        }

        DB::beginTransaction();

        try {
            $samples = $sampleHeader->samples()->orderBy('id', 'asc')->get();

            Log::info('Regenerating sample codes for batch', [
                'batch_id' => $sampleHeader->id,
                'batch_code' => $sampleHeader->batch_code,
                'sample_count' => $samples->count(),
            ]);

            $updatedCodes = [];

            foreach ($samples as $sample) {
                $prefix = JobSampleNumberingService::PREFIX_CHEMISTRY;
                if (preg_match('/-([MLC])\d{3}$/', (string) $sample->sample_code, $matches)) {
                    $prefix = $matches[1];
                }

                $newSampleCode = $this->numberingService->nextSampleCode((string) $sampleHeader->batch_code, $prefix);
                $oldCode = $sample->sample_code;
                $sample->update([
                    'sample_code' => $newSampleCode,
                    'sample_no' => $this->numberingService->sampleNumberFromCode($newSampleCode),
                ]);

                $updatedCodes[] = [
                    'sample_id' => $sample->id,
                    'old_code' => $oldCode,
                    'new_code' => $newSampleCode,
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'updated_codes' => $updatedCodes,
                'message' => 'Sample codes regenerated successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to regenerate sample codes', [
                'batch_id' => $sampleHeader->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to regenerate sample codes: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Resolve customer ID from linked submission request
     */
    private function resolveCustomerIdFromLinkedRequest(SubmissionFormInstance $instance): ?string
    {
        $candidateIds = collect([
            $instance->getAttribute('sample_submission_request_id'),
            $instance->getAttribute('target_record_type') === SampleSubmissionRequest::class
                ? $instance->getAttribute('target_record_id')
                : null,
            $instance->getAttribute('portal_request_id'),
        ])
            ->filter(fn ($id) => !empty($id))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        foreach ($candidateIds as $requestId) {
            $submissionRequest = SampleSubmissionRequest::query()->find($requestId);
            if ($submissionRequest && !empty($submissionRequest->crm_customer_id)) {
                return (string) $submissionRequest->crm_customer_id;
            }
        }

        return null;
    }

    /**
     * Resolve sample type ID for the form instance
     */
    private function resolveSampleTypeId(SubmissionFormInstance $instance): ?string
    {
        // 1. Try to get from linked request
        $candidateIds = collect([
            $instance->getAttribute('sample_submission_request_id'),
            $instance->getAttribute('target_record_type') === SampleSubmissionRequest::class
                ? $instance->getAttribute('target_record_id')
                : null,
            $instance->getAttribute('portal_request_id'),
        ])
            ->filter(fn ($id) => !empty($id))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        foreach ($candidateIds as $requestId) {
            $submissionRequest = SampleSubmissionRequest::query()->find($requestId);
            if ($submissionRequest && !empty($submissionRequest->sample_type_id)) {
                return (string) $submissionRequest->sample_type_id;
            }
        }

        // 2. Try to get from form template sample types pivot
        $formSampleTypes = $instance->submissionForm->sampleTypes;
        if ($formSampleTypes->count() === 1) {
            return (string) $formSampleTypes->first()->id;
        }

        return null;
    }

    /**
     * Resolve CRM unit ID for the form instance
     */
    private function resolveUnitId(SubmissionFormInstance $instance, ?string $customerId): ?string
    {
        // 1. Try to get from linked request
        $candidateIds = collect([
            $instance->getAttribute('sample_submission_request_id'),
            $instance->getAttribute('target_record_type') === SampleSubmissionRequest::class
                ? $instance->getAttribute('target_record_id')
                : null,
            $instance->getAttribute('portal_request_id'),
        ])
            ->filter(fn ($id) => !empty($id))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        foreach ($candidateIds as $requestId) {
            $submissionRequest = SampleSubmissionRequest::query()->find($requestId);
            if ($submissionRequest && $this->isValidUuid($submissionRequest->crm_unit_id)) {
                return (string) $submissionRequest->crm_unit_id;
            }
        }

        // 2. Try to get from customer units (if only one exists)
        if ($this->isValidUuid($customerId)) {
            $customer = CRMCustomer::find($customerId);
            if ($customer && $customer->units->count() === 1) {
                return (string) $customer->units->first()->id;
            }
        }

        return null;
    }

    /**
     * Validate if a string is a valid UUID
     */
    private function isValidUuid($value): bool
    {
        if (empty($value) || !is_string($value)) {
            return false;
        }

        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $value) === 1;
    }
}

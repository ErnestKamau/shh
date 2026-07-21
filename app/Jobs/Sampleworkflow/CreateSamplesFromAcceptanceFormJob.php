<?php

namespace App\Jobs\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisType;
use App\Invoice;
use App\InvoiceDetails;
use App\Models\CRM\CRMCustomer;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\Sampleworkflow\AnalysisAcceptanceFormLine;
use App\Models\SubmissionFormInstance;
use App\SampleDate;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Billing\InvoiceNumberGenerator;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\Sampleworkflow\AcceptanceFormSampleHeaderService;
use App\Services\Sampleworkflow\JobSampleNumberingService;
use App\Services\Sampleworkflow\SampleAnalysisSetupService;
use App\Services\Sampleworkflow\SampleDetailCreationService;
use App\Services\Sampleworkflow\TrfSampleFieldMapper;
use App\TaxRegime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class CreateSamplesFromAcceptanceFormJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $acceptanceFormId
    ) {}

    public function handle(
        AcceptanceFormSampleHeaderService $sampleHeaderService,
        SampleAnalysisSetupService $analysisSetupService,
        AcceptanceFormPricingService $pricingService,
        AcceptanceFormSampleConfigService $sampleConfigService,
        InvoiceNumberGenerator $invoiceNumberGenerator,
        SampleDetailCreationService $sampleDetailCreationService,
        JobSampleNumberingService $numberingService,
    ): void {
        $form = AnalysisAcceptanceForm::query()
            ->with([
                'lines',
                'submissionFormInstance.batches',
                'sampleSubmissionRequest',
            ])
            ->find($this->acceptanceFormId);

        if (!$form || $form->sample_header_id) {
            return;
        }

        try {
            DB::transaction(function () use ($form, $sampleHeaderService, $analysisSetupService, $pricingService, $sampleConfigService, $invoiceNumberGenerator, $sampleDetailCreationService, $numberingService) {
                $approvedLines = $form->lines->where('is_approved', true)->values();
                if ($approvedLines->isEmpty()) {
                    throw new \RuntimeException('No approved analysis lines on acceptance form.');
                }

                $configPayload = is_array($form->sample_configuration_payload)
                    ? $form->sample_configuration_payload
                    : [];

                $primarySampleTypeId = $this->resolvePrimarySampleTypeId($form, $approvedLines, $configPayload);
                if ($primarySampleTypeId === '') {
                    throw new \RuntimeException(
                        'Cannot create sample batch: sample type is missing on the acceptance form. '
                        .'Ensure each sample configuration and analysis line has a sample type.'
                    );
                }

                $instance = $this->resolveLinkedSubmissionFormInstance($form);

                $primaryZoneId = $this->resolvePrimaryZoneIdFromConfig($configPayload);

                // Lab / lab section come from TRF receiving context and Analysis Type master data —
                // not from wizard overrides (Lab/Lab section columns are hidden on Acceptance).
                $headerAttributes = $sampleHeaderService->buildCreateAttributes(
                    $form,
                    $primarySampleTypeId,
                    $primaryZoneId,
                    null,
                );

                $batchCode = (string) $headerAttributes['batch_code'];
                $header = SampleHeader::query()->create($headerAttributes);

                if ($instance !== null && $numberingService->isJobNumberFormat($batchCode)) {
                    $numberingService->persistJobNumberOnSubmissionInstance($instance, $batchCode);
                }

                $candidateIds = collect([
                    $form->sample_submission_request_id,
                    $instance?->getAttribute('sample_submission_request_id'),
                    $instance?->getAttribute('target_record_type') === \App\Models\SampleSubmissionRequest::class
                        ? $instance?->getAttribute('target_record_id')
                        : null,
                    $instance?->getAttribute('portal_request_id'),
                ])
                    ->filter(fn ($id) => !empty($id))
                    ->map(fn ($id) => (string) $id)
                    ->unique()
                    ->values();

                $submissionRequestsById = \App\Models\SampleSubmissionRequest::query()
                    ->whereIn('id', $candidateIds->all())
                    ->get()
                    ->keyBy(fn ($request) => (string) $request->id);

                foreach ($candidateIds as $requestId) {
                    $submissionRequest = $submissionRequestsById->get($requestId);
                    if ($submissionRequest) {
                        $submissionRequest->update([
                            'sample_header_id' => $header->id,
                            'status' => 'received_at_lab',
                        ]);
                    }
                }

                $detailPlans = $configPayload !== []
                    ? $sampleConfigService->buildDetailPlansFromConfigs($configPayload)
                    : $this->buildDetailPlans($approvedLines, max(1, (int) $form->number_of_samples));

                $elementFlagOverrides = $this->resolveElementFlagOverrides($form, $candidateIds, $submissionRequestsById);

                $details = $this->createSampleDetails(
                    $header,
                    $detailPlans,
                    $analysisSetupService,
                    $sampleDetailCreationService,
                    $numberingService,
                    $form->created_by ? (string) $form->created_by : null,
                    $approvedLines,
                    $elementFlagOverrides,
                );

                $analysisSetupService->syncBatchLabSectionIdsFromAnalysisTypes($header->fresh());

                $targetDate = SampleDate::query()
                    ->where('sample_header_id', $header->id)
                    ->where('name', 'Target Date')
                    ->first() ?? new SampleDate();

                $targetDate->sample_header_id = $header->id;
                $targetDate->name = 'Target Date';
                $targetDate->date = $this->calculateTargetDate($header, $approvedLines, (string) $form->mode_of_work);
                $targetDate->save();

                $header->date_expected = $targetDate->date;
                $header->save();

                $invoice = $this->createInvoiceFromForm(
                    $form,
                    $header,
                    $details,
                    $pricingService,
                    $invoiceNumberGenerator,
                    $candidateIds,
                    $submissionRequestsById,
                );

                $acceptedQuotationId = $this->resolveAcceptedQuotationId($form, $candidateIds, $submissionRequestsById);
                if ($acceptedQuotationId !== null) {
                    $header->quote_id = $acceptedQuotationId;
                    $header->save();
                }

                $form->update([
                    'sample_header_id' => $header->id,
                    'invoice_id' => $invoice?->id,
                    'processing_error' => null,
                ]);

                $form->refresh();
                $sampleHeaderService->applyToBatch($header->fresh(), $form, $primaryZoneId);
                $sampleHeaderService->syncLinkedSubmissionForm($form);

                $actingUserId = $form->created_by ? (string) $form->created_by : null;
                $attachmentService = app(\App\Services\Sampleworkflow\BatchWorkflowDocumentAttachmentService::class);
                $attachmentService->attachSamplePhotos($header->fresh(), collect($details), $actingUserId);
                $attachmentService->attachForAcceptedBatch($header->fresh(), $actingUserId);
            });
        } catch (\Throwable $e) {
            Log::error('CreateSamplesFromAcceptanceFormJob failed', [
                'acceptance_form_id' => $this->acceptanceFormId,
                'error' => $e->getMessage(),
            ]);

            AnalysisAcceptanceForm::query()
                ->where('id', $this->acceptanceFormId)
                ->update([
                    'processing_error' => $e->getMessage(),
                    'status' => AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN,
                ]);

            throw $e;
        }
    }

    /**
     * @param  Collection<int, AnalysisAcceptanceFormLine>  $approvedLines
     * @param  list<array<string, mixed>>  $configPayload
     */
    private function resolvePrimarySampleTypeId(
        AnalysisAcceptanceForm $form,
        Collection $approvedLines,
        array $configPayload,
    ): string {
        foreach ($approvedLines as $line) {
            $sampleTypeId = trim((string) ($line->sample_type_id ?? ''));
            if ($sampleTypeId !== '') {
                return $sampleTypeId;
            }
        }

        foreach ($configPayload as $config) {
            if (! is_array($config)) {
                continue;
            }

            $sampleTypeId = trim((string) ($config['sample_type_id'] ?? ''));
            if ($sampleTypeId !== '') {
                return $sampleTypeId;
            }
        }

        $analysisTypeIds = $approvedLines
            ->pluck('analysis_type_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        if ($analysisTypeIds !== []) {
            $fromAnalysisType = AnalysisType::query()
                ->whereIn('id', $analysisTypeIds)
                ->whereNotNull('sample_type_id')
                ->value('sample_type_id');

            if ($fromAnalysisType) {
                return (string) $fromAnalysisType;
            }
        }

        $configAnalysisTypeIds = collect($configPayload)
            ->filter(fn ($config) => is_array($config))
            ->pluck('analysis_type_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        if ($configAnalysisTypeIds !== []) {
            $fromConfigAnalysisType = AnalysisType::query()
                ->whereIn('id', $configAnalysisTypeIds)
                ->whereNotNull('sample_type_id')
                ->value('sample_type_id');

            if ($fromConfigAnalysisType) {
                return (string) $fromConfigAnalysisType;
            }
        }

        $instance = $this->resolveLinkedSubmissionFormInstance($form);
        if ($instance !== null) {
            $instance->loadMissing(['values.element', 'submissionForm.sampleTypes', 'batches']);

            $fromBatch = trim((string) ($instance->batches->first()?->sample_type_id ?? ''));
            if ($fromBatch !== '') {
                return $fromBatch;
            }

            $fromFormTemplate = trim((string) ($instance->submissionForm?->sampleTypes?->first()?->id ?? ''));
            if ($fromFormTemplate !== '') {
                return $fromFormTemplate;
            }
        }

        if ($form->sample_submission_request_id) {
            $submissionRequest = $form->relationLoaded('sampleSubmissionRequest')
                ? $form->sampleSubmissionRequest
                : \App\Models\SampleSubmissionRequest::query()->find($form->sample_submission_request_id);

            $fromRequest = trim((string) ($submissionRequest?->sample_type_id ?? ''));
            if ($fromRequest !== '') {
                return $fromRequest;
            }
        }

        return '';
    }

    private function resolveLinkedSubmissionFormInstance(AnalysisAcceptanceForm $form): ?SubmissionFormInstance
    {
        if ($form->sample_submission_request_id) {
            $submissionRequest = $form->relationLoaded('sampleSubmissionRequest')
                ? $form->sampleSubmissionRequest
                : \App\Models\SampleSubmissionRequest::query()->find($form->sample_submission_request_id);

            if ($submissionRequest?->submission_form_instance_id) {
                $instance = SubmissionFormInstance::query()
                    ->with('batches')
                    ->find($submissionRequest->submission_form_instance_id);

                if ($instance !== null) {
                    return $instance;
                }
            }

            $instance = $submissionRequest?->resolveLinkedFormInstance();
            if ($instance !== null) {
                return $instance->loadMissing('batches');
            }
        }

        if ($form->submission_form_instance_id) {
            return SubmissionFormInstance::query()
                ->with('batches')
                ->find($form->submission_form_instance_id);
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $configPayload
     */
    private function resolvePrimaryZoneIdFromConfig(array $configPayload): ?string
    {
        foreach ($configPayload as $config) {
            if (! is_array($config)) {
                continue;
            }

            $zoneId = $config['zone_id'] ?? null;

            if ($zoneId !== null && (string) $zoneId !== '') {
                return (string) $zoneId;
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, AnalysisAcceptanceFormLine>  $approvedLines
     * @return list<array{sample_type_id: ?string, analysis_type_ids: list<string>, count: int}>
     */
    private function buildDetailPlans(Collection $approvedLines, int $numberOfSamples): array
    {
        $grouped = $approvedLines->groupBy(
            fn (AnalysisAcceptanceFormLine $line) => (string) ($line->sample_type_id ?? '')
        );

        $singleSampleTypeGroup = $grouped->count() === 1;
        $plans = [];

        foreach ($grouped as $sampleTypeKey => $linesForType) {
            $analysisTypeIds = $linesForType
                ->pluck('analysis_type_id')
                ->filter()
                ->unique()
                ->values()
                ->map(fn ($id) => (string) $id)
                ->all();

            $plans[] = [
                'sample_type_id' => $sampleTypeKey !== '' ? $sampleTypeKey : null,
                'analysis_type_ids' => $analysisTypeIds,
                'count' => $singleSampleTypeGroup ? $numberOfSamples : 1,
                'sample_code_prefix' => $this->inferPrefixForAnalysisTypes($analysisTypeIds),
            ];
        }

        return $plans;
    }

    /**
     * @param  list<array<string, mixed>>  $detailPlans
     * @param  \Illuminate\Support\Collection<int, AnalysisAcceptanceFormLine>  $approvedLines
     * @return list<SampleDetails>
     */
    private function createSampleDetails(
        SampleHeader $header,
        array $detailPlans,
        SampleAnalysisSetupService $analysisSetupService,
        SampleDetailCreationService $sampleDetailCreationService,
        JobSampleNumberingService $numberingService,
        ?string $actingUserId = null,
        $approvedLines = null,
        ?array $elementFlagOverrides = null,
    ): array {
        $usesLegacyCountShape = isset($detailPlans[0]['count']);

        $portalRequest = null;
        if ($header->submission_form_instance_id) {
            $portalRequest = \App\Models\SampleSubmissionRequest::where('sample_header_id', $header->id)
                ->orWhere('submission_form_instance_id', $header->submission_form_instance_id)
                ->first();
        }
        if (!$portalRequest && isset($header->id)) {
            $acceptanceForm = \App\Models\Sampleworkflow\AnalysisAcceptanceForm::where('sample_header_id', $header->id)->first();
            if ($acceptanceForm && $acceptanceForm->sample_submission_request_id) {
                $portalRequest = \App\Models\SampleSubmissionRequest::find($acceptanceForm->sample_submission_request_id);
            }
        }

        $exhibits = $portalRequest
            ? $portalRequest->exhibits()->orderBy('serial_number')->orderBy('id')->get()
            : collect();

        $trfRows = [];
        $formSamplingLocation = null;
        $crmCustomerId = (string) ($header->crm_customer_id ?? '');
        if ($header->submission_form_instance_id) {
            $sfi = SubmissionFormInstance::query()
                ->with('values.element')
                ->find($header->submission_form_instance_id);
            if ($sfi !== null) {
                $formData = app(\App\Services\SubmissionForm\SubmissionFormValueNormalizer::class)
                    ->valuesMapFromInstance($sfi);
                $trfRows = app(TrfSampleFieldMapper::class)->sampleRowsFromFormData($formData);
                $formSamplingLocation = isset($formData['sampling_location'])
                    ? (string) $formData['sampling_location']
                    : null;
            }
        }
        $trfMapper = app(TrfSampleFieldMapper::class);

        $allAnalysisTypeIds = collect($detailPlans)
            ->flatMap(fn (array $plan) => $plan['analysis_type_ids'] ?? [])
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        $analysisTypesById = AnalysisType::query()
            ->whereIn('id', $allAnalysisTypeIds)
            ->get()
            ->keyBy(fn (AnalysisType $type) => (string) $type->id);

        $elementsByAnalysisType = $analysisSetupService->preloadElementsByAnalysisType($allAnalysisTypeIds);
        $allElements = $elementsByAnalysisType->flatten(1);
        $reportingUnitsByKey = $analysisSetupService->preloadReportingUnits($allElements);
        $lab = $this->resolveLabOnce($header);

        $details = [];
        $detailIndex = 0;

        foreach ($detailPlans as $plan) {
            $iterations = $usesLegacyCountShape ? max(1, (int) $plan['count']) : 1;

            for ($i = 0; $i < $iterations; $i++) {
                $detailIndex++;
                $prefix = (string) ($plan['sample_code_prefix'] ?? '');
                if ($prefix === '' && ! empty($plan['analysis_type_ids'])) {
                    $prefix = $this->inferPrefixForAnalysisTypes($plan['analysis_type_ids'], $analysisTypesById, $numberingService);
                }

                $detailAttributes = [
                    'sample_type_id' => $plan['sample_type_id'] ?? null,
                    'analysis_type_id' => implode(',', $plan['analysis_type_ids'] ?? []),
                ];

                if (! $usesLegacyCountShape) {
                    if (! empty($plan['sample_condition_id'])) {
                        $detailAttributes['sample_condition_id'] = $plan['sample_condition_id'];
                    }
                    if (! empty($plan['main_standard_id'])) {
                        $detailAttributes['main_standard'] = $plan['main_standard_id'];
                    }
                    if (! empty($plan['secondary_standard_id'])) {
                        $detailAttributes['secondary_standard'] = $plan['secondary_standard_id'];
                    }
                    if (! empty($plan['lab_id'])) {
                        $detailAttributes['lab_id'] = $plan['lab_id'];
                    }
                    if (! empty($plan['disposal_date'])) {
                        $detailAttributes['disposal_date'] = $plan['disposal_date'];
                    }
                    if (! empty($plan['photo_path'])) {
                        $detailAttributes['photo_url'] = $plan['photo_path'];
                    }
                    if (! empty($plan['sample_marking'])) {
                        $detailAttributes['comments'] = $plan['sample_marking'];
                    }
                    if (! empty($plan['customer_sample_id'])) {
                        $detailAttributes['barcode'] = $plan['customer_sample_id'];
                        $detailAttributes['customer_sample_id'] = $plan['customer_sample_id'];
                    }
                    if (! empty($plan['zone_id'])) {
                        $detailAttributes['processing_zone_id'] = $plan['zone_id'];
                    }
                }

                $trfRowIndex = $detailIndex - 1;
                if (isset($trfRows[$trfRowIndex])) {
                    $detailAttributes = $trfMapper->mergeFillGaps(
                        $detailAttributes,
                        $trfMapper->mapToSampleDetail(
                            $trfRows[$trfRowIndex],
                            $trfRowIndex,
                            $crmCustomerId !== '' ? $crmCustomerId : null,
                            $formSamplingLocation,
                        ),
                    );
                } elseif ($formSamplingLocation !== null && $formSamplingLocation !== '') {
                    $pointId = $trfMapper->resolveSamplePointId(
                        $crmCustomerId !== '' ? $crmCustomerId : null,
                        $formSamplingLocation,
                    );
                    if ($pointId !== null) {
                        $detailAttributes = $trfMapper->mergeFillGaps(
                            $detailAttributes,
                            ['sample_point_id' => $pointId],
                        );
                    }
                }

                $detail = $sampleDetailCreationService->create(
                    $header,
                    $prefix,
                    $detailAttributes,
                    $plan['customer_sample_id'] ?? null,
                );

                $sampleCode = (string) $detail->sample_code;

                // Link exhibit sequentially to this sample detail if available
                if ($portalRequest && isset($exhibits[$detailIndex - 1])) {
                    $exhibit = $exhibits[$detailIndex - 1];
                    $exhibit->update(['sample_detail_id' => $detail->id]);

                    $updates = [];
                    if (empty($detail->comments) && !empty($exhibit->item_description)) {
                        $updates['comments'] = $exhibit->item_description;
                    }
                    if (empty($detail->barcode) && !empty($exhibit->serial_number)) {
                        $updates['barcode'] = $exhibit->serial_number;
                        $updates['customer_sample_id'] = $exhibit->serial_number;
                    }
                    if ($updates !== []) {
                        $detail->update($updates);
                    }
                }

                $analysisTypeIds = $plan['analysis_type_ids'] ?? [];
                $analysisSetupService->syncAnalysisRelations($header, $detail, $analysisTypeIds);

                $elementFilter = $usesLegacyCountShape
                    ? null
                    : ($plan['analysis_element_ids'] ?? []);

                $standardsByKey = $analysisSetupService->preloadStandardsForDetail($detail, $allElements);

                foreach ($analysisTypeIds as $analysisTypeId) {
                    $typeKey = (string) $analysisTypeId;
                    $analysisSetupService->createCapturedResultsForAnalysisType(
                        (string) $header->id,
                        (string) $detail->id,
                        $typeKey,
                        $sampleCode,
                        ! empty($plan['assigned_user_id'])
                            ? (string) $plan['assigned_user_id']
                            : $actingUserId,
                        is_array($elementFilter) && $elementFilter !== [] ? $elementFilter : null,
                        $elementFlagOverrides,
                        null,
                        [
                            'sample_header' => $header,
                            'sample_detail' => $detail,
                            'analysis_type' => $analysisTypesById->get($typeKey),
                            'lab' => $lab,
                            'reporting_units_by_key' => $reportingUnitsByKey,
                            'standards_by_key' => $standardsByKey,
                            'analysis_elements' => $elementsByAnalysisType->get($typeKey, collect()),
                        ],
                    );
                }

                $details[] = $detail;
            }
        }

        return $details;
    }

    private function resolveLabOnce(SampleHeader $header): ?\App\Lab
    {
        $labs = $header->labs(true);
        if (empty($labs)) {
            return null;
        }

        $labstr = implode(',', $labs);
        $labarr = explode(' - ', $labstr);
        if (count($labarr) >= 2) {
            return \App\Lab::where('code', $labarr[0])->where('name', $labarr[1])->first();
        }

        return null;
    }

    /**
     * @param  list<string>  $analysisTypeIds
     * @param  \Illuminate\Support\Collection<string, AnalysisType>|null  $analysisTypesById
     */
    private function inferPrefixForAnalysisTypes(
        array $analysisTypeIds,
        $analysisTypesById = null,
        ?JobSampleNumberingService $numberingService = null,
    ): string {
        $numberingService ??= app(JobSampleNumberingService::class);

        foreach ($analysisTypeIds as $analysisTypeId) {
            $analysis = $analysisTypesById?->get((string) $analysisTypeId)
                ?? AnalysisType::query()->find($analysisTypeId);
            if ($analysis) {
                return $numberingService->inferPrefixFromAnalysisTypeName($analysis->name);
            }
        }

        return JobSampleNumberingService::PREFIX_CHEMISTRY;
    }

    /**
     * @param  list<SampleDetails>  $details
     * @param  \Illuminate\Support\Collection<string, \App\Models\SampleSubmissionRequest>|null  $submissionRequestsById
     */
    private function createInvoiceFromForm(
        AnalysisAcceptanceForm $form,
        SampleHeader $header,
        array $details,
        AcceptanceFormPricingService $pricingService,
        InvoiceNumberGenerator $invoiceNumberGenerator,
        \Illuminate\Support\Collection $candidateIds,
        $submissionRequestsById = null,
    ): ?Invoice {
        $customer = CRMCustomer::query()->find($form->crm_customer_id);
        if (!$customer) {
            return null;
        }

        $pricelist = $pricingService->resolvePricelist((string) $form->crm_customer_id);
        if (!$pricelist) {
            return null;
        }

        $invoice = new Invoice();
        $invoice->pricelist_id = $pricelist->id;
        $invoice->currency_id = $pricelist->currency_id;
        $invoice->customer_id = $customer->id;
        $acceptedQuotationId = $this->resolveAcceptedQuotationId($form, $candidateIds, $submissionRequestsById);
        if ($acceptedQuotationId !== null) {
            $invoice->quotation_header_id = $acceptedQuotationId;
        }
        $invoice->save();

        $creditDays = (int) ($customer->credit_days ?? 0);
        $invoice->due_date = $creditDays > 0
            ? now()->addDays($creditDays)->format('Y-m-d')
            : now()->addDays(30)->format('Y-m-d');

        $invoice->invoice_number = $invoiceNumberGenerator->next();
        $invoice->save();

        if ($details === []) {
            return $invoice;
        }

        $taxRate = TaxRegime::query()->where('active', 1)->first();
        $approvedLines = $form->lines->where('is_approved', true);
        $analysisTypeIds = $approvedLines
            ->pluck('analysis_type_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $analysisTypesById = AnalysisType::query()
            ->whereIn('id', $analysisTypeIds)
            ->get()
            ->keyBy(fn (AnalysisType $type) => (string) $type->id);

        $existingDetailsByType = InvoiceDetails::query()
            ->where('invoice_id', $invoice->id)
            ->get()
            ->keyBy(fn (InvoiceDetails $detail) => (string) $detail->analysis_type);

        foreach ($approvedLines as $line) {
            if (empty($line->analysis_type_id)) {
                continue;
            }

            $typeKey = (string) $line->analysis_type_id;
            $existing = $existingDetailsByType->get($typeKey);

            $quantity = max(1, (int) $line->number_of_samples);
            $sellingPrice = (float) $line->unit_amount;

            if ($existing) {
                $existing->quantity = (int) $existing->quantity + $quantity;
                $existing->total = (float) $existing->total + ($sellingPrice * $quantity);
                $existing->save();
                continue;
            }

            $sampleDetail = $this->resolveSampleDetailForLine($details, $line);
            if (!$sampleDetail) {
                continue;
            }

            $analysis = $analysisTypesById->get($typeKey);
            $invoiceDetail = new InvoiceDetails();
            $invoiceDetail->crm_customer_id = $form->crm_customer_id;
            $invoiceDetail->analysis_type = $line->analysis_type_id;
            $invoiceDetail->analysis_type_name = $analysis->name ?? $line->parameter_label;
            $invoiceDetail->sample_header_id = $header->id;
            $invoiceDetail->sample_detail_id = $sampleDetail->id;
            $invoiceDetail->invoice_id = $invoice->id;
            $invoiceDetail->cost_price = $sellingPrice;
            $invoiceDetail->selling_price = $sellingPrice;
            $invoiceDetail->quantity = $quantity;

            if ($taxRate && $line->unit_amount > 0) {
                $tax = ($taxRate->value / 100) * $sellingPrice;
                $invoiceDetail->tax_rate = (string) $taxRate->value;
                $invoiceDetail->tax_amount = $tax * $quantity;
                $invoiceDetail->selling_amount = ($sellingPrice + $tax) * $quantity;
                $invoiceDetail->total = $invoiceDetail->selling_amount;
            } else {
                $invoiceDetail->selling_amount = $sellingPrice * $quantity;
                $invoiceDetail->total = $invoiceDetail->selling_amount;
            }

            $invoiceDetail->save();
            $existingDetailsByType->put($typeKey, $invoiceDetail);
        }

        $invoice->syncTotalsFromDetails();

        $header->invoice_id = $invoice->id;
        $header->save();

        return $invoice->fresh();
    }

    /**
     * @param  list<SampleDetails>  $details
     */
    private function resolveSampleDetailForLine(array $details, AnalysisAcceptanceFormLine $line): ?SampleDetails
    {
        if ($line->sample_type_id) {
            foreach ($details as $detail) {
                if ((string) $detail->sample_type_id === (string) $line->sample_type_id) {
                    return $detail;
                }
            }
        }

        return $details[0] ?? null;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $candidateIds
     * @param  \Illuminate\Support\Collection<string, \App\Models\SampleSubmissionRequest>|null  $submissionRequestsById
     * @return array<string, array{accredited: bool, subcontracted: bool}>
     */
    private function resolveElementFlagOverrides(
        AnalysisAcceptanceForm $form,
        \Illuminate\Support\Collection $candidateIds,
        $submissionRequestsById = null,
    ): array {
        $readinessService = app(EnquiryReceptionReadinessService::class);

        foreach ($candidateIds as $requestId) {
            $submissionRequest = $submissionRequestsById?->get((string) $requestId)
                ?? \App\Models\SampleSubmissionRequest::find($requestId);
            if ($submissionRequest === null) {
                continue;
            }

            $quotation = $readinessService->resolveAcceptedQuotation($submissionRequest);
            $flags = $readinessService->resolveElementFlagsFromQuotation($quotation);
            if ($flags !== []) {
                return $flags;
            }
        }

        if ($form->sample_submission_request_id) {
            $submissionRequest = $submissionRequestsById?->get((string) $form->sample_submission_request_id)
                ?? \App\Models\SampleSubmissionRequest::find($form->sample_submission_request_id);
            if ($submissionRequest !== null) {
                return $readinessService->resolveElementFlagsFromQuotation(
                    $readinessService->resolveAcceptedQuotation($submissionRequest)
                );
            }
        }

        return [];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $candidateIds
     * @param  \Illuminate\Support\Collection<string, \App\Models\SampleSubmissionRequest>|null  $submissionRequestsById
     */
    private function resolveAcceptedQuotationId(
        AnalysisAcceptanceForm $form,
        \Illuminate\Support\Collection $candidateIds,
        $submissionRequestsById = null,
    ): ?string {
        $readinessService = app(\App\Services\Commercial\EnquiryReceptionReadinessService::class);

        foreach ($candidateIds as $requestId) {
            $submissionRequest = $submissionRequestsById?->get((string) $requestId)
                ?? \App\Models\SampleSubmissionRequest::find($requestId);
            if ($submissionRequest === null) {
                continue;
            }

            $quotation = $readinessService->resolveAcceptedQuotation($submissionRequest);
            if ($quotation !== null) {
                return (string) $quotation->id;
            }
        }

        if ($form->sample_submission_request_id) {
            $submissionRequest = $submissionRequestsById?->get((string) $form->sample_submission_request_id)
                ?? \App\Models\SampleSubmissionRequest::find($form->sample_submission_request_id);
            if ($submissionRequest !== null) {
                $quotation = $readinessService->resolveAcceptedQuotation($submissionRequest);

                return $quotation !== null ? (string) $quotation->id : null;
            }
        }

        return null;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, AnalysisAcceptanceFormLine>  $approvedLines
     */
    private function calculateTargetDate(SampleHeader $header, \Illuminate\Support\Collection $approvedLines, string $modeOfWork): string
    {
        $analysisTypeIds = $approvedLines
            ->pluck('analysis_type_id')
            ->filter()
            ->unique()
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();

        $maxReportingTime = 7;

        if ($analysisTypeIds !== []) {
            $analysisMaxReportingTime = (int) (AnalysisType::query()
                ->whereIn('id', $analysisTypeIds)
                ->max('reporting_time') ?? 0);
            $elementsMaxReportingTime = (int) (AnalysisElements::query()
                ->whereIn('analysis_type_id', $analysisTypeIds)
                ->max('reporting_time') ?? 0);

            $maxReportingTime = max(1, $analysisMaxReportingTime, $elementsMaxReportingTime);
        }

        if (strtolower($modeOfWork) === 'express') {
            $maxReportingTime = max(1, (int) ceil($maxReportingTime * 0.75));
        }

        $baseDate = $header->receipt_date
            ? Carbon::parse($header->receipt_date)
            : now();

        return $baseDate->copy()->addDays($maxReportingTime)->format('Y-m-d');
    }

}

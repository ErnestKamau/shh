<?php

namespace App\Jobs\Sampleworkflow;

use App\Invoice;
use App\InvoiceDetails;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\Sampleworkflow\AnalysisAcceptanceFormLine;
use App\Models\SubmissionFormInstance;
use App\SampleDate;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Billing\InvoiceNumberGenerator;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\Sampleworkflow\AcceptanceFormSampleHeaderService;
use App\Services\Sampleworkflow\SampleAnalysisSetupService;
use App\TaxRegime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        InvoiceNumberGenerator $invoiceNumberGenerator
    ): void {
        $form = AnalysisAcceptanceForm::query()
            ->with(['lines', 'submissionFormInstance.batches'])
            ->find($this->acceptanceFormId);

        if (!$form || $form->sample_header_id) {
            return;
        }

        try {
            DB::transaction(function () use ($form, $sampleHeaderService, $analysisSetupService, $pricingService, $sampleConfigService, $invoiceNumberGenerator) {
                $approvedLines = $form->lines->where('is_approved', true)->values();
                if ($approvedLines->isEmpty()) {
                    throw new \RuntimeException('No approved analysis lines on acceptance form.');
                }

                $primarySampleTypeId = (string) ($approvedLines->first()->sample_type_id ?? '');
                $instance = $form->submission_form_instance_id
                    ? SubmissionFormInstance::query()->with('batches')->find($form->submission_form_instance_id)
                    : null;

                [$crmUnitId, $crmUnitName] = $this->resolveCrmUnit($instance, (string) ($form->crm_customer_id ?? ''));

                $configPayload = is_array($form->sample_configuration_payload)
                    ? $form->sample_configuration_payload
                    : [];

                $primaryZoneId = $this->resolvePrimaryZoneIdFromConfig($configPayload);

                $headerAttributes = $sampleHeaderService->buildCreateAttributes(
                    $form,
                    $primarySampleTypeId,
                    $primaryZoneId,
                    $crmUnitId,
                    $crmUnitName,
                    $form->created_by ? (string) $form->created_by : null,
                );

                $batchCode = (string) $headerAttributes['batch_code'];
                $header = SampleHeader::query()->create($headerAttributes);

                $detailPlans = $configPayload !== []
                    ? $sampleConfigService->buildDetailPlansFromConfigs($configPayload)
                    : $this->buildDetailPlans($approvedLines, max(1, (int) $form->number_of_samples));

                $details = $this->createSampleDetails(
                    $header,
                    $batchCode,
                    $detailPlans,
                    $analysisSetupService,
                    $form->created_by ? (string) $form->created_by : null,
                    $approvedLines
                );

                $targetDate = SampleDate::query()
                    ->where('sample_header_id', $header->id)
                    ->where('name', 'Target Date')
                    ->first() ?? new SampleDate();

                $targetDate->sample_header_id = $header->id;
                $targetDate->name = 'Target Date';
                $targetDate->date = now()->addDays(7)->format('Y-m-d');
                $targetDate->save();

                $invoice = $this->createInvoiceFromForm($form, $header, $details, $pricingService, $invoiceNumberGenerator);

                $form->update([
                    'sample_header_id' => $header->id,
                    'invoice_id' => $invoice?->id,
                    'processing_error' => null,
                ]);
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
     * @param  list<array<string, mixed>>  $configPayload
     */
    private function resolvePrimaryZoneIdFromConfig(array $configPayload): ?string
    {
        foreach ($configPayload as $config) {
            if (! is_array($config)) {
                continue;
            }

            $zoneId = $config['zone_id'] ?? $config['lab_id'] ?? null;

            if ($zoneId !== null && (string) $zoneId !== '') {
                return (string) $zoneId;
            }
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $configPayload
     */
    private function resolvePrimaryLabIdFromConfig(array $configPayload): ?string
    {
        foreach ($configPayload as $config) {
            $labId = $config['lab_id'] ?? null;
            if ($labId !== null && (string) $labId !== '') {
                return (string) $labId;
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
        string $batchCode,
        array $detailPlans,
        SampleAnalysisSetupService $analysisSetupService,
        ?string $actingUserId = null,
        $approvedLines = null
    ): array {
        $usesLegacyCountShape = isset($detailPlans[0]['count']);
        $totalDetails = $usesLegacyCountShape
            ? array_sum(array_column($detailPlans, 'count'))
            : count($detailPlans);

        $details = [];
        $detailIndex = 0;

        foreach ($detailPlans as $plan) {
            $iterations = $usesLegacyCountShape ? max(1, (int) $plan['count']) : 1;

            for ($i = 0; $i < $iterations; $i++) {
                $detailIndex++;
                $sampleCode = $this->resolveSampleCode($batchCode, $detailIndex, max(1, $totalDetails));

                $detailData = [
                    'sample_header_id' => $header->id,
                    'sample_type_id' => $plan['sample_type_id'] ?? null,
                    'sample_code' => $sampleCode,
                    'analysis_type_id' => implode(',', $plan['analysis_type_ids'] ?? []),
                ];

                if (!$usesLegacyCountShape) {
                    if (!empty($plan['sample_condition_id'])) {
                        $detailData['sample_condition_id'] = $plan['sample_condition_id'];
                    }
                    if (!empty($plan['main_standard_id'])) {
                        $detailData['main_standard'] = $plan['main_standard_id'];
                    }
                    if (!empty($plan['sample_marking'])) {
                        $detailData['comments'] = $plan['sample_marking'];
                    }
                    if (!empty($plan['customer_sample_id'])) {
                        $detailData['barcode'] = $plan['customer_sample_id'];
                    }
                    if (!empty($plan['zone_id'])) {
                        $detailData['processing_zone_id'] = $plan['zone_id'];
                    }
                }

                $detail = SampleDetails::query()->create($detailData);

                $analysisTypeIds = $plan['analysis_type_ids'] ?? [];
                $analysisSetupService->syncAnalysisRelations($header, $detail, $analysisTypeIds);

                $elementFilter = $usesLegacyCountShape
                    ? null
                    : ($plan['analysis_element_ids'] ?? []);

                foreach ($analysisTypeIds as $analysisTypeId) {
                    $analysisSetupService->createCapturedResultsForAnalysisType(
                        (string) $header->id,
                        (string) $detail->id,
                        $analysisTypeId,
                        $sampleCode,
                        $actingUserId,
                        is_array($elementFilter) && $elementFilter !== [] ? $elementFilter : null
                    );
                }

                $details[] = $detail;
            }
        }

        return $details;
    }

    private function resolveSampleCode(string $batchCode, int $detailIndex, int $totalDetails): string
    {
        if ($totalDetails <= 1) {
            return $batchCode;
        }

        return $batchCode . '-' . sprintf('%02d', $detailIndex);
    }

    /**
     * @param  list<SampleDetails>  $details
     */
    private function createInvoiceFromForm(
        AnalysisAcceptanceForm $form,
        SampleHeader $header,
        array $details,
        AcceptanceFormPricingService $pricingService,
        InvoiceNumberGenerator $invoiceNumberGenerator
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

        foreach ($form->lines->where('is_approved', true) as $line) {
            if (empty($line->analysis_type_id)) {
                continue;
            }

            $existing = InvoiceDetails::query()
                ->where('invoice_id', $invoice->id)
                ->where('analysis_type', $line->analysis_type_id)
                ->first();

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

            $analysis = getAnalysisTypeID($line->analysis_type_id);
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
        }

        $header->invoice_id = $invoice->id;
        $header->save();

        return $invoice;
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
     * Resolve crm_unit_id and crm_unit_name for the sample header.
     *
     * Priority:
     *  1. Form element mapped to `crm_unit_id` on `sample_headers`
     *  2. If the customer has exactly one unit, use it
     *  3. Fall back to null / 'N/A'
     *
     * @return array{0: ?string, 1: string}
     */
    private function resolveCrmUnit(?SubmissionFormInstance $instance, string $crmCustomerId): array
    {
        $unitId = null;

        // 1. Try the form instance's mapped crm_unit_id element
        if ($instance) {
            $unitValue = DB::table('submission_form_instance_values as v')
                ->join('submission_form_elements as e', 'e.id', '=', 'v.submission_form_element_id')
                ->where('v.submission_form_instance_id', $instance->id)
                ->where('e.mapping_field', 'crm_unit_id')
                ->value('v.value');

            if ($unitValue) {
                // values are encrypted; retrieve via the model so the cast is applied
                $unitId = $instance->values()
                    ->whereHas('element', fn ($q) => $q->where('mapping_field', 'crm_unit_id'))
                    ->value('value');
            }
        }

        // 2. If customer has exactly one unit, use it
        if (!$unitId && $crmCustomerId !== '') {
            $customer = CRMCustomer::query()->with('units')->find($crmCustomerId);
            if ($customer && $customer->units->count() === 1) {
                $unitId = (string) $customer->units->first()->id;
            }
        }

        if (!$unitId) {
            return [null, 'N/A'];
        }

        $unit = CRMCompanyUnit::query()->find($unitId);

        return [$unitId, $unit?->name ?? 'N/A'];
    }
}

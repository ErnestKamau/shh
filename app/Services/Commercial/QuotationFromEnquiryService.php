<?php

namespace App\Services\Commercial;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\CRM\CustomerContact;
use App\Models\SampleSubmissionRequest;
use App\QuotationDetailAnalysisSplit;
use App\QuotationDetails;
use App\QuotationHeader;
use App\SampleType;
use App\Services\Billing\QuotationLineTaxResolver;
use App\Services\Billing\QuotationPricingResolver;
use App\Services\Billing\QuotationReportService;
use App\Services\Billing\QuotationRevisionService;
use App\Services\Lab\UncertaintyBudgetResolver;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\SampleIntegrityCheckService;
use App\Services\SubmissionForm\SubmissionFormInstanceDocumentAttachmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

final class QuotationFromEnquiryService
{
    public function __construct(
        private AcceptanceFormPricingService $pricingService,
        private UncertaintyBudgetResolver $uncertaintyBudgetResolver,
        private QuotationLineTaxResolver $taxResolver,
        private QuotationRevisionService $quotationRevisionService,
        private QuotationPricingResolver $quotationPricingResolver,
    ) {}

    public function createOrOpen(SampleSubmissionRequest $enquiry): QuotationHeader
    {
        $enquiry = app(CommercialEnquiryCustomerResolver::class)->persistResolvedCustomer($enquiry);
        $enquiry->loadMissing(['customer', 'contact', 'requestedAnalyses']);

        if ($enquiry->current_quotation_header_id) {
            $existing = QuotationHeader::query()->find($enquiry->current_quotation_header_id);
            if ($existing !== null) {
                $existing = $this->syncHeaderCustomerFromEnquiry($existing, $enquiry);
                $existing = $this->syncHeaderPricelistAndCurrency($existing, $enquiry);

                if ($enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW
                    && $existing->sent_to_customer_at !== null) {
                    return $this->createRevision($enquiry, $existing);
                }

                return $existing;
            }
        }

        return $this->createNewFromEnquiry($enquiry);
    }

    public function createNewFromEnquiry(SampleSubmissionRequest $enquiry): QuotationHeader
    {
        $enquiry = app(CommercialEnquiryCustomerResolver::class)->persistResolvedCustomer($enquiry);
        $enquiry->loadMissing(['customer', 'contact', 'requestedAnalyses']);

        return DB::transaction(function () use ($enquiry): QuotationHeader {
            $contactId = $this->resolveContactId($enquiry);
            $pricelist = $this->pricingService->resolvePricelist((string) $enquiry->crm_customer_id);
            $preparedById = Auth::id();

            if ($preparedById === null) {
                throw new RuntimeException('You must be signed in to create a quotation.');
            }

            $header = new QuotationHeader();
            $header->crm_customer_id = $enquiry->crm_customer_id;
            $header->crm_customer_contact_id = $contactId;
            $header->quote_date = now()->toDateString();
            $header->expiring_date = now()->addDays(30)->toDateString();
            $header->prepared_by_id = (string) $preparedById;
            $header->quotation_type = 'Analysis';
            $header->status = QuotationApprovalService::HEADER_STATUS_IN_PREPARATION;
            $header->from_enquiry = true;
            $header->sample_submission_request_id = $enquiry->id;
            $header->pricelist_id = $pricelist?->id;
            $header->currency_id = $pricelist?->currency_id
                ?? $enquiry->customer?->currency_id;
            $header->is_draft = 0;
            $header->is_complete = 0;
            $header->is_approved = 0;
            $header->show_loq_column = true;
            $header->show_mu_column = true;
            $header->show_unit_price_column = true;
            $header->save();

            AmSpecQuotationNumberGenerator::assignIfMissing($header);

            app(QuotationReportService::class)->seedDefaultTermsOfSale($header);
            app(QuotationReportService::class)->seedDefaultStructuredTerms($header);

            $storedConfig = is_array($enquiry->enquiry_sample_configuration)
                ? $enquiry->enquiry_sample_configuration
                : [];
            if ($storedConfig === []) {
                $lines = $this->buildInlineLines($enquiry);
                if ($lines !== []) {
                    $this->persistInlineLines($header, $lines);
                }
            }

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS;
            $enquiry->save();

            return $header->fresh(['details']);
        });
    }

    public function detachExistingQuotationFromEnquiry(
        SampleSubmissionRequest $enquiry,
        string $quotationHeaderId,
    ): SampleSubmissionRequest {
        return DB::transaction(function () use ($enquiry, $quotationHeaderId): SampleSubmissionRequest {
            if ((string) $enquiry->current_quotation_header_id !== $quotationHeaderId) {
                return $enquiry;
            }

            $enquiry->current_quotation_header_id = null;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS;
            $enquiry->save();

            return $enquiry->fresh() ?? $enquiry;
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function buildInlineLines(SampleSubmissionRequest $enquiry): array
    {
        $enquiry->loadMissing('requestedAnalyses');
        $customerId = (string) $enquiry->crm_customer_id;
        $pricelist = $this->pricingService->resolvePricelist($customerId);
        $lines = [];

        foreach ($enquiry->requestedAnalyses as $index => $analysis) {
            $sampleTypeId = (string) ($analysis->sample_type_id ?? $enquiry->sample_type_id ?? $enquiry->batch_sample_type_id ?? '');
            $analysisTypeId = (string) ($analysis->analysis_type_id ?? $enquiry->matrix_id ?? '');
            $elementId = (string) ($analysis->analysis_element_id ?? $analysis->analysis_key ?? '');
            $qty = max(1, (int) ($analysis->number_of_samples ?? $enquiry->number_of_samples ?? 1));
            $isSubcontracted = false;

            $label = (string) ($analysis->analysis_label ?? 'Parameter');
            if ($elementId !== '') {
                $element = AnalysisElements::query()->with('analyte')->find($elementId);
                if ($element !== null) {
                    $label = (string) ($element->analyte->name ?? $element->name ?? $label);
                    $isSubcontracted = (bool) ($element->sub_contracted ?? false);
                }
            }

            $unitPrice = $this->pricingService->resolveLinePrice(
                $pricelist,
                $sampleTypeId,
                $analysisTypeId,
                $elementId !== '' ? $elementId : null,
            );

            $lines[] = [
                'line_no' => $index + 1,
                'sample_type_id' => $sampleTypeId,
                'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
                'analysis_type_id' => $analysisTypeId,
                'analysis_type_name' => $analysisTypeId !== '' ? (AnalysisType::find($analysisTypeId)?->name ?? '') : '',
                'analysis_element_id' => $elementId !== '' ? $elementId : null,
                'parameter_label' => $label,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'tax' => $this->taxResolver->resolveLineTaxPercent(
                    $pricelist,
                    $sampleTypeId !== '' ? $sampleTypeId : null,
                    $analysisTypeId,
                    $elementId !== '' ? $elementId : null,
                ),
                'subcontracted' => $isSubcontracted,
            ];
        }

        if ($lines === [] && is_array($enquiry->parameter_ids) && $enquiry->parameter_ids !== []) {
            foreach ($enquiry->parameter_ids as $index => $parameterId) {
                $sampleTypeId = (string) ($enquiry->sample_type_id ?? '');
                $analysisTypeId = (string) ($enquiry->matrix_id ?? '');
                $elementId = trim((string) $parameterId);
                $qty = max(1, (int) ($enquiry->number_of_samples ?? 1));
                $lines[] = [
                    'line_no' => $index + 1,
                    'sample_type_id' => $sampleTypeId,
                    'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
                    'analysis_type_id' => $analysisTypeId,
                    'analysis_type_name' => $analysisTypeId !== '' ? (AnalysisType::find($analysisTypeId)?->name ?? '') : '',
                    'analysis_element_id' => $elementId,
                    'parameter_label' => 'Parameter',
                    'quantity' => $qty,
                    'unit_price' => $this->pricingService->resolveLinePrice($pricelist, $sampleTypeId, $analysisTypeId, $elementId),
                    'tax' => $this->taxResolver->resolveLineTaxPercent(
                        $pricelist,
                        $sampleTypeId !== '' ? $sampleTypeId : null,
                        $analysisTypeId,
                        $elementId,
                    ),
                    'subcontracted' => $this->isElementSubcontracted($elementId),
                ];
            }
        }

        return $this->uncertaintyBudgetResolver->enrichLinesWithLabMetrics(
            $this->pricingService->applyPackagePricingToLines(
                $lines,
                $customerId,
                $this->pricingService->resolveCustomerAssignedPricelist($customerId) ?? $pricelist,
            )
        );
    }

    /**
     * @param  list<array<string, mixed>>  $acceptanceLines
     * @return list<array<string, mixed>>
     */
    public function buildInlineLinesFromAcceptanceLines(SampleSubmissionRequest $enquiry, array $acceptanceLines): array
    {
        $customerId = (string) $enquiry->crm_customer_id;
        $preferredPricelist = $this->pricingService->resolveCustomerAssignedPricelist($customerId);
        $lines = [];

        foreach ($acceptanceLines as $index => $line) {
            $sampleTypeId = (string) ($line['sample_type_id'] ?? '');
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');
            $elementId = (string) ($line['analysis_element_id'] ?? '');
            $physicalSampleCount = max(1, (int) ($line['number_of_samples'] ?? $line['quantity'] ?? 1));
            $resolved = $this->pricingService->resolveLinePriceWithPricelist(
                $customerId,
                $sampleTypeId !== '' ? $sampleTypeId : null,
                $analysisTypeId,
                $elementId !== '' ? $elementId : null,
                $preferredPricelist,
            );
            // Prefer an explicit positive amount; otherwise use the pricelist resolution.
            // Catalog/config expansion often sets unit_amount=0, which must not block Sync.
            $providedPrice = (float) ($line['unit_price'] ?? $line['unit_amount'] ?? 0);
            $unitPrice = $providedPrice > 0 ? $providedPrice : (float) $resolved['price'];
            $pricelistForTax = $resolved['pricelist'] ?? $preferredPricelist;
            $vatState = $this->taxResolver->resolveLineVatState(
                $pricelistForTax,
                $sampleTypeId !== '' ? $sampleTypeId : null,
                $analysisTypeId,
                $elementId !== '' ? $elementId : null,
            );

            $lines[] = [
                'line_no' => $index + 1,
                'sample_type_id' => $sampleTypeId,
                'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
                'analysis_type_id' => $analysisTypeId,
                'analysis_type_name' => $analysisTypeId !== '' ? (AnalysisType::find($analysisTypeId)?->name ?? '') : '',
                'analysis_element_id' => $elementId !== '' ? $elementId : null,
                'parameter_label' => (string) ($line['parameter_label'] ?? 'Parameter'),
                'acceptance_config_key' => $line['acceptance_config_key'] ?? null,
                'physical_sample_count' => $physicalSampleCount,
                'quantity' => $physicalSampleCount,
                'unit_price' => $unitPrice,
                'tax' => $vatState['tax'],
                'vat_from_pricelist' => $vatState['vat_from_pricelist'],
                'vat_manual' => false,
                'subcontracted' => (bool) (
                    $line['subcontracted']
                    ?? ($elementId !== '' ? $this->isElementSubcontracted($elementId) : false)
                ),
            ];
        }

        return $this->uncertaintyBudgetResolver->enrichLinesWithLabMetrics(
            $this->pricingService->applyPackagePricingToLines($lines, $customerId, $preferredPricelist)
        );
    }

    private function isElementSubcontracted(?string $analysisElementId): bool
    {
        $elementId = trim((string) $analysisElementId);
        if ($elementId === '') {
            return false;
        }

        return (bool) AnalysisElements::query()
            ->whereKey($elementId)
            ->value('sub_contracted');
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    public function applyTaxFromAssignedPricelist(SampleSubmissionRequest $enquiry, array $lines, bool $overwrite = true): array
    {
        $pricelist = $this->pricingService->resolveCustomerAssignedPricelist((string) $enquiry->crm_customer_id);

        return $this->taxResolver->applyTaxToLines($pricelist, $lines, $overwrite);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    public function applyTaxFromPricelist(SampleSubmissionRequest $enquiry, array $lines, bool $overwrite = true): array
    {
        $pricelist = $this->pricingService->resolvePricelist((string) $enquiry->crm_customer_id);

        return $this->taxResolver->applyTaxToLines($pricelist, $lines, $overwrite);
    }

    /**
     * Internal quotation snapshot for scheduled contract customers (not sent to customer).
     */
    public function createInternalContractQuotation(SampleSubmissionRequest $enquiry): QuotationHeader
    {
        return DB::transaction(function () use ($enquiry): QuotationHeader {
            $enquiry->loadMissing(['customer', 'contact', 'requestedAnalyses']);

            $contactId = $this->resolveContactId($enquiry);
            $pricelist = $this->pricingService->resolvePricelist((string) $enquiry->crm_customer_id);
            $preparedById = Auth::id();

            $header = new QuotationHeader();
            $header->crm_customer_id = $enquiry->crm_customer_id;
            $header->crm_customer_contact_id = $contactId;
            $header->quote_date = now()->toDateString();
            $header->expiring_date = now()->addDays(30)->toDateString();
            $header->prepared_by_id = $preparedById !== null ? (string) $preparedById : null;
            $header->quotation_type = 'Analysis';
            $header->status = 'Quote Complete';
            $header->from_enquiry = true;
            $header->sample_submission_request_id = $enquiry->id;
            $header->pricelist_id = $pricelist?->id;
            $header->currency_id = $pricelist?->currency_id
                ?? $enquiry->customer?->currency_id;
            $header->is_draft = 0;
            $header->is_complete = 1;
            $header->is_approved = 1;
            $header->show_loq_column = true;
            $header->show_mu_column = true;
            $header->show_unit_price_column = true;
            $header->save();

            AmSpecQuotationNumberGenerator::assignIfMissing($header);

            $lines = $this->buildInlineLines($enquiry);
            $this->persistInlineLines($header, $lines);

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->accepted_quotation_header_id = (string) $header->id;
            if ((string) $enquiry->pricing_source === '') {
                $enquiry->pricing_source = 'sampling_contract';
            }
            $enquiry->save();

            return $header->fresh(['details']);
        });
    }

    /**
     * Rebuild inline editor rows from a persisted quotation header.
     *
     * @return list<array<string, mixed>>
     */
    public function buildInlineLinesFromQuotationHeader(QuotationHeader $header): array
    {
        $header->loadMissing('details');

        $lines = [];
        $lineNo = 1;

        foreach ($header->details->sortBy('id')->values() as $detail) {
            foreach ($this->expandDetailToInlineRows($header, $detail) as $row) {
                $row['line_no'] = $lineNo++;
                $lines[] = $row;
            }
        }

        if ($lines === []) {
            return $lines;
        }

        return $this->uncertaintyBudgetResolver->enrichLinesWithLabMetrics($lines);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function expandDetailToInlineRows(QuotationHeader $header, QuotationDetails $detail): array
    {
        $sampleTypeId = trim((string) ($detail->sample_type ?? ''));
        $quantity = max(1, (int) ($detail->quantity ?? 1));
        $unitPrice = (float) ($detail->unit_price ?? 0);
        $tax = (float) ($detail->tax ?? 0);
        $subcontractedIds = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($detail->subcontracted_analytes ?? ''))
        )));

        if (($header->quotation_type ?? 'Analysis') === 'General') {
            return [[
                'sample_type_id' => $sampleTypeId,
                'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
                'analysis_type_id' => '',
                'analysis_type_name' => '',
                'analysis_element_id' => null,
                'parameter_label' => (string) ($detail->item_name ?: $detail->description ?: 'Item'),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax' => $tax,
                'vat_from_pricelist' => false,
                'vat_from_quotation' => true,
                'vat_manual' => false,
                'subcontracted' => false,
            ]];
        }

        if ((bool) ($detail->is_package ?? false)) {
            $elementIds = $this->quotationPricingResolver->collectElementIdsFromDetail($detail);
            $analysisTypeId = trim((string) ($detail->part_no ?? ''));
            $elementLabels = AnalysisElements::query()
                ->with('analyte:id,name')
                ->whereIn('id', $elementIds)
                ->orderBy('level')
                ->get()
                ->map(fn (AnalysisElements $element): string => (string) ($element->analyte?->name ?? $element->name ?? 'Parameter'))
                ->values()
                ->all();

            $analysisTypeName = $analysisTypeId !== ''
                ? (string) (AnalysisType::find($analysisTypeId)?->name ?? '')
                : '';

            return [[
                'sample_type_id' => $sampleTypeId,
                'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
                'analysis_type_id' => $analysisTypeId,
                'analysis_type_name' => $analysisTypeName,
                'analysis_element_id' => null,
                'parameter_label' => (string) ($detail->description ?: ($analysisTypeName.' package ('.count($elementIds).' parameters)')),
                'physical_sample_count' => $quantity,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax' => $tax,
                'vat_from_pricelist' => false,
                'vat_from_quotation' => true,
                'vat_manual' => false,
                'subcontracted' => $subcontractedIds !== [],
                'is_package' => true,
                'package_element_ids' => $elementIds,
                'package_element_labels' => $elementLabels,
            ]];
        }

        $elementIds = $this->quotationPricingResolver->collectElementIdsFromDetail($detail);
        $analysisTypeIds = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($detail->part_no ?? ''))
        )));

        $expandedRows = [];

        if ($elementIds !== []) {
            foreach ($elementIds as $elementId) {
                $element = AnalysisElements::query()->with('analyte')->find($elementId);
                $analysisTypeId = (string) ($element?->analysis_type_id ?? '');
                if ($analysisTypeId === '' && $analysisTypeIds !== []) {
                    $analysisTypeId = $analysisTypeIds[0];
                }

                $expandedRows[] = $this->makeInlineRowFromDetail(
                    $sampleTypeId,
                    $analysisTypeId,
                    $elementId,
                    (string) ($element?->analyte?->name ?? $element?->name ?? $detail->description ?? 'Parameter'),
                    $quantity,
                    $unitPrice,
                    $tax,
                    in_array($elementId, $subcontractedIds, true) || $this->isElementSubcontracted($elementId),
                );
            }
        } elseif ($analysisTypeIds !== []) {
            // Type-only quotation lines must not expand into every active element
            // under the analysis type — that incorrectly tests unselected parameters.
            foreach ($analysisTypeIds as $analysisTypeId) {
                $analysisType = AnalysisType::query()->find($analysisTypeId);
                $expandedRows[] = $this->makeInlineRowFromDetail(
                    $sampleTypeId,
                    $analysisTypeId,
                    null,
                    (string) ($analysisType?->name ?? $detail->description ?? 'Analysis'),
                    $quantity,
                    $unitPrice,
                    $tax,
                    false,
                );
            }
        } else {
            $expandedRows[] = $this->makeInlineRowFromDetail(
                $sampleTypeId,
                '',
                null,
                (string) ($detail->description ?? 'Parameter'),
                $quantity,
                $unitPrice,
                $tax,
                false,
            );
        }

        if (count($expandedRows) <= 1) {
            return $expandedRows;
        }

        $pricePerRow = round($unitPrice / count($expandedRows), 2);
        $remainder = round($unitPrice - ($pricePerRow * count($expandedRows)), 2);

        foreach ($expandedRows as $index => &$row) {
            $row['unit_price'] = $pricePerRow + ($index === 0 ? $remainder : 0.0);
        }
        unset($row);

        return $expandedRows;
    }

    /**
     * @return array<string, mixed>
     */
    private function makeInlineRowFromDetail(
        string $sampleTypeId,
        string $analysisTypeId,
        ?string $elementId,
        string $parameterLabel,
        int $quantity,
        float $unitPrice,
        float $tax,
        bool $subcontracted,
    ): array {
        return [
            'sample_type_id' => $sampleTypeId,
            'sample_type_name' => $sampleTypeId !== '' ? (SampleType::find($sampleTypeId)?->name ?? '') : '',
            'analysis_type_id' => $analysisTypeId,
            'analysis_type_name' => $analysisTypeId !== '' ? (AnalysisType::find($analysisTypeId)?->name ?? '') : '',
            'analysis_element_id' => $elementId !== null && $elementId !== '' ? $elementId : null,
            'parameter_label' => $parameterLabel,
            'physical_sample_count' => $quantity,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax' => $tax,
            'vat_from_pricelist' => false,
            'vat_from_quotation' => true,
            'vat_manual' => false,
            'subcontracted' => $subcontracted,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function persistInlineLines(QuotationHeader $header, array $lines, ?SampleSubmissionRequest $enquiry = null): void
    {
        QuotationDetails::query()->where('quotation_header_id', $header->id)->delete();

        $subTotal = 0.0;
        $taxTotal = 0.0;
        $subcontractedElementIds = [];

        foreach ($lines as $line) {
            $qty = max(1, (int) ($line['physical_sample_count'] ?? $line['quantity'] ?? 1));
            $unitPrice = (float) ($line['unit_price'] ?? 0);
            $taxRate = (float) ($line['tax'] ?? 0);
            $analysisTypeId = (string) ($line['analysis_type_id'] ?? '');
            $isPackage = ! empty($line['is_package']);
            $packageElementIds = $isPackage
                ? array_values(array_filter(array_map('strval', $line['package_element_ids'] ?? [])))
                : [];
            $elementId = $isPackage
                ? implode(',', $packageElementIds)
                : (string) ($line['analysis_element_id'] ?? '');

            if (! $isPackage && ! empty($line['subcontracted']) && $elementId !== '') {
                $subcontractedElementIds[] = $elementId;
            }

            $detail = QuotationDetails::query()->create([
                'quotation_header_id' => $header->id,
                'sample_type' => $line['sample_type_id'] ?? null,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'tax' => $taxRate,
                'part_no' => $analysisTypeId,
                'accredited_analytes' => $elementId,
                'subcontracted_analytes' => ! $isPackage && ! empty($line['subcontracted']) ? $elementId : '',
                'default_analytes' => $elementId,
                'sub_acc_analytes' => '',
                'description' => (string) ($line['parameter_label'] ?? ''),
                'test_method' => (string) ($line['test_method'] ?? ''),
                'loq' => (string) ($line['loq'] ?? ''),
                'mu_percent' => (string) ($line['mu_percent'] ?? ''),
                'is_package' => $isPackage,
            ]);

            if ($analysisTypeId !== '') {
                QuotationDetailAnalysisSplit::query()->create([
                    'quotation_detail_id' => $detail->id,
                    'analysis_type_id' => $analysisTypeId,
                ]);
            }

            $extended = $qty * $unitPrice;
            $subTotal += $extended;
            if ($taxRate > 0) {
                $taxTotal += ($taxRate / 100) * $extended;
            }
        }

        $header->sub_total = $subTotal;
        $header->tax = $taxTotal;
        $header->total_amount = $subTotal + $taxTotal;
        $header->save();

        $linkedEnquiry = $enquiry
            ?? SampleSubmissionRequest::query()
                ->where('current_quotation_header_id', $header->id)
                ->orWhere('accepted_quotation_header_id', $header->id)
                ->first();

        if ($linkedEnquiry !== null) {
            app(SampleIntegrityCheckService::class)->syncSubcontractedElementIdsOntoEnquiry(
                $linkedEnquiry,
                $subcontractedElementIds,
            );
        }
    }

    public function generatePdf(QuotationHeader $header): QuotationHeader
    {
        $this->ensureHeaderReadyForPrint($header);

        $header = app(QuotationReportService::class)->storePdf($header->fresh());

        if (empty($header->upload_url)) {
            throw new RuntimeException('PDF was not saved. Check quotation lines and customer contact.');
        }

        return $header;
    }

    public function sendToCustomer(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $header,
        bool $sendPortal = true,
        bool $sendEmail = false,
    ): SampleSubmissionRequest {
        if (! $sendPortal && ! $sendEmail) {
            throw new RuntimeException('Select at least one delivery channel (portal or email).');
        }

        // Enquiry-built quotations (build new) require lab-manager approval first.
        // Reused existing quotes are already Quote Complete + approved.
        if ((bool) $header->from_enquiry && (int) $header->is_approved !== 1) {
            app(QuotationApprovalService::class)->assertReadyToSend($header);
        }

        $enquiry = DB::transaction(function () use ($enquiry, $header, $sendEmail): SampleSubmissionRequest {
            if (empty($header->upload_url)) {
                $header = $this->generatePdf($header);
            }

            $now = now();
            $header->sent_to_customer_at = $now;
            $header->email_to_customer = $sendEmail ? $now->toDateString() : $header->email_to_customer;
            $header->status = QuotationApprovalService::HEADER_STATUS_COMPLETE;
            $header->is_approved = 1;
            $header->is_complete = 1;
            $header->save();

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_SENT;
            app(QuotationAcceptanceTatService::class)->stampFirstSentAt($enquiry, $now);
            $enquiry->save();

            $instance = $enquiry->submissionFormInstance;
            if ($instance !== null) {
                try {
                    $uploaderId = Auth::id() !== null ? (string) Auth::id() : null;
                    if ($uploaderId === null || ! Str::isUuid($uploaderId)) {
                        $uploaderId = trim((string) ($header->approved_by ?? $header->created_by ?? '')) ?: null;
                    }

                    app(SubmissionFormInstanceDocumentAttachmentService::class)->attachQuotation(
                        $instance,
                        $header->fresh() ?? $header,
                        $uploaderId,
                    );
                } catch (\Throwable $exception) {
                    Log::warning('Failed to attach quotation PDF to submission instance.', [
                        'instance_id' => $instance->id,
                        'quotation_id' => $header->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }

            try {
                \App\Models\QuotationApprovalLog::query()->create([
                    'quotation_header_id' => $header->id,
                    'sample_submission_request_id' => $enquiry->id,
                    'actor_user_id' => Auth::id() !== null ? (string) Auth::id() : null,
                    'assignee_user_id' => $header->approved_by,
                    'action' => \App\Models\QuotationApprovalLog::ACTION_SENT,
                    'comments' => null,
                ]);
            } catch (\Throwable) {
                // Approval log is optional for legacy quotes without the table yet.
            }

            return $this->ensureEnquiryReflectsSentQuotation(
                $enquiry->fresh(['customer', 'contact', 'requestedAnalyses', 'currentQuotation', 'submissionFormInstance'])
                    ?? $enquiry,
                $header->fresh() ?? $header,
            );
        });

        // Deliver email after status/PDF commit so a mail failure cannot leave
        // the enquiry stuck on Quotation Ready to Send.
        if ($sendEmail && $enquiry->contact?->email) {
            try {
                $this->emailQuotation($enquiry->currentQuotation ?? $header, $enquiry);
            } catch (\Throwable $exception) {
                Log::warning('Quotation email delivery failed after send status was recorded.', [
                    'quotation_id' => $header->id,
                    'enquiry_id' => $enquiry->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $enquiry;
    }

    public function quotationWasSentToCustomer(SampleSubmissionRequest $enquiry): bool
    {
        $enquiry->loadMissing('currentQuotation');
        $quotation = $enquiry->currentQuotation;

        if ($quotation === null && $enquiry->current_quotation_header_id) {
            $quotation = QuotationHeader::query()->find($enquiry->current_quotation_header_id);
        }

        return $quotation !== null && $quotation->sent_to_customer_at !== null;
    }

    /**
     * Align enquiry status when a quotation was sent but the enquiry row was not updated (e.g. walk-in email before status fix).
     */
    public function ensureEnquiryReflectsSentQuotation(SampleSubmissionRequest $enquiry, ?QuotationHeader $header = null): SampleSubmissionRequest
    {
        $header ??= $enquiry->currentQuotation;

        if ($header === null && $enquiry->current_quotation_header_id) {
            $header = QuotationHeader::query()->find($enquiry->current_quotation_header_id);
        }

        if ($header === null || $header->sent_to_customer_at === null) {
            return $enquiry;
        }

        $terminalStatuses = [
            SampleSubmissionRequest::STATUS_QUOTATION_SENT,
            SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW,
            SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
            SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
        ];

        $dirty = false;

        if ((string) $enquiry->current_quotation_header_id !== (string) $header->id) {
            $enquiry->current_quotation_header_id = $header->id;
            $dirty = true;
        }

        if (! in_array((string) $enquiry->status, $terminalStatuses, true)) {
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_SENT;
            $dirty = true;
        }

        if ($dirty) {
            $enquiry->save();

            return $enquiry->fresh(['customer', 'contact', 'requestedAnalyses', 'currentQuotation']);
        }

        return $enquiry;
    }

    public function createRevision(SampleSubmissionRequest $enquiry, QuotationHeader $priorHeader): QuotationHeader
    {
        return DB::transaction(function () use ($enquiry, $priorHeader): QuotationHeader {
            $header = $this->quotationRevisionService->createRevision($priorHeader, [
                'from_enquiry' => true,
                'sample_submission_request_id' => $enquiry->id,
                'status' => QuotationApprovalService::HEADER_STATUS_IN_PREPARATION,
                'is_complete' => 0,
                'is_approved' => 0,
            ]);

            $enquiry->current_quotation_header_id = $header->id;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS;
            $enquiry->save();

            return $header->fresh(['details']);
        });
    }

    public function ensureHeaderReadyForPrint(QuotationHeader $header): QuotationHeader
    {
        $header->loadMissing(['customer', 'contact', 'sampleSubmissionRequest']);

        if (empty($header->crm_customer_id) && $header->sampleSubmissionRequest !== null) {
            $header = $this->syncHeaderCustomerFromEnquiry($header, $header->sampleSubmissionRequest);
        }

        if (empty($header->crm_customer_contact_id) && ! empty($header->crm_customer_id)) {
            $contactId = CustomerContact::query()
                ->where('crm_customer_id', $header->crm_customer_id)
                ->orderBy('id')
                ->value('id');

            if ($contactId !== null) {
                $header->crm_customer_contact_id = (string) $contactId;
                $header->save();
            }
        }

        if (empty($header->prepared_by_id) && Auth::id() !== null) {
            $header->prepared_by_id = (string) Auth::id();
            $header->save();
        }

        if (empty($header->crm_customer_id)) {
            throw new RuntimeException('Quotation is missing a customer.');
        }

        return $header->fresh() ?? $header;
    }

    private function resolveContactId(SampleSubmissionRequest $enquiry): ?string
    {
        if (! empty($enquiry->crm_contact_id)) {
            return (string) $enquiry->crm_contact_id;
        }

        $fallback = CustomerContact::query()
            ->where('crm_customer_id', $enquiry->crm_customer_id)
            ->orderBy('id')
            ->value('id');

        return $fallback !== null ? (string) $fallback : null;
    }

    /**
     * Complete, unexpired quotations for a customer (eligible for Process Enquiry "use existing").
     *
     * @return \Illuminate\Support\Collection<int, QuotationHeader>
     */
    public function eligibleQuotationsForCustomer(string $customerId): \Illuminate\Support\Collection
    {
        if ($customerId === '') {
            return collect();
        }

        return QuotationHeader::query()
            ->where('crm_customer_id', $customerId)
            ->where('status', 'Quote Complete')
            ->whereNull('sample_submission_request_id')
            ->whereNotNull('expiring_date')
            ->whereDate('expiring_date', '>=', now()->toDateString())
            ->orderByDesc('quote_date')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Bind an existing saved quotation to the enquiry without rebuilding lines.
     */
    public function attachExistingQuotation(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $header,
    ): QuotationHeader {
        return DB::transaction(function () use ($enquiry, $header): QuotationHeader {
            $lockedHeader = QuotationHeader::query()
                ->lockForUpdate()
                ->find($header->id);
            /** @var SampleSubmissionRequest|null $lockedEnquiry */
            $lockedEnquiry = SampleSubmissionRequest::query()
                ->lockForUpdate()
                ->find($enquiry->id);

            if ($lockedHeader === null || $lockedEnquiry === null) {
                throw new RuntimeException('The enquiry or quotation no longer exists.');
            }

            if ((string) $lockedHeader->crm_customer_id !== (string) $lockedEnquiry->crm_customer_id) {
                throw new RuntimeException('Selected quotation belongs to a different customer.');
            }

            if ((string) $lockedHeader->status !== 'Quote Complete') {
                throw new RuntimeException('Only completed quotations can be selected.');
            }

            $linkedEnquiryId = trim((string) ($lockedHeader->sample_submission_request_id ?? ''));
            if ($linkedEnquiryId !== '' && $linkedEnquiryId !== (string) $lockedEnquiry->id) {
                throw new RuntimeException('Selected quotation is already owned by another enquiry.');
            }

            $expiresOn = $lockedHeader->expiring_date !== null
                ? \Carbon\Carbon::parse($lockedHeader->expiring_date)->startOfDay()
                : null;
            if ($expiresOn === null || $expiresOn->lt(now()->startOfDay())) {
                throw new RuntimeException('Selected quotation has expired.');
            }

            $lockedHeader->sample_submission_request_id = $lockedEnquiry->id;
            $lockedHeader->from_enquiry = true;
            // Existing completed quotations are treated as already approved for customer send.
            $lockedHeader->is_approved = 1;
            $lockedHeader->is_complete = 1;
            $lockedHeader->save();

            $lockedEnquiry->current_quotation_header_id = $lockedHeader->id;
            if ($lockedEnquiry->status === SampleSubmissionRequest::STATUS_REQUESTED
                || $lockedEnquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW) {
                $lockedEnquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS;
            }
            $lockedEnquiry->save();

            $freshHeader = $lockedHeader->fresh(['details', 'currency']) ?? $lockedHeader;
            $this->seedEnquirySubcontractFlagsFromQuotation($lockedEnquiry, $freshHeader);

            return $freshHeader;
        }, attempts: 3);
    }

    /**
     * Element IDs marked Subcontracted on a billing quotation's Quotation Parameters.
     *
     * @return list<string>
     */
    public function subcontractedElementIdsFromQuotation(QuotationHeader $header): array
    {
        $header->loadMissing('details');
        $ids = [];

        foreach ($header->details as $detail) {
            foreach (explode(',', (string) ($detail->subcontracted_analytes ?? '')) as $rawId) {
                $elementId = trim($rawId);
                if ($elementId !== '') {
                    $ids[] = $elementId;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Prefill Integrity / enquiry subcontract flags from billing quote prep.
     */
    public function seedEnquirySubcontractFlagsFromQuotation(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $header,
    ): SampleSubmissionRequest {
        return app(SampleIntegrityCheckService::class)->syncSubcontractedElementIdsOntoEnquiry(
            $enquiry,
            $this->subcontractedElementIdsFromQuotation($header),
        );
    }

    /**
     * Soft mismatch messages: Step 2 parameter keys vs elements covered by the quotation.
     *
     * @param  list<array<string, mixed>>  $sampleConfigs
     * @return list<string>
     */
    public function quotationMismatchWarnings(array $sampleConfigs, QuotationHeader $header): array
    {
        $header->loadMissing('details');

        $configElementIds = [];
        foreach ($sampleConfigs as $config) {
            if (! is_array($config)) {
                continue;
            }
            foreach (is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [] as $key) {
                $id = trim((string) $key);
                if ($id !== '') {
                    $configElementIds[$id] = true;
                }
            }
        }
        $configIds = array_keys($configElementIds);

        $quoteElementIds = [];
        foreach ($header->details as $detail) {
            foreach ($this->quotationPricingResolver->collectElementIdsFromDetail($detail) as $elementId) {
                $quoteElementIds[$elementId] = true;
            }
        }
        $quoteIds = array_keys($quoteElementIds);

        $warnings = [];
        $missingOnQuote = array_values(array_diff($configIds, $quoteIds));
        $extraOnQuote = array_values(array_diff($quoteIds, $configIds));

        if ($missingOnQuote !== []) {
            $warnings[] = sprintf(
                'Sample configuration includes %d parameter(s) not covered by the selected quotation. The quotation terms still apply.',
                count($missingOnQuote)
            );
        }

        if ($extraOnQuote !== [] && $configIds !== []) {
            $warnings[] = sprintf(
                'Selected quotation includes %d parameter(s) not in the current sample configuration.',
                count($extraOnQuote)
            );
        }

        if ($configIds === [] && $quoteIds !== []) {
            $warnings[] = 'Sample configuration has no selected parameters; the quotation will still be sent as saved.';
        }

        return $warnings;
    }

    public function syncHeaderPricelistAndCurrency(
        QuotationHeader $header,
        SampleSubmissionRequest $enquiry,
    ): QuotationHeader {
        $enquiry->loadMissing('customer');
        $pricelist = $this->pricingService->resolvePricelist((string) $enquiry->crm_customer_id);
        $currencyId = $pricelist?->currency_id ?? $enquiry->customer?->currency_id;

        $dirty = false;

        if ($pricelist !== null && (string) $header->pricelist_id !== (string) $pricelist->id) {
            $header->pricelist_id = $pricelist->id;
            $dirty = true;
        }

        if ($currencyId !== null && (string) $header->currency_id !== (string) $currencyId) {
            $header->currency_id = $currencyId;
            $dirty = true;
        }

        if ($dirty) {
            $header->save();

            return $header->fresh(['currency']) ?? $header;
        }

        return $header;
    }

    private function syncHeaderCustomerFromEnquiry(
        QuotationHeader $header,
        SampleSubmissionRequest $enquiry,
    ): QuotationHeader {
        $enquiry = app(CommercialEnquiryCustomerResolver::class)->persistResolvedCustomer($enquiry);
        $enquiry->loadMissing(['customer', 'contact']);

        if (empty($header->crm_customer_id) && ! empty($enquiry->crm_customer_id)) {
            $header->crm_customer_id = $enquiry->crm_customer_id;
        }

        if (empty($header->crm_customer_contact_id)) {
            $contactId = $this->resolveContactId($enquiry);
            if ($contactId !== null) {
                $header->crm_customer_contact_id = $contactId;
            }
        }

        if ($header->isDirty()) {
            $header->save();
        }

        return $header->fresh(['customer', 'contact']) ?? $header;
    }

    private function emailQuotation(QuotationHeader $header, SampleSubmissionRequest $enquiry): void
    {
        $contact = $enquiry->contact ?? CustomerContact::query()->find($header->crm_customer_contact_id);
        if ($contact === null || empty($contact->email) || empty($header->upload_url)) {
            return;
        }

        $company = getActiveCompany();
        $name = trim(implode(' ', array_filter([
            $contact->first_name ?? '',
            $contact->middle_name ?? '',
            $contact->last_name ?? '',
        ])));
        $message = 'Quotation '.$header->quote_number.' has been sent to you from '.($company->name ?? 'GCLA').'. Kindly find it attached.';
        $body = 'Hi '.$name.', <br>'.$message.'<br>Regards,<br>'.($company->name ?? 'GCLA');
        $subject = '['.($company->name ?? 'GCLA').'] Quotation '.$header->quote_number;
        $file = storage_path('app'.$header->upload_url);
        notify_user($body, $contact->email, $subject, $file);
    }

    /**
     * Record in-person / walk-in client acceptance of a sent quotation.
     *
     * @param  array{
     *     signature?: string|null,
     *     signer_name?: string|null,
     *     contact_id?: string|null,
     *     signed_at?: string|\DateTimeInterface|null,
     * }  $acceptance
     */
    public function recordWalkInAcceptance(
        SampleSubmissionRequest $enquiry,
        ?string $clientPoNumber = null,
        bool $poSkipped = false,
        array $acceptance = [],
    ): SampleSubmissionRequest {
        $enquiry = $this->ensureEnquiryReflectsSentQuotation(
            $enquiry->fresh(['currentQuotation'])
        );

        if (! $this->quotationWasSentToCustomer($enquiry)) {
            throw new RuntimeException('Quotation has not been sent to the customer yet.');
        }

        if ($enquiry->status !== SampleSubmissionRequest::STATUS_QUOTATION_SENT) {
            throw new RuntimeException('Quotation can only be accepted when the enquiry is Quotation Sent.');
        }

        $signature = trim((string) ($acceptance['signature'] ?? ''));
        $signerName = trim((string) ($acceptance['signer_name'] ?? ''));

        if ($signature === '' || ! str_starts_with($signature, 'data:image/')) {
            throw new RuntimeException('Customer signature is required to accept the quotation.');
        }

        if ($signerName === '') {
            throw new RuntimeException('Customer signer name is required to accept the quotation.');
        }

        $quotation = $enquiry->currentQuotation;
        if ($quotation === null) {
            throw new RuntimeException('No current quotation is linked to this enquiry.');
        }

        $acceptedAt = now();
        $signedAt = ! empty($acceptance['signed_at'])
            ? \Illuminate\Support\Carbon::parse($acceptance['signed_at'])
            : $acceptedAt;

        DB::transaction(function () use (
            $enquiry,
            $quotation,
            $acceptedAt,
            $signedAt,
            $signature,
            $signerName,
            $acceptance,
        ): void {
            $quotation->customer_acceptance_signature = $signature;
            $quotation->customer_acceptance_signer_name = $signerName;
            $quotation->customer_acceptance_signed_at = $signedAt;
            $quotation->customer_acceptance_contact_id = filled($acceptance['contact_id'] ?? null)
                ? (string) $acceptance['contact_id']
                : null;
            $quotation->customer_acceptance_channel = QuotationHeader::ACCEPTANCE_CHANNEL_WALK_IN;
            $quotation->save();

            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED;
            $enquiry->accepted_quotation_header_id = (string) $quotation->id;
            $enquiry->quotation_accepted_at = $acceptedAt;
            $enquiry->save();
        });

        $signedQuotation = null;

        try {
            $signedQuotation = app(QuotationReportService::class)->storePdf($quotation->fresh());
        } catch (\Throwable $exception) {
            Log::warning('Failed to regenerate quotation PDF after walk-in acceptance.', [
                'quotation_header_id' => (string) $quotation->id,
                'message' => $exception->getMessage(),
            ]);
        }

        if ($signedQuotation !== null) {
            $this->refreshQuotationAttachment($enquiry, $signedQuotation);
        }

        app(QuotationAcceptanceTatService::class)->recalculateCustomerTat((string) $enquiry->crm_customer_id);

        if ($clientPoNumber !== null || $poSkipped) {
            return app(EnquiryReceptionReadinessService::class)->markReadyForReception(
                $enquiry->fresh(),
                (string) $quotation->id,
                [
                    'client_po_number' => $clientPoNumber,
                    'po_skipped' => $poSkipped,
                ],
            );
        }

        return $enquiry->fresh(['customer', 'contact', 'requestedAnalyses']);
    }

    /**
     * The request attachment stores a physical copy of the quotation PDF taken when it was
     * sent, so it has to be re-copied once the signed PDF replaces it.
     */
    private function refreshQuotationAttachment(SampleSubmissionRequest $enquiry, QuotationHeader $quotation): void
    {
        $instance = $enquiry->submissionFormInstance;
        if ($instance === null || blank($quotation->upload_url)) {
            return;
        }

        try {
            app(SubmissionFormInstanceDocumentAttachmentService::class)->attachQuotation(
                $instance,
                $quotation,
                Auth::id(),
            );
        } catch (\Throwable $exception) {
            Log::warning('Failed to refresh the quotation attachment after acceptance.', [
                'instance_id' => (string) $instance->id,
                'quotation_header_id' => (string) $quotation->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}

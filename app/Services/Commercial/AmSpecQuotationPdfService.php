<?php

namespace App\Services\Commercial;

use App\AnalysisElements;
use App\AnalysisType;
use App\Company;
use App\Models\Currency;
use App\Models\SampleSubmissionRequest;
use App\QuotationDetailAnalysisSplit;
use App\QuotationDetails;
use App\QuotationHeader;
use App\SampleType;
use App\Services\Lab\UncertaintyBudgetResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use PDF;
use RuntimeException;

final class AmSpecQuotationPdfService
{
    public function __construct(
        private readonly UncertaintyBudgetResolver $uncertaintyBudgetResolver,
    ) {}
    /**
     * @return list<string>
     */
    private const DEFAULT_TERMS = [
        'This quotation is valid for thirty (30) days from the date of issue unless otherwise stated.',
        'Prices are quoted in the currency indicated and are exclusive of VAT unless stated otherwise.',
        'Turnaround time (TAT) commences upon receipt of samples in acceptable condition and clearance of any required advance payment.',
        'Samples must be submitted in appropriate containers with complete chain-of-custody documentation where applicable.',
        'The laboratory reserves the right to subcontract tests that cannot be performed in-house; subcontracted results are provided under the same accreditation scope where applicable.',
        'Reporting will be issued in the language specified on the test request form unless otherwise agreed in writing.',
        'Statement of conformity, where requested, will be reported in accordance with the agreed rules of decision.',
        'Samples not collected within the agreed retention period may be disposed of without further notice.',
        'Any dispute regarding this quotation must be raised in writing within seven (7) days of receipt.',
        'Payment terms are as per the customer account agreement or pro-forma invoice requirements.',
        'The laboratory is not liable for delays caused by factors outside its reasonable control.',
        'This quotation is valid for a minimum order value of AED __________.',
        'By accepting this quotation the client agrees to the laboratory terms and conditions of service.',
        'Acceptance of this quotation constitutes acceptance of terms and conditions on webpage: https://www.amspecgroup.com/terms-conditions',
    ];

    /** @var list<string> */
    private const META_CONFIG_KEYS = [
        'min_order_aed',
        'terms_url',
        'footer_text',
        'closing_text',
        'signature_company_name',
    ];

    public function generateAndStore(QuotationHeader $header): QuotationHeader
    {
        $header = AmSpecQuotationNumberGenerator::assignIfMissing($header->fresh() ?? $header);

        $header->is_print = 1;
        $header->save();

        $viewModel = $this->buildViewModel($header);
        $customer = getCrmCustomerByID($header->crm_customer_id);
        $customerName = preg_replace('/[^A-Za-z0-9]/', '', (string) ($customer->name ?? 'Customer'));
        $filename = urlencode(
            $customerName.'-'.$viewModel['laboratory_ref'].'-'.date('d-M-Y', strtotime(getTodayDate())).'.pdf'
        );
        $qrUrl = url('/storage/quotations/'.$customerName.'/'.$filename);
        $viewModel['qrcode'] = base64_encode(
            \QrCode::format('svg')->size(50)->errorCorrection('H')->generate($qrUrl)
        );

        $directory = storage_path('app/quotations/'.$customerName);
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Unable to create quotation storage directory.');
        }

        $pdf = PDF::loadView('layouts.lab.invoice.print-quotation-amspec', $viewModel);
        $pdf->getDomPDF()->set_option('enable_php', true);
        app(\App\Services\Reports\ReportWatermarkService::class)->applyToPdf($pdf, $viewModel['company'] ?? null);
        $pdf->save($directory.'/'.$filename);

        $header->upload_url = '/quotations/'.$customerName.'/'.$filename;
        $header->save();

        return $header->fresh() ?? $header;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildViewModel(QuotationHeader $header): array
    {
        $header->loadMissing(['customer', 'contact', 'details', 'sampleSubmissionRequest']);

        $company = getActiveCompany();
        $enquiry = $header->sampleSubmissionRequest;
        $details = QuotationDetails::query()
            ->where('quotation_header_id', $header->id)
            ->orderBy('id')
            ->get();

        $flatRows = $this->buildFlatRows($details, $enquiry);
        $groupedLines = $this->groupRows($flatRows);

        $subTotal = (float) ($header->sub_total ?? 0);
        $taxAmount = (float) ($header->tax ?? 0);
        $totalAmount = (float) ($header->total_amount ?? ($subTotal + $taxAmount));
        $vatRate = $subTotal > 0 ? round(($taxAmount / $subTotal) * 100, 2) : 0.0;

        $currency = $header->currency_id
            ? Currency::query()->find($header->currency_id)
            : null;

        $contactName = trim(implode(' ', array_filter([
            $header->contact?->first_name ?? '',
            $header->contact?->middle_name ?? '',
            $header->contact?->last_name ?? '',
        ])));

        $logos = $this->resolveLogoPaths($company);
        $watermark = app(\App\Services\Reports\ReportWatermarkService::class)->absolutePathFor($company)
            ?? $logos['primary'];

        return [
            'header' => $header,
            'company' => $company,
            'laboratory_ref' => (string) $header->quote_number,
            'quote_date' => $header->quote_date,
            'expiring_date' => $header->expiring_date,
            'attention' => $contactName !== '' ? $contactName : (string) ($header->customer?->name ?? ''),
            'subject' => $this->buildSubject($enquiry, $details),
            'company_unit' => $this->resolveCompanyUnit($enquiry, $header),
            'sampling_location' => $this->resolveSamplingLocation($enquiry),
            'customer_name' => (string) ($header->customer?->name ?? ''),
            'customer_address' => (string) ($header->customer?->physical_address ?? $header->customer?->postal_address ?? ''),
            'intro_salutation' => 'Dear Sir,',
            'intro_body' => $this->buildIntroBody($enquiry),
            'grouped_lines' => $groupedLines,
            'currency_code' => (string) ($currency?->code ?? $currency?->name ?? 'AED'),
            'net_amount' => $subTotal,
            'vat_rate' => $vatRate,
            'vat_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'terms' => $this->resolveTerms(),
            'footer_text' => $this->resolveConfigValue('footer_text', 'This quotation is issued subject to the Terms & Conditions on the following page and on https://www.amspecgroup.com/terms-conditions'),
            'closing_text' => $this->resolveConfigValue('closing_text', 'We hope our offer will meet with your requirements and look forward to work with you for long time. Should you require further information or assistance, please do not hesitate to contact us.'),
            'signature_company_name' => $this->resolveConfigValue('signature_company_name', 'AMSPEC MIDDLE EAST INSPECTION & TESTING SERVICES L.L.C'),
            'terms_url' => $this->resolveConfigValue('terms_url', 'https://www.amspecgroup.com/terms-conditions'),
            'logo_path' => $logos['primary'],
            'logo_secondary_path' => $logos['secondary'],
            'watermark_path' => $watermark,
            'qrcode' => '',
            'request_date_of_service' => $enquiry?->request_date_of_service,
            'service_priority' => $enquiry?->mode_of_service_priority ?? $enquiry?->priority,
            'show_loq_column' => (bool) ($header->show_loq_column ?? true),
            'show_mu_column' => (bool) ($header->show_mu_column ?? true),
            'show_unit_price_column' => (bool) ($header->show_unit_price_column ?? true),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, QuotationDetails>  $details
     * @return list<array<string, mixed>>
     */
    private function buildFlatRows($details, ?SampleSubmissionRequest $enquiry): array
    {
        $rows = [];
        $sno = 0;

        $detailIds = $details->pluck('id')->all();
        $splitsByDetail = QuotationDetailAnalysisSplit::query()
            ->whereIn('quotation_detail_id', $detailIds)
            ->get()
            ->groupBy('quotation_detail_id');

        $elementIds = [];
        foreach ($details as $detail) {
            $elementIds = array_merge($elementIds, $this->resolveElementIds($detail));
        }
        $elementIds = array_values(array_unique(array_filter($elementIds)));

        /** @var Collection<string, AnalysisElements> $elementsById */
        $elementsById = $elementIds === []
            ? collect()
            : AnalysisElements::query()->with(['mmethod', 'ltmethod', 'analyte', 'methodSequence'])->whereIn('id', $elementIds)->get()->keyBy('id');

        $analyteIds = $elementsById->pluck('analyte_id')->filter()->map(fn ($id) => (string) $id)->unique()->values()->all();
        $siblingsByAnalyte = $this->uncertaintyBudgetResolver->preloadActiveElementsByAnalyteIds($analyteIds);
        $budgetElements = $elementsById->values();
        foreach ($siblingsByAnalyte as $siblings) {
            $budgetElements = $budgetElements->merge($siblings);
        }
        $budgets = $this->uncertaintyBudgetResolver->preloadForElements($budgetElements->unique('id')->values());

        foreach ($details as $detail) {
            $detail->setRelation(
                'analysisSplits',
                $splitsByDetail->get($detail->id, collect())
            );

            $elementIds = $this->resolveElementIds($detail);

            if ($elementIds === []) {
                $sno++;
                $rows[] = $this->buildRow($sno, $detail, null, $enquiry, $elementsById, $budgets, $siblingsByAnalyte);

                continue;
            }

            foreach ($elementIds as $elementId) {
                $sno++;
                $rows[] = $this->buildRow($sno, $detail, $elementId, $enquiry, $elementsById, $budgets, $siblingsByAnalyte);
            }
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function resolveElementIds(QuotationDetails $detail): array
    {
        $raw = (string) ($detail->accredited_analytes ?: $detail->default_analytes ?: '');
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  Collection<string, AnalysisElements>  $elementsById
     * @param  Collection<int, \App\UncertaintyBudget>  $budgets
     */
    private function buildRow(
        int $sno,
        QuotationDetails $detail,
        ?string $elementId,
        ?SampleSubmissionRequest $enquiry,
        Collection $elementsById,
        Collection $budgets,
        ?Collection $siblingsByAnalyte = null,
    ): array {
        $element = $elementId !== null ? $elementsById->get($elementId) : null;

        $testName = (string) ($detail->description ?? '');
        if ($testName === '' && $element !== null) {
            $testName = (string) ($element->analyte?->name ?? $element->parametername ?? 'Test');
        }

        $metrics = $element !== null
            ? $this->uncertaintyBudgetResolver->resolveLabMetricsForElement($element, $budgets, $siblingsByAnalyte)
            : ['test_method' => '', 'loq' => '', 'mu_percent' => ''];

        $methodName = trim((string) ($detail->test_method ?? '')) !== ''
            ? (string) $detail->test_method
            : $metrics['test_method'];
        $loq = trim((string) ($detail->loq ?? '')) !== ''
            ? (string) $detail->loq
            : $metrics['loq'];
        $mu = trim((string) ($detail->mu_percent ?? '')) !== ''
            ? (string) $detail->mu_percent
            : $metrics['mu_percent'];

        return [
            'sno' => $sno,
            'test' => $testName,
            'method' => $methodName,
            'loq' => $loq,
            'mu' => $mu,
            'unit_price' => (float) $detail->unit_price,
            'total_price' => round((float) $detail->unit_price * (int) $detail->quantity, 2),
            'quantity' => (int) $detail->quantity,
            'subcontracted' => trim((string) $detail->subcontracted_analytes) !== '',
            'category' => $this->resolveCategory($detail, $enquiry),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{category: string, rows: list<array<string, mixed>>}>
     */
    private function groupRows(array $rows): array
    {
        $grouped = [];
        $categoryCounters = [];

        foreach ($rows as $row) {
            $category = (string) ($row['category'] ?? 'General');
            if (! isset($grouped[$category])) {
                $grouped[$category] = [];
                $categoryCounters[$category] = 0;
            }

            $categoryCounters[$category]++;
            $row['sno'] = $categoryCounters[$category];
            $grouped[$category][] = $row;
        }

        $result = [];
        foreach ($grouped as $category => $categoryRows) {
            $result[] = [
                'category' => $category,
                'rows' => $categoryRows,
            ];
        }

        return $result;
    }

    private function resolveCategory(QuotationDetails $detail, ?SampleSubmissionRequest $enquiry): string
    {
        $splits = $detail->relationLoaded('analysisSplits')
            ? $detail->getRelation('analysisSplits')
            : QuotationDetailAnalysisSplit::query()->where('quotation_detail_id', $detail->id)->get();

        foreach ($splits as $split) {
            $analysisTypeId = (string) ($split->analysis_type_id ?? '');
            if ($analysisTypeId !== '') {
                $name = AnalysisType::query()->find($analysisTypeId)?->name;
                if (is_string($name) && trim($name) !== '') {
                    return trim($name);
                }
            }
        }

        $partNo = trim((string) ($detail->part_no ?? ''));
        if ($partNo !== '') {
            $name = AnalysisType::query()->find($partNo)?->name;
            if (is_string($name) && trim($name) !== '') {
                return trim($name);
            }
        }

        if ($enquiry !== null && is_array($enquiry->sample_lines)) {
            foreach ($enquiry->sample_lines as $line) {
                if (! is_array($line)) {
                    continue;
                }

                $lineSampleType = (string) ($line['sample_type_id'] ?? '');
                $matchesSampleType = $lineSampleType !== '' && (string) $detail->sample_type === $lineSampleType;

                if ($matchesSampleType && ! empty($line['parameter_category'])) {
                    return (string) $line['parameter_category'];
                }
            }

            foreach ($enquiry->sample_lines as $line) {
                if (! is_array($line)) {
                    continue;
                }

                if (! empty($line['parameter_category'])) {
                    return (string) $line['parameter_category'];
                }
            }
        }

        if (! empty($detail->sample_type)) {
            return (string) (SampleType::find($detail->sample_type)?->name ?? 'General');
        }

        return 'General';
    }

    private function resolveSamplingLocation(?SampleSubmissionRequest $enquiry): string
    {
        if ($enquiry === null) {
            return '';
        }

        $collection = is_array($enquiry->collection_data) ? $enquiry->collection_data : [];
        if (! empty($collection['sampling_location'])) {
            return (string) $collection['sampling_location'];
        }

        if (is_array($enquiry->sample_lines)) {
            foreach ($enquiry->sample_lines as $line) {
                if (! is_array($line)) {
                    continue;
                }

                $location = (string) ($line['location'] ?? $line['sampling_point'] ?? '');
                if ($location !== '') {
                    return $location;
                }
            }
        }

        $enquiry->loadMissing('submissionFormInstance');
        $instance = $enquiry->submissionFormInstance ?? $enquiry->resolveLinkedFormInstance();
        if ($instance !== null) {
            $display = $instance->resolveDisplayValueByName('sampling_location');
            if (filled($display)) {
                return (string) $display;
            }
        }

        return '';
    }

    private function resolveCompanyUnit(?SampleSubmissionRequest $enquiry, QuotationHeader $header): string
    {
        $batch = \App\SampleHeader::query()
            ->where('quote_id', $header->id)
            ->orderByDesc('created_at')
            ->first();

        if ($batch !== null) {
            $fromName = trim((string) ($batch->crm_unit_name ?? ''));
            if ($fromName !== '') {
                return $fromName;
            }

            $fromId = $this->resolveCompanyUnitLabel((string) ($batch->crm_unit_id ?? ''));
            if ($fromId !== '') {
                return $fromId;
            }
        }

        if ($enquiry === null) {
            return '';
        }

        $enquiry->loadMissing('submissionFormInstance');
        $instance = $enquiry->submissionFormInstance ?? $enquiry->resolveLinkedFormInstance();

        if ($instance !== null) {
            foreach (['company_unit_id', 'company_unit', 'client_unit_id', 'client_unit'] as $fieldName) {
                $display = $instance->resolveDisplayValueByName($fieldName);
                if (filled($display)) {
                    $label = $this->resolveCompanyUnitLabel((string) $display);
                    if ($label !== '') {
                        return $label;
                    }
                }

                $raw = $instance->getValueByElementName($fieldName);
                if (filled($raw)) {
                    $label = $this->resolveCompanyUnitLabel((string) $raw);
                    if ($label !== '') {
                        return $label;
                    }
                }
            }
        }

        $collection = is_array($enquiry->collection_data) ? $enquiry->collection_data : [];
        foreach (['company_unit_id', 'company_unit', 'client_unit_id', 'crm_unit_name', 'crm_unit_id'] as $key) {
            if (! empty($collection[$key])) {
                $label = $this->resolveCompanyUnitLabel((string) $collection[$key]);
                if ($label !== '') {
                    return $label;
                }
            }
        }

        return '';
    }

    private function resolveCompanyUnitLabel(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (! \Illuminate\Support\Str::isUuid($value) && ! ctype_digit($value)) {
            return $value;
        }

        $unitName = \App\Models\CRM\CRMCompanyUnit::query()->whereKey($value)->value('name');

        return is_string($unitName) && trim($unitName) !== '' ? trim($unitName) : '';
    }

    /**
     * @param  \Illuminate\Support\Collection<int, QuotationDetails>  $details
     */
    private function buildSubject(?SampleSubmissionRequest $enquiry, $details): string
    {
        if ($enquiry?->purpose) {
            return 'Quotation for '.(string) $enquiry->purpose;
        }

        $sampleTypes = $details
            ->map(fn (QuotationDetails $detail): string => (string) (SampleType::find($detail->sample_type)?->name ?? ''))
            ->filter()
            ->unique()
            ->values();

        if ($sampleTypes->isNotEmpty()) {
            return 'Quotation for '.$sampleTypes->implode(', ');
        }

        return 'Laboratory Testing Services Quotation';
    }

    private function buildIntroBody(?SampleSubmissionRequest $enquiry): string
    {
        $body = 'Thank you for your enquiry, we are pleased to quote you our best prices for the following testing services';

        if ($enquiry?->purpose) {
            $body .= ' relating to '.(string) $enquiry->purpose;
        }

        $body .= '. Hopefully you will find it competitive and as per your requirements.';

        return $body;
    }

    /**
     * @return list<string>
     */
    private function resolveTerms(): array
    {
        $configType = getConfigTypeByName('AmSpec Quotation Terms');
        if ($configType === null) {
            return $this->applyMinOrderToTerms(self::DEFAULT_TERMS);
        }

        $configs = getconfigByID($configType->id);
        if ($configs === null || $configs->isEmpty()) {
            return $this->applyMinOrderToTerms(self::DEFAULT_TERMS);
        }

        $terms = $configs
            ->filter(fn ($config): bool => ! in_array((string) $config->key, self::META_CONFIG_KEYS, true))
            ->sortBy('key')
            ->pluck('value')
            ->filter(fn ($value): bool => is_string($value) && trim($value) !== '')
            ->values()
            ->all();

        return $terms !== [] ? $this->applyMinOrderToTerms($terms) : $this->applyMinOrderToTerms(self::DEFAULT_TERMS);
    }

    /**
     * @param  list<string>  $terms
     * @return list<string>
     */
    private function applyMinOrderToTerms(array $terms): array
    {
        $minOrder = $this->resolveConfigValue('min_order_aed', '');
        if ($minOrder === '') {
            return $terms;
        }

        return array_map(function (string $term) use ($minOrder): string {
            return str_replace('__________', $minOrder, $term);
        }, $terms);
    }

    private function resolveConfigValue(string $key, string $default = ''): string
    {
        $configType = getConfigTypeByName('AmSpec Quotation Terms');
        if ($configType === null) {
            return $default;
        }

        $configs = getconfigByID($configType->id);
        $value = $configs?->firstWhere('key', $key)?->value;

        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        return $default;
    }

    /**
     * @return array{primary: string|null, secondary: string|null}
     */
    private function resolveLogoPaths(?Company $company): array
    {
        $primary = $this->resolveNamedLogoPath($company, 'quotation_primary')
            ?? $this->resolveFilePath($company?->report_logo)
            ?? $this->resolveFilePath($company?->logo);

        $secondary = $this->resolveNamedLogoPath($company, 'quotation_secondary');

        return [
            'primary' => $primary,
            'secondary' => $secondary,
        ];
    }

    private function resolveNamedLogoPath(?Company $company, string $name): ?string
    {
        if ($company === null) {
            return null;
        }

        $path = $company->getReportLogoPath($name);
        if ($path === null || $path === $company->report_logo) {
            return null;
        }

        return $this->resolveFilePath($path);
    }

    private function resolveFilePath(mixed $candidate): ?string
    {
        if (! is_string($candidate) || $candidate === '') {
            return null;
        }

        if (File::exists($candidate)) {
            return $candidate;
        }

        if (str_starts_with($candidate, '/')) {
            $absolute = public_path(ltrim($candidate, '/'));
            if (File::exists($absolute)) {
                return $absolute;
            }
        }

        $storagePath = storage_path('app/'.ltrim($candidate, '/'));
        if (File::exists($storagePath)) {
            return $storagePath;
        }

        return null;
    }
}

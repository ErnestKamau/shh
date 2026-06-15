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
use Illuminate\Support\Facades\File;
use PDF;
use RuntimeException;

final class AmSpecQuotationPdfService
{
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

        return [
            'header' => $header,
            'company' => $company,
            'laboratory_ref' => (string) $header->quote_number,
            'quote_date' => $header->quote_date,
            'expiring_date' => $header->expiring_date,
            'attention' => $contactName !== '' ? $contactName : (string) ($header->customer?->name ?? ''),
            'subject' => $this->buildSubject($enquiry, $details),
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
            'watermark_path' => $logos['primary'],
            'qrcode' => '',
            'request_date_of_service' => $enquiry?->request_date_of_service,
            'service_priority' => $enquiry?->mode_of_service_priority ?? $enquiry?->priority,
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

        foreach ($details as $detail) {
            $detail->setRelation(
                'analysisSplits',
                $splitsByDetail->get($detail->id, collect())
            );

            $elementIds = $this->resolveElementIds($detail);

            if ($elementIds === []) {
                $sno++;
                $rows[] = $this->buildRow($sno, $detail, null, $enquiry);

                continue;
            }

            foreach ($elementIds as $elementId) {
                $sno++;
                $rows[] = $this->buildRow($sno, $detail, $elementId, $enquiry);
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
    private function buildRow(int $sno, QuotationDetails $detail, ?string $elementId, ?SampleSubmissionRequest $enquiry): array
    {
        $element = $elementId !== null
            ? AnalysisElements::query()->with(['mmethod', 'ltmethod', 'analyte'])->find($elementId)
            : null;

        $testName = (string) ($detail->description ?? '');
        if ($testName === '' && $element !== null) {
            $testName = (string) ($element->analyte?->name ?? $element->parametername ?? 'Test');
        }

        $methodName = '';
        if ($element !== null) {
            $methodName = (string) ($element->ltmethod?->name ?? $element->mmethod?->name ?? '');
        }

        $loq = '';
        if ($element !== null && $element->lod !== null && (float) $element->lod > 0) {
            $loq = rtrim(rtrim(number_format((float) $element->lod, 6, '.', ''), '0'), '.');
        }

        return [
            'sno' => $sno,
            'test' => $testName,
            'method' => $methodName,
            'loq' => $loq,
            'mu' => '',
            'unit_price' => (float) $detail->unit_price,
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

        return '';
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

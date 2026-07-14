<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\Analyte;
use App\Models\CRM\SamplePoint;
use App\Models\System\SystemConfiguration;
use App\QuotationDetails;
use App\QuotationHeader;
use App\SampleHeader;
use App\Services\Lab\UncertaintyBudgetResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class QuotationReportService
{
    /**
     * @var list<string>
     */
    private const DEFAULT_TERMS_AND_CONDITIONS = [
        'This quotation is valid for 30 days from the date of issue.',
        'Samples must be submitted/collected in suitable condition and quantity for the requested analysis.',
        'Report turnaround time commences upon receipt and acceptance of the sample.',
        'Test results apply only to the samples submitted and tested.',
        'All client information and test results will be treated as confidential.',
        'Reports shall not be reproduced except in full without written approval from the laboratory.',
        'The laboratory reserves the right to subcontract specific tests to competent laboratories when required.',
        'Complaints and appeals will be handled in accordance with the laboratory\'s documented procedures.',
        'Samples will be retained and disposed of as per the laboratory\'s retention policy.',
        'Orders cancelled after confirmation may be subject to applicable charges for work already performed, materials procured, or commitments made by the laboratory.',
        'Payment shall be made within the credit terms stated in the quotation.',
        'This quotation is valid for a minimum order value of AED _____________.',
        'The laboratory shall not be liable for delays caused by circumstances beyond its reasonable control.',
        'Acceptance of this quotation constitutes acceptance of the laboratory terms and conditions of service.',
    ];

    /**
     * @var array<string, string>
     */
    public const STRUCTURED_TERM_DEFINITIONS = [
        'tat' => 'Turnaround Time (TAT)',
        'vat' => 'VAT',
        'validity' => 'Validity',
        'confidentiality' => 'Confidentiality',
        'payment' => 'Payment',
        'cancellation' => 'Cancellation',
        'amendments' => 'Amendments',
        'retention_disposal' => 'Retention & Disposal',
        'subcontract' => 'Subcontract',
        'min_invoice_value' => 'Minimum Invoice Value',
    ];

    /**
     * @var array<string, string>
     */
    public const DEFAULT_STRUCTURED_TERMS = [
        'tat' => 'Report turnaround time commences upon receipt and acceptance of the sample.',
        'vat' => 'VAT at applicable rate will be charged where required by law.',
        'validity' => 'This quotation is valid for 30 days from the date of issue.',
        'confidentiality' => 'All client information and test results will be treated as confidential.',
        'payment' => 'Payment shall be made within the credit terms stated in this quotation.',
        'cancellation' => 'Orders cancelled after confirmation may be subject to applicable charges for work already performed.',
        'amendments' => 'Any amendments to this quotation must be agreed in writing by both parties.',
        'retention_disposal' => 'Samples will be retained and disposed of as per the laboratory retention policy.',
        'subcontract' => 'The laboratory reserves the right to subcontract specific tests to competent laboratories when required.',
        'min_invoice_value' => 'This quotation is subject to a minimum invoice value of AED _____________.',
    ];

    public function __construct(
        private readonly QuotationPricingResolver $pricingResolver,
        private readonly UncertaintyBudgetResolver $uncertaintyBudgetResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(QuotationHeader $header, bool $forPdf = false): array
    {
        $header->loadMissing(['customer', 'contact', 'currency', 'samplePoint']);

        $enrichedHeader = $this->buildEnrichedHeader($header);
        $realGroups = $this->buildGroupedLineItems($header);
        $hasLineItems = $realGroups !== [];
        $groups = $realGroups;
        $branding = $this->resolveCompanyBranding($forPdf);
        $terms = $this->resolveTerms($header);
        $copy = $this->resolveCopySettings();
        $currency = $header->currency;
        $termsOfSale = $this->resolveTermsOfSale($header);
        $structuredTerms = $this->resolveStructuredTerms($header);
        $bankDetails = $this->resolveBankDetails();
        $totals = $this->resolveTotals($header, $groups, ! $hasLineItems);
        $reportViewUrl = $this->resolveReportViewUrl($header);
        $company = getActiveCompany();

        return [
            'reportHeader' => $enrichedHeader,
            'groups' => $groups,
            'hasLineItems' => $hasLineItems,
            'isPlaceholderTable' => false,
            'branding' => $branding,
            'terms' => $terms,
            'termsOfSale' => $termsOfSale,
            'structuredTerms' => $structuredTerms,
            'bankDetails' => $bankDetails,
            'copy' => $copy,
            'currency' => $currency,
            'currencyCode' => $currency?->code ?? $currency?->name ?? 'AED',
            'totals' => $totals,
            'company' => $company,
            'forPdf' => $forPdf,
            'showLoqColumn' => true,
            'showMuColumn' => (bool) ($header->show_mu_column ?? true),
            'showUnitPriceColumn' => (bool) ($header->show_unit_price_column ?? true),
            'reportViewUrl' => $reportViewUrl,
            'qrCode' => $this->buildQrCode($reportViewUrl, $forPdf ? 56 : 90),
        ];
    }

    public function renderHtml(QuotationHeader $header, bool $forPdf = false): string
    {
        $data = $this->buildViewData($header, $forPdf);
        $view = $forPdf ? 'billing.quotations.amspec.pdf' : 'billing.quotations.amspec.document';

        return view($view, $data)->render();
    }

    public function streamPdf(QuotationHeader $header)
    {
        $pdf = $this->makePdf($header);

        $filename = $this->buildFilename($header);

        return $pdf->stream($filename);
    }

    public function storePdf(QuotationHeader $header): QuotationHeader
    {
        $pdf = $this->makePdf($header);
        $customer = getCrmCustomerByID($header->crm_customer_id);
        $customerName = preg_replace('/[^A-Za-z0-9]/', '', $customer->name ?? 'customer');
        $filename = $this->buildFilename($header);
        $directory = storage_path('app/quotations/'.$customerName);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $pdf->save($directory.'/'.$filename);

        $header->upload_url = '/quotations/'.$customerName.'/'.$filename;
        $header->is_print = 1;
        $header->save();

        return $header;
    }

    public function generateLaboratoryRef(QuotationHeader $header): string
    {
        $prefix = $this->configValue('quotation_lab_ref_prefix', 'AMSQ');
        $quoteDate = $header->quote_date ?? now()->toDateString();
        $datePart = date('ymd', strtotime($quoteDate));
        $idPart = str_pad((string) (crc32((string) $header->id) % 1000), 3, '0', STR_PAD_LEFT);

        return $prefix.$datePart.'-'.$idPart;
    }

    public function ensureHeaderMetadata(QuotationHeader $header): void
    {
        $dirty = false;

        if (empty($header->laboratory_ref)) {
            $header->laboratory_ref = $this->generateLaboratoryRef($header);
            $dirty = true;
        }

        if (empty($header->subject)) {
            $header->subject = $this->defaultSubject($header);
            $dirty = true;
        }

        if ($dirty) {
            $header->save();
        }
    }

    public function seedDefaultTermsOfSale(QuotationHeader $header): void
    {
        $config = $this->resolveTermsOfSaleConfig();
        $updates = [];

        foreach (['service_delivery', 'payments', 'quote_specification', 'additional_info'] as $field) {
            if (! $this->isUsableTermValue($header->{$field}) && ! empty($config[$field])) {
                $updates[$field] = $this->plaintextValue((string) $config[$field]);
            }
        }

        if (! $this->isUsableTermValue($header->payment_info)) {
            $updates['payment_info'] = 'YOU MAY SUBMIT YOUR PAYMENT IN ACCORDANCE TO THE BELOW INSTRUCTIONS BANK OR MOBILE REMITTANCE';
        }

        $this->persistQuotationHeaderColumns($header, $updates);
    }

    /**
     * Decrypt legacy ciphertext stored on quotation header term fields.
     */
    public function normalizeStoredTerms(QuotationHeader $header): void
    {
        $updates = [];

        foreach (['service_delivery', 'payments', 'quote_specification', 'additional_info', 'payment_info'] as $field) {
            $current = (string) ($header->{$field} ?? '');
            if ($current === '') {
                continue;
            }

            $plain = $this->plaintextValue($current);
            if ($plain !== $current && ! $this->looksLikeEncryptedPayload($plain)) {
                $updates[$field] = $plain;
            }
        }

        $this->persistQuotationHeaderColumns($header, $updates);
    }

    public function plaintextValue(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '' || ! $this->looksLikeEncryptedPayload($value)) {
            return $value;
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            if (! $this->looksLikeEncryptedPayload($value)) {
                break;
            }

            $decrypted = $this->decryptPayload($value);
            if ($decrypted === $value) {
                break;
            }

            $value = $decrypted;
        }

        return $value;
    }

    public function seedDefaultStructuredTerms(QuotationHeader $header): void
    {
        $config = $this->resolveStructuredTermsConfig();
        $stored = is_array($header->structured_terms) ? $header->structured_terms : [];
        $dirty = false;

        foreach (self::STRUCTURED_TERM_DEFINITIONS as $key => $label) {
            if (empty($stored[$key]) && ! empty($config[$key])) {
                $stored[$key] = $config[$key];
                $dirty = true;
            }
        }

        if ($dirty) {
            $this->persistQuotationHeaderColumns($header, ['structured_terms' => $stored]);
        }
    }

    /**
     * @param  array<string, mixed>  $updates
     */
    private function persistQuotationHeaderColumns(QuotationHeader $header, array $updates): void
    {
        if ($updates === []) {
            return;
        }

        QuotationHeader::query()
            ->whereKey($header->id)
            ->update($updates);

        $header->fill($updates);
        $header->syncOriginal();
    }

    private function decryptPayload(string $value): string
    {
        try {
            return trim(Crypt::decryptString($value));
        } catch (\Throwable) {
            try {
                return trim(decrypt($value));
            } catch (\Throwable) {
                return $value;
            }
        }
    }

    private function looksLikeEncryptedPayload(string $value): bool
    {
        return str_starts_with($value, 'eyJ');
    }

    private function isUsableTermValue(mixed $value): bool
    {
        $plain = $this->plaintextValue(is_string($value) ? $value : (string) ($value ?? ''));

        return $plain !== '' && ! $this->looksLikeEncryptedPayload($plain);
    }

    /**
     * @return array<string, string>
     */
    public function resolveStructuredTermsConfig(): array
    {
        $type = getConfigTypeByName('Quotation Structured Terms');
        $configs = $type ? getconfigByID($type->id) : collect();
        $result = self::DEFAULT_STRUCTURED_TERMS;

        foreach ($configs as $config) {
            if (array_key_exists((string) $config->key, self::STRUCTURED_TERM_DEFINITIONS)) {
                $result[(string) $config->key] = $this->plaintextValue((string) $config->value);
            }
        }

        return $result;
    }

    /**
     * @return array{items: list<array{key: string, label: string, value: string}>}
     */
    public function resolveStructuredTerms(QuotationHeader $header): array
    {
        $config = $this->resolveStructuredTermsConfig();
        $stored = is_array($header->structured_terms) ? $header->structured_terms : [];
        $items = [];

        foreach (self::STRUCTURED_TERM_DEFINITIONS as $key => $label) {
            $value = trim($this->plaintextValue((string) ($stored[$key] ?? $config[$key] ?? '')));
            if ($value !== '') {
                $items[] = [
                    'key' => $key,
                    'label' => $label,
                    'value' => $value,
                ];
            }
        }

        return ['items' => $items];
    }

    /**
     * @return array<string, string>
     */
    public function resolveTermsOfSaleConfig(): array
    {
        $type = getConfigTypeByName('Terms of Sale');
        $configs = $type ? getconfigByID($type->id) : collect();
        $result = [];

        foreach ($configs as $config) {
            $result[(string) $config->key] = $this->plaintextValue((string) $config->value);
        }

        return $result;
    }

    /**
     * @return array{service_delivery: string, payments: string, quote_specification: string, additional_info: string, payment_info: string, prices: string}
     */
    public function resolveTermsOfSale(QuotationHeader $header): array
    {
        $config = $this->resolveTermsOfSaleConfig();

        return [
            'service_delivery' => $this->resolveTermField($header->service_delivery, $config['service_delivery'] ?? ''),
            'payments' => $this->resolveTermField($header->payments, $config['payments'] ?? ''),
            'quote_specification' => $this->resolveTermField($header->quote_specification, $config['quote_specification'] ?? ''),
            'additional_info' => $this->resolveTermField($header->additional_info, $config['additional_info'] ?? ''),
            'payment_info' => $this->resolveTermField(
                $header->payment_info,
                'YOU MAY SUBMIT YOUR PAYMENT IN ACCORDANCE TO THE BELOW INSTRUCTIONS BANK OR MOBILE REMITTANCE'
            ),
            'prices' => (string) ($config['prices'] ?? ''),
        ];
    }

    private function resolveTermField(mixed $value, string $fallback): string
    {
        $plain = $this->plaintextValue(is_string($value) ? $value : (string) ($value ?? ''));
        if ($plain === '' || $this->looksLikeEncryptedPayload($plain)) {
            return $fallback;
        }

        return $plain;
    }

    /**
     * @return array<string, string>
     */
    public function resolveBankDetails(): array
    {
        $type = getConfigTypeByName('Bank Details');
        $configs = $type ? getconfigByID($type->id) : collect();
        $result = [];

        foreach ($configs as $config) {
            $result[(string) $config->key] = (string) $config->value;
        }

        return $result;
    }

    /**
     * @return list<array{sample_type_name: string, rows: list<array<string, mixed>>}>
     */
    private function buildGroupedLineItems(QuotationHeader $header): array
    {
        $details = QuotationDetails::where('quotation_header_id', $header->id)->get();
        $grouped = [];

        $elementIds = [];
        foreach ($details as $detail) {
            if ($header->quotation_type !== 'General') {
                $elementIds = array_merge(
                    $elementIds,
                    $this->pricingResolver->collectElementIdsFromDetail($detail),
                );
            }
        }
        $elementIds = array_values(array_unique(array_filter($elementIds)));

        /** @var Collection<string, AnalysisElements> $elementsById */
        $elementsById = $elementIds === []
            ? collect()
            : AnalysisElements::with(['ltmethod', 'mmethod', 'methodSequence'])
                ->whereIn('id', $elementIds)
                ->get()
                ->keyBy('id');

        $analyteIds = $elementsById->pluck('analyte_id')->filter()->map(fn ($id) => (string) $id)->unique()->values()->all();
        $siblingsByAnalyte = $this->uncertaintyBudgetResolver->preloadActiveElementsByAnalyteIds($analyteIds);
        $budgetElements = $elementsById->values();
        foreach ($siblingsByAnalyte as $siblings) {
            $budgetElements = $budgetElements->merge($siblings);
        }
        $budgets = $this->uncertaintyBudgetResolver->preloadForElements($budgetElements->unique('id')->values());

        foreach ($details as $detail) {
            if ($header->quotation_type === 'General') {
                $sampleTypeName = $detail->item_name ?: 'General Items';
                $grouped[$sampleTypeName][] = $this->makeLineRow(
                    $detail->item_name ?: $detail->description,
                    $detail->part_no ?: '',
                    '',
                    '',
                    (float) $detail->unit_price,
                    (int) $detail->quantity
                );

                continue;
            }

            $sampleTypeName = getSampleTypeByID($detail->sample_type)?->name ?? 'Tests';
            $elementIds = $this->pricingResolver->collectElementIdsFromDetail($detail);

            if ((bool) ($detail->is_package ?? false)) {
                $packageLabel = trim((string) ($detail->description ?? ''));
                if ($packageLabel === '') {
                    $analysisTypeId = trim((string) ($detail->part_no ?? ''));
                    $packageLabel = $analysisTypeId !== ''
                        ? (string) (AnalysisType::find($analysisTypeId)?->name ?? 'Analysis').' package'
                        : 'Analysis package';
                }

                $grouped[$sampleTypeName][] = $this->makeLineRow(
                    $packageLabel,
                    (string) ($detail->test_method ?? ''),
                    (string) ($detail->loq ?? ''),
                    (string) ($detail->mu_percent ?? ''),
                    (float) $detail->unit_price,
                    (int) $detail->quantity
                );

                foreach ($elementIds as $elementId) {
                    $element = $elementsById->get($elementId);
                    if ($element === null) {
                        continue;
                    }

                    $analyte = Analyte::find($element->analyte_id);
                    $metrics = $this->uncertaintyBudgetResolver->resolveLabMetricsForElement(
                        $element,
                        $budgets,
                        $siblingsByAnalyte,
                    );
                    $subRow = $this->makeLineRow(
                        '· '.($analyte?->name ?? $element->parametername ?? 'Parameter'),
                        $metrics['test_method'],
                        $metrics['loq'],
                        $metrics['mu_percent'],
                        0.0,
                        (int) $detail->quantity
                    );
                    $subRow['is_package_sub_item'] = true;
                    $grouped[$sampleTypeName][] = $subRow;
                }

                continue;
            }

            if ($elementIds === []) {
                $analysisTypeIds = array_filter(explode(',', (string) $detail->part_no));
                foreach ($analysisTypeIds as $analysisTypeId) {
                    $analysisType = AnalysisType::find($analysisTypeId);
                    if (! $analysisType) {
                        continue;
                    }

                    $grouped[$sampleTypeName][] = $this->makeLineRow(
                        $analysisType->name ?? 'Test',
                        '',
                        '',
                        '',
                        (float) $detail->unit_price,
                        (int) $detail->quantity
                    );
                }

                continue;
            }

            foreach ($elementIds as $elementId) {
                $element = $elementsById->get($elementId);
                if ($element === null) {
                    continue;
                }

                $grouped[$sampleTypeName][] = $this->buildElementLineRow(
                    $header,
                    $detail,
                    $element,
                    (float) $detail->unit_price,
                    $budgets,
                    $siblingsByAnalyte,
                );
            }
        }

        $result = [];
        $serial = 1;

        foreach ($grouped as $sampleTypeName => $rows) {
            foreach ($rows as &$row) {
                $row['serial'] = $serial++;
            }
            unset($row);

            $result[] = [
                'sample_type_name' => $sampleTypeName,
                'rows' => $rows,
            ];
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEnrichedHeader(QuotationHeader $header): object
    {
        $this->ensureHeaderMetadata($header);
        $header->refresh();
        $header->loadMissing(['customer', 'contact', 'currency', 'revisionOf']);

        $preparedBy = getUserById($header->prepared_by_id);
        $position = $preparedBy?->position
            ? \App\ModulePreConfigs::find($preparedBy->position)?->name
            : null;

        $batch = SampleHeader::where('quote_id', $header->id)->orderByDesc('created_at')->first();
        $samplingLocation = $header->sampling_location;
        if (! $samplingLocation && $header->sample_point_id) {
            $point = SamplePoint::find($header->sample_point_id);
            $samplingLocation = $point?->display_name;
        }

        $contact = $header->contact;
        $attention = trim(implode(' ', array_filter([
            $contact?->first_name ?? '',
            $contact?->middle_name ?? '',
            $contact?->last_name ?? '',
        ])));

        if (! empty($contact?->job_occupation)) {
            $attention .= ' ('.$contact->job_occupation.')';
        }

        $row = (object) array_merge($header->toArray(), [
            'customer_name' => $header->customer?->name,
            'postal_address' => $header->customer?->postal_address,
            'physical_address' => $header->customer?->physical_address,
            'prepared_by_name' => $preparedBy?->name,
            'prepared_by_position' => $position,
            'prepared_by_email' => $preparedBy?->email,
            'prepared_by_phone' => $preparedBy?->phone,
            'prepared_by_signature' => $preparedBy?->electronic_sig ?? null,
            'attention' => $attention,
            'sampling_location_display' => $samplingLocation ?? '-',
            'laboratory_ref_display' => $header->laboratory_ref ?? $batch?->batch_code ?? $header->quote_number,
            'revision_number' => (int) ($header->revision_number ?? 1),
            'revision_of_quote_number' => $header->revisionOf?->quote_number,
            'quote_date_formatted' => $header->quote_date
                ? date('d-m-Y', strtotime($header->quote_date))
                : date('d-m-Y'),
        ]);

        return $row;
    }

    /**
     * @return array{
     *     primary: string,
     *     accent: string,
     *     logoSrc: string,
     *     logoUrl: string,
     *     logoDataUri: string,
     *     watermarkSrc: string,
     *     wordmarkDataUri: string,
     *     hexClusterDataUri: string
     * }
     */
    public function resolveCompanyBranding(bool $forPdf = false): array
    {
        $primary = $this->configValue('sys_quotation_primary_color')
            ?: $this->configValue('sys_theme_primary_color', \App\Services\System\ThemeService::PRIMARY);
        $accent = $this->configValue('sys_quotation_accent_color', '#4CAF50');
        $logoDataUri = $this->resolveCompanyLogoDataUri();
        $logoUrl = $this->resolveCompanyLogoUrl();
        $logoSrc = $forPdf ? $logoDataUri : ($logoUrl !== '' ? $logoUrl : $logoDataUri);

        return [
            'primary' => $primary,
            'accent' => $accent,
            'logoSrc' => $logoSrc,
            'logoUrl' => $logoUrl,
            'logoDataUri' => $logoDataUri,
            'watermarkSrc' => $logoDataUri !== '' ? $logoDataUri : $logoSrc,
            'wordmarkDataUri' => $this->buildAmSpecWordmarkDataUri($primary),
            'hexClusterDataUri' => $this->buildHexClusterDataUri($primary),
        ];
    }

    /**
     * @return array{items: list<array{number: int, text: string}>, override: ?string}
     */
    public function resolveTerms(QuotationHeader $header): array
    {
        if (! empty($header->terms_override)) {
            $lines = preg_split('/\r\n|\r|\n/', trim($header->terms_override)) ?: [];
            $items = collect($lines)
                ->filter()
                ->values()
                ->map(fn (string $text, int $index) => ['number' => $index + 1, 'text' => $text])
                ->all();

            if ($items !== []) {
                return [
                    'items' => $items,
                    'override' => $header->terms_override,
                ];
            }
        }

        $type = getConfigTypeByName('Quotation Terms and Conditions');
        $configs = $type ? getconfigByID($type->id) : collect();
        $items = [];

        foreach ($configs as $config) {
            if (! str_starts_with((string) $config->key, 'term_')) {
                continue;
            }

            $number = (int) str_replace('term_', '', (string) $config->key);
            if ($number > 0 && filled($config->value)) {
                $items[$number] = ['number' => $number, 'text' => $config->value];
            }
        }

        ksort($items);

        if ($items === []) {
            $items = $this->defaultTermsAndConditionsItems();
        }

        return [
            'items' => array_values($items),
            'override' => null,
        ];
    }

    /**
     * @return array<int, array{number: int, text: string}>
     */
    private function defaultTermsAndConditionsItems(): array
    {
        $items = [];

        foreach (self::DEFAULT_TERMS_AND_CONDITIONS as $index => $text) {
            $items[$index + 1] = ['number' => $index + 1, 'text' => $text];
        }

        return $items;
    }

    /**
     * @return array{intro: string, closing: string, legal_entity: string, terms_url: string}
     */
    private function resolveCopySettings(): array
    {
        $company = getActiveCompany();

        return [
            'intro' => $this->configValue('quotation_intro_text', ''),
            'closing' => $this->configValue('quotation_closing_text', ''),
            'legal_entity' => $this->configValue('quotation_legal_entity', $company?->name ?? ''),
            'terms_url' => $this->configValue('quotation_terms_url', ''),
        ];
    }

    /**
     * @param  list<array{sample_type_name: string, rows: list<array<string, mixed>>}>  $groups
     * @return array{net: float, vat: float, total: float, vat_rate: ?float}
     */
    private function resolveTotals(QuotationHeader $header, array $groups, bool $isEmptyTable = false): array
    {
        if ($isEmptyTable) {
            return [
                'net' => 0.0,
                'vat' => 0.0,
                'total' => 0.0,
                'vat_rate' => null,
            ];
        }

        if ($header->sub_total !== null && $header->total_amount !== null) {
            return [
                'net' => (float) $header->sub_total,
                'vat' => (float) ($header->tax ?? 0),
                'total' => (float) $header->total_amount,
                'vat_rate' => $header->sub_total > 0
                    ? round(((float) ($header->tax ?? 0) / (float) $header->sub_total) * 100, 2)
                    : null,
            ];
        }

        $net = 0.0;
        foreach ($groups as $group) {
            foreach ($group['rows'] as $row) {
                $net += ((float) $row['unit_price']) * ((int) ($row['quantity'] ?? 1));
            }
        }

        return [
            'net' => $net,
            'vat' => 0.0,
            'total' => $net,
            'vat_rate' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function makeLineRow(
        string $testName,
        string $testMethod,
        string $loq,
        string $muPercent,
        float $unitPrice,
        int $quantity,
        bool $isAccredited = false,
        bool $isSubcontracted = false
    ): array {
        return [
            'serial' => null,
            'test_name' => $testName,
            'test_method' => $testMethod,
            'loq' => $loq,
            'mu_percent' => $muPercent,
            'unit_price' => $unitPrice,
            'total_price' => round($unitPrice * $quantity, 2),
            'quantity' => $quantity,
            'is_placeholder' => false,
            'is_accredited' => $isAccredited,
            'is_subcontracted' => $isSubcontracted,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildElementLineRow(
        QuotationHeader $header,
        QuotationDetails $detail,
        AnalysisElements $element,
        float $storedUnitPrice,
        Collection $budgets,
        ?Collection $siblingsByAnalyte = null,
    ): array {
        $analyte = Analyte::find($element->analyte_id);
        $resolved = $this->pricingResolver->resolveLineUnitPrice(
            $header,
            (string) $detail->sample_type,
            (string) $element->analysis_type_id,
            (string) $element->id,
            $storedUnitPrice,
            true,
        );
        $sourceFlags = $this->elementSourceFlags($detail, (string) $element->id);
        $metrics = $this->uncertaintyBudgetResolver->resolveLabMetricsForElement($element, $budgets, $siblingsByAnalyte);

        return $this->makeLineRow(
            $analyte?->name ?? $element->parametername,
            trim((string) ($detail->test_method ?? '')) !== '' ? (string) $detail->test_method : $metrics['test_method'],
            trim((string) ($detail->loq ?? '')) !== '' ? (string) $detail->loq : $metrics['loq'],
            trim((string) ($detail->mu_percent ?? '')) !== '' ? (string) $detail->mu_percent : $metrics['mu_percent'],
            $resolved['unit_price'],
            (int) $detail->quantity,
            $sourceFlags['is_accredited'],
            $sourceFlags['is_subcontracted']
        );
    }

    /**
     * @return array{is_accredited: bool, is_subcontracted: bool}
     */
    private function elementSourceFlags(QuotationDetails $detail, string $elementId): array
    {
        $accredited = array_filter(explode(',', (string) $detail->accredited_analytes));
        $subcontracted = array_filter(explode(',', (string) $detail->subcontracted_analytes));
        $subAccredited = array_filter(explode(',', (string) $detail->sub_acc_analytes));

        return [
            'is_accredited' => in_array($elementId, $accredited, true),
            'is_subcontracted' => in_array($elementId, $subcontracted, true)
                || in_array($elementId, $subAccredited, true),
        ];
    }

    private function makePdf(QuotationHeader $header)
    {
        $fontDir = storage_path('fonts');
        if (! is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        $data = $this->buildViewData($header, true);
        $pdf = Pdf::loadView('billing.quotations.amspec.pdf', $data);
        $dompdf = $pdf->getDomPDF();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('enable_php', true);
        $dompdf->set_option('defaultFont', 'DejaVu Sans');
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->set_option('defaultMediaType', 'print');
        $dompdf->set_option('isFontSubsettingEnabled', true);
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    private function buildFilename(QuotationHeader $header): string
    {
        $customer = getCrmCustomerByID($header->crm_customer_id);
        $customerName = preg_replace('/[^A-Za-z0-9]/', '', $customer->name ?? 'customer');

        return urlencode($customerName.'-'.$header->quote_number.'-'.date('d-M-Y').'.pdf');
    }

    private function defaultSubject(QuotationHeader $header): string
    {
        $sampleTypes = QuotationDetails::where('quotation_header_id', $header->id)
            ->pluck('sample_type')
            ->filter()
            ->unique()
            ->map(fn ($id) => getSampleTypeByID($id)?->name)
            ->filter()
            ->implode(', ');

        if ($sampleTypes !== '') {
            return 'Quotation for '.$sampleTypes.' Testing';
        }

        return 'Quotation for Testing Services';
    }

    private function resolveTestMethodName(AnalysisElements $element): string
    {
        if ($element->ltmethod) {
            return (string) $element->ltmethod->name;
        }

        if ($element->mmethod) {
            return (string) $element->mmethod->name;
        }

        if ($element->method) {
            $method = AnalysisMethod::find($element->method);

            return (string) ($method?->name ?? '');
        }

        return '';
    }

    private function configValue(string $key, string $default = ''): string
    {
        $config = SystemConfiguration::query()->where('key', $key)->first();

        return filled($config?->value) ? (string) $config->value : $default;
    }

    private function resolveCompanyLogoDataUri(): string
    {
        $company = getActiveCompany();
        if (! $company) {
            return '';
        }

        foreach ($this->companyLogoCandidates($company) as $path) {
            $absolutePath = $this->resolveAbsoluteLogoPath((string) $path);
            if ($absolutePath !== '') {
                return $this->imagePathToDataUri($absolutePath);
            }
        }

        return '';
    }

    private function resolveCompanyLogoUrl(): string
    {
        $company = getActiveCompany();
        if (! $company) {
            return '';
        }

        foreach ($this->companyLogoCandidates($company) as $path) {
            $url = $this->normalizeLogoUrl((string) $path);
            if ($url !== '') {
                return $url;
            }
        }

        return '';
    }

    /**
     * Company-details logo first, then related fallbacks from the same company record.
     *
     * @return list<string>
     */
    private function companyLogoCandidates(\App\Company $company): array
    {
        return array_values(array_filter([
            $company->logo,
            $company->report_logo,
            $company->getReportLogoPath('quotation'),
        ]));
    }

    private function normalizeLogoUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/'.$path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    private function resolveAbsoluteLogoPath(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'data:')) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH) ?? $path;
        }

        $path = ltrim((string) $path, '/');
        $relative = preg_replace('#^storage/#', '', $path);

        if ($relative !== $path) {
            $fullPath = Storage::disk('public')->path($relative);
            if (is_readable($fullPath)) {
                return $fullPath;
            }
        }

        $filename = basename($path);
        if ($filename !== '') {
            $storagePath = storage_path('app/companies/'.$filename);
            if (is_readable($storagePath)) {
                return $storagePath;
            }
        }

        if (is_readable(public_path($path))) {
            return public_path($path);
        }

        if (is_readable(public_path(ltrim($path, '/')))) {
            return public_path(ltrim($path, '/'));
        }

        return '';
    }

    private function imagePathToDataUri(string $absolutePath): string
    {
        $contents = @file_get_contents($absolutePath);
        if ($contents === false) {
            return '';
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    public function publicReportToken(QuotationHeader $header): string
    {
        return substr(hash('sha256', (string) $header->id.config('app.key')), 0, 40);
    }

    public function resolveReportViewUrl(QuotationHeader $header): string
    {
        return URL::route('quotation.public.report', [
            'id' => $header->id,
            'token' => $this->publicReportToken($header),
        ]);
    }

    private function buildAmSpecWordmarkDataUri(string $primaryColor): string
    {
        $primary = htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8');
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 48" role="img" aria-label="AmSpec">
  <text x="0" y="36" font-family="Arial, Helvetica, sans-serif" font-size="36" font-weight="700" fill="{$primary}">Am</text>
  <text x="62" y="36" font-family="Arial, Helvetica, sans-serif" font-size="36" font-weight="700" fill="none" stroke="{$primary}" stroke-width="2">Spec</text>
  <text x="168" y="40" font-family="Arial, Helvetica, sans-serif" font-size="10" fill="{$primary}">&#174;</text>
</svg>
SVG;

        return $this->svgToDataUri($svg);
    }

    private function buildHexClusterDataUri(string $primaryColor): string
    {
        $primary = htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8');
        $grey = '#8a8a8a';
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 118 88" aria-hidden="true">
  <polygon points="86,2 96,8 96,20 86,26 76,20 76,8" fill="{$primary}" />
  <polygon points="58,18 78,29 78,51 58,62 38,51 38,29" fill="none" stroke="{$grey}" stroke-width="1.3" />
  <polygon points="22,34 34,41 34,55 22,62 10,55 10,41" fill="none" stroke="{$grey}" stroke-width="1.3" />
  <polygon points="68,44 92,58 92,82 68,96 44,82 44,58" fill="none" stroke="{$grey}" stroke-width="1.3" stroke-dasharray="4,3" transform="translate(0,-12)" />
</svg>
SVG;

        return $this->svgToDataUri($svg);
    }

    private function svgToDataUri(string $svg): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private function buildQrCode(string $url, int $size = 90): string
    {
        if ($url === '' || ! class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            return '';
        }

        return base64_encode(
            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size($size)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($url)
        );
    }
}

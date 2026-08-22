<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\Analyte;
use App\Models\CRM\SamplePoint;
use App\Models\SampleSubmissionRequest;
use App\Models\System\SystemConfiguration;
use App\QuotationDetails;
use App\QuotationHeader;
use App\SampleHeader;
use App\Services\Commercial\AccountPaymentTermsService;
use App\Services\Commercial\AmSpecQuotationNumberGenerator;
use App\Services\Lab\UncertaintyBudgetResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
        'Payment shall be made within 30 days credit terms stated in the quotation.',
        'This quotation is valid for a minimum order value of AED _____________.',
        'The laboratory shall not be liable for delays caused by circumstances beyond its reasonable control.',
        'Acceptance of this quotation constitutes acceptance of terms and condition on webpage: NONDISCLOSURE AGREEMENT',
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
        private readonly AccountPaymentTermsService $accountPaymentTermsService,
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
            'showMethodColumn' => true,
            'showLoqColumn' => (bool) ($header->show_loq_column ?? true),
            'showMuColumn' => (bool) ($header->show_mu_column ?? true),
            'showTatColumn' => true,
            'showQuantityColumn' => true,
            'showUnitPriceColumn' => (bool) ($header->show_unit_price_column ?? true),
            'showTotalPriceColumn' => false,
            'reportViewUrl' => $reportViewUrl,
            'qrCode' => $this->buildQrCode(
                $copy['terms_url'] !== ''
                    ? $copy['terms_url']
                    : 'https://www.amspecgroup.com/terms-conditions',
                $forPdf ? 56 : 90
            ),
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
        if (! empty($header->quote_number)
            && ! AmSpecQuotationNumberGenerator::isLegacyNumber($header->quote_number)
        ) {
            return (string) $header->quote_number;
        }

        return AmSpecQuotationNumberGenerator::generate(
            $header->quote_date ? Carbon::parse($header->quote_date) : null,
        );
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

        foreach (['service_delivery', 'quote_specification', 'additional_info', 'payment_info'] as $field) {
            if (! $this->isUsableTermValue($header->{$field}) && ! empty($config[$field])) {
                $updates[$field] = $this->plaintextValue((string) $config[$field]);
            }
        }

        if (! $this->isUsableTermValue($header->payments)) {
            $paymentsDefault = $this->defaultPaymentsFromCustomer($header);
            if ($paymentsDefault !== '') {
                $updates['payments'] = $paymentsDefault;
            } elseif (! empty($config['payments'])) {
                $updates['payments'] = $this->plaintextValue((string) $config['payments']);
            }
        }

        $this->persistQuotationHeaderColumns($header, $updates);
    }

    /**
     * Resolve Payments default from the client's CRM Account Settings.
     */
    public function defaultPaymentsFromCustomer(QuotationHeader $header): string
    {
        $header->loadMissing('customer');
        $customer = $header->customer;
        if ($customer === null) {
            return '';
        }

        $terms = $this->accountPaymentTermsService->resolveFromCustomer($customer);
        if (! filled($terms['label'] ?? null)) {
            return '';
        }

        if (($terms['billing_type'] ?? '') === 'other' && filled($customer->payment_terms_note)) {
            return trim((string) $customer->payment_terms_note);
        }

        return (string) $terms['label'];
    }

    /**
     * @return list<array{id: string, key: string, label: string, billing_type: string, allows_custom: bool}>
     */
    public function accountPaymentOptions(): array
    {
        $type = getConfigTypeByName('Account Settings');
        if (! $type) {
            return [];
        }

        return collect(getconfigByID($type->id))
            ->filter(fn ($account) => (bool) data_get($account, 'status', true))
            ->values()
            ->map(function ($account) {
                $meta = is_array(data_get($account, 'meta')) ? data_get($account, 'meta') : [];
                $billingType = (string) ($meta['billing_type'] ?? 'credit');

                return [
                    'id' => (string) data_get($account, 'id'),
                    'key' => (string) data_get($account, 'key', ''),
                    'label' => $this->accountPaymentTermsService->displayLabel($account),
                    'billing_type' => $billingType,
                    'allows_custom' => $billingType === 'other',
                ];
            })
            ->all();
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
     * Normalize system-config rich text into plain multi-line text for quotes/PDF.
     */
    public function configTextToPlain(?string $value): string
    {
        $value = $this->plaintextValue($value);
        if ($value === '') {
            return '';
        }

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $value) ?? $value;
        $value = preg_replace('/<\/\s*p\s*>/i', "\n", $value) ?? $value;
        $value = preg_replace('/<\/\s*div\s*>/i', "\n", $value) ?? $value;
        $value = strip_tags($value);
        $value = preg_replace("/[ \t]+\n/", "\n", $value) ?? $value;
        $value = preg_replace("/\n{3,}/", "\n\n", $value) ?? $value;

        return trim($value);
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
                $result[(string) $config->key] = $this->configTextToPlain((string) $config->value);
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
            $result[(string) $config->key] = $this->configTextToPlain((string) $config->value);
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
                (string) ($config['payment_info'] ?? '')
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
                    (int) $detail->quantity,
                    false,
                    false,
                    null,
                );

                continue;
            }

            $sampleTypeName = getSampleTypeByID($detail->sample_type)?->name ?? 'Tests';
            $elementIds = $this->pricingResolver->collectElementIdsFromDetail($detail);

            if ((bool) ($detail->is_package ?? false)) {
                // AmSpec layout: one row per parameter with that parameter's method.
                // Qty + unit price appear only on the first row (package billed once).
                $packageTat = $this->resolveRowTat($detail);
                $isFirstParameter = true;

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
                    $loq = $this->resolveDisplayedMetric($detail, 'show_loq_analytes', (string) $element->id, $metrics['loq']);
                    $mu = $this->resolveDisplayedMetric($detail, 'show_mu_analytes', (string) $element->id, $metrics['mu_percent']);

                    $packageMember = $this->makeLineRow(
                        (string) ($analyte?->name ?? $element->parametername ?? 'Parameter'),
                        (string) ($metrics['test_method'] ?? ''),
                        $loq,
                        $mu,
                        $isFirstParameter ? (float) $detail->unit_price : 0.0,
                        $isFirstParameter ? (int) $detail->quantity : 0,
                        false,
                        false,
                        $isFirstParameter ? $packageTat : null,
                    );
                    $packageMember['is_package_member'] = true;
                    $packageMember['is_package_price_row'] = $isFirstParameter;
                    $packageMember['show_commercial_cells'] = $isFirstParameter;
                    $grouped[$sampleTypeName][] = $packageMember;
                    $isFirstParameter = false;
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
                        (int) $detail->quantity,
                        false,
                        false,
                        $this->resolveRowTat($detail),
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
        $header->loadMissing(['customer.country', 'customer.city', 'contact', 'currency', 'revisionOf', 'approvedByUser']);

        $preparedBy = getUserById($header->prepared_by_id);
        // After lab-manager approval, the Authorized Signature block shows the approver.
        $signatory = ((int) $header->is_approved === 1 && ! empty($header->approved_by))
            ? (getUserById($header->approved_by) ?? $header->approvedByUser ?? $preparedBy)
            : $preparedBy;
        $position = $signatory?->position
            ? \App\ModulePreConfigs::find($signatory->position)?->name
            : null;

        $batch = SampleHeader::where('quote_id', $header->id)->orderByDesc('created_at')->first();
        $samplingLocation = $this->resolveSamplingLocation($header);

        $contact = $header->contact;
        $attention = trim(implode(' ', array_filter([
            $contact?->first_name ?? '',
            $contact?->middle_name ?? '',
            $contact?->last_name ?? '',
        ])));

        $customerCity = trim((string) ($header->customer?->city?->name ?? ''));
        $customerCountry = trim((string) ($header->customer?->country?->name ?? ''));
        $cityCountry = trim(implode(', ', array_filter([$customerCity, $customerCountry])));

        $row = (object) array_merge($header->toArray(), [
            'customer_name' => $header->customer?->name,
            'postal_address' => $header->customer?->postal_address,
            'physical_address' => $header->customer?->physical_address,
            'customer_city' => $customerCity,
            'customer_country' => $customerCountry,
            'customer_city_country' => $cityCountry,
            'customer_tel' => trim((string) ($contact?->telephone ?? $header->customer?->telephone1 ?? '')),
            'customer_fax' => trim((string) ($header->customer?->fax ?? '')),
            'customer_mobile' => trim((string) ($contact?->mobile ?? $header->customer?->telephone2 ?? '')),
            'customer_email' => trim((string) ($contact?->email ?? $header->customer?->email ?? '')),
            'prepared_by_name' => $signatory?->name,
            'prepared_by_position' => $position,
            'prepared_by_email' => $signatory?->email,
            'prepared_by_phone' => $signatory?->phone,
            'prepared_by_signature' => function_exists('signatureToDataUri')
                ? signatureToDataUri($signatory?->electronic_sig)
                : ($signatory?->electronic_sig ?? null),
            'attention' => $attention,
            'company_unit_display' => $this->resolveCompanyUnit($header, $batch) ?? '-',
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
        $watermarkSrc = app(\App\Services\Reports\ReportWatermarkService::class)->src(null, $forPdf);
        if ($watermarkSrc === '') {
            $watermarkSrc = $logoDataUri !== '' ? $logoDataUri : $logoSrc;
        }

        return [
            'primary' => $primary,
            'accent' => $accent,
            'logoSrc' => $logoSrc,
            'logoUrl' => $logoUrl,
            'logoDataUri' => $logoDataUri,
            'watermarkSrc' => $watermarkSrc,
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
                $text = $this->configTextToPlain((string) $config->value);
                if ($text !== '') {
                    $items[$number] = ['number' => $number, 'text' => $text];
                }
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
            'terms_url' => $this->configValue('quotation_terms_url', 'https://www.amspecgroup.com/terms-conditions'),
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
            $net = (float) $header->sub_total;
            $vat = (float) ($header->tax ?? 0);
            $total = (float) $header->total_amount;

            if ($vat <= 0) {
                $computed = $this->computeTotalsFromDetails($header);
                if ($computed['vat'] > 0) {
                    $net = $computed['net'];
                    $vat = $computed['vat'];
                    $total = $computed['total'];
                }
            }

            return [
                'net' => $net,
                'vat' => $vat,
                'total' => $total,
                'vat_rate' => $net > 0
                    ? round(($vat / $net) * 100, 2)
                    : null,
            ];
        }

        $net = 0.0;
        foreach ($groups as $group) {
            foreach ($group['rows'] as $row) {
                if (! empty($row['is_package_sub_item']) || ! empty($row['exclude_from_totals'])) {
                    continue;
                }
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

    public function recalculateTotals(QuotationHeader $header): void
    {
        $header->loadMissing('details');

        $subTotal = 0.0;
        $taxes = 0.0;

        foreach ($header->details as $detail) {
            $taxRate = (float) $detail->tax;

            if ($taxRate <= 0 && ($header->quotation_type ?? '') !== 'General') {
                $elementIds = $this->pricingResolver->collectElementIdsFromDetail($detail);
                $suggestion = $this->pricingResolver->suggestManualLinePricing(
                    $header,
                    $detail->sample_type !== null ? (string) $detail->sample_type : null,
                    (string) $detail->part_no,
                    $elementIds,
                );
                $resolvedTax = (float) ($suggestion['tax'] ?? 0);

                if ($resolvedTax > 0) {
                    $taxRate = $resolvedTax;
                    $detail->tax = $taxRate;
                    $detail->save();
                }
            }

            $quantity = max(1, (int) $detail->quantity);
            $unitPrice = (float) $detail->unit_price;
            $extended = $quantity * $unitPrice;
            $subTotal += $extended;

            if ($taxRate > 0) {
                $taxes += ($taxRate / 100) * $extended;
            }
        }

        $header->sub_total = $subTotal;
        $header->tax = $taxes;
        $header->total_amount = $subTotal + $taxes;
        $header->save();
    }

    /**
     * @return array{net: float, vat: float, total: float}
     */
    private function computeTotalsFromDetails(QuotationHeader $header): array
    {
        $header->loadMissing('details');

        $subTotal = 0.0;
        $taxes = 0.0;

        foreach ($header->details as $detail) {
            $quantity = max(1, (int) $detail->quantity);
            $unitPrice = (float) $detail->unit_price;
            $taxRate = (float) $detail->tax;
            $extended = $quantity * $unitPrice;
            $subTotal += $extended;

            if ($taxRate > 0) {
                $taxes += ($taxRate / 100) * $extended;
            }
        }

        return [
            'net' => $subTotal,
            'vat' => $taxes,
            'total' => $subTotal + $taxes,
        ];
    }

    private function resolveSamplingLocation(QuotationHeader $header): ?string
    {
        if (filled($header->sampling_location)) {
            return (string) $header->sampling_location;
        }

        if ($header->sample_point_id) {
            $point = SamplePoint::find($header->sample_point_id);

            if (filled($point?->display_name)) {
                return (string) $point->display_name;
            }
        }

        $enquiry = $this->resolveLinkedEnquiry($header);

        if ($enquiry === null) {
            return null;
        }

        $enquiry->loadMissing('submissionFormInstance');

        $instance = $enquiry->submissionFormInstance ?? $enquiry->resolveLinkedFormInstance();

        if ($instance !== null) {
            $display = $instance->resolveDisplayValueByName('sampling_location');

            if (filled($display)) {
                return (string) $display;
            }

            $raw = $instance->getValueByElementName('sampling_location');

            if (filled($raw)) {
                return $this->resolveSamplePointLabel((string) $raw);
            }
        }

        $collection = is_array($enquiry->collection_data) ? $enquiry->collection_data : [];

        if (! empty($collection['sampling_location'])) {
            return $this->resolveSamplePointLabel((string) $collection['sampling_location']);
        }

        if (is_array($enquiry->sample_lines)) {
            foreach ($enquiry->sample_lines as $line) {
                if (! is_array($line)) {
                    continue;
                }

                $location = (string) ($line['location'] ?? $line['sampling_point'] ?? $line['sampling_location'] ?? '');

                if ($location !== '') {
                    return $this->resolveSamplePointLabel($location);
                }
            }
        }

        return null;
    }

    private function resolveCompanyUnit(QuotationHeader $header, ?SampleHeader $batch = null): ?string
    {
        $fromHeaderUnit = $this->resolveCompanyUnitLabel((string) ($header->crm_company_unit_id ?? ''));
        if ($fromHeaderUnit !== '') {
            return $fromHeaderUnit;
        }

        $batch ??= SampleHeader::query()
            ->where('quote_id', $header->id)
            ->orderByDesc('created_at')
            ->first();

        if ($batch !== null) {
            $fromBatchName = trim((string) ($batch->crm_unit_name ?? ''));
            if ($fromBatchName !== '') {
                return $fromBatchName;
            }

            $fromBatchId = $this->resolveCompanyUnitLabel((string) ($batch->crm_unit_id ?? ''));
            if ($fromBatchId !== '') {
                return $fromBatchId;
            }
        }

        $enquiry = $this->resolveLinkedEnquiry($header);

        if ($enquiry !== null) {
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

            $fromEnquirySamplePoint = $this->resolveCompanyUnitFromSamplePointId(
                (string) ($collection['sampling_location'] ?? '')
            );
            if ($fromEnquirySamplePoint !== null) {
                return $fromEnquirySamplePoint;
            }
        }

        $fromHeaderSamplePoint = $this->resolveCompanyUnitFromSamplePointId(
            (string) ($header->sample_point_id ?? '')
        );
        if ($fromHeaderSamplePoint !== null) {
            return $fromHeaderSamplePoint;
        }

        $header->loadMissing('contact');
        $contact = $header->contact;
        if ($contact !== null) {
            $fromContactId = $this->resolveCompanyUnitLabel((string) ($contact->crm_company_unit_id ?? ''));
            if ($fromContactId !== '') {
                return $fromContactId;
            }

            $fromContactName = $this->resolveCompanyUnitLabel((string) ($contact->unit_name ?? ''));
            if ($fromContactName !== '') {
                return $fromContactName;
            }
        }

        return null;
    }

    private function resolveCompanyUnitFromSamplePointId(string $samplePointId): ?string
    {
        $samplePointId = trim($samplePointId);
        if ($samplePointId === '') {
            return null;
        }

        $unitId = SamplePoint::query()->whereKey($samplePointId)->value('crm_company_unit_id');
        $label = $this->resolveCompanyUnitLabel((string) ($unitId ?? ''));

        return $label !== '' ? $label : null;
    }

    private function resolveCompanyUnitLabel(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (! Str::isUuid($value) && ! ctype_digit($value)) {
            return $value;
        }

        $unitName = \App\Models\CRM\CRMCompanyUnit::query()->whereKey($value)->value('name');

        return is_string($unitName) && trim($unitName) !== '' ? trim($unitName) : '';
    }

    private function resolveLinkedEnquiry(QuotationHeader $header): ?SampleSubmissionRequest
    {
        $header->loadMissing(['sampleSubmissionRequest', 'linkedEnquiries']);

        if ($header->sampleSubmissionRequest !== null) {
            return $header->sampleSubmissionRequest;
        }

        $linked = $header->linkedEnquiries->first();

        if ($linked !== null) {
            return $linked;
        }

        return SampleSubmissionRequest::query()
            ->where('current_quotation_header_id', $header->id)
            ->orWhere('accepted_quotation_header_id', $header->id)
            ->first();
    }

    private function resolveSamplePointLabel(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (! Str::isUuid($value) && ! ctype_digit($value)) {
            return $value;
        }

        $point = SamplePoint::query()->find($value);

        return $point?->display_name ?? $value;
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
        bool $isSubcontracted = false,
        ?int $tat = null,
    ): array {
        return [
            'serial' => null,
            'test_name' => $testName,
            'test_method' => $testMethod,
            'loq' => $loq,
            'mu_percent' => $muPercent,
            'tat' => $tat,
            'unit_price' => $unitPrice,
            'total_price' => round($unitPrice * max(1, $quantity), 2),
            'quantity' => max(1, $quantity),
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
            (string) ($metrics['test_method'] ?? ''),
            $this->resolveDisplayedMetric(
                $detail,
                'show_loq_analytes',
                (string) $element->id,
                trim((string) ($detail->loq ?? '')) !== '' ? (string) $detail->loq : $metrics['loq'],
            ),
            $this->resolveDisplayedMetric(
                $detail,
                'show_mu_analytes',
                (string) $element->id,
                trim((string) ($detail->mu_percent ?? '')) !== '' ? (string) $detail->mu_percent : $metrics['mu_percent'],
            ),
            $resolved['unit_price'],
            (int) $detail->quantity,
            $sourceFlags['is_accredited'],
            $sourceFlags['is_subcontracted'],
            $this->resolveRowTat($detail, $element),
        );
    }

    /**
     * Show LOQ/MU on the quotation only when the parameter checkbox is checked.
     * Legacy rows with a null flag keep previous behaviour (show whenever a value exists).
     */
    private function resolveDisplayedMetric(
        QuotationDetails $detail,
        string $flagColumn,
        string $elementId,
        ?string $value,
    ): string {
        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return '';
        }

        $raw = $detail->{$flagColumn};
        if ($raw === null) {
            return $value;
        }

        $ids = array_values(array_filter(array_map('trim', explode(',', (string) $raw))));

        return in_array($elementId, $ids, true) ? $value : '';
    }

    private function resolveRowTat(QuotationDetails $detail, ?AnalysisElements $element = null): ?int
    {
        if ($element !== null) {
            return $this->pricingResolver->maxTatForElements(
                [(string) $element->id],
                (string) ($element->analysis_type_id ?? $detail->part_no ?? ''),
            );
        }

        $fromSelectedElements = $this->pricingResolver->maxTatForElements(
            $this->pricingResolver->collectElementIdsFromDetail($detail),
            (string) ($detail->part_no ?? ''),
        );

        if ($fromSelectedElements !== null) {
            return $fromSelectedElements;
        }

        if ($detail->tat !== null && (int) $detail->tat > 0) {
            return (int) $detail->tat;
        }

        return null;
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

        $this->recalculateTotals($header);
        $header = $header->fresh() ?? $header;

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
        app(\App\Services\Reports\ReportWatermarkService::class)->applyToPdf($pdf);

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

        if (! filled($config?->value)) {
            return $default;
        }

        $plain = $this->configTextToPlain((string) $config->value);

        return $plain !== '' ? $plain : $default;
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
        $grey = '#6e6e6e';
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 140 105" width="108" height="81" aria-hidden="true">
  <polygon points="70,14 102,32 102,68 70,86 38,68 38,32" fill="none" stroke="{$grey}" stroke-width="2"/>
  <polygon points="36,42 58,54 58,78 36,90 14,78 14,54" fill="none" stroke="{$grey}" stroke-width="2"/>
  <polygon points="92,48 118,63 118,91 92,106 66,91 66,63" fill="none" stroke="{$grey}" stroke-width="1.8" stroke-dasharray="5 3.5" transform="translate(0,-12)"/>
  <polygon points="108,4 122,12 122,28 108,36 94,28 94,12" fill="{$primary}"/>
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

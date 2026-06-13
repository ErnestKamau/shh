<?php

namespace App\Services\Sampleworkflow;

use App\Models\System\SystemConfiguration;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;
use Carbon\Carbon;

class TestRequestFormReportDataBuilder
{
    /** @var array<string, list<string>> */
    private const FOOD_OPTIONS = [
        'sampling_apparatus' => ['STERILE BAG', 'AIR SAMPLER', 'STERILE BOTTLE', 'GRABBER', 'STERILE SWAB', 'OTHERS'],
        'method_of_sampling' => ['APHA', 'US FDA', 'SASO', 'CCFRA', 'ASTM', 'DM', 'SOP', 'OTHERS'],
        'reason_of_collection' => ['CONTRACT', 'NON-CONTRACT', 'HACCP REQUIREMENT', 'DISPUTED/AUDIT'],
        'transport_condition' => ['CHILLER VEHICLE', 'FROZEN', 'AMBIENT'],
        'sample_type' => ['Raw', 'Cooked', 'Ready To Eat'],
        'sample_condition' => ['Acceptable', 'Chilled', 'Frozen', 'Ambient'],
        'statement_of_conformity' => ['YES', 'No', 'As per Contract', 'As per Email'],
        'lab_sample_condition' => ['Acceptable', 'Not Acceptable'],
    ];

    /** @var array<string, list<string>> */
    private const WATER_OPTIONS = [
        'sampling_apparatus' => ['STERILE BAG', 'GRABBER', 'STERILE BOTTLE', 'OTHERS'],
        'method_of_sampling' => ['APHA', 'US FDA', 'SASO', 'CCFRA', 'ASTM', 'DM', 'SOP', 'OTHERS'],
        'reason_of_collection' => ['CONTRACT', 'NON-CONTRACT', 'HACCP REQUIREMENT', 'DISPUTED/AUDIT'],
        'transport_condition' => ['CHILLER VEHICLE', 'FROZEN', 'AMBIENT'],
        'sampling_point' => ['Tap', 'Tank', 'Pool', 'Shower Head', 'Others'],
        'statement_of_conformity' => ['YES', 'No', 'As per Contract', 'As per Email'],
        'lab_sample_condition' => ['Acceptable', 'Not Acceptable'],
    ];

    /**
     * @return array<string, mixed>
     */
    public function build(TestRequestFormInstance $instance, bool $forPdf = true): array
    {
        $instance->loadMissing([
            'testRequestForm.sampleType',
            'submissionFormInstance.crmCustomer.contacts',
            'creator',
        ]);

        return $this->buildPayload(
            $instance->form_data ?? [],
            $instance->testRequestForm?->sampleType,
            $instance->submissionFormInstance,
            $forPdf
        );
    }

    /**
     * Build report payload from unsaved draft data (preview during receiving).
     *
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    public function buildFromDraft(
        array $formData,
        ?\App\SampleType $sampleType,
        $submission = null,
        bool $forPdf = true
    ): array {
        return $this->buildPayload($formData, $sampleType, $submission, $forPdf);
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    private function buildPayload(
        array $formData,
        ?\App\SampleType $sampleType,
        $submission,
        bool $forPdf
    ): array {
        $variant = TestRequestForm::resolveReportVariant($sampleType);
        $company = getActiveCompany();
        $branding = $this->resolveBranding($forPdf);

        $customer = $this->resolveCustomerFields($formData, $submission);
        $collection = $this->resolveCollectionFields($formData, $variant);
        $sampleRows = $this->resolveSampleRows($formData, $variant);
        $conformity = $this->normalizeSingleSelect(
            $formData['statement_of_conformity'] ?? '',
            self::FOOD_OPTIONS['statement_of_conformity']
        );
        $labUse = $this->resolveLabUseFields($formData);

        return [
            'variant' => $variant,
            'formTitle' => $variant === 'food' ? 'TEST REQUEST FORM - FOOD' : 'TEST REQUEST FORM - WATER',
            'documentRef' => $variant === 'food'
                ? 'AMS/QMS/LWS/019 - Test Request Form - Food - V0'
                : 'AMS/QMS/LWS/020 - Test Request Form - Water - V0',
            'serialNumber' => $this->resolveSerialNumber($submission),
            'company' => $company,
            'branding' => $branding,
            'logoSrc' => $branding['logoSrc'],
            'hexClusterSrc' => $branding['hexClusterDataUri'],
            'customer' => $customer,
            'collection' => $collection,
            'sampleRows' => $sampleRows,
            'signatures' => [
                'statement_of_conformity' => $conformity,
                'sampled_by' => (string) ($formData['sampled_by'] ?? ''),
                'customer_rep_name' => (string) ($formData['customer_rep_name'] ?? ''),
                'customer_rep_contact' => (string) ($formData['customer_rep_contact'] ?? ''),
                'remarks' => (string) ($formData['remarks'] ?? ''),
            ],
            'labUse' => $labUse,
            'options' => $variant === 'food' ? self::FOOD_OPTIONS : self::WATER_OPTIONS,
            'forPdf' => $forPdf,
        ];
    }

    /**
     * @return array{L: bool, SS: bool, S: bool}
     */
    public static function stateOfSampleChecks(?string $stored): array
    {
        $map = [
            'liquid' => 'L',
            'semi solid' => 'SS',
            'semi-solid' => 'SS',
            'solid' => 'S',
            'l' => 'L',
            'ss' => 'SS',
            's' => 'S',
        ];

        $normalized = $map[strtolower(trim((string) $stored))] ?? null;

        return [
            'L' => $normalized === 'L',
            'SS' => $normalized === 'SS',
            'S' => $normalized === 'S',
        ];
    }

    /**
     * @param  mixed  $value
     * @param  list<string>  $allOptions
     * @return array<string, bool>
     */
    public static function normalizeCheckboxGroup($value, array $allOptions): array
    {
        $selected = self::normalizeToSelectedList($value);
        $selectedUpper = array_map(static fn ($item) => strtoupper(trim((string) $item)), $selected);

        $result = [];
        foreach ($allOptions as $option) {
            $result[$option] = in_array(strtoupper($option), $selectedUpper, true);
        }

        return $result;
    }

    /**
     * @param  mixed  $value
     */
    public static function normalizeSingleSelect($value, array $allOptions): string
    {
        if (is_array($value)) {
            if (array_keys($value) !== range(0, count($value) - 1)) {
                foreach ($value as $key => $checked) {
                    if ($checked) {
                        return (string) $key;
                    }
                }
            }

            $value = implode(', ', array_filter($value, static fn ($item) => $item !== '' && $item !== null));
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return '';
        }

        foreach ($allOptions as $option) {
            if (strcasecmp($option, $normalized) === 0) {
                return $option;
            }
        }

        return $normalized;
    }

    /**
     * @param  mixed  $value
     * @return list<string>
     */
    public static function normalizeToSelectedList($value): array
    {
        if ($value === null || $value === '' || $value === false) {
            return [];
        }

        if (is_bool($value)) {
            return $value ? ['Yes'] : [];
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        if (! is_array($value)) {
            return [(string) $value];
        }

        if (array_keys($value) !== range(0, count($value) - 1)) {
            $selected = [];
            foreach ($value as $key => $checked) {
                if ($checked) {
                    $selected[] = (string) $key;
                }
            }

            return $selected;
        }

        return array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $value)));
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, string>
     */
    private function resolveCustomerFields(array $formData, $submission): array
    {
        $crm = $submission?->crmCustomer;
        $contact = $crm && method_exists($crm, 'contacts') ? $crm->contacts()->first() : null;

        return [
            'job_number' => (string) ($formData['job_number'] ?? ''),
            'customer_name' => (string) ($formData['customer_name'] ?? $formData['client_name'] ?? $crm?->name ?? ''),
            'customer_address' => (string) ($formData['customer_address'] ?? $formData['address'] ?? $crm?->physical_address ?? $crm?->postal_address ?? ''),
            'customer_phone' => (string) ($formData['customer_phone'] ?? $formData['tel_fax_no'] ?? $crm?->telephone1 ?? $crm?->telephone2 ?? ''),
            'contact_person' => (string) ($formData['contact_person'] ?? $contact?->name ?? ''),
            'mobile_number' => (string) ($formData['mobile_number'] ?? $crm?->cell_phone ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    private function resolveCollectionFields(array $formData, string $variant): array
    {
        $options = $variant === 'food' ? self::FOOD_OPTIONS : self::WATER_OPTIONS;

        return [
            'sampling_date' => $this->formatDate($formData['sampling_date'] ?? ''),
            'sampling_time' => (string) ($formData['sampling_time'] ?? ''),
            'sampling_location' => (string) ($formData['sampling_location'] ?? ''),
            'thermometer_id' => (string) ($formData['thermometer_id'] ?? ''),
            'sampling_apparatus' => self::normalizeCheckboxGroup($formData['sampling_apparatus'] ?? [], $options['sampling_apparatus']),
            'method_of_sampling' => self::normalizeCheckboxGroup($formData['method_of_sampling'] ?? [], $options['method_of_sampling']),
            'reason_of_collection' => self::normalizeCheckboxGroup($formData['reason_of_collection'] ?? [], $options['reason_of_collection']),
            'transport_condition' => self::normalizeCheckboxGroup($formData['transport_condition'] ?? [], $options['transport_condition']),
        ];
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return list<array<string, mixed>>
     */
    private function resolveSampleRows(array $formData, string $variant): array
    {
        $rows = $formData['sample_rows'] ?? [];
        if (! is_array($rows) || $rows === []) {
            return [];
        }

        $normalized = [];
        foreach (array_values($rows) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            if ($variant === 'food') {
                $normalized[] = [
                    'serial' => $index + 1,
                    'sample_no' => (string) ($row['sample_no'] ?? ''),
                    'sample_description' => (string) ($row['sample_description'] ?? ''),
                    'sampling_point' => (string) ($row['sampling_point'] ?? ''),
                    'qty' => (string) ($row['qty'] ?? ''),
                    'sample_type' => self::normalizeSingleSelect($row['sample_type'] ?? '', self::FOOD_OPTIONS['sample_type']),
                    'sample_condition' => self::normalizeSingleSelect($row['sample_condition'] ?? '', self::FOOD_OPTIONS['sample_condition']),
                    'sample_temp' => (string) ($row['sample_temp'] ?? ''),
                    'production_date' => $this->formatDate($row['production_date'] ?? ''),
                    'expiration_date' => $this->formatDate($row['expiration_date'] ?? ''),
                    'batch_number' => (string) ($row['batch_number'] ?? ''),
                    'parameters' => (string) ($row['parameters'] ?? ''),
                    'state_of_sample' => self::stateOfSampleChecks($row['state_of_sample'] ?? null),
                ];
                continue;
            }

            $normalized[] = [
                'serial' => $index + 1,
                'sample_no' => (string) ($row['sample_no'] ?? ''),
                'sample_description' => (string) ($row['sample_description'] ?? ''),
                'location' => (string) ($row['location'] ?? ''),
                'qty' => (string) ($row['qty'] ?? ''),
                'sampling_point' => self::normalizeSingleSelect($row['sampling_point'] ?? '', self::WATER_OPTIONS['sampling_point']),
                'ph' => (string) ($row['ph'] ?? ''),
                'appearance' => (string) ($row['appearance'] ?? ''),
                'residual_chlorine' => (string) ($row['residual_chlorine'] ?? ''),
                'odor' => (string) ($row['odor'] ?? ''),
                'sample_temp' => (string) ($row['sample_temp'] ?? ''),
                'microbiology' => filter_var($row['microbiology'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'legionella' => filter_var($row['legionella'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'chemical_analysis' => filter_var($row['chemical_analysis'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    private function resolveLabUseFields(array $formData): array
    {
        $receivedAt = $formData['lab_received_datetime'] ?? '';
        if ($receivedAt !== '') {
            try {
                $receivedAt = Carbon::parse($receivedAt)->format('d/m/Y H:i');
            } catch (\Throwable) {
                $receivedAt = (string) $receivedAt;
            }
        }

        return [
            'lab_received_datetime' => $receivedAt,
            'lab_received_by' => (string) ($formData['lab_received_by'] ?? ''),
            'lab_sample_condition' => self::normalizeSingleSelect(
                $formData['lab_sample_condition'] ?? '',
                self::FOOD_OPTIONS['lab_sample_condition']
            ),
        ];
    }

    private function resolveSerialNumber($submission): string
    {
        if (! $submission) {
            return '';
        }

        if (! empty($submission->form_number)) {
            return (string) $submission->form_number;
        }

        if (! empty($submission->sequence_number)) {
            return (string) $submission->sequence_number;
        }

        return '';
    }

    private function formatDate($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    /**
     * @return array<string, string>
     */
    private function resolveBranding(bool $forPdf): array
    {
        $primary = SystemConfiguration::query()
            ->where('key', 'sys_quotation_primary_color')
            ->value('value')
            ?: SystemConfiguration::query()
                ->where('key', 'sys_theme_primary_color')
                ->value('value')
            ?: '#6D0A0E';

        $logoSrc = $this->resolveLogoAsDataUri();

        return [
            'primary' => $primary,
            'logoSrc' => $logoSrc,
            'hexClusterDataUri' => $this->buildHexClusterDataUri($primary),
        ];
    }

    private function resolveLogoAsDataUri(): string
    {
        $company = getActiveCompany();

        if ($company && ! empty($company->logo)) {
            $path = $company->logo;

            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $path = parse_url($path, PHP_URL_PATH) ?? $path;
            }
            $path = ltrim($path, '/');
            $filename = basename($path);

            if ($filename !== '') {
                $relative = preg_replace('#^storage/#', '', $path);
                if ($relative !== $path) {
                    $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($relative);
                    if (file_exists($fullPath)) {
                        return $this->imagePathToDataUri($fullPath);
                    }
                }

                $fullPath = storage_path('app/companies/' . $filename);
                if (file_exists($fullPath)) {
                    return $this->imagePathToDataUri($fullPath);
                }

                if (file_exists(public_path($path))) {
                    return $this->imagePathToDataUri(public_path($path));
                }

                if (file_exists(public_path(ltrim($path, '/')))) {
                    return $this->imagePathToDataUri(public_path(ltrim($path, '/')));
                }
            }
        }

        $defaultLogo = public_path('images/logo.png');
        if (file_exists($defaultLogo)) {
            return $this->imagePathToDataUri($defaultLogo);
        }

        return '';
    }

    private function imagePathToDataUri(string $absolutePath): string
    {
        if ($absolutePath === '' || ! is_readable($absolutePath)) {
            return '';
        }

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

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
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

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}

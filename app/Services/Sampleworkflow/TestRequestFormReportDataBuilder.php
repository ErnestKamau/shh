<?php

namespace App\Services\Sampleworkflow;

use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\Models\System\SystemConfiguration;
use App\Models\SubmissionFormInstance;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use App\Services\Lab\AnalysisReferenceLabelResolver;
use Carbon\Carbon;
use Illuminate\Support\Str;

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

    /** @var array<string, list<string>> */
    private const WASTE_WATER_OPTIONS = [
        'sampling_apparatus' => ['STERILE BOTTLE', 'BOTTLE CATCHER', 'OTHERS'],
        'method_of_sampling' => ['APHA', 'US FDA', 'EPA', 'CCFRA', 'DM', 'SOP', 'OTHERS'],
        'reason_of_collection' => ['CONTRACT', 'NON-CONTRACT', 'DM REQUIREMENT', 'DISPUTED/AUDIT'],
        'sampling_technique' => ['GRAB', 'COMPOSITE', 'OTHER'],
        'sampling_source' => ['TANK', 'HOLDING TANK', 'IND./DOMESTIC EFFLUENT', 'POOL WATER', 'DISCHARGE TO MARINE', 'GROUND WATER', 'STP', 'MUNICIPAL TAP WATER'],
        'sample_types_ww' => ['LIQUID', 'SEMI SOLID', 'SLUDGE', 'MARINE SEDIMENT'],
        'transport_condition' => ['CHILLER VEHICLE', 'FROZEN', 'AMBIENT'],
        'field_data_requirements' => ['MICROBIOLOGY', 'CHEMISTRY', 'MICROBIOLOGY + CHEMISTRY'],
        'statement_of_conformity' => ['YES', 'No', 'As per Contract', 'As per Email'],
        'lab_sample_condition' => ['Acceptable', 'Not Acceptable'],
    ];

    /**
     * @return array<string, mixed>
     */
    public function buildFromSubmissionFormInstance(SubmissionFormInstance $instance, bool $forPdf = true): array
    {
        $instance->loadMissing([
            'submissionForm.sampleTypes',
            'crmCustomer.contacts',
            'submittedBy',
            'values.element',
            'batches.samples',
        ]);

        $formData = app(SubmissionFormValueNormalizer::class)->valuesMapFromInstance($instance);
        $sampleType = $instance->submissionForm?->sampleTypes->first();

        return $this->buildPayload(
            $formData,
            $sampleType,
            $instance,
            $forPdf,
            $instance->submittedBy,
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
        bool $forPdf,
        $creator = null,
    ): array {
        $documentCode = null;
        if ($submission instanceof SubmissionFormInstance) {
            $documentCode = $submission->submissionForm?->document_code;
        } elseif (is_object($submission) && isset($submission->submissionForm)) {
            $documentCode = $submission->submissionForm->document_code ?? null;
        }

        $variant = $this->resolveVariant($sampleType, $documentCode);
        $company = getActiveCompany();
        $branding = $this->resolveBranding($forPdf);

        $customer = $this->resolveCustomerFields($formData, $submission);
        $collection = $this->resolveCollectionFields($formData, $variant);
        $wasteWaterFields = $variant === 'waste_water'
            ? $this->resolveWasteWaterFields($formData)
            : [];
        $collectionGrid = $this->resolveCollectionGrid($collection, $variant, $wasteWaterFields);
        $sampleRows = $variant === 'waste_water'
            ? []
            : $this->padSampleRows(
                $this->enrichSampleRowsWithLabCodes(
                    $this->resolveSampleRows($formData, $variant),
                    $submission
                ),
                $variant === 'food' ? 5 : 10
            );
        $companyHeader = $this->resolveCompanyHeader($company);
        $conformity = self::normalizeSingleSelect(
            $formData['statement_of_conformity'] ?? '',
            self::FOOD_OPTIONS['statement_of_conformity']
        );
        $labUse = $this->resolveLabUseFields($formData, $submission, $creator);

        $formTitle = match ($variant) {
            'food' => 'TEST REQUEST FORM - FOOD',
            'waste_water' => 'TEST REQUEST FORM - WASTE WATER',
            default => 'TEST REQUEST FORM - WATER',
        };

        $documentRef = match ($variant) {
            'food' => 'AMS/QMS/LWS/019 - Test Request Form - Food - V0',
            'waste_water' => 'AMS/QMS/LWS/036 - Test Request Form - Waste Water - V0',
            default => 'AMS/QMS/LWS/020 - Test Request Form - Water - V0',
        };

        return [
            'variant' => $variant,
            'formTitle' => $formTitle,
            'documentRef' => $documentRef,
            'serialNumber' => $this->resolveSerialNumber($submission),
            'company' => $company,
            'companyHeader' => $companyHeader,
            'branding' => $branding,
            'logoSrc' => $branding['logoSrc'],
            'customer' => $customer,
            'collection' => $collection,
            'collectionGrid' => $collectionGrid,
            'sampleRows' => $sampleRows,
            'wasteWaterFields' => $wasteWaterFields,
            'signatures' => [
                'statement_of_conformity' => $conformity,
                'sampled_by' => (string) ($formData['sampled_by'] ?? $creator?->name ?? ''),
                'customer_rep_name' => (string) ($formData['customer_rep_name'] ?? ''),
                'customer_rep_signature' => (string) ($formData['customer_rep_signature'] ?? $formData['customer_representative_signature'] ?? ''),
                'customer_rep_contact' => (string) ($formData['customer_rep_contact'] ?? ''),
                'remarks' => (string) ($formData['remarks'] ?? ''),
            ],
            'labUse' => $labUse,
            'options' => match ($variant) {
                'food' => self::FOOD_OPTIONS,
                'waste_water' => self::WASTE_WATER_OPTIONS,
                default => self::WATER_OPTIONS,
            },
            'forPdf' => $forPdf,
        ];
    }

    /**
     * @return array{L: bool, SS: bool, S: bool}
     */
    /**
     * @return array<string, bool>
     */
    public static function sampleTypeChecks(?string $stored): array
    {
        $selected = self::normalizeSingleSelect($stored ?? '', self::FOOD_OPTIONS['sample_type']);

        return [
            'Raw' => strcasecmp($selected, 'Raw') === 0,
            'Cooked' => strcasecmp($selected, 'Cooked') === 0,
            'Ready To Eat' => strcasecmp($selected, 'Ready To Eat') === 0,
        ];
    }

    /**
     * @return array<string, bool>
     */
    public static function sampleConditionChecks(?string $stored): array
    {
        $selected = self::normalizeSingleSelect($stored ?? '', self::FOOD_OPTIONS['sample_condition']);

        return [
            'Acceptable' => strcasecmp($selected, 'Acceptable') === 0,
            'Chilled' => strcasecmp($selected, 'Chilled') === 0,
            'Frozen' => strcasecmp($selected, 'Frozen') === 0,
            'Ambient' => strcasecmp($selected, 'Ambient') === 0,
        ];
    }

    /**
     * @return array<string, bool>
     */
    public static function samplingPointChecks(?string $stored): array
    {
        return self::samplingLocationChecks($stored);
    }

    /**
     * Tick Tap/Tank/Pool/Shower Head/Others when the CRM sampling location
     * name matches (exact or contains) one of those categories.
     *
     * @return array<string, bool>
     */
    public static function samplingLocationChecks(?string $stored): array
    {
        $keys = self::WATER_OPTIONS['sampling_point'];
        $selected = self::normalizeSingleSelect($stored ?? '', $keys);

        if ($selected !== '' && in_array($selected, $keys, true)) {
            return [
                'Tap' => strcasecmp($selected, 'Tap') === 0,
                'Tank' => strcasecmp($selected, 'Tank') === 0,
                'Pool' => strcasecmp($selected, 'Pool') === 0,
                'Shower Head' => strcasecmp($selected, 'Shower Head') === 0,
                'Others' => strcasecmp($selected, 'Others') === 0,
            ];
        }

        $normalized = strtolower(trim((string) $stored));
        $matched = '';
        $sorted = $keys;
        usort($sorted, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
        foreach ($sorted as $key) {
            if ($normalized !== '' && str_contains($normalized, strtolower($key))) {
                $matched = $key;
                break;
            }
        }

        return [
            'Tap' => $matched === 'Tap',
            'Tank' => $matched === 'Tank',
            'Pool' => $matched === 'Pool',
            'Shower Head' => $matched === 'Shower Head',
            'Others' => $matched === 'Others',
        ];
    }

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
     * @param  array<string, bool>  $requirements
     * @return array{MICROBIOLOGY: bool, CHEMISTRY: bool}
     */
    public static function fieldDataRequirementChecks(array $requirements): array
    {
        $microbiology = (bool) ($requirements['MICROBIOLOGY'] ?? false);
        $chemistry = (bool) ($requirements['CHEMISTRY'] ?? false);
        $combined = (bool) ($requirements['MICROBIOLOGY + CHEMISTRY'] ?? false);

        return [
            'MICROBIOLOGY' => $microbiology || $combined,
            'CHEMISTRY' => $chemistry || $combined,
        ];
    }

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
        $resolvedContact = $this->resolveContactFromFormValue($formData['contact_person'] ?? null, $crm);
        $fallbackContact = $resolvedContact ?? ($crm && method_exists($crm, 'contacts') ? $crm->contacts()->first() : null);

        $customerEmail = (string) ($formData['customer_email'] ?? $crm?->email ?? '');

        return [
            'job_number' => $this->resolveJobNumber($formData, $submission),
            'customer_name' => (string) ($formData['customer_name'] ?? $formData['client_name'] ?? $crm?->name ?? ''),
            'customer_address' => (string) ($formData['customer_address'] ?? $formData['address'] ?? $crm?->physical_address ?? $crm?->postal_address ?? ''),
            'customer_phone' => (string) ($formData['customer_phone'] ?? $formData['tel_fax_no'] ?? $crm?->telephone1 ?? $crm?->telephone2 ?? ''),
            'contact_person' => $this->formatContactName($resolvedContact) !== ''
                ? $this->formatContactName($resolvedContact)
                : $this->resolveContactPersonLabel((string) ($formData['contact_person'] ?? ''), $fallbackContact),
            'mobile_number' => (string) ($formData['mobile_number'] ?? $resolvedContact?->mobile ?? $resolvedContact?->telephone ?? $fallbackContact?->mobile ?? $crm?->cell_phone ?? ''),
            'customer_tax_id' => '',
            'customer_email' => $customerEmail,
            'has_customer_extras' => $this->hasAnyFilledValues([$customerEmail]),
        ];
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private function resolveJobNumber(array $formData, $submission): string
    {
        $formNumber = '';
        if ($submission && ! empty($submission->form_number)) {
            $formNumber = trim((string) $submission->form_number);
        }

        $candidates = [];

        if ($submission) {
            $submission->loadMissing('batches');
            $batchCode = trim((string) ($submission->batches->first()?->batch_code ?? ''));
            if ($batchCode !== '') {
                $candidates[] = $batchCode;
            }
        }

        if (! empty($formData['job_number'])) {
            $candidates[] = trim((string) $formData['job_number']);
        }

        if ($submission) {
            $value = \App\Models\SubmissionFormInstanceValue::query()
                ->where('submission_form_instance_id', $submission->id)
                ->whereHas('element', static fn ($query) => $query->where('name', 'job_number'))
                ->value('value');

            if (! empty($value)) {
                $candidates[] = trim((string) $value);
            }
        }

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            // Job Number must stay distinct from TRF S.No. (form_number).
            if ($formNumber !== '' && $candidate === $formNumber) {
                continue;
            }

            return $candidate;
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    private function resolveCollectionFields(array $formData, string $variant): array
    {
        $options = match ($variant) {
            'food' => self::FOOD_OPTIONS,
            'waste_water' => self::WASTE_WATER_OPTIONS,
            default => self::WATER_OPTIONS,
        };

        $collectionExtras = [
            'date_received' => $this->formatDate($formData['date_received'] ?? ''),
            'packaging' => (string) ($formData['packaging'] ?? ''),
            'sample_weight' => (string) ($formData['sample_weight'] ?? ''),
            'sample_information' => (string) ($formData['sample_information'] ?? ''),
            'ship_name' => (string) ($formData['ship_name'] ?? ''),
            'port_of_loading' => (string) ($formData['port_of_loading'] ?? ''),
            'port_of_discharge' => (string) ($formData['port_of_discharge'] ?? ''),
            'seal_number' => (string) ($formData['seal_number'] ?? ''),
        ];

        return [
            'sampling_date' => $this->formatOrdinalDate($formData['sampling_date'] ?? ''),
            'sampling_date_raw' => $this->formatDate($formData['sampling_date'] ?? ''),
            'sampling_time' => (string) ($formData['sampling_time'] ?? ''),
            'sampling_location' => $this->resolveSamplePointLabel((string) ($formData['sampling_location'] ?? '')),
            'thermometer_id' => (string) ($formData['thermometer_id'] ?? ''),
            'sampling_apparatus' => self::normalizeCheckboxGroup($formData['sampling_apparatus'] ?? [], $options['sampling_apparatus']),
            'method_of_sampling' => self::normalizeCheckboxGroup($formData['method_of_sampling'] ?? [], $options['method_of_sampling']),
            'reason_of_collection' => self::normalizeCheckboxGroup($formData['reason_of_collection'] ?? [], $options['reason_of_collection']),
            'transport_condition' => self::normalizeCheckboxGroup($formData['transport_condition'] ?? [], $options['transport_condition']),
            'collection_extras' => $collectionExtras,
            'has_collection_extras' => $this->hasAllFilledValues($collectionExtras),
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
                $sampleType = self::normalizeSingleSelect($row['sample_type'] ?? '', self::FOOD_OPTIONS['sample_type']);
                $sampleCondition = self::normalizeSingleSelect($row['sample_condition'] ?? '', self::FOOD_OPTIONS['sample_condition']);
                $normalized[] = [
                    'serial' => $index + 1,
                    'sample_no' => (string) ($row['sample_no'] ?? $row['lims_sample_no'] ?? ''),
                    'sample_description' => $this->plainTextField($row['sample_description'] ?? ''),
                    'sampling_location' => $this->resolveSamplePointLabel((string) ($row['sampling_point'] ?? $row['sampling_location'] ?? '')),
                    'sampling_point' => trim((string) ($row['sampling_point_manual'] ?? $row['manual_sampling_point'] ?? '')),
                    'qty' => $this->formatRowQuantity($row),
                    'sample_type' => $sampleType,
                    'sample_type_checks' => self::sampleTypeChecks($sampleType),
                    'sample_condition' => $sampleCondition,
                    'sample_condition_checks' => self::sampleConditionChecks($sampleCondition),
                    'sample_temp' => (string) ($row['sample_temp'] ?? ''),
                    'production_date' => $this->formatDate($row['production_date'] ?? ''),
                    'expiration_date' => $this->formatDate($row['expiration_date'] ?? ''),
                    'batch_number' => (string) ($row['batch_number'] ?? ''),
                    'parameters' => app(AnalysisReferenceLabelResolver::class)->resolveMixed($row['parameters'] ?? ''),
                    'state_of_sample' => self::stateOfSampleChecks($row['state_of_sample'] ?? null),
                ];
                continue;
            }

            $samplingLocationLabel = $this->resolveSamplePointLabel((string) (
                $row['sampling_point']
                ?? $row['sampling_location']
                ?? $row['location']
                ?? ''
            ));
            $normalized[] = [
                'serial' => $index + 1,
                'sample_no' => (string) ($row['sample_no'] ?? $row['lims_sample_no'] ?? ''),
                'sample_description' => $this->plainTextField($row['sample_description'] ?? ''),
                'location' => $samplingLocationLabel,
                'sampling_location' => $samplingLocationLabel,
                'qty' => $this->formatRowQuantity($row),
                'sampling_point' => trim((string) ($row['sampling_point_manual'] ?? $row['manual_sampling_point'] ?? '')),
                'sampling_location_checks' => self::samplingLocationChecks($samplingLocationLabel),
                'sampling_point_checks' => self::samplingLocationChecks($samplingLocationLabel),
                'ph' => (string) ($row['ph'] ?? $row['field_ph'] ?? ''),
                'appearance' => (string) ($row['appearance'] ?? $row['field_appearance'] ?? ''),
                'residual_chlorine' => (string) ($row['residual_chlorine'] ?? $row['field_residual_chlorine'] ?? ''),
                'odor' => (string) ($row['odor'] ?? $row['field_odor'] ?? ''),
                'sample_temp' => (string) ($row['sample_temp'] ?? $row['field_sample_temp'] ?? ''),
                'microbiology' => filter_var($row['microbiology'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'legionella' => filter_var($row['legionella'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'chemistry' => filter_var($row['chemistry'] ?? $row['chemical_analysis'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        return $normalized;
    }

    /**
     * Fill blank TRF sample_no cells from linked batch sample codes after acceptance.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function enrichSampleRowsWithLabCodes(array $rows, $submission): array
    {
        if ($rows === [] || ! $submission instanceof SubmissionFormInstance) {
            return $rows;
        }

        $labCodes = $submission->batches
            ->flatMap(static fn ($batch) => $batch->samples ?? collect())
            ->map(static fn ($detail): string => trim((string) ($detail->sample_code ?? $detail->sample_no ?? '')))
            ->filter(static fn (string $code): bool => $code !== '')
            ->values()
            ->all();

        if ($labCodes === []) {
            return $rows;
        }

        $codeIndex = 0;
        foreach ($rows as $index => $row) {
            $existing = trim((string) ($row['sample_no'] ?? ''));
            if ($existing !== '') {
                continue;
            }
            if (! isset($labCodes[$codeIndex])) {
                break;
            }
            $rows[$index]['sample_no'] = $labCodes[$codeIndex];
            $codeIndex++;
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    private function resolveLabUseFields(array $formData, $submission = null, $creator = null): array
    {
        $receivedAt = $formData['lab_received_datetime'] ?? '';
        if ($receivedAt === '' && $submission?->submitted_at) {
            $receivedAt = $submission->submitted_at;
        }

        if ($receivedAt !== '') {
            try {
                $receivedAt = Carbon::parse($receivedAt)->format('d/m/Y H:i');
            } catch (\Throwable) {
                $receivedAt = (string) $receivedAt;
            }
        }

        return [
            'lab_received_datetime' => $receivedAt,
            'lab_received_by' => (string) ($formData['lab_received_by'] ?? $creator?->name ?? ''),
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

    private function formatOrdinalDate($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            $date = Carbon::parse($value);
            $day = (int) $date->format('j');
            $suffix = match ($day % 10) {
                1 => $day % 100 === 11 ? 'th' : 'st',
                2 => $day % 100 === 12 ? 'th' : 'nd',
                3 => $day % 100 === 13 ? 'th' : 'rd',
                default => 'th',
            };

            return $day . $suffix . ' ' . $date->format('F Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function formatMetaField(string $label, string $value = ''): string
    {
        if ($value !== '') {
            $valueHtml = nl2br(e($value));
        } else {
            $valueHtml = '&nbsp;';
        }

        return '<span class="trf-meta-key">' . e($label) . '</span>'
            . '<span class="trf-meta-val">' . $valueHtml . '</span>';
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function padSampleRows(array $rows, int $minRows): array
    {
        return $rows;
    }

    /**
     * @param  array<string, mixed>  $collection
     * @param  array<string, mixed>  $wasteWaterFields
     * @return array<string, mixed>
     */
    private function resolveCollectionGrid(array $collection, string $variant, array $wasteWaterFields = []): array
    {
        $apparatus = $collection['sampling_apparatus'] ?? [];
        $method = $collection['method_of_sampling'] ?? [];
        $reason = $collection['reason_of_collection'] ?? [];
        $transport = $collection['transport_condition'] ?? [];
        $thermometerId = (string) ($collection['thermometer_id'] ?? '');

        $metaRows = [
            [
                'label' => 'Sampling Date:',
                'value' => (string) ($collection['sampling_date'] ?? ''),
            ],
            [
                'label' => 'Sampling Time:',
                'value' => (string) ($collection['sampling_time'] ?? ''),
            ],
            [
                'label' => 'Sampling Location:',
                'value' => (string) ($collection['sampling_location'] ?? ''),
            ],
        ];

        $collectionExtras = $collection['collection_extras'] ?? [];
        $hasCollectionExtras = (bool) ($collection['has_collection_extras'] ?? false);

        $methodOrderedKeys = ['APHA', 'US FDA', 'SASO', 'CCFRA', 'ASTM', 'DM', 'OTHERS', 'SOP'];

        if ($variant === 'food') {
            return [
                'meta_rows' => $metaRows,
                'apparatus' => $apparatus,
                'apparatus_ordered_keys' => ['STERILE BAG', 'AIR SAMPLER', 'STERILE BOTTLE', 'GRABBER', 'STERILE SWAB', 'OTHERS'],
                'thermometer_id' => $thermometerId,
                'method' => $method,
                'method_ordered_keys' => $methodOrderedKeys,
                'reason' => $reason,
                'reason_ordered_keys' => self::FOOD_OPTIONS['reason_of_collection'],
                'transport' => $transport,
                'transport_ordered_keys' => self::FOOD_OPTIONS['transport_condition'],
                'collection_extras' => $collectionExtras,
                'has_collection_extras' => $hasCollectionExtras,
            ];
        }

        if ($variant === 'waste_water') {
            return [
                'meta_rows' => $metaRows,
                'sample_description' => (string) ($wasteWaterFields['sample_description'] ?? ''),
                'apparatus' => $wasteWaterFields['sampling_apparatus'] ?? $apparatus,
                'apparatus_ordered_keys' => self::WASTE_WATER_OPTIONS['sampling_apparatus'],
                'thermometer_id' => (string) ($wasteWaterFields['thermometer_id'] ?? $thermometerId),
                'ph_meter_id' => (string) ($wasteWaterFields['ph_meter_id'] ?? ''),
                'chlorine_meter_id' => (string) ($wasteWaterFields['chlorine_meter_id'] ?? ''),
                'sampling_apparatus_others' => (string) ($wasteWaterFields['sampling_apparatus_others'] ?? ''),
                'method' => $wasteWaterFields['method_of_sampling'] ?? $method,
                'method_ordered_keys' => ['APHA', 'US FDA', 'EPA', 'CCFRA', 'DM', 'SOP', 'OTHERS'],
                'reason' => $wasteWaterFields['reason_of_collection'] ?? $reason,
                'reason_ordered_keys' => self::WASTE_WATER_OPTIONS['reason_of_collection'],
                'technique' => $wasteWaterFields['sampling_technique'] ?? [],
                'technique_ordered_keys' => self::WASTE_WATER_OPTIONS['sampling_technique'],
            ];
        }

        return [
            'meta_rows' => $metaRows,
            'apparatus' => $apparatus,
            'apparatus_ordered_keys' => ['STERILE BAG', 'GRABBER', 'STERILE BOTTLE', 'OTHERS'],
            'thermometer_id' => $thermometerId,
            'method' => $method,
            'method_ordered_keys' => $methodOrderedKeys,
            'reason' => $reason,
            'reason_ordered_keys' => self::WATER_OPTIONS['reason_of_collection'],
            'transport' => $transport,
            'transport_ordered_keys' => self::WATER_OPTIONS['transport_condition'],
            'collection_extras' => $collectionExtras,
            'has_collection_extras' => $hasCollectionExtras,
        ];
    }

    /**
     * @param  array<int, string>  $values
     */
    private function hasAnyFilledValues(array $values): bool
    {
        foreach ($values as $value) {
            if (trim($value) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, string>  $values
     */
    private function hasAllFilledValues(array $values): bool
    {
        foreach ($values as $value) {
            if (trim($value) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    private function resolveWasteWaterFields(array $formData): array
    {
        $fieldDataRequirements = self::normalizeCheckboxGroup(
            $formData['field_data_requirements'] ?? [],
            self::WASTE_WATER_OPTIONS['field_data_requirements']
        );

        return [
            'sample_number' => (string) ($formData['sample_number'] ?? ''),
            'sample_description' => (string) ($formData['sample_description'] ?? ''),
            'sampling_apparatus' => self::normalizeCheckboxGroup(
                $formData['sampling_apparatus'] ?? [],
                self::WASTE_WATER_OPTIONS['sampling_apparatus']
            ),
            'thermometer_id' => (string) ($formData['thermometer_id'] ?? ''),
            'ph_meter_id' => (string) ($formData['ph_meter_id'] ?? ''),
            'chlorine_meter_id' => (string) ($formData['chlorine_meter_id'] ?? ''),
            'sampling_apparatus_others' => (string) ($formData['sampling_apparatus_others'] ?? ''),
            'method_of_sampling' => self::normalizeCheckboxGroup(
                $formData['method_of_sampling'] ?? [],
                self::WASTE_WATER_OPTIONS['method_of_sampling']
            ),
            'reason_of_collection' => self::normalizeCheckboxGroup(
                $formData['reason_of_collection'] ?? [],
                self::WASTE_WATER_OPTIONS['reason_of_collection']
            ),
            'sampling_technique' => self::normalizeCheckboxGroup(
                $formData['sampling_technique'] ?? [],
                self::WASTE_WATER_OPTIONS['sampling_technique']
            ),
            'sampling_source' => self::normalizeCheckboxGroup(
                $formData['sampling_source'] ?? [],
                self::WASTE_WATER_OPTIONS['sampling_source']
            ),
            'sample_types_ww' => self::normalizeCheckboxGroup(
                $formData['sample_types_ww'] ?? [],
                self::WASTE_WATER_OPTIONS['sample_types_ww']
            ),
            'transport_condition' => self::normalizeCheckboxGroup(
                $formData['transport_condition'] ?? [],
                self::WASTE_WATER_OPTIONS['transport_condition']
            ),
            'field_data_quantity' => (string) ($formData['field_data_quantity'] ?? ''),
            'field_data_appearance' => (string) ($formData['field_data_appearance'] ?? ''),
            'field_data_color' => (string) ($formData['field_data_color'] ?? ''),
            'field_data_odor' => (string) ($formData['field_data_odor'] ?? ''),
            'field_data_ph' => (string) ($formData['field_data_ph'] ?? ''),
            'field_data_temperature' => (string) ($formData['field_data_temperature'] ?? ''),
            'field_data_free_chlorine' => (string) ($formData['field_data_free_chlorine'] ?? ''),
            'field_data_requirements' => $fieldDataRequirements,
            'field_data_requirement_checks' => self::fieldDataRequirementChecks($fieldDataRequirements),
        ];
    }

    /**
     * @param  object|null  $company
     * @return array<string, string>
     */
    private function resolveCompanyHeader($company): array
    {
        if (! $company) {
            return [
                'name' => '',
                'telephone' => '',
                'email' => '',
                'address' => '',
                'po_box' => '',
                'fax' => '',
                'website' => '',
            ];
        }

        $addressParts = array_filter([
            $company->address ?? null,
            $company->street ?? null,
            $company->location ?? null,
        ]);

        $poBox = '';
        foreach (['po_box', 'postal_address', 'postal_box'] as $field) {
            if (! empty($company->{$field})) {
                $poBox = (string) $company->{$field};
                break;
            }
        }

        return [
            'name' => (string) ($company->name ?? ''),
            'telephone' => (string) ($company->telephone ?? $company->telephone1 ?? ''),
            'email' => (string) ($company->email ?? ''),
            'address' => implode(', ', $addressParts),
            'po_box' => $poBox,
            'fax' => (string) ($company->fax ?? ''),
            'website' => (string) ($company->website ?? ''),
        ];
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
            ?: \App\Services\System\ThemeService::PRIMARY;

        $logoSrc = $this->resolveLogoAsDataUri();

        return [
            'primary' => $primary,
            'logoSrc' => $logoSrc,
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

    /**
     * @param  array<string, mixed>  $row
     */
    private function formatRowQuantity(array $row): string
    {
        $quantity = trim((string) ($row['sample_quantity'] ?? ''));
        $unit = trim((string) ($row['sample_quantity_unit'] ?? ''));

        if ($quantity !== '' && $unit !== '') {
            return $quantity.' '.$unit;
        }

        if ($quantity !== '') {
            return $quantity;
        }

        $legacy = trim((string) ($row['qty'] ?? ''));

        return $legacy !== '' ? $legacy : '';
    }

    private function plainTextField(mixed $value): string
    {
        $string = trim((string) $value);
        if ($string === '') {
            return '';
        }

        $decoded = html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = trim(strip_tags($decoded));

        return trim(preg_replace('/\s+/u', ' ', $plain) ?? $plain);
    }

    private function resolveVariant(?\App\SampleType $sampleType, ?string $documentCode): string
    {
        $code = strtoupper((string) $documentCode);
        if (str_contains($code, 'FOOD')) {
            return 'food';
        }
        if (str_contains($code, 'WASTE')) {
            return 'waste_water';
        }
        if (str_contains($code, 'WATER')) {
            return 'water';
        }

        if ($sampleType === null) {
            return 'water';
        }

        $isFood = stripos($sampleType->name, 'Food') !== false || stripos($sampleType->code, 'FOOD') !== false;
        $isWasteWater = stripos($sampleType->name, 'Waste Water') !== false || stripos($sampleType->code, 'WWTR') !== false;
        $isWater = ! $isWasteWater && (stripos($sampleType->name, 'Water') !== false || stripos($sampleType->code, 'WTR') !== false);

        if ($isFood) {
            return 'food';
        }
        if ($isWasteWater) {
            return 'waste_water';
        }
        if ($isWater) {
            return 'water';
        }

        return 'water';
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
        if ($point === null) {
            return $value;
        }

        $name = trim((string) ($point->display_name ?? $point->name ?? ''));

        return $name !== '' ? $name : $value;
    }

    private function resolveContactFromFormValue(mixed $value, $crm): ?CustomerContact
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        if (Str::isUuid($raw) || ctype_digit($raw)) {
            $contact = CustomerContact::query()->find($raw);
            if ($contact !== null) {
                return $contact;
            }
        }

        if ($crm && method_exists($crm, 'contacts')) {
            return $crm->contacts()
                ->get()
                ->first(function (CustomerContact $contact) use ($raw): bool {
                    return strcasecmp($this->formatContactName($contact), $raw) === 0;
                });
        }

        return null;
    }

    private function resolveContactPersonLabel(string $value, mixed $fallbackContact): string
    {
        $value = trim($value);
        if ($value === '') {
            return $this->formatContactName($fallbackContact instanceof CustomerContact ? $fallbackContact : null);
        }

        if (! Str::isUuid($value) && ! ctype_digit($value)) {
            return $value;
        }

        $contact = CustomerContact::query()->find($value);
        $resolved = $this->formatContactName($contact);
        if ($resolved !== '') {
            return $resolved;
        }

        return $this->formatContactName($fallbackContact instanceof CustomerContact ? $fallbackContact : null);
    }

    private function formatContactName(?CustomerContact $contact): string
    {
        if ($contact === null) {
            return '';
        }

        return trim(implode(' ', array_filter([
            (string) ($contact->first_name ?? ''),
            (string) ($contact->middle_name ?? ''),
            (string) ($contact->last_name ?? ''),
        ])));
    }

}

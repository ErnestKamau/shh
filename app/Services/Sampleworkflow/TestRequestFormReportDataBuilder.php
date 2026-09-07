<?php

namespace App\Services\Sampleworkflow;

use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\SamplePoint;
use App\Models\SamplePoint as MasterSamplePoint;
use App\Models\System\SystemConfiguration;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use App\SampleType;
use App\Services\SubmissionForm\PortalTestRequestFormSampleTypeResolver;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use App\Services\Lab\AnalysisReferenceLabelResolver;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
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
            'submissionForm.sampleTypeCategories',
            'crmCustomer.contacts',
            'submittedBy',
            'values.element',
            'batches.samples',
        ]);

        $formData = app(SubmissionFormValueNormalizer::class)->valuesMapFromInstance($instance);
        $sampleType = $instance->submissionForm !== null
            ? app(PortalTestRequestFormSampleTypeResolver::class)->resolveForForm($instance->submissionForm)->first()
            : null;

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

        $foodSampleTypeColumns = $variant === 'food'
            ? $this->resolveFoodSampleTypeColumns($formData)
            : [];

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
            'foodSampleTypeColumns' => $foodSampleTypeColumns,
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
    public static function sampleConditionChecks(mixed $stored): array
    {
        $tokens = self::sampleConditionTokens($stored);

        return [
            'Acceptable' => in_array('acceptable', $tokens, true),
            'Chilled' => in_array('chilled', $tokens, true),
            'Frozen' => in_array('frozen', $tokens, true),
            'Ambient' => in_array('ambient', $tokens, true),
        ];
    }

    /**
     * @return list<string>
     */
    public static function sampleConditionTokens(mixed $stored): array
    {
        if (is_array($stored)) {
            $selectedKeys = SubmissionFormSchemaHelper::selectedCheckboxKeys($stored);
            if ($selectedKeys !== null) {
                $tokens = [];
                foreach ($stored as $key => $checked) {
                    if (filter_var($checked, FILTER_VALIDATE_BOOLEAN)) {
                        $tokens[] = strtolower(trim((string) $key));
                    }
                }

                return array_values(array_unique($tokens));
            }

            $stored = implode(',', array_map('strval', $stored));
        }

        if ($stored === null || $stored === '') {
            return [];
        }

        $tokens = [];
        foreach (preg_split('/[,;|]+/', (string) $stored) ?: [] as $token) {
            $token = strtolower(trim($token));
            if ($token === '') {
                continue;
            }

            if (! in_array($token, $tokens, true)) {
                $tokens[] = $token;
            }
        }

        if ($tokens === []) {
            $selected = self::normalizeSingleSelect((string) $stored, self::FOOD_OPTIONS['sample_condition']);
            if ($selected !== '') {
                $tokens[] = strtolower($selected);
            }
        }

        return $tokens;
    }

    /**
     * @return array<string, bool>
     */
    public static function foodMicroChemChecks(mixed $testCategory): array
    {
        $tokens = SubmissionFormSchemaHelper::testCategoryTokens($testCategory);

        return [
            'Micro' => in_array('microbiology', $tokens, true),
            'Chem' => in_array('chemistry', $tokens, true),
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
        $selectedCanon = array_values(array_filter(array_map(
            static fn ($item): string => self::checkboxCanonical((string) $item),
            $selected
        )));
        $optionCanons = [];
        foreach ($allOptions as $option) {
            $optionCanons[$option] = self::checkboxCanonical((string) $option);
        }
        $exactOptionCanons = array_values(array_filter($optionCanons));

        $result = [];
        foreach ($allOptions as $option) {
            $optionCanon = $optionCanons[$option];
            $matched = $optionCanon !== '' && in_array($optionCanon, $selectedCanon, true);

            if (! $matched && $optionCanon !== '') {
                foreach ($selectedCanon as $selCanon) {
                    // Prefer exact option matches (e.g. MICROBIOLOGY) over prefixing a longer
                    // label (e.g. MICROBIOLOGY + CHEMISTRY) when the slug equals an option.
                    if (in_array($selCanon, $exactOptionCanons, true)) {
                        continue;
                    }

                    // Form slugs → PDF labels: haccp→HACCP REQUIREMENT, chiller→CHILLER VEHICLE
                    if (strlen($selCanon) >= 4 && str_starts_with($optionCanon, $selCanon)) {
                        $matched = true;
                        break;
                    }
                }
            }

            $result[$option] = $matched;
        }

        return $result;
    }

    /**
     * Canonical form for checkbox matching (strips spaces, punctuation, underscores).
     */
    public static function checkboxCanonical(string $value): string
    {
        return preg_replace('/[^A-Z0-9]+/', '', strtoupper(trim($value))) ?? '';
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
        $crm?->loadMissing(['mainContact', 'contacts']);
        $fallbackContact = $resolvedContact ?? $crm?->mainContact ?? ($crm && method_exists($crm, 'contacts') ? $crm->contacts()->first() : null);

        $customerEmail = (string) ($formData['customer_email'] ?? $resolvedContact?->email ?? $fallbackContact?->email ?? $crm?->email ?? '');

        return [
            'job_number' => $this->resolveJobNumber($formData, $submission),
            'customer_name' => (string) ($formData['customer_name'] ?? $formData['client_name'] ?? $crm?->name ?? ''),
            'customer_address' => (string) ($formData['customer_address'] ?? $formData['address'] ?? $crm?->physical_address ?? $crm?->postal_address ?? ''),
            'company_unit_name' => $this->resolveCompanyUnitLabel((string) ($formData['company_unit_id'] ?? '')),
            'customer_phone' => (string) ($formData['customer_phone'] ?? $formData['tel_fax_no'] ?? $resolvedContact?->telephone ?? $fallbackContact?->telephone ?? $crm?->telephone1 ?? $crm?->telephone2 ?? ''),
            'contact_person' => $this->formatContactName($resolvedContact) !== ''
                ? $this->formatContactName($resolvedContact)
                : $this->resolveContactPersonLabel((string) ($formData['contact_person'] ?? ''), $fallbackContact),
            'mobile_number' => (string) ($formData['mobile_number'] ?? $resolvedContact?->mobile ?? $resolvedContact?->telephone ?? $fallbackContact?->mobile ?? $fallbackContact?->telephone ?? $crm?->telephone2 ?? $crm?->telephone1 ?? ''),
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

        if ($submission instanceof SubmissionFormInstance) {
            $labIdentifiers = $this->resolveLabIdentifiersFromLinkedBatches($submission);
            if ($labIdentifiers['job_number'] !== '') {
                $candidates[] = $labIdentifiers['job_number'];
            }
        }

        if (! empty($formData['job_number'])) {
            $candidates[] = trim((string) $formData['job_number']);
        }

        if ($submission instanceof SubmissionFormInstance) {
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
            'sampling_location' => $this->resolveSamplePointDisplayLabel((string) ($formData['sampling_location'] ?? '')),
            'thermometer_id' => $variant === 'waste_water'
                ? (string) ($formData['thermometer_id'] ?? '')
                : app(TrfSamplingEquipmentResolver::class)->formatForPdf($formData['thermometer_id'] ?? ''),
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
                $sampleConditionRaw = $row['sample_condition'] ?? '';
                $sampleConditionTokens = self::sampleConditionTokens($sampleConditionRaw);
                $sampleConditionLabel = implode(', ', array_map(
                    static fn (string $token): string => ucfirst($token),
                    $sampleConditionTokens
                ));
                $normalized[] = [
                    'serial' => $index + 1,
                    'sample_no' => (string) ($row['sample_no'] ?? $row['lims_sample_no'] ?? ''),
                    'sample_description' => $this->plainTextField($row['sample_description'] ?? ''),
                    'sampling_point' => trim((string) ($row['sampling_point_manual'] ?? $row['manual_sampling_point'] ?? '')),
                    'qty' => $this->formatRowQuantity($row),
                    'sample_condition' => $sampleConditionLabel,
                    'sample_condition_checks' => self::sampleConditionChecks($sampleConditionRaw),
                    'sample_temp' => (string) ($row['sample_temp'] ?? $row['field_sample_temp'] ?? ''),
                    'production_date' => $this->formatDate($row['production_date'] ?? ''),
                    'expiration_date' => $this->formatDate($row['expiration_date'] ?? ''),
                    'batch_number' => (string) ($row['batch_number'] ?? ''),
                    'micro_chem_checks' => self::foodMicroChemChecks($row['test_category'] ?? null),
                    'sample_type_checks' => self::sampleTypeChecks(
                        is_string($row['sample_type'] ?? null) ? $row['sample_type'] : null
                    ),
                    'sample_type_ticks' => $this->resolveFoodSampleTypeTicks($row),
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

            if ($variant === 'water') {
                $testRequirementChecks = $this->resolveWaterTestRequirementChecks($row);
                $normalized[] = [
                    'serial' => $index + 1,
                    'sample_no' => (string) ($row['sample_no'] ?? $row['lims_sample_no'] ?? ''),
                    'sample_description' => $this->plainTextField($row['sample_description'] ?? ''),
                    'qty' => $this->formatRowQuantity($row),
                    'sampling_point' => trim((string) ($row['sampling_point_manual'] ?? $row['manual_sampling_point'] ?? '')),
                    'ph' => (string) ($row['ph'] ?? $row['field_ph'] ?? ''),
                    'appearance' => (string) ($row['appearance'] ?? $row['field_appearance'] ?? ''),
                    'residual_chlorine' => (string) ($row['residual_chlorine'] ?? $row['field_residual_chlorine'] ?? ''),
                    'odor' => (string) ($row['odor'] ?? $row['field_odor'] ?? ''),
                    'sample_temp' => (string) ($row['sample_temp'] ?? $row['field_sample_temp'] ?? ''),
                    'microbiology' => $testRequirementChecks['microbiology'],
                    'legionella' => $testRequirementChecks['legionella'],
                    'chemistry' => $testRequirementChecks['chemistry'],
                ];

                continue;
            }

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
     * @param  array<string, mixed>  $row
     * @return array{microbiology: bool, legionella: bool, chemistry: bool}
     */
    private function resolveWaterTestRequirementChecks(array $row): array
    {
        $requirementSource = $row['test_requirements']
            ?? $row['test_category']
            ?? null;

        if ($requirementSource === null || $requirementSource === '') {
            if (
                filter_var($row['microbiology'] ?? false, FILTER_VALIDATE_BOOLEAN)
                || filter_var($row['legionella'] ?? false, FILTER_VALIDATE_BOOLEAN)
                || filter_var($row['chemistry'] ?? false, FILTER_VALIDATE_BOOLEAN)
                || filter_var($row['chemical_analysis'] ?? false, FILTER_VALIDATE_BOOLEAN)
            ) {
                return [
                    'microbiology' => filter_var($row['microbiology'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'legionella' => filter_var($row['legionella'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'chemistry' => filter_var($row['chemistry'] ?? $row['chemical_analysis'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ];
            }

            return [
                'microbiology' => false,
                'legionella' => false,
                'chemistry' => false,
            ];
        }

        $tokens = SubmissionFormSchemaHelper::testCategoryTokens($requirementSource);

        return [
            'microbiology' => in_array('microbiology', $tokens, true),
            'legionella' => in_array('legionella', $tokens, true),
            'chemistry' => in_array('chemistry', $tokens, true),
        ];
    }

    /**
     * Fill TRF sample_no cells from linked batch sample codes after acceptance.
     * Lab sample codes always win once samples exist (customer/placeholder values are replaced).
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function enrichSampleRowsWithLabCodes(array $rows, $submission): array
    {
        if ($rows === [] || ! $submission instanceof SubmissionFormInstance) {
            return $rows;
        }

        $labCodes = $this->resolveLabIdentifiersFromLinkedBatches($submission)['sample_codes'];

        if ($labCodes === []) {
            return $rows;
        }

        foreach ($rows as $index => $row) {
            if (! isset($labCodes[$index])) {
                break;
            }
            $rows[$index]['sample_no'] = $labCodes[$index];
        }

        return $rows;
    }

    /**
     * Job / sample numbers assigned at Sample Integrity acceptance.
     *
     * @return array{job_number: string, sample_codes: list<string>}
     */
    private function resolveLabIdentifiersFromLinkedBatches(SubmissionFormInstance $submission): array
    {
        $batches = $this->linkedBatchesWithSamples($submission);

        $jobNumber = '';
        $sampleCodes = [];

        foreach ($batches as $batch) {
            $batchCode = trim((string) ($batch->batch_code ?? ''));
            if ($jobNumber === '' && $batchCode !== '') {
                $jobNumber = $batchCode;
            }

            $samples = $batch->relationLoaded('samples')
                ? $batch->samples
                : $batch->samples()->orderBy('id')->get();

            foreach ($samples as $detail) {
                $code = trim((string) ($detail->sample_code ?? ''));
                if ($code === '') {
                    $code = trim((string) ($detail->sample_no ?? ''));
                }
                if ($code !== '') {
                    $sampleCodes[] = $code;
                }
            }
        }

        return [
            'job_number' => $jobNumber,
            'sample_codes' => $sampleCodes,
        ];
    }

    /**
     * @return Collection<int, SampleHeader>
     */
    private function linkedBatchesWithSamples(SubmissionFormInstance $submission): Collection
    {
        $submission->loadMissing(['batches.samples']);

        $batches = $submission->batches;
        if ($batches instanceof Collection && $batches->isNotEmpty()) {
            return $batches->sortBy('id')->values();
        }

        return SampleHeader::query()
            ->with(['samples' => static fn ($query) => $query->orderBy('id')])
            ->where('submission_form_instance_id', $submission->id)
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    private function resolveLabUseFields(array $formData, $submission = null, $creator = null): array
    {
        $enquiry = null;
        if ($submission instanceof SubmissionFormInstance) {
            $submission->loadMissing('sampleSubmissionRequest');
            $enquiry = $submission->sampleSubmissionRequest;
        }

        $labCondition = self::normalizeSingleSelect(
            $formData['lab_sample_condition'] ?? '',
            self::FOOD_OPTIONS['lab_sample_condition']
        );

        $configs = is_array($enquiry?->enquiry_sample_configuration)
            ? $enquiry->enquiry_sample_configuration
            : [];

        // Received Date/Time and Received By are filled only after Receive Samples
        // (Ready for Reception). Do not fall back to submit time or form creator.
        $hasBeenPhysicallyReceived = filled($enquiry?->received_by_full_name)
            || filled($enquiry?->received_by_date);

        if ($enquiry !== null && ! $hasBeenPhysicallyReceived) {
            return [
                'lab_received_datetime' => '',
                'lab_received_by' => '',
                'lab_sample_condition' => $labCondition,
            ];
        }

        $receivedAt = '';
        $receivedBy = '';

        if ($hasBeenPhysicallyReceived || $enquiry === null) {
            $receivedAt = $formData['lab_received_datetime'] ?? '';
            if ($receivedAt === '' && $enquiry?->received_by_date) {
                $date = optional($enquiry->received_by_date)->format('Y-m-d')
                    ?? (string) $enquiry->received_by_date;
                $time = trim((string) ($enquiry->received_by_time ?? ''));
                $receivedAt = trim($date.' '.$time);
            }

            $receivedBy = trim((string) ($formData['lab_received_by'] ?? ''));
            if ($receivedBy === '') {
                $receivedBy = trim((string) ($enquiry?->received_by_full_name ?? ''));
            }
        }

        if ($receivedAt !== '') {
            try {
                $receivedAt = Carbon::parse($receivedAt)->format('d/m/Y H:i');
            } catch (\Throwable) {
                $receivedAt = (string) $receivedAt;
            }
        }

        if ($hasBeenPhysicallyReceived && $configs !== []) {
            $labCondition = TrfLabUseFieldsService::labSampleConditionFromConfigs($configs);
        }

        return [
            'lab_received_datetime' => $receivedAt,
            'lab_received_by' => $receivedBy,
            'lab_sample_condition' => $labCondition,
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
                'apparatus' => $wasteWaterFields['sampling_apparatus'] ?? [],
                'apparatus_ordered_keys' => self::WASTE_WATER_OPTIONS['sampling_apparatus'],
                'thermometer_id' => (string) ($wasteWaterFields['thermometer_id'] ?? ''),
                'ph_meter_id' => (string) ($wasteWaterFields['ph_meter_id'] ?? ''),
                'chlorine_meter_id' => (string) ($wasteWaterFields['chlorine_meter_id'] ?? ''),
                'sampling_apparatus_others' => (string) ($wasteWaterFields['sampling_apparatus_others'] ?? ''),
                'extra_sampling_equipment' => (string) ($wasteWaterFields['extra_sampling_equipment'] ?? ''),
                'method' => $wasteWaterFields['method_of_sampling'] ?? [],
                'method_ordered_keys' => ['APHA', 'US FDA', 'EPA', 'CCFRA', 'DM', 'SOP', 'OTHERS'],
                'reason' => $wasteWaterFields['reason_of_collection'] ?? [],
                'reason_ordered_keys' => self::WASTE_WATER_OPTIONS['reason_of_collection'],
                'technique' => $wasteWaterFields['sampling_technique'] ?? [],
                'technique_ordered_keys' => self::WASTE_WATER_OPTIONS['sampling_technique'],
            ];
        }

        return [
            'meta_rows' => $metaRows,
            'apparatus' => $apparatus,
            'apparatus_ordered_keys' => self::WATER_OPTIONS['sampling_apparatus'],
            'thermometer_id' => $thermometerId,
            'method' => $method,
            'method_ordered_keys' => self::WATER_OPTIONS['method_of_sampling'],
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
            'sample_number' => $this->plainTextField($formData['sample_number'] ?? ''),
            // Collection "Sample & sampling point description" only — never row sample_description.
            'sample_description' => $this->plainTextField($formData['sample_sampling_point_description'] ?? ''),
            'sampling_apparatus' => self::normalizeCheckboxGroup(
                $formData['sampling_apparatus'] ?? [],
                self::WASTE_WATER_OPTIONS['sampling_apparatus']
            ),
            'thermometer_id' => $this->plainTextField($formData['thermometer_id'] ?? ''),
            'ph_meter_id' => $this->plainTextField($formData['ph_meter_id'] ?? ''),
            'chlorine_meter_id' => $this->plainTextField($formData['chlorine_meter_id'] ?? ''),
            'sampling_apparatus_others' => $this->plainTextField($formData['sampling_apparatus_others'] ?? ''),
            'extra_sampling_equipment' => $this->formatExtraSamplingEquipmentForPdf($formData['extra_sampling_equipment'] ?? null),
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
            'field_data_quantity' => $this->plainTextField($this->firstScalarFromPossiblyRowValue($formData['field_data_quantity'] ?? '')),
            'field_data_appearance' => $this->plainTextField($this->firstScalarFromPossiblyRowValue($formData['field_data_appearance'] ?? '')),
            'field_data_color' => $this->plainTextField($this->firstScalarFromPossiblyRowValue($formData['field_data_color'] ?? '')),
            'field_data_odor' => $this->plainTextField($this->firstScalarFromPossiblyRowValue($formData['field_data_odor'] ?? '')),
            'field_data_ph' => $this->plainTextField($this->firstScalarFromPossiblyRowValue($formData['field_data_ph'] ?? '')),
            'field_data_temperature' => $this->plainTextField($this->firstScalarFromPossiblyRowValue($formData['field_data_temperature'] ?? '')),
            'field_data_free_chlorine' => $this->plainTextField($this->firstScalarFromPossiblyRowValue($formData['field_data_free_chlorine'] ?? '')),
            'field_data_requirements' => $fieldDataRequirements,
            'field_data_requirement_checks' => self::fieldDataRequirementChecks($fieldDataRequirements),
        ];
    }

    /**
     * Row fields from instances are often list-shaped (index 0 = first sample).
     */
    private function firstScalarFromPossiblyRowValue(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if ($value === []) {
            return '';
        }

        if (array_is_list($value)) {
            return $value[0] ?? '';
        }

        return $value;
    }

    private function formatExtraSamplingEquipmentForPdf(mixed $value): string
    {
        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($value)) {
            return '';
        }

        $parts = [];
        foreach ($value as $row) {
            if (! is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            $id = trim((string) ($row['id'] ?? ''));
            if ($label === '' && $id === '') {
                continue;
            }

            $parts[] = $label !== '' && $id !== ''
                ? $label.': '.$id
                : ($label !== '' ? $label : $id);
        }

        return implode('; ', $parts);
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
        if (is_array($value)) {
            $value = $this->firstScalarFromPossiblyRowValue($value);
            if (is_array($value)) {
                return '';
            }
        }

        if ($value === null || is_bool($value)) {
            return '';
        }

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
        return $this->resolveSamplePointDisplayLabel($value);
    }

    private function resolveSamplePointDisplayLabel(string $value): string
    {
        $value = $this->decryptStoredValue($value);
        if ($value === '') {
            return '';
        }

        if (! Str::isUuid($value) && ! ctype_digit($value)) {
            return $value;
        }

        $point = SamplePoint::query()->with('unit')->find($value);
        if ($point !== null) {
            $pointName = trim((string) ($point->display_name ?? $point->name ?? ''));
            $unitName = trim((string) ($point->unit?->name ?? ''));

            if ($unitName !== '' && $pointName !== '') {
                return $unitName.', '.$pointName;
            }

            if ($pointName !== '') {
                return $pointName;
            }
        }

        $masterPoint = MasterSamplePoint::query()->find($value);
        if ($masterPoint !== null) {
            $name = trim((string) ($masterPoint->name ?? ''));

            if ($name !== '') {
                return $name;
            }
        }

        return '';
    }

    private function decryptStoredValue(mixed $value): string
    {
        $value = trim((string) $value);
        if ($value === '' || ! str_starts_with($value, 'eyJ')) {
            return $value;
        }

        try {
            return trim(Crypt::decryptString($value));
        } catch (\Throwable) {
            return $value;
        }
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return list<array{id: string, name: string}>
     */
    private function resolveFoodSampleTypeColumns(array $formData): array
    {
        $rows = $formData['sample_rows'] ?? [];
        if (! is_array($rows) || $rows === []) {
            return [];
        }

        $orderedIds = [];
        foreach (array_values($rows) as $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($this->extractReferenceTokens($row['sample_type_id'] ?? null) as $id) {
                if (! in_array($id, $orderedIds, true)) {
                    $orderedIds[] = $id;
                }
            }
        }

        if ($orderedIds === []) {
            return [];
        }

        $typesById = SampleType::query()
            ->whereIn('id', $orderedIds)
            ->get()
            ->keyBy(fn (SampleType $type): string => (string) $type->id);

        $columns = [];
        foreach ($orderedIds as $id) {
            $type = $typesById->get($id);
            $columns[] = [
                'id' => $id,
                'name' => trim((string) ($type?->name ?? '')),
            ];
        }

        return $columns;
    }

    /**
     * Tick map for food SAMPLE TYPE columns (selected sample_type_id values only).
     *
     * @param  array<string, mixed>  $row
     * @return array<string, bool>
     */
    private function resolveFoodSampleTypeTicks(array $row): array
    {
        $ticks = [];
        foreach ($this->extractReferenceTokens($row['sample_type_id'] ?? null) as $id) {
            $ticks[$id] = true;
        }

        return $ticks;
    }

    /**
     * @return list<string>
     */
    private function extractReferenceTokens(mixed $raw): array
    {
        return app(AnalysisReferenceLabelResolver::class)->extractTokens($raw);
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

        $unitName = CRMCompanyUnit::query()->whereKey($value)->value('name');

        return is_string($unitName) && trim($unitName) !== '' ? trim($unitName) : $value;
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

<?php

namespace Database\Seeders\Concerns;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormSection;
use App\SampleType;
use Illuminate\Support\Str;

trait BuildsSubmissionFormTrfSections
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createOrRefreshTrfSubmissionForm(array $attributes): SubmissionForm
    {
        $targetCode = (string) $attributes['document_code'];

        $form = SubmissionForm::query()->firstOrNew(['document_code' => $targetCode]);
        $hasStructure = $form->exists && $form->sections()->exists();
        $hasInstances = $form->exists && $form->instances()->exists();

        $form->fill(array_merge([
            'is_published' => true,
            'is_active' => true,
            'is_customer_portal_form' => true,
            'is_customer_request_form' => false,
            'form_type' => 'template',
            'placement_slot' => ['customer_portal', 'admin_portal', 'samples_receiving'],
        ], $attributes));
        $form->save();

        try {
            $stages = \App\SampleAnalysisStage::pluck('id')->toArray();
            $form->sampleAnalysisStages()->sync($stages);
        } catch (\Exception $e) {
            $this->command?->warn('Could not sync stages: '.$e->getMessage());
        }

        if ($hasStructure || $hasInstances) {
            return $form;
        }

        $form->sections()->each(function ($section): void {
            $section->elementHolders()->each(function ($holder): void {
                if ($holder->elements()->exists()) {
                    $holder->elements()->each(function ($element): void {
                        if (\App\Models\SubmissionFormInstanceValue::where('submission_form_element_id', $element->id)->exists()) {
                            return;
                        }
                        $element->delete();
                    });
                }
                if (! $holder->elements()->exists()) {
                    $holder->delete();
                }
            });
            if (! $section->elementHolders()->exists()) {
                $section->delete();
            }
        });

        return $form;
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: int, 4?: mixed, 5?: bool}>
     */
    protected function foodTrfRowFields(): array
    {
        return [
            ['textarea', 'Sample description', 'sample_description', 1],
            ['text', 'Sampling point / location', 'sampling_point', 2],
            ['number', 'Qty', 'sample_quantity', 3],
            ['text', 'Unit', 'sample_quantity_unit', 4],
            ['analysis_type_select', 'Sample Type', 'analysis_type_id', 5, null, true],
            ['analysis_elements_select', 'Parameters', 'parameters', 6],
            ['radio', 'State of sample', 'state_of_sample', 7, [
                ['value' => 'L', 'label' => 'L - Liquid'],
                ['value' => 'SS', 'label' => 'SS - Semi solid'],
                ['value' => 'S', 'label' => 'S - Solid'],
            ]],
            ['date', 'Production date', 'production_date', 8],
            ['date', 'Expiration date', 'expiration_date', 9],
            ['text', 'Batch number', 'batch_number', 10],
            ['radio', 'Test category', 'test_category', 11, [
                ['value' => 'microbiology', 'label' => 'Microbiology'],
                ['value' => 'chemistry', 'label' => 'Chemistry'],
            ]],
        ];
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: int, 4?: mixed, 5?: bool}>
     */
    protected function waterTrfRowFields(): array
    {
        return [
            ['textarea', 'Sample description', 'sample_description', 1],
            ['text', 'Location', 'location', 2],
            ['number', 'Qty', 'sample_quantity', 3],
            ['text', 'Unit', 'sample_quantity_unit', 4],
            ['select', 'Sampling point', 'sampling_point', 5, [
                ['value' => 'Tap', 'label' => 'Tap'],
                ['value' => 'Tank', 'label' => 'Tank'],
                ['value' => 'Pool', 'label' => 'Pool'],
                ['value' => 'Shower Head', 'label' => 'Shower Head'],
                ['value' => 'Others', 'label' => 'Others'],
            ]],
            ['text', 'Field data - pH', 'field_ph', 6],
            ['text', 'Field data - Appearance', 'field_appearance', 7],
            ['text', 'Field data - Residual chlorine', 'field_residual_chlorine', 8],
            ['text', 'Field data - Odor', 'field_odor', 9],
            ['text', 'Field data - Sample temp (°C)', 'field_sample_temp', 10],
            ['analysis_type_select', 'Sample Type', 'analysis_type_id', 11, null, true],
            ['analysis_elements_select', 'Parameters', 'parameters', 12],
            ['radio', 'Test requirements', 'test_requirements', 13, [
                ['value' => 'microbiology', 'label' => 'Microbiology'],
                ['value' => 'legionella', 'label' => 'Legionella'],
                ['value' => 'chemistry', 'label' => 'Chemistry'],
            ]],
        ];
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: int, 4?: mixed, 5?: bool}>
     */
    protected function wasteWaterTrfRowFields(): array
    {
        return [
            ['textarea', 'Sample description', 'sample_description', 1],
            ['text', 'Sampling point / location', 'sampling_point', 2],
            ['number', 'Qty', 'sample_quantity', 3],
            ['text', 'Unit', 'sample_quantity_unit', 4],
            ['sample_type_select', 'Type of sample', 'sample_type_id', 5, null, true],
            ['analysis_type_select', 'Matrix', 'analysis_type_id', 6, null, true],
            ['analysis_elements_select', 'Parameters', 'parameters', 7],
            ['text', 'Field data - Appearance', 'field_appearance', 8],
            ['text', 'Field data - Color', 'field_color', 9],
            ['text', 'Field data - Odor', 'field_odor', 10],
            ['text', 'Field data - pH', 'field_ph', 11],
            ['text', 'Field data - Temperature (°C)', 'field_sample_temp', 12],
            ['text', 'Field data - Free chlorine', 'field_free_chlorine', 13],
            ['select', 'Sample condition', 'sample_condition', 14, [
                ['value' => 'acceptable', 'label' => 'Acceptable'],
                ['value' => 'chilled', 'label' => 'Chilled'],
                ['value' => 'frozen', 'label' => 'Frozen'],
                ['value' => 'ambient', 'label' => 'Ambient'],
            ]],
            ['select', 'State of sample', 'state_of_sample', 15, [
                ['value' => 'L', 'label' => 'L - Liquid'],
                ['value' => 'SS', 'label' => 'SS - Semi solid'],
                ['value' => 'S', 'label' => 'S - Solid'],
            ]],
            ['checkbox', 'Test requirements', 'test_requirements', 16, [
                ['value' => 'microbiology', 'label' => 'Microbiology'],
                ['value' => 'chemistry', 'label' => 'Chemistry'],
            ]],
            ['camera_photo', 'Picture of sample(s)', 'picture_of_samples', 17],
        ];
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string, 3: int, 4?: mixed, 5?: bool}>  $rowFields
     */
    protected function patchSampleRowsSection(SubmissionForm $form, array $rowFields, string $sectionTitle = 'Test & sample information'): void
    {
        $section = $form->sections()
            ->where('section_type', 'rows_section')
            ->where('title', $sectionTitle)
            ->first();

        if ($section === null) {
            $this->createSampleRowsSection($form, 3, $sectionTitle, $rowFields);

            return;
        }

        $holder = $section->elementHolders()->where('holder_type', 'field')->first();
        if ($holder === null) {
            $holder = $section->elementHolders()->create([
                'id' => (string) Str::uuid7(),
                'holder_type' => 'field',
                'max_elements' => count($rowFields),
                'sort_order' => 1,
            ]);
        }

        $holder->update(['max_elements' => max((int) $holder->max_elements, count($rowFields))]);

        foreach ($rowFields as $field) {
            $this->upsertRowElement($holder, $field);
        }
    }

    /**
     * @param  list<string>  $names
     */
    protected function removeRowElementsByName(SubmissionForm $form, array $names, string $sectionTitle = 'Test & sample information'): void
    {
        if ($names === []) {
            return;
        }

        $section = $form->sections()
            ->where('section_type', 'rows_section')
            ->where('title', $sectionTitle)
            ->first();

        if ($section === null) {
            return;
        }

        foreach ($section->elementHolders as $holder) {
            $holder->elements()
                ->whereIn('name', $names)
                ->each(function (SubmissionFormElement $element): void {
                    $hasValues = \App\Models\SubmissionFormInstanceValue::query()
                        ->where('submission_form_element_id', $element->id)
                        ->exists();

                    if (! $hasValues) {
                        $element->delete();
                    }
                });
        }
    }

    /**
     * @param  array{0: string, 1: string, 2: string, 3: int, 4?: mixed, 5?: bool}  $field
     */
    protected function upsertRowElement(SubmissionFormElementHolder $holder, array $field): SubmissionFormElement
    {
        $element = $holder->elements()->where('name', $field[2])->first();

        $payload = [
            'element_type' => $field[0],
            'label' => $field[1],
            'name' => $field[2],
            'is_required' => (bool) ($field[5] ?? false),
            'sort_order' => $field[3],
            'is_readonly' => (bool) ($field[6] ?? false),
        ];

        if ($field[0] === 'analysis_type_select') {
            $payload['depends_on_type'] = 'sample_type_select';
            $payload['depends_on_field'] = 'sample_type_id';
        }

        if ($field[0] === 'analysis_elements_select') {
            $payload['depends_on_type'] = 'analysis_type_select';
            $payload['depends_on_field'] = 'analysis_type_id';
        }

        if (in_array($field[0], ['select', 'checkbox', 'radio'], true) && isset($field[4]) && is_array($field[4])) {
            $payload['options'] = $field[4];
        }

        if ($element === null) {
            return $holder->elements()->create(array_merge($payload, [
                'id' => (string) Str::uuid7(),
            ]));
        }

        $hasValues = \App\Models\SubmissionFormInstanceValue::query()
            ->where('submission_form_element_id', $element->id)
            ->exists();

        if ($hasValues) {
            $element->update([
                'label' => $payload['label'],
                'sort_order' => $payload['sort_order'],
                'is_required' => $payload['is_required'],
            ]);
        } else {
            $element->update($payload);
        }

        return $element->fresh();
    }

    /**
     * @param  array{0: string, 1: string, 2: string, 3: int, 4?: mixed}  $field
     * @param  array<int, array<string, string>>|null  $conditional
     */
    protected function upsertScalarElement(SubmissionFormElementHolder $holder, array $field, ?array $conditional = null): SubmissionFormElement
    {
        $element = $holder->elements()->where('name', $field[2])->first();

        $payload = [
            'element_type' => $field[0],
            'label' => $field[1],
            'name' => $field[2],
            'is_required' => false,
            'sort_order' => $field[3],
        ];

        if ($conditional !== null) {
            $payload['conditional_logic'] = $conditional;
        }

        if (in_array($field[0], ['select', 'checkbox', 'radio'], true) && isset($field[4]) && is_array($field[4])) {
            $payload['options'] = $field[4];
        }

        if ($element === null) {
            return $holder->elements()->create(array_merge($payload, [
                'id' => (string) Str::uuid7(),
            ]));
        }

        $hasValues = \App\Models\SubmissionFormInstanceValue::query()
            ->where('submission_form_element_id', $element->id)
            ->exists();

        if ($hasValues) {
            $element->update([
                'label' => $payload['label'],
                'sort_order' => $payload['sort_order'],
                'is_required' => $payload['is_required'],
            ]);
        } else {
            $element->update($payload);
        }

        return $element->fresh();
    }

    /**
     * @param  list<array{value: string, label: string}>  $extraApparatusOptions
     * @param  list<array{0: string, 1: string, 2: string, 3: int, 4?: list<array{value: string, label: string}>}>  $extraFields
     */
    protected function patchCollectionDataSection(
        SubmissionForm $form,
        bool $alwaysVisible = true,
        array $extraApparatusOptions = [],
        array $extraFields = [],
    ): void {
        $sections = $form->sections()
            ->where('section_type', 'regular')
            ->where('title', 'Sample collection data')
            ->get();

        if ($sections->isEmpty()) {
            $this->createCollectionDataSection($form, 2, $alwaysVisible, $extraApparatusOptions, $extraFields);

            return;
        }

        $conditional = $alwaysVisible ? null : [
            ['field' => 'request_for_sampling', 'operator' => 'equals', 'value' => '1'],
        ];

        $apparatusOptions = $this->baseSamplingApparatusOptions($extraApparatusOptions);

        $baseFields = [
            ['date', 'Sampling date', 'sampling_date', 1],
            ['text', 'Sampling time', 'sampling_time', 2],
            ['text', 'Sampling location', 'sampling_location', 3],
            ['checkbox', 'Sampling apparatus', 'sampling_apparatus', 4, $apparatusOptions],
            ['radio', 'Method of sampling', 'method_of_sampling', 5, [
                ['value' => 'apha', 'label' => 'APHA'],
                ['value' => 'saso', 'label' => 'SASO'],
                ['value' => 'astm', 'label' => 'ASTM'],
                ['value' => 'others', 'label' => 'Others'],
                ['value' => 'us_fda', 'label' => 'US FDA'],
                ['value' => 'ccfra', 'label' => 'CCFRA'],
                ['value' => 'dm', 'label' => 'DM'],
                ['value' => 'sop', 'label' => 'SOP'],
            ]],
            ['radio', 'Reason of collection', 'reason_of_collection', 6, [
                ['value' => 'contract', 'label' => 'Contract'],
                ['value' => 'non_contract', 'label' => 'Non-contract'],
                ['value' => 'haccp', 'label' => 'HACCP requirement'],
                ['value' => 'disputed', 'label' => 'Disputed/Audit'],
            ]],
            ['checkbox', 'Transport condition', 'transport_condition', 7, [
                ['value' => 'chiller', 'label' => 'Chiller vehicle'],
                ['value' => 'frozen', 'label' => 'Frozen'],
                ['value' => 'ambient', 'label' => 'Ambient'],
            ]],
            ['date', 'Date received', 'date_received', 8],
            ['text', 'Packaging', 'packaging', 9],
            ['text', 'Sample weight', 'sample_weight', 10],
            ['text', 'Sample information', 'sample_information', 11],
            ['text', 'Ship / vessel', 'ship_name', 12],
            ['text', 'Port of loading', 'port_of_loading', 13],
            ['text', 'Port of discharge', 'port_of_discharge', 14],
            ['text', 'Seal', 'seal_number', 15],
        ];

        foreach ($sections as $section) {
            $holder = $section->elementHolders()->where('holder_type', 'field')->first();
            if ($holder === null) {
                $holder = $section->elementHolders()->create([
                    'id' => (string) Str::uuid7(),
                    'holder_type' => 'field',
                    'max_elements' => 30,
                    'sort_order' => 1,
                ]);
            }

            $holder->update(['max_elements' => max((int) $holder->max_elements, 30)]);

            foreach (array_merge($baseFields, $extraFields) as $field) {
                $this->upsertScalarElement($holder, $field, $conditional);
            }
        }
    }

    protected function removeTrfStorageElements(SubmissionForm $form): void
    {
        SubmissionFormElement::query()
            ->where('name', 'trf_sample_rows')
            ->whereHas('holder.section', fn ($query) => $query->where('submission_form_id', $form->id))
            ->each(function (SubmissionFormElement $element): void {
                if (! \App\Models\SubmissionFormInstanceValue::query()->where('submission_form_element_id', $element->id)->exists()) {
                    $element->delete();
                }
            });

        $form->sections()
            ->where('title', 'TRF storage')
            ->each(function (SubmissionFormSection $section): void {
                if (! $section->elementHolders()->whereHas('elements')->exists()) {
                    $section->delete();
                }
            });
    }

    /**
     * @param  list<string>  $codes
     */
    protected function syncSampleTypesByCodes(SubmissionForm $form, array $codes): void
    {
        try {
            $ids = SampleType::query()
                ->whereIn('code', $codes)
                ->pluck('id')
                ->unique()
                ->values()
                ->all();

            if ($ids === []) {
                $this->command?->warn('No sample types matched for '.$form->name.' (codes: '.implode(', ', $codes).').');

                return;
            }

            $form->sampleTypes()->sync($ids);
            $this->command?->info('Linked '.count($ids).' sample type(s) to '.$form->name.'.');
        } catch (\Exception $e) {
            $this->command?->warn('Could not sync sample types: '.$e->getMessage());
        }
    }

    /**
     * @param  list<string>  $namePatterns
     */
    protected function syncSampleTypesByNamePatterns(SubmissionForm $form, array $namePatterns): void
    {
        try {
            $query = SampleType::query();
            $query->where(function ($builder) use ($namePatterns): void {
                foreach ($namePatterns as $pattern) {
                    $builder->orWhere('name', 'ilike', '%'.$pattern.'%')
                        ->orWhere('code', 'ilike', '%'.$pattern.'%');
                }
            });

            $ids = $query->pluck('id')->unique()->values()->all();
            if ($ids === []) {
                $this->command?->warn('No sample types matched for '.$form->name.' (patterns: '.implode(', ', $namePatterns).').');

                return;
            }

            $form->sampleTypes()->sync($ids);
            $this->command?->info('Linked '.count($ids).' sample type(s) to '.$form->name.'.');
        } catch (\Exception $e) {
            $this->command?->warn('Could not sync sample types: '.$e->getMessage());
        }
    }

    protected function createCustomerDetailsSection(SubmissionForm $form, int $sortOrder = 1): void
    {
        $section = $form->sections()->create([
            'title' => 'Customer details',
            'description' => 'Customer information for this test request.',
            'section_type' => 'regular',
            'sort_order' => $sortOrder,
        ]);

        $holder = $section->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 12,
            'sort_order' => 1,
        ]);

        foreach ([
            ['text', 'Name', 'customer_name', 1, true],
            ['textarea', 'Address', 'customer_address', 2, true],
            ['text', 'Tel / Fax no.', 'customer_phone', 3, false],
            ['text', 'Mobile number', 'mobile_number', 4, false],
            ['client_contact_select', 'Contact person', 'contact_person', 5, false],
            ['text', 'CNPJ / Tax ID', 'customer_tax_id', 6, false],
            ['text', 'Email', 'customer_email', 7, false],
            ['text', 'CRM contact ID', 'crm_contact_id', 8, false],
        ] as [$type, $label, $name, $order, $readonly]) {
            $holder->elements()->create([
                'element_type' => $type,
                'label' => $label,
                'name' => $name,
                'is_readonly' => $readonly,
                'is_required' => false,
                'sort_order' => $order,
            ]);
        }
    }

    protected function patchCustomerDetailsSection(SubmissionForm $form): void
    {
        $section = $form->sections()
            ->where('section_type', 'regular')
            ->where('title', 'Customer details')
            ->first();

        if ($section === null) {
            $this->createCustomerDetailsSection($form, 1);

            return;
        }

        $holder = $section->elementHolders()->where('holder_type', 'field')->first();
        if ($holder === null) {
            $holder = $section->elementHolders()->create([
                'id' => (string) Str::uuid7(),
                'holder_type' => 'field',
                'max_elements' => 12,
                'sort_order' => 1,
            ]);
        }

        $holder->update(['max_elements' => max((int) $holder->max_elements, 12)]);

        foreach ([
            ['text', 'Name', 'customer_name', 1, true],
            ['textarea', 'Address', 'customer_address', 2, true],
            ['text', 'Tel / Fax no.', 'customer_phone', 3, false],
            ['text', 'Mobile number', 'mobile_number', 4, false],
            ['client_contact_select', 'Contact person', 'contact_person', 5, false],
            ['text', 'CNPJ / Tax ID', 'customer_tax_id', 6, false],
            ['text', 'Email', 'customer_email', 7, false],
            ['text', 'CRM contact ID', 'crm_contact_id', 8, false],
        ] as [$type, $label, $name, $order, $readonly]) {
            $element = $holder->elements()->where('name', $name)->first();

            $payload = [
                'element_type' => $type,
                'label' => $label,
                'name' => $name,
                'is_readonly' => $readonly,
                'is_required' => false,
                'sort_order' => $order,
            ];

            if ($element === null) {
                $holder->elements()->create(array_merge($payload, [
                    'id' => (string) Str::uuid7(),
                ]));

                continue;
            }

            $hasValues = \App\Models\SubmissionFormInstanceValue::query()
                ->where('submission_form_element_id', $element->id)
                ->exists();

            if ($hasValues) {
                $element->update([
                    'label' => $payload['label'],
                    'sort_order' => $payload['sort_order'],
                    'is_required' => $payload['is_required'],
                ]);
            } else {
                $element->update($payload);
            }
        }
    }

    /**
     * @param  list<array{value: string, label: string}>  $extraApparatusOptions
     * @param  list<array{0: string, 1: string, 2: string, 3: int, 4?: list<array{value: string, label: string}>}>  $extraFields
     */
    protected function createCollectionDataSection(
        SubmissionForm $form,
        int $sortOrder,
        bool $alwaysVisible = true,
        array $extraApparatusOptions = [],
        array $extraFields = [],
    ): void {
        $section = $form->sections()->create([
            'title' => 'Sample collection data',
            'description' => 'Sampling details, apparatus, method, and transport.',
            'section_type' => 'regular',
            'sort_order' => $sortOrder,
        ]);

        $holder = $section->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 30,
            'sort_order' => 1,
        ]);

        $conditional = $alwaysVisible ? null : [
            ['field' => 'request_for_sampling', 'operator' => 'equals', 'value' => '1'],
        ];

        $apparatusOptions = $this->baseSamplingApparatusOptions($extraApparatusOptions);

        $baseFields = [
            ['date', 'Sampling date', 'sampling_date', 1],
            ['text', 'Sampling time', 'sampling_time', 2],
            ['text', 'Sampling location', 'sampling_location', 3],
            ['checkbox', 'Sampling apparatus', 'sampling_apparatus', 4, $apparatusOptions],
            ['radio', 'Method of sampling', 'method_of_sampling', 5, [
                ['value' => 'apha', 'label' => 'APHA'],
                ['value' => 'saso', 'label' => 'SASO'],
                ['value' => 'astm', 'label' => 'ASTM'],
                ['value' => 'others', 'label' => 'Others'],
                ['value' => 'us_fda', 'label' => 'US FDA'],
                ['value' => 'ccfra', 'label' => 'CCFRA'],
                ['value' => 'dm', 'label' => 'DM'],
                ['value' => 'sop', 'label' => 'SOP'],
            ]],
            ['radio', 'Reason of collection', 'reason_of_collection', 6, [
                ['value' => 'contract', 'label' => 'Contract'],
                ['value' => 'non_contract', 'label' => 'Non-contract'],
                ['value' => 'haccp', 'label' => 'HACCP requirement'],
                ['value' => 'disputed', 'label' => 'Disputed/Audit'],
            ]],
            ['checkbox', 'Transport condition', 'transport_condition', 7, [
                ['value' => 'chiller', 'label' => 'Chiller vehicle'],
                ['value' => 'frozen', 'label' => 'Frozen'],
                ['value' => 'ambient', 'label' => 'Ambient'],
            ]],
            ['date', 'Date received', 'date_received', 8],
            ['text', 'Packaging', 'packaging', 9],
            ['text', 'Sample weight', 'sample_weight', 10],
            ['text', 'Sample information', 'sample_information', 11],
            ['text', 'Ship / vessel', 'ship_name', 12],
            ['text', 'Port of loading', 'port_of_loading', 13],
            ['text', 'Port of discharge', 'port_of_discharge', 14],
            ['text', 'Seal', 'seal_number', 15],
        ];

        foreach (array_merge($baseFields, $extraFields) as $field) {
            $payload = [
                'element_type' => $field[0],
                'label' => $field[1],
                'name' => $field[2],
                'is_required' => false,
                'sort_order' => $field[3],
            ];

            if ($conditional !== null) {
                $payload['conditional_logic'] = $conditional;
            }

            if (in_array($field[0], ['select', 'checkbox', 'radio'], true) && isset($field[4])) {
                $payload['options'] = $field[4];
            }

            $holder->elements()->create($payload);
        }
    }

    /**
     * @param  list<array{value: string, label: string}>  $extra
     * @return list<array{value: string, label: string}>
     */
    protected function baseSamplingApparatusOptions(array $extra = []): array
    {
        return array_merge([
            ['value' => 'sterile_bag', 'label' => 'Sterile bag'],
            ['value' => 'sterile_bottle', 'label' => 'Sterile bottle'],
            ['value' => 'sterile_swab', 'label' => 'Sterile swab'],
            ['value' => 'grabber', 'label' => 'Grabber'],
            ['value' => 'others', 'label' => 'Others'],
            ['value' => 'thermometer_ams_c_ins_116', 'label' => 'Thermometer ID AMS/C/INS/116'],
        ], $extra);
    }

    protected function createSubmitAndSignSection(SubmissionForm $form, int $sortOrder): void
    {
        $section = $form->sections()->create([
            'title' => 'Submit & sign',
            'description' => 'Statement of conformity, sampling officer, and customer representative.',
            'section_type' => 'regular',
            'sort_order' => $sortOrder,
        ]);

        $holder = $section->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 10,
            'sort_order' => 1,
        ]);

        $fields = [
            ['radio', 'Statement of conformity required in reports', 'statement_of_conformity', 1, [
                ['value' => 'yes', 'label' => 'Yes'],
                ['value' => 'no', 'label' => 'No'],
                ['value' => 'as_per_contract', 'label' => 'As per contract'],
                ['value' => 'as_per_email', 'label' => 'As per email'],
            ]],
            ['text', 'Sampled by (name and employee ID)', 'sampled_by', 2],
            ['text', 'Customer representative name', 'customer_representative_name', 3],
            ['text', 'Customer representative contact number', 'customer_representative_contact', 4],
            ['signature', 'Customer representative signature', 'customer_representative_signature', 5],
            ['textarea', 'Remarks', 'remarks', 6],
        ];

        foreach ($fields as $field) {
            $payload = [
                'element_type' => $field[0],
                'label' => $field[1],
                'name' => $field[2],
                'is_required' => false,
                'sort_order' => $field[3],
            ];

            if (in_array($field[0], ['select', 'checkbox', 'radio'], true) && isset($field[4])) {
                $payload['options'] = $field[4];
            }

            $holder->elements()->create($payload);
        }
    }

    /**
     * @param  list<array{0: string, 1: string, 2: string, 3: int, 4?: mixed, 5?: bool}>  $rowFields
     */
    protected function createSampleRowsSection(
        SubmissionForm $form,
        int $sortOrder,
        string $title,
        array $rowFields,
    ): SubmissionFormSection {
        $section = $form->sections()->create([
            'title' => $title,
            'description' => 'Sample lines with test requirements.',
            'section_type' => 'rows_section',
            'sort_order' => $sortOrder,
        ]);

        $holder = $section->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => count($rowFields),
            'sort_order' => 1,
        ]);

        foreach ($rowFields as $field) {
            $payload = [
                'element_type' => $field[0],
                'label' => $field[1],
                'name' => $field[2],
                'is_required' => (bool) ($field[5] ?? false),
                'sort_order' => $field[3],
                'is_readonly' => (bool) ($field[6] ?? false),
            ];

            if ($field[0] === 'analysis_type_select') {
                $payload['depends_on_type'] = 'sample_type_select';
                $payload['depends_on_field'] = 'sample_type_id';
            }

            if ($field[0] === 'analysis_elements_select') {
                $payload['depends_on_type'] = 'analysis_type_select';
                $payload['depends_on_field'] = 'analysis_type_id';
            }

            if (in_array($field[0], ['select', 'checkbox', 'radio'], true) && isset($field[4]) && is_array($field[4])) {
                $payload['options'] = $field[4];
            }

            $holder->elements()->create($payload);
        }

        return $section;
    }

    protected function clearCaches(): void
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
        } catch (\Exception $e) {
            $this->command?->warn('Failed to clear cache: '.$e->getMessage());
        }
    }
}

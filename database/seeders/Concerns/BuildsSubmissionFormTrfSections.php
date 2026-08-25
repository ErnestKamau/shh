<?php

namespace Database\Seeders\Concerns;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormInstanceValue;
use App\Models\SubmissionFormSection;
use App\SampleType;
use App\SampleTypeCategory;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
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
            // Pivot requires an explicit id (PostgreSQL has no default on some DBs).
            $syncData = [];
            foreach (\App\SampleAnalysisStage::query()->pluck('id') as $stageId) {
                $syncData[(string) $stageId] = ['id' => (string) Str::uuid7()];
            }
            $form->sampleAnalysisStages()->sync($syncData);
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
            ['text', 'Sampling Point', 'sampling_point_manual', 2],
            ['number', 'Qty', 'sample_quantity', 3],
            ['text', 'Unit', 'sample_quantity_unit', 4],
            ['radio', 'State of sample', 'state_of_sample', 5, [
                ['value' => 'L', 'label' => 'L - Liquid'],
                ['value' => 'SS', 'label' => 'SS - Semi solid'],
                ['value' => 'S', 'label' => 'S - Solid'],
            ]],
            ['date', 'Production date', 'production_date', 6],
            ['date', 'Expiration date', 'expiration_date', 7],
            ['text', 'Batch number', 'batch_number', 8],
            ['sample_type_select', 'Sample type', 'sample_type_id', 9, null, true],
            ['analysis_type_select', 'Analysis Type', 'analysis_type_id', 10, null, true],
            ['checkbox', 'Test category', 'test_category', 11, [
                ['value' => 'chemistry', 'label' => 'Chemistry'],
                ['value' => 'microbiology', 'label' => 'Microbiology'],
            ]],
            ['checkbox', 'Sample condition', 'sample_condition', 12, [
                ['value' => 'acceptable', 'label' => 'Acceptable'],
                ['value' => 'chilled', 'label' => 'Chilled'],
                ['value' => 'frozen', 'label' => 'Frozen'],
                ['value' => 'ambient', 'label' => 'Ambient'],
            ]],
            ['text', 'Sample Temp (°C)', 'sample_temp', 13],
            ['analysis_elements_select', 'Tests', 'parameters', 14],
        ];
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: int, 4?: mixed, 5?: bool}>
     */
    protected function waterTrfRowFields(): array
    {
        return [
            ['textarea', 'Sample description', 'sample_description', 1],
            ['text', 'Sampling Point', 'sampling_point_manual', 2],
            ['number', 'Qty', 'sample_quantity', 3],
            ['text', 'Unit', 'sample_quantity_unit', 4],
            ['text', 'Field data - pH', 'field_ph', 5],
            ['text', 'Field data - Appearance', 'field_appearance', 6],
            ['text', 'Field data - Residual chlorine', 'field_residual_chlorine', 7],
            ['text', 'Field data - Odor', 'field_odor', 8],
            ['text', 'Field data - Sample temp (°C)', 'field_sample_temp', 9],
            ['checkbox', 'Test requirements', 'test_requirements', 10, [
                ['value' => 'microbiology', 'label' => 'Microbiology'],
                ['value' => 'legionella', 'label' => 'Legionella'],
                ['value' => 'chemistry', 'label' => 'Chemical Analysis'],
            ]],
            ['sample_type_select', 'Sample type', 'sample_type_id', 11, null, true],
            ['analysis_type_select', 'Analysis Type', 'analysis_type_id', 12, null, true],
            ['analysis_elements_select', 'Tests', 'parameters', 13],
        ];
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: int, 4?: mixed, 5?: bool}>
     */
    protected function wasteWaterTrfRowFields(): array
    {
        return [
            ['textarea', 'Sample description', 'sample_description', 1],
            ['text', 'Sampling Point', 'sampling_point_manual', 2],
            ['number', 'Qty', 'sample_quantity', 3],
            ['text', 'Unit', 'sample_quantity_unit', 4],
            ['sample_type_select', 'Sample type', 'sample_type_id', 5, null, true],
            ['analysis_type_select', 'Analysis Type', 'analysis_type_id', 6, null, true],
            ['analysis_elements_select', 'Tests', 'parameters', 7],
            ['select', 'Sample condition', 'sample_condition', 8, [
                ['value' => 'acceptable', 'label' => 'Acceptable'],
                ['value' => 'chilled', 'label' => 'Chilled'],
                ['value' => 'frozen', 'label' => 'Frozen'],
                ['value' => 'ambient', 'label' => 'Ambient'],
            ]],
            ['select', 'State of sample', 'state_of_sample', 9, [
                ['value' => 'L', 'label' => 'L - Liquid'],
                ['value' => 'SS', 'label' => 'SS - Semi solid'],
                ['value' => 'S', 'label' => 'S - Solid'],
            ]],
            ['checkbox', 'Test requirements', 'test_requirements', 10, [
                ['value' => 'microbiology', 'label' => 'Microbiology'],
                ['value' => 'chemistry', 'label' => 'Chemistry'],
            ]],
            ['camera_photo', 'Picture of sample(s)', 'picture_of_samples', 11],
        ];
    }

    /**
     * AmSpec LWS-036 collection fields (section 2).
     *
     * @return list<array{0: string, 1: string, 2: string, 3: int, 4?: list<array{value: string, label: string}>}>
     */
    protected function wasteWaterCollectionDataFields(): array
    {
        return [
            ['date', 'Sampling date', 'sampling_date', 1],
            ['time', 'Sampling time', 'sampling_time', 2],
            ['customer_sample_point_select', 'Sampling location', 'sampling_location', 3],
            ['rich_text', 'Sample & sampling point description', 'sample_sampling_point_description', 4],
            ['checkbox', 'Sampling apparatus', 'sampling_apparatus', 5, [
                ['value' => 'sterile_bottle', 'label' => 'Sterile bottle'],
                ['value' => 'bottle_catcher', 'label' => 'Bottle catcher'],
                ['value' => 'others', 'label' => 'Others'],
            ]],
            ['text', 'Thermometer ID', 'thermometer_id', 6],
            ['text', 'pH meter ID', 'ph_meter_id', 7],
            ['text', 'Chlorine meter ID', 'chlorine_meter_id', 8],
            ['text', 'Sampling apparatus — others', 'sampling_apparatus_others', 9],
            ['textarea', 'Extra sampling equipment', 'extra_sampling_equipment', 10],
            ['checkbox', 'Method of sampling', 'method_of_sampling', 11, [
                ['value' => 'apha', 'label' => 'APHA'],
                ['value' => 'us_fda', 'label' => 'US FDA'],
                ['value' => 'epa', 'label' => 'EPA'],
                ['value' => 'ccfra', 'label' => 'CCFRA'],
                ['value' => 'dm', 'label' => 'DM'],
                ['value' => 'sop', 'label' => 'SOP'],
                ['value' => 'others', 'label' => 'Others'],
            ]],
            ['checkbox', 'Reason of collection', 'reason_of_collection', 12, [
                ['value' => 'contract', 'label' => 'Contract'],
                ['value' => 'non_contract', 'label' => 'Non-contract'],
                ['value' => 'dm_requirement', 'label' => 'DM requirement'],
                ['value' => 'disputed', 'label' => 'Disputed/Audit'],
            ]],
            ['checkbox', 'Sampling technique', 'sampling_technique', 13, [
                ['value' => 'grab', 'label' => 'Grab'],
                ['value' => 'composite', 'label' => 'Composite'],
                ['value' => 'other', 'label' => 'Other'],
            ]],
            ['checkbox', 'Sampling source', 'sampling_source', 14, [
                ['value' => 'tank', 'label' => 'Tank'],
                ['value' => 'holding_tank', 'label' => 'Holding tank'],
                ['value' => 'ind_domestic_effluent', 'label' => 'Ind./Domestic effluent'],
                ['value' => 'pool_water', 'label' => 'Pool water'],
                ['value' => 'discharge_to_marine', 'label' => 'Discharge to marine'],
                ['value' => 'ground_water', 'label' => 'Ground water'],
                ['value' => 'stp', 'label' => 'STP'],
                ['value' => 'municipal_tap_water', 'label' => 'Municipal tap water'],
            ]],
            ['checkbox', 'Transport condition', 'transport_condition', 15, [
                ['value' => 'chiller', 'label' => 'Chiller vehicle'],
                ['value' => 'frozen', 'label' => 'Frozen'],
                ['value' => 'ambient', 'label' => 'Ambient'],
            ]],
            ['checkbox', 'Sample types', 'sample_types_ww', 16, [
                ['value' => 'liquid', 'label' => 'Liquid'],
                ['value' => 'semi_solid', 'label' => 'Semi solid'],
                ['value' => 'sludge', 'label' => 'Sludge'],
                ['value' => 'marine_sediment', 'label' => 'Marine sediment'],
            ]],
            ['checkbox', 'Test category', 'field_data_requirements', 17, [
                ['value' => 'microbiology', 'label' => 'Microbiology'],
                ['value' => 'chemistry', 'label' => 'Chemistry'],
            ]],
            ['text', 'Field data — Quantity', 'field_data_quantity', 18],
            ['text', 'Field data — Appearance', 'field_data_appearance', 19],
            ['text', 'Field data — Color', 'field_data_color', 20],
            ['text', 'Field data — Odor', 'field_data_odor', 21],
            ['text', 'Field data — pH', 'field_data_ph', 22],
            ['text', 'Field data — Temperature (°C)', 'field_data_temperature', 23],
            ['text', 'Field data — Free chlorine', 'field_data_free_chlorine', 24],
            ['date', 'Date received', 'date_received', 25],
        ];
    }

    /**
     * @return list<string>
     */
    protected function obsoleteWasteWaterCollectionFieldNames(): array
    {
        return [
            'sample_physical_state',
        ];
    }

    protected function createWasteWaterCollectionDataSection(SubmissionForm $form, int $sortOrder, bool $alwaysVisible = true): void
    {
        if ($form->sections()->where('title', 'Sample collection data')->exists()) {
            return;
        }

        $section = $form->sections()->create([
            'title' => 'Sample collection data',
            'description' => 'Sampling details, apparatus, method, transport, and field data.',
            'section_type' => 'regular',
            'sort_order' => $sortOrder,
        ]);

        $holder = $section->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 40,
            'sort_order' => 1,
        ]);

        $conditional = $alwaysVisible ? null : [
            ['field' => 'request_for_sampling', 'operator' => 'equals', 'value' => '1'],
        ];

        foreach ($this->wasteWaterCollectionDataFields() as $field) {
            $this->upsertScalarElement($holder, $field, $conditional);
        }
    }

    protected function patchWasteWaterCollectionDataSection(SubmissionForm $form, bool $alwaysVisible = true): void
    {
        $sections = $form->sections()
            ->where('section_type', 'regular')
            ->where('title', 'Sample collection data')
            ->get();

        if ($sections->isEmpty()) {
            $this->createWasteWaterCollectionDataSection($form, 2, $alwaysVisible);
            $this->removeMiscellaneousFieldsFromCollectionSection($form);

            return;
        }

        $conditional = $alwaysVisible ? null : [
            ['field' => 'request_for_sampling', 'operator' => 'equals', 'value' => '1'],
        ];

        foreach ($sections as $section) {
            $holder = $section->elementHolders()->where('holder_type', 'field')->first();
            if ($holder === null) {
                $holder = $section->elementHolders()->create([
                    'id' => (string) Str::uuid7(),
                    'holder_type' => 'field',
                    'max_elements' => 40,
                    'sort_order' => 1,
                ]);
            }

            $holder->update(['max_elements' => max((int) $holder->max_elements, 40)]);

            foreach ($this->wasteWaterCollectionDataFields() as $field) {
                $this->upsertScalarElement($holder, $field, $conditional);
            }

            $this->removeCollectionElementsByName($form, $this->obsoleteWasteWaterCollectionFieldNames());
            $this->removeMiscellaneousFieldsFromCollectionSection($form);
        }
    }

    /**
     * @param  list<string>  $names
     */
    protected function removeCollectionElementsByName(SubmissionForm $form, array $names): void
    {
        if ($names === []) {
            return;
        }

        $form->sections()
            ->where('section_type', 'regular')
            ->where('title', 'Sample collection data')
            ->each(function (SubmissionFormSection $section) use ($names): void {
                foreach ($section->elementHolders as $holder) {
                    $holder->elements()
                        ->whereIn('name', $names)
                        ->each(function (SubmissionFormElement $element): void {
                            $hasValues = \App\Models\SubmissionFormInstanceValue::query()
                                ->where('submission_form_element_id', $element->id)
                                ->exists();

                            if (! $hasValues) {
                                $element->delete();

                                return;
                            }

                            $element->update([
                                'is_hidden' => true,
                                'is_required' => false,
                            ]);
                        });
                }
            });
    }

    protected function patchWasteWaterTrfSections(SubmissionForm $form): void
    {
        $this->patchCustomerDetailsSection($form);
        $this->patchWasteWaterCollectionDataSection($form, true);
        $this->patchSampleRowsSection($form, $this->wasteWaterTrfRowFields());
        $this->removeRowElementsByName($form, [
            'field_appearance',
            'field_color',
            'field_odor',
            'field_ph',
            'field_sample_temp',
            'field_free_chlorine',
        ]);
        $this->patchMiscellaneousSection($form);
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: int, 4?: mixed, 5?: bool}>
     */
    protected function swabTrfRowFields(): array
    {
        return [
            ['textarea', 'Sample description', 'sample_description', 1],
            ['text', 'Sampling Point', 'sampling_point_manual', 2],
            ['number', 'Qty', 'sample_quantity', 3],
            ['text', 'Unit', 'sample_quantity_unit', 4],
            ['sample_type_select', 'Sample type', 'sample_type_id', 5, null, true],
            ['analysis_type_select', 'Analysis Type', 'analysis_type_id', 6, null, true],
            ['analysis_elements_select', 'Tests', 'parameters', 7],
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

                        return;
                    }

                    // Keep rows with historic values, but hide obsolete Step 3 controls.
                    $element->update([
                        'is_hidden' => true,
                        'is_required' => false,
                    ]);
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
            'is_hidden' => false,
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
            // Keep historic values, but allow safe control upgrades (e.g. select → CRM dropdown)
            // and label/order/options sync from the canonical TRF field definitions.
            $safeUpdate = [
                'label' => $payload['label'],
                'element_type' => $payload['element_type'],
                'sort_order' => $payload['sort_order'],
                'is_required' => $payload['is_required'],
                'is_hidden' => false,
            ];
            if (array_key_exists('options', $payload)) {
                $safeUpdate['options'] = $payload['options'];
            }
            $element->update($safeUpdate);
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
            $update = [
                'label' => $payload['label'],
                'sort_order' => $payload['sort_order'],
                'is_required' => $payload['is_required'],
            ];

            if (isset($payload['options'])) {
                $update['options'] = $payload['options'];
            }

            if (in_array($field[2], [
                'thermometer_id',
                'reason_of_collection',
                'sampling_technique',
                'sampling_source',
                'sample_types_ww',
                'field_data_requirements',
                'sample_sampling_point_description',
                'sampling_apparatus',
                'method_of_sampling',
            ], true)) {
                $update['element_type'] = $payload['element_type'];
            }

            $element->update($update);
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
            $this->removeMiscellaneousFieldsFromCollectionSection($form);

            return;
        }

        $conditional = $alwaysVisible ? null : [
            ['field' => 'request_for_sampling', 'operator' => 'equals', 'value' => '1'],
        ];

        $apparatusOptions = $this->baseSamplingApparatusOptions($extraApparatusOptions);

        $baseFields = $this->standardCollectionDataFields($apparatusOptions);

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

            $this->removeMiscellaneousFieldsFromCollectionSection($form);
        }
    }

    /**
     * @return list<string>
     */
    protected function miscellaneousTrfFieldNames(): array
    {
        return SubmissionFormSchemaHelper::miscellaneousTrfFieldNames();
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: int}>
     */
    protected function miscellaneousTrfFields(): array
    {
        return [
            ['text', 'Packaging', 'packaging', 1],
            ['text', 'Sample weight', 'sample_weight', 2],
            ['text', 'Sample information', 'sample_information', 3],
            ['text', 'Ship / vessel', 'ship_name', 4],
            ['text', 'Port of loading', 'port_of_loading', 5],
            ['text', 'Port of discharge', 'port_of_discharge', 6],
            ['text', 'Seal', 'seal_number', 7],
        ];
    }

    protected function createMiscellaneousSection(SubmissionForm $form, int $sortOrder = 4): void
    {
        if ($form->sections()->where('title', 'Miscellaneous')->exists()) {
            return;
        }

        $section = $form->sections()->create([
            'title' => 'Miscellaneous',
            'description' => 'Packaging, shipping, and other sample handling details.',
            'section_type' => 'regular',
            'sort_order' => $sortOrder,
        ]);

        $holder = $section->elementHolders()->create([
            'holder_type' => 'field',
            'max_elements' => 12,
            'sort_order' => 1,
        ]);

        foreach ($this->miscellaneousTrfFields() as $field) {
            $this->upsertScalarElement($holder, $field);
        }
    }

    protected function patchMiscellaneousSection(SubmissionForm $form): void
    {
        $rowsSection = $form->sections()
            ->where('section_type', 'rows_section')
            ->where('title', 'Test & sample information')
            ->first();

        $sortOrder = $rowsSection !== null
            ? ((int) $rowsSection->sort_order + 1)
            : 4;

        $section = $form->sections()
            ->where('section_type', 'regular')
            ->where('title', 'Miscellaneous')
            ->first();

        if ($section === null) {
            $this->createMiscellaneousSection($form, $sortOrder);

            $section = $form->sections()
                ->where('section_type', 'regular')
                ->where('title', 'Miscellaneous')
                ->first();
        }

        if ($section === null) {
            return;
        }

        $section->update(['sort_order' => $sortOrder]);

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

        $collectionSection = $form->sections()
            ->where('section_type', 'regular')
            ->where('title', 'Sample collection data')
            ->first();

        foreach ($this->miscellaneousTrfFields() as $field) {
            $name = $field[2];
            $existing = $holder->elements()->where('name', $name)->first();

            if ($existing === null && $collectionSection !== null) {
                $collectionHolder = $collectionSection->elementHolders()->where('holder_type', 'field')->first();
                $moved = $collectionHolder?->elements()->where('name', $name)->first();
                if ($moved !== null) {
                    $moved->update([
                        'submission_form_element_holder_id' => $holder->id,
                        'sort_order' => $field[3],
                    ]);

                    continue;
                }
            }

            $this->upsertScalarElement($holder, $field);
        }

        $form->sections()
            ->whereIn('title', ['Submit & sign', 'Submit and sign'])
            ->update(['sort_order' => $sortOrder + 1]);

        $this->removeMiscellaneousFieldsFromCollectionSection($form);
    }

    protected function removeMiscellaneousFieldsFromCollectionSection(SubmissionForm $form): void
    {
        $miscHolder = $form->sections()
            ->where('section_type', 'regular')
            ->where('title', 'Miscellaneous')
            ->first()
            ?->elementHolders()
            ->where('holder_type', 'field')
            ->first();

        $form->sections()
            ->where('section_type', 'regular')
            ->where('title', 'Sample collection data')
            ->each(function (SubmissionFormSection $collectionSection) use ($miscHolder): void {
                $collectionHolder = $collectionSection->elementHolders()->where('holder_type', 'field')->first();
                if ($collectionHolder === null) {
                    return;
                }

                $collectionHolder->elements()
                    ->whereIn('name', $this->miscellaneousTrfFieldNames())
                    ->each(function (SubmissionFormElement $element) use ($miscHolder): void {
                        if ($miscHolder !== null) {
                            $target = $miscHolder->elements()->where('name', $element->name)->first();
                            if ($target !== null && $target->id !== $element->id) {
                                $this->migrateSubmissionFormElementValues((string) $element->id, (string) $target->id);
                            }
                        }

                        $element->delete();
                    });
            });
    }

    protected function migrateSubmissionFormElementValues(string $fromElementId, string $toElementId): void
    {
        SubmissionFormInstanceValue::query()
            ->where('submission_form_element_id', $fromElementId)
            ->each(function (SubmissionFormInstanceValue $value) use ($toElementId): void {
                $duplicateQuery = SubmissionFormInstanceValue::query()
                    ->where('submission_form_instance_id', $value->submission_form_instance_id)
                    ->where('submission_form_element_id', $toElementId);

                if ($value->array_index !== null) {
                    $duplicateQuery->where('array_index', $value->array_index);
                }

                if ($duplicateQuery->exists()) {
                    $value->delete();

                    return;
                }

                $value->update(['submission_form_element_id' => $toElementId]);
            });
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
     * Bind TRF to sample type categories and clear the legacy sample-type pivot.
     *
     * @param  list<string>  $categoryNames
     */
    protected function syncSampleTypeCategoriesByNames(SubmissionForm $form, array $categoryNames): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('submission_form_sample_type_categories')) {
                $this->command?->warn('Pivot submission_form_sample_type_categories missing — run migrations first.');

                return;
            }

            $normalized = collect($categoryNames)
                ->map(fn (string $name): string => mb_strtolower(trim($name)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $ids = SampleTypeCategory::query()
                ->where(function ($query) use ($normalized): void {
                    foreach ($normalized as $name) {
                        $query->orWhereRaw('LOWER(TRIM(sample_type_category)) = ?', [$name]);
                    }
                })
                ->pluck('id')
                ->unique()
                ->values()
                ->all();

            if ($ids === []) {
                $this->command?->warn('No sample type categories matched for '.$form->name.' ('.implode(', ', $categoryNames).').');

                return;
            }

            $form->sampleTypeCategories()->sync($ids);

            $this->command?->info('Linked categories ['.implode(', ', $categoryNames).'] to '.$form->name.'.');
        } catch (\Exception $e) {
            $this->command?->warn('Could not sync sample type categories: '.$e->getMessage());
        }
    }

    /**
     * @param  list<string>  $codes
     */
    protected function syncSampleTypesByCodes(SubmissionForm $form, array $codes): void
    {
        try {
            $normalized = collect($codes)
                ->map(fn (string $code): string => mb_strtolower(trim($code)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            // Match sample type code or name (Amspec Dubai uses code "Waste Water",
            // while parameter rows / docs often use "SMP WWTR").
            $categoryIds = SampleType::query()
                ->where(function ($query) use ($normalized): void {
                    foreach ($normalized as $token) {
                        $query->orWhereRaw('LOWER(TRIM(code)) = ?', [$token])
                            ->orWhereRaw('LOWER(TRIM(name)) = ?', [$token])
                            ->orWhereRaw('LOWER(TRIM(code)) LIKE ?', ['%'.$token.'%']);
                    }
                })
                ->whereNotNull('sample_type_category')
                ->pluck('sample_type_category')
                ->unique()
                ->values()
                ->all();

            if ($categoryIds === []) {
                $this->command?->warn('No categories matched for '.$form->name.' (codes: '.implode(', ', $codes).').');

                return;
            }

            $form->sampleTypeCategories()->syncWithoutDetaching($categoryIds);
            $this->command?->info('Linked '.count($categoryIds).' category(ies) to '.$form->name.'.');
        } catch (\Exception $e) {
            $this->command?->warn('Could not sync categories from sample type codes: '.$e->getMessage());
        }
    }

    /**
     * @param  list<string>  $names
     */
    protected function syncSampleTypesByExactNames(SubmissionForm $form, array $names): void
    {
        try {
            $normalizedNames = collect($names)
                ->map(fn (string $name): string => mb_strtolower(trim($name)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $categoryIds = SampleType::query()
                ->where(function ($query) use ($normalizedNames): void {
                    foreach ($normalizedNames as $name) {
                        $query->orWhereRaw('LOWER(TRIM(name)) = ?', [$name]);
                    }
                })
                ->whereNotNull('sample_type_category')
                ->pluck('sample_type_category')
                ->unique()
                ->values()
                ->all();

            if ($categoryIds === []) {
                $this->command?->warn('No categories matched for '.$form->name.' (names: '.implode(', ', $names).').');

                return;
            }

            $form->sampleTypeCategories()->syncWithoutDetaching($categoryIds);
            $this->command?->info('Linked '.count($categoryIds).' category(ies) by name to '.$form->name.'.');
        } catch (\Exception $e) {
            $this->command?->warn('Could not sync categories from sample type names: '.$e->getMessage());
        }
    }

    protected function detachFoodAndFeedSampleTypes(SubmissionForm $form): void
    {
        try {
            $categoryIds = SampleTypeCategory::query()
                ->where(function ($query): void {
                    $query->whereRaw("LOWER(TRIM(sample_type_category)) IN ('food & feed', 'food and feed')");
                })
                ->pluck('id')
                ->all();

            if ($categoryIds === []) {
                return;
            }

            $form->sampleTypeCategories()->detach($categoryIds);
        } catch (\Exception $e) {
            $this->command?->warn('Could not detach Food & Feed categories: '.$e->getMessage());
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

            $categoryIds = $query->whereNotNull('sample_type_category')
                ->pluck('sample_type_category')
                ->unique()
                ->values()
                ->all();

            if ($categoryIds === []) {
                $this->command?->warn('No categories matched for '.$form->name.' (patterns: '.implode(', ', $namePatterns).').');

                return;
            }

            $form->sampleTypeCategories()->syncWithoutDetaching($categoryIds);
            $this->command?->info('Linked '.count($categoryIds).' category(ies) by pattern to '.$form->name.'.');
        } catch (\Exception $e) {
            $this->command?->warn('Could not sync categories by pattern: '.$e->getMessage());
        }
    }

    protected function createCustomerDetailsSection(SubmissionForm $form, int $sortOrder = 1): void
    {
        if ($form->sections()->where('title', 'Customer details')->exists()) {
            return;
        }

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
            ['client_unit_select', 'Company unit / Site name', 'company_unit_id', 3, false],
            ['text', 'Tel / Fax no.', 'customer_phone', 4, false],
            ['text', 'Mobile number', 'mobile_number', 5, false],
            ['client_contact_select', 'Contact person', 'contact_person', 6, false],
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
            ['client_unit_select', 'Company unit / Site name', 'company_unit_id', 3, false],
            ['text', 'Tel / Fax no.', 'customer_phone', 4, false],
            ['text', 'Mobile number', 'mobile_number', 5, false],
            ['client_contact_select', 'Contact person', 'contact_person', 6, false],
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

        $holder->elements()
            ->where('name', 'customer_tax_id')
            ->each(function (SubmissionFormElement $element): void {
                $hasValues = \App\Models\SubmissionFormInstanceValue::query()
                    ->where('submission_form_element_id', $element->id)
                    ->exists();

                if (! $hasValues) {
                    $element->delete();
                }
            });
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
        if ($form->sections()->where('title', 'Sample collection data')->exists()) {
            return;
        }

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

        $baseFields = $this->standardCollectionDataFields($apparatusOptions);

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
     * @param  list<array{value: string, label: string}>  $apparatusOptions
     * @return list<array{0: string, 1: string, 2: string, 3: int, 4?: list<array{value: string, label: string}>}>
     */
    protected function standardCollectionDataFields(array $apparatusOptions): array
    {
        return [
            ['date', 'Sampling date', 'sampling_date', 1],
            ['time', 'Sampling time', 'sampling_time', 2],
            ['customer_sample_point_select', 'Sampling location', 'sampling_location', 3],
            ['checkbox', 'Sampling apparatus', 'sampling_apparatus', 4, $apparatusOptions],
            ['text', 'Thermometer ID', 'thermometer_id', 5],
            ['checkbox', 'Method of sampling', 'method_of_sampling', 6, [
                ['value' => 'apha', 'label' => 'APHA'],
                ['value' => 'saso', 'label' => 'SASO'],
                ['value' => 'astm', 'label' => 'ASTM'],
                ['value' => 'others', 'label' => 'Others'],
                ['value' => 'us_fda', 'label' => 'US FDA'],
                ['value' => 'ccfra', 'label' => 'CCFRA'],
                ['value' => 'dm', 'label' => 'DM'],
                ['value' => 'sop', 'label' => 'SOP'],
            ]],
            ['radio', 'Reason of collection', 'reason_of_collection', 7, [
                ['value' => 'contract', 'label' => 'Contract'],
                ['value' => 'non_contract', 'label' => 'Non-contract'],
                ['value' => 'haccp', 'label' => 'HACCP requirement'],
                ['value' => 'disputed', 'label' => 'Disputed/Audit'],
            ]],
            ['checkbox', 'Transport condition', 'transport_condition', 8, [
                ['value' => 'chiller', 'label' => 'Chiller vehicle'],
                ['value' => 'frozen', 'label' => 'Frozen'],
                ['value' => 'ambient', 'label' => 'Ambient'],
            ]],
            ['date', 'Date received', 'date_received', 9],
        ];
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
        ], $extra);
    }

    protected function createSubmitAndSignSection(SubmissionForm $form, int $sortOrder): void
    {
        if ($form->sections()->whereIn('title', ['Submit & sign', 'Submit and sign'])->exists()) {
            return;
        }

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
        $existing = $form->sections()->where('title', $title)->first();
        if ($existing !== null) {
            return $existing;
        }

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
        $this->ensureAllTrfFormsArePortalSubmittable();

        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
        } catch (\Exception $e) {
            $this->command?->warn('Failed to clear cache: '.$e->getMessage());
        }
    }

    protected function ensureAllTrfFormsArePortalSubmittable(): void
    {
        SubmissionForm::query()
            ->where('document_code', 'like', 'TRF-%')
            ->where('document_code', 'not like', '%-OLD-%')
            ->get()
            ->each(function (SubmissionForm $form): void {
                $form->update([
                    'is_customer_portal_form' => true,
                    'is_published' => true,
                    'is_active' => true,
                    'is_customer_request_form' => false,
                    'form_type' => 'template',
                    'placement_slot' => ['customer_portal', 'admin_portal', 'samples_receiving'],
                ]);
            });
    }
}

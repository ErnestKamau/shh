<?php

namespace Database\Seeders\Concerns;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormSection;
use App\SampleType;
trait BuildsTestRequestFormSections
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createOrRefreshTestRequestForm(array $attributes): SubmissionForm
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
            'max_elements' => 10,
            'sort_order' => 1,
        ]);

        foreach ([
            ['text', 'Name', 'customer_name', 1, true],
            ['textarea', 'Address', 'customer_address', 2, true],
            ['text', 'Tel / Fax no.', 'customer_phone', 3, false],
            ['text', 'Mobile number', 'mobile_number', 4, false],
            ['client_contact_select', 'Contact person', 'contact_person', 5, false],
            ['text', 'CRM contact ID', 'crm_contact_id', 6, false],
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
            'max_elements' => 20,
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

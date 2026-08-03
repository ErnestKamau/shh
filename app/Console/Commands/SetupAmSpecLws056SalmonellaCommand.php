<?php

namespace App\Console\Commands;

use App\Analyte;
use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\Enums\GroupedWorksheetItemType;
use App\LabInventoryCategory;
use App\LabSubCategory;
use App\Models\Equipments\Equipment;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use App\Models\Procedures\ProcedureConfigField;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\Procedures\ProcedureWorksheetStep;
use App\Models\StageHeader;
use App\Models\System\SystemConfiguration;
use App\Models\TestStage;
use App\ReportingUnit;
use App\SampleType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SetupAmSpecLws056SalmonellaCommand extends Command
{
    protected $signature = 'amspec:setup-lws056-salmonella {--force : Recreate stages/procedure steps}';

    protected $description = 'Configure AmSpec LWS-056 Salmonella media, controls, stage header, procedure, grouped pipeline, and bindings';

    public function handle(): int
    {
        return DB::transaction(function (): int {
            $companyId = DB::table('companies')->value('id');
            $locationId = DB::table('inventory_locations')->value('id');
            $uomId = ReportingUnit::query()->value('id');

            if (! $uomId) {
                $this->error('No reporting unit found.');

                return self::FAILURE;
            }

            $mediaCat = $this->firstOrCreateCategory('Media', $companyId, $locationId);
            $ctrlCat = $this->firstOrCreateCategory('Controls', $companyId, $locationId);
            $dilCat = $this->firstOrCreateCategory('Diluents', $companyId, $locationId);

            foreach ([
                'media_solution_type_id' => $mediaCat->id,
                'control_solution_type_id' => $ctrlCat->id,
                'diluent_solution_type_id' => $dilCat->id,
            ] as $key => $value) {
                SystemConfiguration::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'status' => true]
                );
            }

            $media = [
                'pre' => $this->firstOrCreateSub('Pre-enrichment broth (BPW)', $mediaCat->id, $uomId),
                'rv' => $this->firstOrCreateSub('RV broth', $mediaCat->id, $uomId),
                'ttb' => $this->firstOrCreateSub('TTB', $mediaCat->id, $uomId),
                'xlda' => $this->firstOrCreateSub('XLDA', $mediaCat->id, $uomId),
                'hea' => $this->firstOrCreateSub('HEA', $mediaCat->id, $uomId),
                'bsa' => $this->firstOrCreateSub('BSA', $mediaCat->id, $uomId),
                'tsi' => $this->firstOrCreateSub('TSI agar', $mediaCat->id, $uomId),
                'lia' => $this->firstOrCreateSub('LIA', $mediaCat->id, $uomId),
                'lys' => $this->firstOrCreateSub('L-lysine decarboxylation broth', $mediaCat->id, $uomId),
                'tryp' => $this->firstOrCreateSub('Tryptone broth', $mediaCat->id, $uomId),
                'ure' => $this->firstOrCreateSub('Urease broth/agar', $mediaCat->id, $uomId),
                'mrvp' => $this->firstOrCreateSub('MR-VP broth', $mediaCat->id, $uomId),
                'dul' => $this->firstOrCreateSub('Phenol red dulcitol broth', $mediaCat->id, $uomId),
                'suc' => $this->firstOrCreateSub('Phenol red sucrose broth', $mediaCat->id, $uomId),
                'lac' => $this->firstOrCreateSub('Phenol red lactose broth', $mediaCat->id, $uomId),
                'mal' => $this->firstOrCreateSub('Malonate broth', $mediaCat->id, $uomId),
                'cit' => $this->firstOrCreateSub('Simmons citrate agar', $mediaCat->id, $uomId),
            ];

            $controls = [
                'pos' => $this->firstOrCreateSub('Salmonella typhimurium ATCC 14028', $ctrlCat->id, $uomId),
                'media' => $this->firstOrCreateSub('Media control (uninoculated)', $ctrlCat->id, $uomId),
                'neg' => $this->firstOrCreateSub('Negative control (non-target)', $ctrlCat->id, $uomId),
            ];

            $this->firstOrCreateSub('Sterile diluent', $dilCat->id, $uomId);

            foreach (array_merge(array_values($media), array_values($controls)) as $sub) {
                if (! $sub->current_batch_number) {
                    $sub->update([
                        'current_batch_number' => 'BATCH-'.Str::upper(Str::random(6)),
                        'batch_prepared_date' => now()->toDateString(),
                        'batch_status' => 'active',
                    ]);
                }
            }

            $sampleType = SampleType::query()->where('name', 'Food')->first()
                ?? SampleType::query()->where('name', 'like', '%Food%')->first();

            if (! $sampleType) {
                $sampleType = SampleType::create([
                    'name' => 'Food',
                    'code' => 'FOOD',
                    'active' => 1,
                    'company_id' => $companyId,
                ]);
            }

            $method = AnalysisMethod::query()
                ->where('name', 'like', '%Chapter 36%')
                ->orderByRaw("CASE WHEN name ILIKE '%Reference method%' THEN 0 ELSE 1 END")
                ->first();

            if (! $method) {
                $method = AnalysisMethod::create([
                    'name' => 'CMMEF 5th Edition, Chapter 36',
                    'code' => 'CMMEF-CH36',
                    'active' => 1,
                    'company_id' => $companyId,
                ]);
            }

            $analyte = Analyte::query()->where('name', 'Detection of Salmonella in Food Samples')->first()
                ?? Analyte::query()->where('name', 'Salmonella')->first();

            if (! $analyte) {
                $analyte = Analyte::create([
                    'name' => 'Detection of Salmonella in Food Samples',
                    'code' => 'SALM-DET',
                    'decimal_places' => 0,
                    'reporting_unit' => '/25g',
                    'reporting_symbol' => '-',
                    'non_detectable' => false,
                    'non_accredited' => false,
                    'active' => 1,
                    'company_id' => $companyId,
                    'show_on_report' => 1,
                ]);
            }

            $analysisType = AnalysisType::query()->where('name', 'General Foods')->first();
            if (! $analysisType) {
                $analysisType = AnalysisType::create([
                    'name' => 'General Foods',
                    'code' => 'GEN-FOOD',
                    'sample_type_id' => $sampleType->id,
                    'active' => 1,
                    'company_id' => $companyId,
                ]);
            }

            $incubator = Equipment::query()->where('name', 'like', '%Incub%')->first()
                ?? Equipment::query()->first();

            $header = StageHeader::query()->where('name', 'LWS-056 Salmonella spp. CMMEF Ch.36')->first()
                ?? StageHeader::create([
                    'name' => 'LWS-056 Salmonella spp. CMMEF Ch.36',
                    'method_id' => $method->id,
                    'analyte_id' => $analyte->id,
                    'sample_type_id' => $sampleType->id,
                    'is_multi_stage' => true,
                    'total_days' => 0,
                ]);

            $header->update([
                'method_id' => $method->id,
                'analyte_id' => $analyte->id,
                'sample_type_id' => $sampleType->id,
                'is_multi_stage' => true,
            ]);

            if ($this->option('force') || $header->testStages()->count() === 0) {
                TestStage::query()->where('stage_header_id', $header->id)->delete();
                $this->createStages($header, $media, $controls, $incubator?->id);
            }

            $header->update(['total_days' => (int) $header->testStages()->max('order')]);

            $procedure = $this->upsertProcedure($media);
            $holder = $this->upsertGroupedHolder($header, $procedure);

            $analysisType->update([
                'grouped_worksheet_holder_id' => $holder->id,
                'procedure_worksheet_id' => null,
                'hybrid_worksheet_id' => null,
            ]);

            $element = AnalysisElements::query()
                ->where('analysis_type_id', $analysisType->id)
                ->where('analyte_id', $analyte->id)
                ->first();

            $payload = [
                'analysis_type_id' => $analysisType->id,
                'analyte_id' => $analyte->id,
                'method' => $method->id,
                'active' => 1,
                'show_on_report' => 1,
                'has_method_sequence' => true,
                'stage_header_id' => $header->id,
                'method_sequence_id' => null,
                'procedure_worksheet_id' => null,
            ];

            if ($element) {
                $element->update($payload);
            } else {
                $element = AnalysisElements::create($payload);
            }

            $this->info('LWS-056 Salmonella setup complete.');
            $this->line('Sample type: '.$sampleType->name);
            $this->line('Analysis type: '.$analysisType->name.' ('.$analysisType->id.')');
            $this->line('Analyte: '.$analyte->name.' ('.$analyte->id.')');
            $this->line('Method: '.$method->name);
            $this->line('Stage header: '.$header->name.' ('.$header->id.')');
            $this->line('Procedure: '.$procedure->name.' ('.$procedure->id.')');
            $this->line('Grouped holder: '.$holder->name.' ('.$holder->id.')');
            $this->line('Element: '.$element->id);

            return self::SUCCESS;
        });
    }

    protected function firstOrCreateCategory(string $name, ?string $companyId, ?string $locationId): LabInventoryCategory
    {
        $row = LabInventoryCategory::query()->where('name', $name)->first();
        if ($row) {
            return $row;
        }

        return LabInventoryCategory::create([
            'name' => $name,
            'active' => 1,
            'company_id' => $companyId,
            'inventory_location_id' => $locationId,
        ]);
    }

    protected function firstOrCreateSub(string $name, string $categoryId, string $uomId): LabSubCategory
    {
        $row = LabSubCategory::query()
            ->where('name', $name)
            ->where('category_id', $categoryId)
            ->first();

        if ($row) {
            return $row;
        }

        return LabSubCategory::create([
            'name' => $name,
            'category_id' => $categoryId,
            'reporting_unit' => $uomId,
            'active' => 1,
            'stock' => 0,
        ]);
    }

    /**
     * @param  array<string, LabSubCategory>  $media
     * @param  array<string, LabSubCategory>  $controls
     */
    protected function createStages(StageHeader $header, array $media, array $controls, ?string $incubatorId): void
    {
        $req = fn (string $id, string $nature = 'qualitative') => [
            'id' => $id,
            'result_nature' => $nature,
            'is_mandatory' => true,
        ];

        $equipment = $incubatorId ? [$incubatorId] : [];

        // Four MS ops stages. Observations live in procedure matrices (interleaved pipeline chips).
        // is_result_stage = false so MS does not show its own sample-result UI.
        TestStage::create([
            'stage_header_id' => $header->id,
            'order' => 1,
            'stage_name' => 'Pre-enrichment',
            'duration_hours' => 24,
            'safe_duration' => 18,
            'instructions' => '25 g sample + 225 mL pre-enrichment broth. Room temperature 60 min & 35 °C for 24 ± 2 h. Record turbidity in Enrichment – Pre observations.',
            'media_required' => [$req($media['pre']->id, 'none')],
            'controls_required' => [$req($controls['pos']->id), $req($controls['media']->id), $req($controls['neg']->id)],
            'equipment_required' => $equipment,
            'is_result_stage' => false,
            'is_end_stage' => false,
        ]);

        TestStage::create([
            'stage_header_id' => $header->id,
            'order' => 2,
            'stage_name' => 'Secondary enrichment (RV + TTB)',
            'duration_hours' => 24,
            'safe_duration' => 18,
            'instructions' => '0.1 or 1 mL into 10 mL RV broth at 42 °C for 24 h; 1 mL into 10 mL TTB at 35 °C / 43 °C for 24 h. Record turbidity in Enrichment – RV/TTB observations.',
            'media_required' => [$req($media['rv']->id, 'none'), $req($media['ttb']->id, 'none')],
            'controls_required' => [$req($controls['pos']->id), $req($controls['media']->id)],
            'equipment_required' => $equipment,
            'is_result_stage' => false,
            'is_end_stage' => false,
        ]);

        TestStage::create([
            'stage_header_id' => $header->id,
            'order' => 3,
            'stage_name' => 'Selective isolation (XLDA / HEA / BSA)',
            'duration_hours' => 48,
            'safe_duration' => 24,
            'instructions' => 'Loopful from RV and TTB onto XLDA, HEA & BSA. 35 °C for 24–48 h. Record colony morphology in Selective isolation observations.',
            'media_required' => [$req($media['xlda']->id, 'none'), $req($media['hea']->id, 'none'), $req($media['bsa']->id, 'none')],
            'controls_required' => [$req($controls['pos']->id), $req($controls['media']->id), $req($controls['neg']->id)],
            'equipment_required' => $equipment,
            'is_result_stage' => false,
            'is_end_stage' => false,
        ]);

        TestStage::create([
            'stage_header_id' => $header->id,
            'order' => 4,
            'stage_name' => 'Confirmation tests',
            'duration_hours' => 48,
            'safe_duration' => 24,
            'instructions' => 'Complete biochemical confirmation panel. Record outcomes in Confirmation tests matrix. Final D/ND is posted in Results capture.',
            'media_required' => [
                $req($media['tsi']->id, 'none'),
                $req($media['lia']->id, 'none'),
                $req($media['lys']->id, 'none'),
                $req($media['tryp']->id, 'none'),
                $req($media['ure']->id, 'none'),
                $req($media['mrvp']->id, 'none'),
            ],
            'controls_required' => [$req($controls['pos']->id), $req($controls['neg']->id)],
            'equipment_required' => $equipment,
            'is_result_stage' => false,
            'is_end_stage' => true,
            'end_if_pass' => true,
            'end_if_fail' => true,
        ]);
    }

    /**
     * @param  array<string, LabSubCategory>  $media
     */
    protected function upsertProcedure(array $media): ProcedureWorksheet
    {
        $procedure = ProcedureWorksheet::query()->where('name', 'LWS-056 Salmonella Confirmation')->first()
            ?? ProcedureWorksheet::create([
                'name' => 'LWS-056 Salmonella Confirmation',
                'description' => 'AmSpec AMS/QMS/LWS/056 confirmation and isolation observations',
                'is_active' => true,
                'document_control_no' => 'AMS/QMS/LWS/056',
                'revision' => '11.2025.R0',
                'issue_date' => '2025-11-03',
            ]);

        $procedure->update([
            'is_active' => true,
            'document_control_no' => 'AMS/QMS/LWS/056',
            'revision' => '11.2025.R0',
            'issue_date' => '2025-11-03',
        ]);

        if ($this->option('force') || $procedure->configFields()->count() === 0) {
            ProcedureConfigField::query()->where('procedure_worksheet_id', $procedure->id)->delete();
            $config = [
                'Job Number',
                'Sample Name',
                'Other Details',
                'Food Product',
                'Analyst Name',
                'Analysis Start Date',
                'Completion Date',
                'Incubation Start Time',
                'Observation Date & Time',
                'Incubator ID',
            ];
            foreach ($config as $i => $label) {
                ProcedureConfigField::create([
                    'procedure_worksheet_id' => $procedure->id,
                    'label' => $label,
                    'field_value_name' => Str::slug($label, '_'),
                    'field_type' => 'input',
                    'order' => $i + 1,
                    'is_required' => false,
                ]);
            }
        }

        if ($this->option('force') || $procedure->steps()->count() === 0) {
            ProcedureWorksheetStep::query()->where('procedure_worksheet_id', $procedure->id)->delete();

            $turbidity = ['Turbidity Observed', 'No Turbidity'];
            $plateCtrl = ['Typical colony', 'Atypical colony', 'No Growth'];
            $xlda = ['Pink colony with black center', 'Pink colony without black center', 'No Growth'];
            $hea = ['Blue-green colony with black center', 'Blue-green colony without black center', 'No Growth'];
            $bsa = ['Black colony with metallic sheen', 'Black colony without metallic sheen', 'No Growth'];

            $steps = [
                // Enrichment
                ['Pre-enrichment – Observation', $turbidity],
                ['Pre-enrichment – Media Control', $turbidity],
                ['Pre-enrichment – Positive Control', $turbidity],
                ['RV enrichment – Observation', $turbidity],
                ['RV enrichment – Media Control', $turbidity],
                ['RV enrichment – Positive Control', $turbidity],
                ['TTB enrichment – Observation', $turbidity],
                ['TTB enrichment – Media Control', $turbidity],
                ['TTB enrichment – Positive Control', $turbidity],

                // Selective isolation — sample + controls (custom selects)
                ['XLDA from RV', $xlda],
                ['XLDA from RV – Media Control', $plateCtrl],
                ['XLDA from RV – Positive Control', $plateCtrl],
                ['XLDA from RV – Negative Control', $plateCtrl],
                ['HEA from RV', $hea],
                ['HEA from RV – Media Control', $plateCtrl],
                ['HEA from RV – Positive Control', $plateCtrl],
                ['HEA from RV – Negative Control', $plateCtrl],
                ['BSA from RV', $bsa],
                ['BSA from RV – Media Control', $plateCtrl],
                ['BSA from RV – Positive Control', $plateCtrl],
                ['BSA from RV – Negative Control', $plateCtrl],
                ['XLDA from TTB', $xlda],
                ['XLDA from TTB – Media Control', $plateCtrl],
                ['XLDA from TTB – Positive Control', $plateCtrl],
                ['XLDA from TTB – Negative Control', $plateCtrl],
                ['HEA from TTB', $hea],
                ['HEA from TTB – Media Control', $plateCtrl],
                ['HEA from TTB – Positive Control', $plateCtrl],
                ['HEA from TTB – Negative Control', $plateCtrl],
                ['BSA from TTB', $bsa],
                ['BSA from TTB – Media Control', $plateCtrl],
                ['BSA from TTB – Positive Control', $plateCtrl],
                ['BSA from TTB – Negative Control', $plateCtrl],
                ['Selective isolation Result (per 25 g)', ['Detected', 'Not Detected']],

                // Confirmation
                ['TSI', ['Alkaline Slant & Acid Butt', 'Acid slant and acid Butt', 'No Growth']],
                ['TSI – Media Control', ['No Growth', 'Growth']],
                ['TSI – Positive Control', ['Alkaline Slant & Acid Butt', 'Acid slant and acid Butt', 'No Growth']],
                ['LIA', ['Alkaline Slant & Alkaline Butt', 'Alkaline Slant & Acid Butt', 'No Growth']],
                ['LIA – Media Control', ['No Growth', 'Growth']],
                ['LIA – Positive Control', ['Alkaline Slant & Alkaline Butt', 'Alkaline Slant & Acid Butt', 'No Growth']],
                ['Lysine decarboxylation Test', ['Purple color', 'Yellow color']],
                ['Lysine decarboxylation Test – Media Control', ['No color change', 'Purple color', 'Yellow color']],
                ['Lysine decarboxylation Test – Positive Control', ['Purple color', 'Yellow color']],
                ['Indole Test', ['No red ring', 'Red ring']],
                ['Indole Test – Media Control', ['No red ring', 'Red ring']],
                ['Indole Test – Positive Control', ['No red ring', 'Red ring']],
                ['Urease Test', ['No color change', 'Pink color']],
                ['Urease Test – Media Control', ['No color change', 'Pink color']],
                ['Urease Test – Positive Control', ['No color change', 'Pink color']],
                ['Voges-Proskauer Test', ['No color change', 'Red color']],
                ['Voges-Proskauer Test – Media Control', ['No color change', 'Red color']],
                ['Voges-Proskauer Test – Positive Control', ['No color change', 'Red color']],
                ['Methyl red', ['Red color', 'No color change']],
                ['Methyl red – Media Control', ['Red color', 'No color change']],
                ['Methyl red – Positive Control', ['Red color', 'No color change']],
                ['Dulcitol', ['Yellow color change with gas production', 'No color change and No gas production']],
                ['Dulcitol – Media Control', ['Yellow color change with gas production', 'No color change and No gas production']],
                ['Dulcitol – Positive Control', ['Yellow color change with gas production', 'No color change and No gas production']],
                ['Sucrose', ['Yellow color change with gas production', 'No color change and No gas production']],
                ['Sucrose – Media Control', ['Yellow color change with gas production', 'No color change and No gas production']],
                ['Sucrose – Positive Control', ['Yellow color change with gas production', 'No color change and No gas production']],
                ['Lactose', ['Yellow color change with gas production', 'No color change and No gas production']],
                ['Lactose – Media Control', ['Yellow color change with gas production', 'No color change and No gas production']],
                ['Lactose – Positive Control', ['Yellow color change with gas production', 'No color change and No gas production']],
                ['Malonate', ['No color change', 'Blue color']],
                ['Malonate – Media Control', ['No color change', 'Blue color']],
                ['Malonate – Positive Control', ['No color change', 'Blue color']],
                ['Citrate utilization Test', ['No color change', 'Blue color']],
                ['Citrate utilization Test – Media Control', ['No color change', 'Blue color']],
                ['Citrate utilization Test – Positive Control', ['No color change', 'Blue color']],
                ['O antigen Agglutination Test', ['Agglutination', 'No agglutination']],
                ['O antigen Agglutination Test – Media Control', ['Agglutination', 'No agglutination']],
                ['O antigen Agglutination Test – Positive Control', ['Agglutination', 'No agglutination']],
                ['H antigen Agglutination Test', ['Agglutination', 'No agglutination']],
                ['H antigen Agglutination Test – Media Control', ['Agglutination', 'No agglutination']],
                ['H antigen Agglutination Test – Positive Control', ['Agglutination', 'No agglutination']],
            ];

            foreach ($steps as $i => [$label, $options]) {
                ProcedureWorksheetStep::create([
                    'procedure_worksheet_id' => $procedure->id,
                    'step' => $label,
                    'value_type' => 'custom_select',
                    'select_options' => $options,
                    'order' => $i + 1,
                    'is_active' => true,
                    'is_result_step' => $label === 'Selective isolation Result (per 25 g)',
                ]);
            }
        }

        // Always upsert layout_settings (idempotent JSON).
        $procedure->update([
            'layout_settings' => $this->buildLayoutSettings($media),
        ]);

        return $procedure->fresh(['steps', 'configFields']);
    }

    /**
     * Build the sectioned-matrix layout_settings JSON for LWS-056.
     *
     * @param  array<string, LabSubCategory>  $media
     * @return array<string, mixed>
     */
    protected function buildLayoutSettings(array $media): array
    {
        // Reagent column helper.
        $reagentCol = fn (string $key, string $label, string $mediaKey, bool $multiply = true, string $defaultUom = 'mL') => [
            'key' => $key,
            'label' => $label,
            'type' => 'reagent_input',
            'shared' => true,
            'default_value' => '',
            'default_uom' => $defaultUom,
            'uom_selectable' => true,
            'lab_sub_category_id' => $media[$mediaKey]->id,
            'multiply_by_sample_count' => $multiply,
        ];

        $textCol = fn (string $key, string $label, array $defaultByRow = []) => [
            'key' => $key,
            'label' => $label,
            'type' => 'text',
            'shared' => true,
            'default_value' => '',
            'default_value_by_row' => $defaultByRow,
        ];

        $obsCol = fn (string $key, string $label, array $stepKeyByRow) => [
            'key' => $key,
            'label' => $label,
            'type' => 'step_select',
            'shared' => false,
            'step_key_by_row' => $stepKeyByRow,
        ];

        return [
            'mode' => 'sectioned_matrix',
            'sections' => [
                // ── ENRICHMENT ────────────────────────────────────────────
                [
                    'key' => 'enrichment',
                    'label' => 'Enrichment',
                    'rows' => [
                        ['key' => 'pre_enrichment', 'label' => 'Pre-enrichment (BPW)'],
                        ['key' => 'rv_enrichment', 'label' => 'RV enrichment'],
                        ['key' => 'ttb_enrichment', 'label' => 'TTB enrichment'],
                    ],
                    'columns' => [
                        [
                            'key' => 'sample_volume',
                            'label' => 'Sample Vol.',
                            'type' => 'reagent_input',
                            'shared' => true,
                            'default_value_by_row' => [
                                'pre_enrichment' => '25',
                                'rv_enrichment' => '0.1',
                                'ttb_enrichment' => '1',
                            ],
                            'default_uom' => 'g',
                            'uom_selectable' => true,
                            'lab_sub_category_id' => null, // sample itself — no stock deduction
                            'multiply_by_sample_count' => false,
                        ],
                        $reagentCol('media_volume', 'Media Vol.', 'pre', true, 'mL') + [
                            'default_value_by_row' => [
                                'pre_enrichment' => '225',
                                'rv_enrichment' => '10',
                                'ttb_enrichment' => '10',
                            ],
                            'lab_sub_category_id_by_row' => [
                                'pre_enrichment' => $media['pre']->id,
                                'rv_enrichment' => $media['rv']->id,
                                'ttb_enrichment' => $media['ttb']->id,
                            ],
                        ],
                        $textCol('incubation', 'Incubation', [
                            'pre_enrichment' => '35°C, 24±2h',
                            'rv_enrichment' => '42°C, 24h',
                            'ttb_enrichment' => '35/43°C, 24h',
                        ]),
                        $obsCol('observation', 'Observation', [
                            'pre_enrichment' => 'Pre-enrichment – Observation',
                            'rv_enrichment' => 'RV enrichment – Observation',
                            'ttb_enrichment' => 'TTB enrichment – Observation',
                        ]),
                        $obsCol('media_ctrl', 'Media Ctrl', [
                            'pre_enrichment' => 'Pre-enrichment – Media Control',
                            'rv_enrichment' => 'RV enrichment – Media Control',
                            'ttb_enrichment' => 'TTB enrichment – Media Control',
                        ]),
                        $obsCol('pos_ctrl', 'Pos Ctrl', [
                            'pre_enrichment' => 'Pre-enrichment – Positive Control',
                            'rv_enrichment' => 'RV enrichment – Positive Control',
                            'ttb_enrichment' => 'TTB enrichment – Positive Control',
                        ]),
                    ],
                ],

                // ── SELECTIVE ISOLATION ───────────────────────────────────
                [
                    'key' => 'selective',
                    'label' => 'Selective Isolation',
                    'rows' => [
                        ['key' => 'xlda_rv', 'label' => 'XLDA from RV'],
                        ['key' => 'hea_rv', 'label' => 'HEA from RV'],
                        ['key' => 'bsa_rv', 'label' => 'BSA from RV'],
                        ['key' => 'xlda_ttb', 'label' => 'XLDA from TTB'],
                        ['key' => 'hea_ttb', 'label' => 'HEA from TTB'],
                        ['key' => 'bsa_ttb', 'label' => 'BSA from TTB'],
                    ],
                    'columns' => [
                        $textCol('incubation', 'Incubation', [
                            'xlda_rv' => '35°C, 24–48h',
                            'hea_rv' => '35°C, 24–48h',
                            'bsa_rv' => '35°C, 24–48h',
                            'xlda_ttb' => '35°C, 24–48h',
                            'hea_ttb' => '35°C, 24–48h',
                            'bsa_ttb' => '35°C, 24–48h',
                        ]),
                        $obsCol('observation', 'Observation', [
                            'xlda_rv' => 'XLDA from RV',
                            'hea_rv' => 'HEA from RV',
                            'bsa_rv' => 'BSA from RV',
                            'xlda_ttb' => 'XLDA from TTB',
                            'hea_ttb' => 'HEA from TTB',
                            'bsa_ttb' => 'BSA from TTB',
                        ]),
                        $obsCol('media_ctrl', 'Media Ctrl', [
                            'xlda_rv' => 'XLDA from RV – Media Control',
                            'hea_rv' => 'HEA from RV – Media Control',
                            'bsa_rv' => 'BSA from RV – Media Control',
                            'xlda_ttb' => 'XLDA from TTB – Media Control',
                            'hea_ttb' => 'HEA from TTB – Media Control',
                            'bsa_ttb' => 'BSA from TTB – Media Control',
                        ]),
                        $obsCol('pos_ctrl', 'Pos Ctrl', [
                            'xlda_rv' => 'XLDA from RV – Positive Control',
                            'hea_rv' => 'HEA from RV – Positive Control',
                            'bsa_rv' => 'BSA from RV – Positive Control',
                            'xlda_ttb' => 'XLDA from TTB – Positive Control',
                            'hea_ttb' => 'HEA from TTB – Positive Control',
                            'bsa_ttb' => 'BSA from TTB – Positive Control',
                        ]),
                        $obsCol('neg_ctrl', 'Neg Ctrl', [
                            'xlda_rv' => 'XLDA from RV – Negative Control',
                            'hea_rv' => 'HEA from RV – Negative Control',
                            'bsa_rv' => 'BSA from RV – Negative Control',
                            'xlda_ttb' => 'XLDA from TTB – Negative Control',
                            'hea_ttb' => 'HEA from TTB – Negative Control',
                            'bsa_ttb' => 'BSA from TTB – Negative Control',
                        ]),
                    ],
                ],

                // ── CONFIRMATION TESTS ────────────────────────────────────
                [
                    'key' => 'confirmation',
                    'label' => 'Confirmation Tests',
                    'rows' => [
                        ['key' => 'tsi', 'label' => 'TSI'],
                        ['key' => 'lia', 'label' => 'LIA'],
                        ['key' => 'lysine', 'label' => 'Lysine decarboxylation'],
                        ['key' => 'indole', 'label' => 'Indole Test'],
                        ['key' => 'urease', 'label' => 'Urease Test'],
                        ['key' => 'vp', 'label' => 'Voges-Proskauer'],
                        ['key' => 'mr', 'label' => 'Methyl red'],
                        ['key' => 'dulcitol', 'label' => 'Dulcitol'],
                        ['key' => 'sucrose', 'label' => 'Sucrose'],
                        ['key' => 'lactose', 'label' => 'Lactose'],
                        ['key' => 'malonate', 'label' => 'Malonate'],
                        ['key' => 'citrate', 'label' => 'Citrate utilization'],
                        ['key' => 'o_antigen', 'label' => 'O antigen Agglutination'],
                        ['key' => 'h_antigen', 'label' => 'H antigen Agglutination'],
                    ],
                    'columns' => [
                        $obsCol('observation', 'Observation', [
                            'tsi' => 'TSI',
                            'lia' => 'LIA',
                            'lysine' => 'Lysine decarboxylation Test',
                            'indole' => 'Indole Test',
                            'urease' => 'Urease Test',
                            'vp' => 'Voges-Proskauer Test',
                            'mr' => 'Methyl red',
                            'dulcitol' => 'Dulcitol',
                            'sucrose' => 'Sucrose',
                            'lactose' => 'Lactose',
                            'malonate' => 'Malonate',
                            'citrate' => 'Citrate utilization Test',
                            'o_antigen' => 'O antigen Agglutination Test',
                            'h_antigen' => 'H antigen Agglutination Test',
                        ]),
                        $obsCol('media_ctrl', 'Media Ctrl', [
                            'tsi' => 'TSI – Media Control',
                            'lia' => 'LIA – Media Control',
                            'lysine' => 'Lysine decarboxylation Test – Media Control',
                            'indole' => 'Indole Test – Media Control',
                            'urease' => 'Urease Test – Media Control',
                            'vp' => 'Voges-Proskauer Test – Media Control',
                            'mr' => 'Methyl red – Media Control',
                            'dulcitol' => 'Dulcitol – Media Control',
                            'sucrose' => 'Sucrose – Media Control',
                            'lactose' => 'Lactose – Media Control',
                            'malonate' => 'Malonate – Media Control',
                            'citrate' => 'Citrate utilization Test – Media Control',
                            'o_antigen' => 'O antigen Agglutination Test – Media Control',
                            'h_antigen' => 'H antigen Agglutination Test – Media Control',
                        ]),
                        $obsCol('pos_ctrl', 'Pos Ctrl', [
                            'tsi' => 'TSI – Positive Control',
                            'lia' => 'LIA – Positive Control',
                            'lysine' => 'Lysine decarboxylation Test – Positive Control',
                            'indole' => 'Indole Test – Positive Control',
                            'urease' => 'Urease Test – Positive Control',
                            'vp' => 'Voges-Proskauer Test – Positive Control',
                            'mr' => 'Methyl red – Positive Control',
                            'dulcitol' => 'Dulcitol – Positive Control',
                            'sucrose' => 'Sucrose – Positive Control',
                            'lactose' => 'Lactose – Positive Control',
                            'malonate' => 'Malonate – Positive Control',
                            'citrate' => 'Citrate utilization Test – Positive Control',
                            'o_antigen' => 'O antigen Agglutination Test – Positive Control',
                            'h_antigen' => 'H antigen Agglutination Test – Positive Control',
                        ]),
                    ],
                ],
            ],
        ];
    }

    protected function upsertGroupedHolder(StageHeader $header, ProcedureWorksheet $procedure): GroupedWorksheetHolder
    {
        $holder = GroupedWorksheetHolder::query()->where('name', 'LWS-056 Salmonella Pipeline')->first()
            ?? GroupedWorksheetHolder::create([
                'name' => 'LWS-056 Salmonella Pipeline',
                'description' => 'AmSpec LABORATORY WORK SHEET – DETECTION OF SALMONELLA SPP',
                'is_active' => true,
                'document_control_no' => 'AMS/QMS/LWS/056',
                'revision' => '11.2025.R0',
                'issue_date' => '2025-11-03',
            ]);

        $holder->update([
            'is_active' => true,
            'document_control_no' => 'AMS/QMS/LWS/056',
            'revision' => '11.2025.R0',
            'issue_date' => '2025-11-03',
            'description' => 'AmSpec LABORATORY WORK SHEET – DETECTION OF SALMONELLA SPP',
            // Phased pipeline: operator sees stage → observe per phase.
            'settings' => ['pipeline_mode' => 'phased'],
        ]);

        GroupedWorksheetItem::query()->where('grouped_worksheet_holder_id', $holder->id)->delete();

        /*
         | Combined phase chips — StageHeader item with optional embedded procedure matrix:
         | {
         |   "stage_order": N,
         |   "procedure_worksheet_id": "...",
         |   "section_key": "...",
         |   "row_keys": [...],          // optional
         |   "show_config_fields": bool  // optional, default false when embedded
         | }
         | Omit procedure_* keys → classic MS-only chip.
         | Results capture is appended virtually by GroupedWorksheetPipelineStages.
         */
        $phase = function (
            int $order,
            string $label,
            int $sort,
            string $sectionKey,
            ?array $rowKeys,
            bool $showConfig
        ) use ($holder, $header, $procedure): void {
            GroupedWorksheetItem::create([
                'grouped_worksheet_holder_id' => $holder->id,
                'sort_order' => $sort,
                'label' => $label,
                'item_type' => GroupedWorksheetItemType::StageHeader,
                'reference_id' => $header->id,
                'is_required' => true,
                'config' => array_filter([
                    'stage_order' => $order,
                    'procedure_worksheet_id' => $procedure->id,
                    'section_key' => $sectionKey,
                    'row_keys' => $rowKeys,
                    'show_config_fields' => $showConfig,
                ], fn ($v) => $v !== null),
            ]);
        };

        $phase(1, 'Pre-enrichment', 1, 'enrichment', ['pre_enrichment'], false);
        $phase(2, 'Secondary enrichment', 2, 'enrichment', ['rv_enrichment', 'ttb_enrichment'], false);
        $phase(3, 'Selective isolation', 3, 'selective', null, false);
        $phase(4, 'Confirmation', 4, 'confirmation', null, false);

        return $holder->fresh('items');
    }
}

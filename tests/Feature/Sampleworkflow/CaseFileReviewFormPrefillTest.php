<?php

namespace Tests\Feature\Sampleworkflow;

use App\CapturedResult;
use App\Enums\GroupedWorksheetItemType;
use App\Models\Formulars\Formula;
use App\Models\Formulars\FormulaMandatoryField;
use App\Models\Formulars\FormulaVersion;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use App\Models\Worksheets\SampleCapturedWorksheetFormula;
use App\Models\Worksheets\SampleWorksheetFormularMandatoryData;
use App\SampleHeader;
use App\Services\Sampleworkflow\CaseFileReviewFormPrefillService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CaseFileReviewFormPrefillTest extends TestCase
{
    use DatabaseTransactions;

    public function test_match_section_maps_pipeline_labels(): void
    {
        $service = app(CaseFileReviewFormPrefillService::class);

        $this->assertSame('screening', $service->matchSection('Sample Screening'));
        $this->assertSame('extraction', $service->matchSection('DNA Extraction'));
        $this->assertSame('pcr', $service->matchSection('PCR Amplification'));
        $this->assertNull($service->matchSection('Unrelated step'));
    }

    public function test_build_prefill_fills_dates_from_grouped_formula_worksheets(): void
    {
        $batch = SampleHeader::query()->first();
        $captured = $batch
            ? CapturedResult::query()->where('sample_header_id', $batch->id)->first()
            : null;

        if (! $batch || ! $captured) {
            $this->markTestSkipped('Requires a batch with captured results.');
        }

        $screeningFormula = $this->createFormulaWithMandatoryDateField('Screening Formula '.uniqid());
        $extractionFormula = $this->createFormulaWithMandatoryDateField('Extraction Formula '.uniqid());

        $holder = GroupedWorksheetHolder::create([
            'name' => 'DNA Pipeline '.uniqid(),
            'is_active' => true,
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 1,
            'label' => 'Sample Screening',
            'item_type' => GroupedWorksheetItemType::Formula,
            'reference_id' => $screeningFormula->id,
            'is_required' => true,
        ]);

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $holder->id,
            'sort_order' => 2,
            'label' => 'Sample Extraction',
            'item_type' => GroupedWorksheetItemType::Formula,
            'reference_id' => $extractionFormula->id,
            'is_required' => true,
        ]);

        $captured->update([
            'grouped_worksheet_holder_id' => $holder->id,
            'has_grouped_worksheet' => true,
            'formular_id' => $screeningFormula->id,
        ]);

        $screeningWorksheet = SampleCapturedWorksheetFormula::create([
            'sample_header_id' => $batch->id,
            'sample_detail_id' => $captured->sample_detail_id,
            'captured_result_id' => $captured->id,
            'formular_id' => $screeningFormula->id,
            'date' => '2026-05-10',
            'lab_no' => $batch->batch_code,
        ]);

        $extractionCaptured = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('id', '!=', $captured->id)
            ->first();

        if (! $extractionCaptured) {
            $extractionCaptured = $captured->replicate();
            $extractionCaptured->id = null;
            $extractionCaptured->formular_id = $extractionFormula->id;
            $extractionCaptured->grouped_worksheet_holder_id = $holder->id;
            $extractionCaptured->has_grouped_worksheet = true;
            $extractionCaptured->save();
        } else {
            $extractionCaptured->update([
                'grouped_worksheet_holder_id' => $holder->id,
                'has_grouped_worksheet' => true,
                'formular_id' => $extractionFormula->id,
            ]);
        }

        SampleCapturedWorksheetFormula::create([
            'sample_header_id' => $batch->id,
            'sample_detail_id' => $extractionCaptured->sample_detail_id,
            'captured_result_id' => $extractionCaptured->id,
            'formular_id' => $extractionFormula->id,
            'date' => '2026-05-12',
            'lab_no' => $batch->batch_code,
        ]);

        $extractionVersion = $extractionFormula->activeVersion;
        $chelexField = FormulaMandatoryField::create([
            'formula_version_id' => $extractionVersion->id,
            'label' => 'Method',
            'field_type' => 'checkbox',
            'order' => 2,
            'field_value_name' => 'method',
            'field_options' => ['options' => ['Chelex', 'Prepfiler']],
        ]);

        $extractionWorksheet = SampleCapturedWorksheetFormula::query()
            ->where('captured_result_id', $extractionCaptured->id)
            ->where('formular_id', $extractionFormula->id)
            ->first();

        SampleWorksheetFormularMandatoryData::create([
            'worksheet_formular_id' => $extractionWorksheet->id,
            'formula_mandatory_field_id' => $chelexField->id,
            'field_value' => json_encode(['Chelex']),
        ]);

        $service = app(CaseFileReviewFormPrefillService::class);
        $prefill = $service->buildPrefill($batch, $service->defaultBatchFields($batch));

        $this->assertSame('2026-05-10', $prefill['screening_date'] ?? null);
        $this->assertSame('2026-05-12', $prefill['extraction_date'] ?? null);
        $this->assertTrue((bool) ($prefill['extraction_method_chelex'] ?? false));

        $screeningWorksheet->delete();
    }

    public function test_merge_fill_empty_only_does_not_overwrite_existing_values(): void
    {
        $service = app(CaseFileReviewFormPrefillService::class);

        $merged = $service->mergeFillEmptyOnly(
            ['screening_date' => '2026-01-01', 'screening_method' => ''],
            ['screening_date' => '2026-05-10', 'screening_method' => 'Phenolphthalein']
        );

        $this->assertSame('2026-01-01', $merged['screening_date']);
        $this->assertSame('Phenolphthalein', $merged['screening_method']);
    }

    protected function createFormulaWithMandatoryDateField(string $name): Formula
    {
        $formula = Formula::create([
            'name' => $name,
            'is_active' => true,
        ]);

        $version = FormulaVersion::create([
            'formula_id' => $formula->id,
            'version_number' => 1,
            'is_active' => true,
        ]);

        FormulaMandatoryField::create([
            'formula_version_id' => $version->id,
            'label' => 'Screening Date',
            'field_type' => 'date',
            'order' => 1,
            'field_value_name' => 'screening_date',
        ]);

        return $formula->fresh(['activeVersion.mandatoryFields']);
    }
}

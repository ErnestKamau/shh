<?php

namespace App\Livewire\ShelfLife;

use App\Analyte;
use App\AnalysisMethod;
use App\CapturedResult;
use App\Models\ShelfLife\ShelfLifePullPoint;
use App\Models\ShelfLife\ShelfLifeStudy;
use App\Models\ShelfLife\ShelfLifeStudyParameterSpec;
use App\ReportingUnit;
use App\Services\ShelfLife\ShelfLifeStudyBootstrapService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;

class StudyDetail extends Component
{
    public string $studyId;

    public string $activeTab = 'definition';

    public string $title = '';

    public string $studyType = ShelfLifeStudy::TYPE_REAL_TIME;

    public ?string $storageTempC = null;

    public ?string $storageRhPercent = null;

    public string $storageConditionLabel = '';

    public ?string $targetDurationValue = null;

    public string $targetDurationUnit = 'months';

    public ?string $startDate = null;

    public string $status = ShelfLifeStudy::STATUS_DRAFT;

    public string $batchLotNo = '';

    public ?string $mfgDate = null;

    public string $notes = '';

    public string $specAnalyteId = '';

    public string $specMethodId = '';

    public string $specReportingUnitId = '';

    public string $specType = ShelfLifeStudyParameterSpec::SPEC_RANGE;

    public ?string $specLow = null;

    public ?string $specHigh = null;

    public ?string $specSafetyMargin = null;

    public string $manualPullLabel = '';

    public int $manualPullOffset = 0;

    public string $manualPullUnit = 'months';

    public bool $manualPullIsBaseline = false;

    public ?string $selectedPullPointId = null;

    /** @var array<string, array<string, string|null>> sampleId => [analyteId => value] */
    public array $resultInputs = [];

    public function mount(string $studyId): void
    {
        $this->studyId = $studyId;
        $this->loadStudyForm();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveDefinition(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'studyType' => ['required', Rule::in([ShelfLifeStudy::TYPE_REAL_TIME, ShelfLifeStudy::TYPE_ACCELERATED])],
            'status' => ['required', Rule::in([
                ShelfLifeStudy::STATUS_DRAFT,
                ShelfLifeStudy::STATUS_ACTIVE,
                ShelfLifeStudy::STATUS_ON_HOLD,
                ShelfLifeStudy::STATUS_COMPLETED,
                ShelfLifeStudy::STATUS_ABORTED,
            ])],
            'targetDurationUnit' => ['required', Rule::in(['days', 'weeks', 'months'])],
            'startDate' => ['nullable', 'date'],
            'mfgDate' => ['nullable', 'date'],
        ]);

        $study = $this->study();
        $study->update([
            'title' => $this->title,
            'study_type' => $this->studyType,
            'storage_temp_c' => $this->storageTempC !== null && $this->storageTempC !== '' ? (float) $this->storageTempC : null,
            'storage_rh_percent' => $this->storageRhPercent !== null && $this->storageRhPercent !== '' ? (float) $this->storageRhPercent : null,
            'storage_condition_label' => $this->storageConditionLabel !== '' ? $this->storageConditionLabel : null,
            'target_duration_value' => $this->targetDurationValue !== null && $this->targetDurationValue !== '' ? (int) $this->targetDurationValue : null,
            'target_duration_unit' => $this->targetDurationUnit,
            'start_date' => $this->startDate,
            'status' => $this->status,
            'batch_lot_no' => $this->batchLotNo !== '' ? $this->batchLotNo : null,
            'mfg_date' => $this->mfgDate,
            'notes' => $this->notes !== '' ? $this->notes : null,
        ]);

        session()->flash('success', 'Study definition saved.');
        $this->loadStudyForm();
    }

    public function addParameterSpec(): void
    {
        $this->validate([
            'specAnalyteId' => ['required', 'uuid'],
            'specType' => ['required', Rule::in([
                ShelfLifeStudyParameterSpec::SPEC_RANGE,
                ShelfLifeStudyParameterSpec::SPEC_MAX,
                ShelfLifeStudyParameterSpec::SPEC_MIN,
                ShelfLifeStudyParameterSpec::SPEC_DELTA_FROM_BASELINE,
                ShelfLifeStudyParameterSpec::SPEC_PANEL_SCORE_MAX,
            ])],
        ]);

        $analyte = Analyte::query()->findOrFail($this->specAnalyteId);
        $study = $this->study();

        ShelfLifeStudyParameterSpec::query()->create([
            'shelf_life_study_id' => $study->id,
            'analyte_id' => $analyte->id,
            'method_id' => $this->specMethodId !== '' ? $this->specMethodId : null,
            'reporting_unit_id' => $this->specReportingUnitId !== '' ? $this->specReportingUnitId : null,
            'parameter_label' => (string) ($analyte->name ?? $analyte->code),
            'spec_type' => $this->specType,
            'spec_low' => $this->specLow !== null && $this->specLow !== '' ? (float) $this->specLow : null,
            'spec_high' => $this->specHigh !== null && $this->specHigh !== '' ? (float) $this->specHigh : null,
            'safety_margin_percent' => $this->specSafetyMargin !== null && $this->specSafetyMargin !== '' ? (float) $this->specSafetyMargin : null,
            'sort_order' => (int) $study->parameterSpecs()->max('sort_order') + 1,
        ]);

        $this->specAnalyteId = '';
        $this->specMethodId = '';
        $this->specReportingUnitId = '';
        $this->specLow = null;
        $this->specHigh = null;
        $this->specSafetyMargin = null;
        session()->flash('success', 'Parameter specification added.');
    }

    public function removeParameterSpec(string $specId): void
    {
        ShelfLifeStudyParameterSpec::query()
            ->where('shelf_life_study_id', $this->studyId)
            ->where('id', $specId)
            ->delete();

        session()->flash('success', 'Parameter specification removed.');
    }

    public function generateDefaultPullSchedule(): void
    {
        $study = $this->study();
        if ($study->pullPoints()->exists()) {
            session()->flash('error', 'Pull points already exist. Clear them before regenerating the default schedule.');

            return;
        }

        app(ShelfLifeStudyBootstrapService::class)->generatePullPoints(
            $study,
            app(ShelfLifeStudyBootstrapService::class)->defaultMonthlyIntervals()
        );

        session()->flash('success', 'Default pull schedule generated (0, 1, 3, 6, 9, 12 months).');
    }

    public function addManualPullPoint(): void
    {
        $this->validate([
            'manualPullLabel' => ['nullable', 'string', 'max:120'],
            'manualPullOffset' => ['required', 'integer', 'min:0'],
            'manualPullUnit' => ['required', Rule::in(['days', 'weeks', 'months'])],
        ]);

        app(ShelfLifeStudyBootstrapService::class)->generatePullPoints(
            $this->study(),
            [[
                'label' => $this->manualPullLabel,
                'offset_value' => $this->manualPullOffset,
                'offset_unit' => $this->manualPullUnit,
                'is_baseline' => $this->manualPullIsBaseline || $this->manualPullOffset === 0,
            ]]
        );

        $this->manualPullLabel = '';
        $this->manualPullOffset = 0;
        $this->manualPullIsBaseline = false;
        session()->flash('success', 'Pull point added.');
    }

    public function removePullPoint(string $pullPointId): void
    {
        ShelfLifePullPoint::query()
            ->where('shelf_life_study_id', $this->studyId)
            ->where('id', $pullPointId)
            ->delete();

        if ($this->selectedPullPointId === $pullPointId) {
            $this->selectedPullPointId = null;
            $this->resultInputs = [];
        }

        session()->flash('success', 'Pull point removed.');
    }

    public function selectPullPoint(string $pullPointId): void
    {
        $this->selectedPullPointId = $pullPointId;
        $this->activeTab = 'results';
        $this->hydrateResultInputs();
    }

    public function updatedSelectedPullPointId(?string $value): void
    {
        if ($value) {
            $this->hydrateResultInputs();
        } else {
            $this->resultInputs = [];
        }
    }

    public function savePullPointResults(): void
    {
        if ($this->selectedPullPointId === null) {
            session()->flash('error', 'Select a pull point first.');

            return;
        }

        if (! Schema::hasColumn('captured_results', 'shelf_life_pull_point_id')) {
            session()->flash('error', 'Database is missing shelf_life_pull_point_id on captured_results. Run migrations.');

            return;
        }

        $study = $this->study()->load(['parameterSpecs', 'sampleHeader.samples']);
        $pullPoint = ShelfLifePullPoint::query()
            ->where('shelf_life_study_id', $study->id)
            ->where('id', $this->selectedPullPointId)
            ->firstOrFail();

        $samples = $study->sampleHeader?->samples ?? collect();
        $saved = 0;

        foreach ($samples as $sample) {
            foreach ($study->parameterSpecs as $spec) {
                $raw = $this->resultInputs[(string) $sample->id][(string) $spec->analyte_id] ?? null;
                if ($raw === null || $raw === '') {
                    continue;
                }

                $existing = CapturedResult::query()
                    ->where('sample_detail_id', $sample->id)
                    ->where('analyte_id', $spec->analyte_id)
                    ->where('shelf_life_pull_point_id', $pullPoint->id)
                    ->first();

                $payload = [
                    'sample_detail_id' => $sample->id,
                    'sample_header_id' => $study->sample_header_id,
                    'sample_detail_code' => $sample->sample_code,
                    'analyte_id' => $spec->analyte_id,
                    'analyte_code' => $spec->analyte?->code ?? $spec->parameter_label,
                    'method_id' => $spec->method_id,
                    'reporting_unit_id' => $spec->reporting_unit_id,
                    'result' => (string) $raw,
                    'shelf_life_pull_point_id' => $pullPoint->id,
                    'user_id' => auth()->id() ? (string) auth()->id() : null,
                ];

                if ($existing) {
                    $existing->update($payload);
                } else {
                    CapturedResult::query()->create($payload);
                }

                $saved++;
            }
        }

        $pullPoint->update([
            'actual_pull_date' => $pullPoint->actual_pull_date ?? now()->toDateString(),
            'status' => ShelfLifePullPoint::STATUS_TESTED,
        ]);

        session()->flash('success', "Saved {$saved} result(s) for {$pullPoint->label}. Samples remain assigned to this study for the next pull.");
        $this->hydrateResultInputs();
    }

    public function render()
    {
        $study = $this->study()->load([
            'customer',
            'sampleHeader.samples',
            'parameterSpecs.analyte',
            'parameterSpecs.method',
            'parameterSpecs.reportingUnit',
            'pullPoints',
        ]);

        return view('livewire.shelf-life.study-detail', [
            'study' => $study,
            'analytes' => Analyte::query()->where('active', 1)->orderBy('name')->get(['id', 'name', 'code']),
            'methods' => AnalysisMethod::query()->where('active', 1)->orderBy('name')->limit(500)->get(['id', 'name', 'code']),
            'reportingUnits' => ReportingUnit::query()->where('active', 1)->orderBy('name')->get(['id', 'name']),
            'selectedPullPoint' => $this->selectedPullPointId
                ? $study->pullPoints->firstWhere('id', $this->selectedPullPointId)
                : null,
            'samples' => $study->sampleHeader?->samples ?? collect(),
        ]);
    }

    private function study(): ShelfLifeStudy
    {
        return ShelfLifeStudy::query()->findOrFail($this->studyId);
    }

    private function loadStudyForm(): void
    {
        $study = $this->study();
        $this->title = (string) $study->title;
        $this->studyType = (string) $study->study_type;
        $this->storageTempC = $study->storage_temp_c !== null ? (string) $study->storage_temp_c : null;
        $this->storageRhPercent = $study->storage_rh_percent !== null ? (string) $study->storage_rh_percent : null;
        $this->storageConditionLabel = (string) ($study->storage_condition_label ?? '');
        $this->targetDurationValue = $study->target_duration_value !== null ? (string) $study->target_duration_value : null;
        $this->targetDurationUnit = (string) ($study->target_duration_unit ?? 'months');
        $this->startDate = $study->start_date?->format('Y-m-d');
        $this->status = (string) $study->status;
        $this->batchLotNo = (string) ($study->batch_lot_no ?? '');
        $this->mfgDate = $study->mfg_date?->format('Y-m-d');
        $this->notes = (string) ($study->notes ?? '');
    }

    private function hydrateResultInputs(): void
    {
        $this->resultInputs = [];
        if ($this->selectedPullPointId === null) {
            return;
        }

        $study = $this->study()->load(['parameterSpecs', 'sampleHeader.samples']);
        $samples = $study->sampleHeader?->samples ?? collect();

        foreach ($samples as $sample) {
            foreach ($study->parameterSpecs as $spec) {
                $existing = CapturedResult::query()
                    ->where('sample_detail_id', $sample->id)
                    ->where('analyte_id', $spec->analyte_id)
                    ->where('shelf_life_pull_point_id', $this->selectedPullPointId)
                    ->value('result');

                $this->resultInputs[(string) $sample->id][(string) $spec->analyte_id] = $existing !== null
                    ? (string) $existing
                    : null;
            }
        }
    }
}

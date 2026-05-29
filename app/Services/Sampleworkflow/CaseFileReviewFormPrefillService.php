<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\Enums\GroupedWorksheetItemType;
use App\Enums\GroupedWorksheetRunItemStatus;
use App\Models\Formulars\Formula;
use App\Models\Formulars\FormulaMandatoryField;
use App\Models\Formulars\FormulaStep;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use App\Models\GroupedWorksheets\GroupedWorksheetRun;
use App\Models\GroupedWorksheets\GroupedWorksheetRunItem;
use App\Models\Worksheets\SampleCapturedWorksheetFormula;
use App\Models\Worksheets\SampleWorksheetFormularMandatoryData;
use App\Models\Worksheets\SampleWorksheetFormularStepData;
use App\SampleAnalysisTypeRelation;
use App\SampleDetails;
use App\SampleHeader;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CaseFileReviewFormPrefillService
{
    /**
     * @return array<string, mixed>
     */
    public function defaultBatchFields(SampleHeader $batch): array
    {
        $sampleDetail = SampleDetails::where('sample_header_id', $batch->id)->first();

        return [
            'batch_id' => $batch->id,
            'lab_no' => $batch->batch_code,
            'file_no' => $sampleDetail?->file_no ?? '',
            'date_in' => $batch->receipt_date
                ? Carbon::parse($batch->receipt_date)->format('Y-m-d')
                : now()->format('Y-m-d'),
            'client' => $batch->customer?->name ?? '',
            'name_of_analyst' => auth()->user()?->name ?? '',
            'no_of_samples' => $batch->samples()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function buildPrefill(SampleHeader $batch, array $existing = []): array
    {
        $base = $existing !== [] ? $existing : $this->defaultBatchFields($batch);
        $holder = $this->resolveHolderForBatch($batch);

        if ($holder === null) {
            return $base;
        }

        $holder->load(['items' => fn ($q) => $q->orderBy('sort_order')]);
        $run = $this->resolveLatestRun($batch, $holder);
        $runItemsByItemId = $run
            ? $run->runItems->keyBy('grouped_worksheet_item_id')
            : collect();

        $fromWorksheets = [];

        foreach ($holder->items as $item) {
            if ($item->getItemTypeEnum() !== GroupedWorksheetItemType::Formula) {
                continue;
            }

            $section = $this->matchSection((string) $item->label);
            if ($section === null) {
                continue;
            }

            $formula = Formula::with('activeVersion.formulaSteps', 'activeVersion.mandatoryFields')
                ->find($item->reference_id);

            if (! $formula?->activeVersion) {
                continue;
            }

            $worksheet = $this->findBestFormulaWorksheet($batch, $holder, $formula);
            if ($worksheet === null || ! $this->worksheetHasSavedData($worksheet)) {
                continue;
            }

            if ($this->requiresPostedOrCompleted() && ! $this->stageIsEligible($item, $runItemsByItemId, $worksheet)) {
                continue;
            }

            $payload = $this->extractFormulaStagePayload($worksheet, $formula, $section);
            $fromWorksheets = $this->mergeFillEmptyOnly($fromWorksheets, $payload);
        }

        return $this->mergeFillEmptyOnly($base, $fromWorksheets);
    }

    public function resolveHolderForBatch(SampleHeader $batch): ?GroupedWorksheetHolder
    {
        $holderIds = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('has_grouped_worksheet', true)
            ->whereNotNull('grouped_worksheet_holder_id')
            ->distinct()
            ->pluck('grouped_worksheet_holder_id')
            ->filter()
            ->values();

        if ($holderIds->isEmpty()) {
            $analysisTypeIds = SampleAnalysisTypeRelation::query()
                ->where('batch_id', $batch->id)
                ->distinct()
                ->pluck('analysis_type_id');

            $holderIds = \App\AnalysisType::query()
                ->whereIn('id', $analysisTypeIds)
                ->whereNotNull('grouped_worksheet_holder_id')
                ->pluck('grouped_worksheet_holder_id')
                ->unique()
                ->values();
        }

        if ($holderIds->isEmpty()) {
            return null;
        }

        if ($holderIds->count() === 1) {
            return GroupedWorksheetHolder::query()
                ->where('id', $holderIds->first())
                ->where('is_active', true)
                ->first();
        }

        return $this->pickBestHolder($batch, $holderIds);
    }

    /**
     * @param  Collection<int, string>  $holderIds
     */
    protected function pickBestHolder(SampleHeader $batch, Collection $holderIds): ?GroupedWorksheetHolder
    {
        $bestId = null;
        $bestScore = -1;

        foreach ($holderIds as $holderId) {
            $completedCount = GroupedWorksheetRunItem::query()
                ->whereHas('run', fn ($q) => $q
                    ->where('sample_header_id', $batch->id)
                    ->where('grouped_worksheet_holder_id', $holderId))
                ->where('status', GroupedWorksheetRunItemStatus::Completed)
                ->count();

            $worksheetCount = SampleCapturedWorksheetFormula::query()
                ->where('sample_header_id', $batch->id)
                ->whereHas('capturedResult', fn ($q) => $q
                    ->where('grouped_worksheet_holder_id', $holderId))
                ->count();

            $score = ($completedCount * 10) + $worksheetCount;

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestId = $holderId;
            }
        }

        return $bestId
            ? GroupedWorksheetHolder::query()->where('id', $bestId)->where('is_active', true)->first()
            : null;
    }

    protected function resolveLatestRun(SampleHeader $batch, GroupedWorksheetHolder $holder): ?GroupedWorksheetRun
    {
        return GroupedWorksheetRun::query()
            ->where('sample_header_id', $batch->id)
            ->where('grouped_worksheet_holder_id', $holder->id)
            ->with('runItems')
            ->orderByDesc('started_at')
            ->first();
    }

    /**
     * @param  Collection<string, GroupedWorksheetRunItem>  $runItemsByItemId
     */
    protected function stageIsEligible(
        GroupedWorksheetItem $item,
        Collection $runItemsByItemId,
        SampleCapturedWorksheetFormula $worksheet,
    ): bool {
        $runItem = $runItemsByItemId->get($item->id);
        if ($runItem && $runItem->status === GroupedWorksheetRunItemStatus::Completed) {
            return true;
        }

        return $worksheet->posted_at !== null;
    }

    protected function requiresPostedOrCompleted(): bool
    {
        return (bool) config('case_file_review.require_posted_or_completed', false);
    }

    protected function findBestFormulaWorksheet(
        SampleHeader $batch,
        GroupedWorksheetHolder $holder,
        Formula $formula,
    ): ?SampleCapturedWorksheetFormula {
        $capturedIds = CapturedResult::query()
            ->where('sample_header_id', $batch->id)
            ->where('grouped_worksheet_holder_id', $holder->id)
            ->where('has_grouped_worksheet', true)
            ->pluck('id');

        if ($capturedIds->isEmpty()) {
            return null;
        }

        $worksheets = SampleCapturedWorksheetFormula::query()
            ->where('sample_header_id', $batch->id)
            ->where('formular_id', $formula->id)
            ->whereIn('captured_result_id', $capturedIds)
            ->with([
                'stepData.formulaStep',
                'mandatoryData.mandatoryField',
                'doneByUser',
            ])
            ->get();

        if ($worksheets->isEmpty()) {
            return null;
        }

        if ($this->requiresPostedOrCompleted()) {
            $posted = $worksheets->filter(fn (SampleCapturedWorksheetFormula $w) => $w->posted_at !== null);
            if ($posted->isNotEmpty()) {
                $worksheets = $posted;
            }
        }

        return $worksheets->sortByDesc(function (SampleCapturedWorksheetFormula $w) {
            $postedBoost = $w->posted_at ? 1_000_000_000_000 : 0;

            return $postedBoost + ($w->updated_at?->timestamp ?? 0);
        })->first();
    }

    protected function worksheetHasSavedData(SampleCapturedWorksheetFormula $worksheet): bool
    {
        if ($worksheet->date !== null) {
            return true;
        }

        foreach ($worksheet->stepData as $stepData) {
            if ($this->valueIsPresent($stepData->step_value)) {
                return true;
            }
        }

        foreach ($worksheet->mandatoryData as $mandatoryData) {
            if ($this->valueIsPresent($mandatoryData->field_value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function extractFormulaStagePayload(
        SampleCapturedWorksheetFormula $worksheet,
        Formula $formula,
        string $section,
    ): array {
        $payload = [];
        $version = $formula->activeVersion;

        $dateField = config("case_file_review.section_date_fields.{$section}");
        if ($dateField && $worksheet->date) {
            $payload[$dateField] = $worksheet->date->format('Y-m-d');
        }

        if ($worksheet->doneByUser && empty($payload['name_of_analyst'])) {
            $payload['name_of_analyst'] = $worksheet->doneByUser->name;
        } elseif ($worksheet->done_by_user_id) {
            $analystName = User::query()->find($worksheet->done_by_user_id)?->name;
            if ($analystName) {
                $payload['name_of_analyst'] = $analystName;
            }
        }

        $sectionAliases = config("case_file_review.section_field_aliases.{$section}", []);

        /** @var Collection<int, FormulaMandatoryField> $mandatoryFields */
        $mandatoryFields = $version->mandatoryFields ?? collect();
        $mandatoryById = $mandatoryFields->keyBy('id');

        foreach ($worksheet->mandatoryData as $row) {
            if (! $row instanceof SampleWorksheetFormularMandatoryData) {
                continue;
            }

            $field = $row->mandatoryField ?? $mandatoryById->get($row->formula_mandatory_field_id);
            if (! $field) {
                continue;
            }

            $this->applyFieldValue($payload, $field, (string) ($row->field_value ?? ''), $section, $sectionAliases);
        }

        /** @var Collection<int, FormulaStep> $steps */
        $steps = $version->formulaSteps ?? collect();
        $stepsById = $steps->keyBy('id');

        foreach ($worksheet->stepData as $row) {
            if (! $row instanceof SampleWorksheetFormularStepData) {
                continue;
            }

            $step = $row->formulaStep ?? $stepsById->get($row->formula_step_id);
            if (! $step) {
                continue;
            }

            $this->applyStepValue($payload, $step, (string) ($row->step_value ?? ''), $section, $sectionAliases);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string|null>  $sectionAliases
     */
    protected function applyFieldValue(
        array &$payload,
        FormulaMandatoryField $field,
        string $rawValue,
        string $section,
        array $sectionAliases,
    ): void {
        if (! $this->valueIsPresent($rawValue)) {
            return;
        }

        $normalizedKey = $this->normalizeKey((string) $field->field_value_name)
            ?: $this->normalizeKey((string) $field->label);

        $target = $this->resolveTargetColumn($normalizedKey, $section, $sectionAliases);

        if ($target === null) {
            $this->applyCheckboxTokens($payload, $rawValue, $field->field_type);

            return;
        }

        if ($field->field_type === 'checkbox') {
            $this->applyCheckboxField($payload, $target, $rawValue, $field);

            return;
        }

        if ($field->field_type === 'date' || $field->field_type === 'datetime') {
            $date = $this->normalizeDate($rawValue);
            if ($date !== null) {
                $payload[$target] = $date;
            }

            return;
        }

        $payload[$target] = trim($rawValue);
        $this->applyCheckboxTokens($payload, $rawValue, $field->field_type);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string|null>  $sectionAliases
     */
    protected function applyStepValue(
        array &$payload,
        FormulaStep $step,
        string $rawValue,
        string $section,
        array $sectionAliases,
    ): void {
        if (! $this->valueIsPresent($rawValue)) {
            return;
        }

        $normalizedKey = $this->normalizeKey((string) $step->variable_name)
            ?: $this->normalizeKey((string) $step->label);

        if ($step->step_type === 'checkbox') {
            $selected = $this->decodeJsonList($rawValue);
            foreach ($selected as $option) {
                $this->applyCheckboxTokens($payload, (string) $option, 'checkbox');
            }

            return;
        }

        $target = $this->resolveTargetColumn($normalizedKey, $section, $sectionAliases);

        if ($target === null) {
            $this->applyCheckboxTokens($payload, $rawValue, $step->step_type);

            return;
        }

        if (in_array($step->step_type, ['input', 'dataset', 'derived', 'lookup'], true)) {
            $payload[$target] = trim($rawValue);
            $this->applyCheckboxTokens($payload, $rawValue, $step->step_type);
        }
    }

    /**
     * @param  array<string, string|null>  $sectionAliases
     */
    protected function resolveTargetColumn(string $normalizedKey, string $section, array $sectionAliases): ?string
    {
        if ($normalizedKey === '') {
            return null;
        }

        $global = config('case_file_review.mandatory_field_aliases.'.$normalizedKey)
            ?? config('case_file_review.step_aliases.'.$normalizedKey);

        if ($global !== null && $global !== '') {
            return $global;
        }

        if (array_key_exists($normalizedKey, $sectionAliases)) {
            $sectionTarget = $sectionAliases[$normalizedKey];

            return $sectionTarget !== null && $sectionTarget !== '' ? $sectionTarget : null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function applyCheckboxField(
        array &$payload,
        string $target,
        string $rawValue,
        FormulaMandatoryField $field,
    ): void {
        $selected = $this->decodeJsonList($rawValue);
        if ($selected === []) {
            $selected = array_map('trim', explode(',', $rawValue));
        }

        $options = $field->checkboxOptionLabels();
        $matched = false;

        foreach ($selected as $token) {
            if ($token === '') {
                continue;
            }

            foreach ($options as $label) {
                if (strcasecmp((string) $token, (string) $label) === 0) {
                    $payload[$target] = true;
                    $matched = true;
                    $this->applyCheckboxTokens($payload, (string) $label, 'checkbox');
                }
            }

            $this->applyCheckboxTokens($payload, (string) $token, 'checkbox');
        }

        if (! $matched && count($selected) === 1 && ! $this->isBooleanColumn($target)) {
            $payload[$target] = $selected[0];
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function applyCheckboxTokens(array &$payload, string $value, ?string $fieldType): void
    {
        $haystack = strtolower($value);
        $tokens = config('case_file_review.checkbox_tokens', []);

        foreach ($tokens as $token => $column) {
            if ($token === '' || $column === '') {
                continue;
            }

            if (! str_contains($haystack, strtolower((string) $token))) {
                continue;
            }

            if ($this->isBooleanColumn((string) $column)) {
                $payload[$column] = true;
            } elseif (! isset($payload[$column]) || $this->isEmptyCaseFieldValue($payload[$column])) {
                $payload[$column] = trim($value);
            }
        }
    }

    protected function isBooleanColumn(string $column): bool
    {
        return str_starts_with($column, 'screening_sample_type_')
            || str_starts_with($column, 'extraction_method_')
            || str_starts_with($column, 'quantification_')
            || str_starts_with($column, 'pcr_')
            || str_starts_with($column, 'injection_instrument_')
            || str_starts_with($column, 'reporting_')
            || str_starts_with($column, 'manager_');
    }

    public function matchSection(string $label): ?string
    {
        $normalizedLabel = strtolower($label);

        foreach (config('case_file_review.stage_label_keywords', []) as $section => $keywords) {
            foreach ((array) $keywords as $keyword) {
                if ($keyword !== '' && str_contains($normalizedLabel, strtolower((string) $keyword))) {
                    return (string) $section;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overlay
     * @return array<string, mixed>
     */
    public function mergeFillEmptyOnly(array $base, array $overlay): array
    {
        foreach ($overlay as $key => $value) {
            if ($key === 'id' || $key === 'created_at' || $key === 'updated_at') {
                continue;
            }

            if (! array_key_exists($key, $base) || $this->isEmptyCaseFieldValue($base[$key])) {
                if (! $this->isEmptyCaseFieldValue($value)) {
                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }

    public function isEmptyCaseFieldValue(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_bool($value)) {
            return $value === false;
        }

        if (is_numeric($value)) {
            return false;
        }

        return trim((string) $value) === '';
    }

    protected function valueIsPresent(mixed $value): bool
    {
        return ! $this->isEmptyCaseFieldValue($value);
    }

    public function normalizeDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            $short = substr(trim($value), 0, 10);

            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $short) ? $short : null;
        }
    }

    protected function normalizeKey(string $value): string
    {
        $value = strtolower(trim($value));

        return (string) preg_replace('/[^a-z0-9]+/', '_', $value);
    }

    /**
     * @return list<string>
     */
    protected function decodeJsonList(string $raw): array
    {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('strval', $decoded), fn ($v) => $v !== ''));
        }

        return [];
    }
}

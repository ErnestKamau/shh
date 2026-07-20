<?php

namespace App\Services\Qc;

use App\AnalysisMethod;
use App\CapturedResult;
use App\Models\QcModule\QcSchemeBinding;
use App\Models\QcModule\QcSchemeRule;
use App\SampleHeader;
use App\Standards;
use Illuminate\Support\Collection;

class QcSchemeResolver
{
    public function __construct(
        private readonly QcSchemeRuleMerger $ruleMerger,
        private readonly QcBindingConditionEvaluator $conditionEvaluator,
    ) {
    }

    /**
     * Resolve effective QC schemes + merged rule set for a captured result.
     *
     * @return array{
     *     scheme_ids: array<int, string>,
     *     primary_scheme_id: string|null,
     *     source: string,
     *     band_standard_id: string|null,
     *     mode: string|null,
     *     rules: array<string, array{value: string|null, scheme_id?: string, mode?: string, source?: string}>,
     *     repeat_tolerance_percent: float|null
     * }
     */
    public function resolve(
        CapturedResult $capturedResult,
        ?string $batchQcSchemeId = null,
        ?SampleHeader $batch = null,
    ): array {
        $method = $this->resolveMethod($capturedResult);
        $batch ??= $this->resolveBatch($capturedResult);

        $methodBindings = $method
            ? $this->applicableBindingsForMethod((string) $method->id, $capturedResult, $batch)
            : collect();

        $standardId = $method?->based_on_standard_id
            ? (string) $method->based_on_standard_id
            : null;

        $standardBindings = $standardId
            ? $this->applicableBindingsForStandard($standardId, $capturedResult, $batch)
            : collect();

        $standardRules = $standardBindings->isNotEmpty()
            ? $this->ruleMerger->rulesFromBindings($standardBindings)
            : ($standardId ? $this->ruleMerger->rulesForSchemes($this->schemeIdsFromStandardPivot($standardId)) : []);

        $standardSchemeIds = $standardBindings->isNotEmpty()
            ? $this->schemeIdsFromBindings($standardBindings)
            : ($standardId ? $this->schemeIdsFromStandardPivot($standardId) : []);

        if ($methodBindings->isNotEmpty()) {
            $mode = (string) ($methodBindings->first()->mode ?? QcSchemeBinding::MODE_OVERRIDE);
            $methodRules = $this->ruleMerger->rulesFromBindings($methodBindings);
            $methodSchemeIds = $this->schemeIdsFromBindings($methodBindings);

            $effectiveRules = $this->ruleMerger->applyMode($mode, $standardRules, $methodRules);
            $schemeIds = match ($mode) {
                QcSchemeBinding::MODE_ADDITIVE => array_values(array_unique(array_merge($standardSchemeIds, $methodSchemeIds))),
                QcSchemeBinding::MODE_MERGE => $methodSchemeIds !== [] ? $methodSchemeIds : $standardSchemeIds,
                default => $methodSchemeIds,
            };

            return $this->payload(
                schemeIds: $schemeIds,
                primarySchemeId: $schemeIds[0] ?? $batchQcSchemeId,
                source: 'method_binding_'.$mode,
                bandStandardId: $this->resolveBandStandardId($capturedResult, $method),
                mode: $mode,
                rules: $effectiveRules,
            );
        }

        if ($standardBindings->isNotEmpty()) {
            return $this->payload(
                schemeIds: $standardSchemeIds,
                primarySchemeId: $standardSchemeIds[0] ?? $batchQcSchemeId,
                source: 'standard_binding',
                bandStandardId: $this->resolveBandStandardId($capturedResult, $method),
                mode: (string) ($standardBindings->first()->mode ?? QcSchemeBinding::MODE_OVERRIDE),
                rules: $this->ruleMerger->applyMode(
                    QcSchemeBinding::MODE_OVERRIDE,
                    $standardRules,
                    []
                ),
            );
        }

        if ($standardSchemeIds !== []) {
            return $this->payload(
                schemeIds: $standardSchemeIds,
                primarySchemeId: $standardSchemeIds[0] ?? $batchQcSchemeId,
                source: 'standard_pivot',
                bandStandardId: $this->resolveBandStandardId($capturedResult, $method),
                mode: QcSchemeBinding::MODE_OVERRIDE,
                rules: $this->ruleMerger->applyMode(
                    QcSchemeBinding::MODE_OVERRIDE,
                    $standardRules,
                    []
                ),
            );
        }

        return $this->payload(
            schemeIds: [],
            primarySchemeId: $batchQcSchemeId,
            source: 'batch_fallback',
            bandStandardId: $this->resolveBandStandardId($capturedResult, $method),
            mode: null,
            rules: [],
        );
    }

    /**
     * @param  array<string, array{value: string|null, scheme_id?: string, mode?: string, source?: string}>  $rules
     * @param  array<int, string>  $schemeIds
     * @return array{
     *     scheme_ids: array<int, string>,
     *     primary_scheme_id: string|null,
     *     source: string,
     *     band_standard_id: string|null,
     *     mode: string|null,
     *     rules: array<string, array{value: string|null, scheme_id?: string, mode?: string, source?: string}>,
     *     repeat_tolerance_percent: float|null
     * }
     */
    private function payload(
        array $schemeIds,
        ?string $primarySchemeId,
        string $source,
        ?string $bandStandardId,
        ?string $mode,
        array $rules,
    ): array {
        return [
            'scheme_ids' => $schemeIds,
            'primary_scheme_id' => $primarySchemeId,
            'source' => $source,
            'band_standard_id' => $bandStandardId,
            'mode' => $mode,
            'rules' => $rules,
            'repeat_tolerance_percent' => $this->ruleMerger->numericRuleValue(
                $rules,
                QcSchemeRule::TYPE_REPEAT_TOLERANCE_PERCENT
            ),
        ];
    }

    private function resolveMethod(CapturedResult $capturedResult): ?AnalysisMethod
    {
        if (! $capturedResult->method_id) {
            return null;
        }

        if ($capturedResult->relationLoaded('method') && $capturedResult->method) {
            return $capturedResult->method;
        }

        return AnalysisMethod::query()->find($capturedResult->method_id);
    }

    private function resolveBatch(CapturedResult $capturedResult): ?SampleHeader
    {
        if ($capturedResult->relationLoaded('sampleHeader') && $capturedResult->sampleHeader) {
            return $capturedResult->sampleHeader;
        }

        return SampleHeader::query()->find($capturedResult->sample_header_id);
    }

    /**
     * @return Collection<int, QcSchemeBinding>
     */
    private function applicableBindingsForMethod(
        string $methodId,
        CapturedResult $capturedResult,
        ?SampleHeader $batch,
    ): Collection {
        return QcSchemeBinding::query()
            ->where('method_id', $methodId)
            ->whereNull('standard_id')
            ->orderByDesc('priority')
            ->orderBy('created_at')
            ->get()
            ->filter(fn (QcSchemeBinding $binding) => $this->conditionEvaluator->matches(
                $binding->conditions,
                $capturedResult,
                $batch
            ))
            ->values();
    }

    /**
     * @return Collection<int, QcSchemeBinding>
     */
    private function applicableBindingsForStandard(
        string $standardId,
        CapturedResult $capturedResult,
        ?SampleHeader $batch,
    ): Collection {
        return QcSchemeBinding::query()
            ->where('standard_id', $standardId)
            ->whereNull('method_id')
            ->orderByDesc('priority')
            ->orderBy('created_at')
            ->get()
            ->filter(fn (QcSchemeBinding $binding) => $this->conditionEvaluator->matches(
                $binding->conditions,
                $capturedResult,
                $batch
            ))
            ->values();
    }

    /**
     * @param  Collection<int, QcSchemeBinding>  $bindings
     * @return array<int, string>
     */
    private function schemeIdsFromBindings(Collection $bindings): array
    {
        return $bindings
            ->pluck('qc_scheme_id')
            ->map(static fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function schemeIdsFromStandardPivot(string $standardId): array
    {
        $standard = Standards::query()->find($standardId);
        if (! $standard) {
            return [];
        }

        return $standard->qcSchemes()
            ->allRelatedIds()
            ->map(static fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    private function resolveBandStandardId(CapturedResult $capturedResult, ?AnalysisMethod $method): ?string
    {
        $sample = $capturedResult->relationLoaded('sample')
            ? $capturedResult->sample
            : $capturedResult->sample()->first();

        $mainStandardId = $sample->main_standard ?? null;
        if ($mainStandardId && ! (is_numeric($mainStandardId) && (float) $mainStandardId <= 0)) {
            return (string) $mainStandardId;
        }

        if (! $method?->based_on_standard_id) {
            return null;
        }

        $basedOn = Standards::query()->find($method->based_on_standard_id);
        if ($basedOn && (int) $basedOn->is_qc_standard === 1 && (int) $basedOn->status === 1) {
            return (string) $basedOn->id;
        }

        return null;
    }
}

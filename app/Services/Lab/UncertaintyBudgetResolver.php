<?php

namespace App\Services\Lab;

use App\AnalysisElements;
use App\CapturedResult;
use App\UncertaintyBudget;
use Illuminate\Support\Collection;

final class UncertaintyBudgetResolver
{
    /**
     * @param  Collection<int, UncertaintyBudget>|null  $preloaded
     */
    public function resolveForElement(AnalysisElements $element, ?string $companyId = null, ?Collection $preloaded = null): ?UncertaintyBudget
    {
        $analyteId = (string) ($element->analyte_id ?? '');
        if ($analyteId === '') {
            return null;
        }

        $methodId = $this->resolveMethodId($element);
        if ($methodId === '') {
            return null;
        }

        $companyId ??= (string) (getUserCompany() ?? '');

        if ($preloaded !== null) {
            return $this->matchFromCollection($preloaded, $analyteId, $this->resolveMethodIds($element));
        }

        $methodIds = $this->resolveMethodIds($element);

        return UncertaintyBudget::query()
            ->where('analyte_id', $analyteId)
            ->where('active', true)
            ->when($companyId !== '', fn ($query) => $query->where('company_id', $companyId))
            ->where(function ($query) use ($methodIds): void {
                foreach ($methodIds as $methodId) {
                    $query->orWhere('method_ids', 'LIKE', '%'.$methodId.'%');
                }
            })
            ->orderByDesc('version_number')
            ->first();
    }

    /**
     * @param  iterable<int, AnalysisElements>  $elements
     * @return Collection<int, UncertaintyBudget>
     */
    public function preloadForElements(iterable $elements, ?string $companyId = null): Collection
    {
        $companyId ??= (string) (getUserCompany() ?? '');
        $analyteIds = [];
        $methodIds = [];

        foreach ($elements as $element) {
            if (! $element instanceof AnalysisElements) {
                continue;
            }

            $analyteId = (string) ($element->analyte_id ?? '');

            if ($analyteId !== '') {
                $analyteIds[] = $analyteId;
            }

            foreach ($this->resolveMethodIds($element) as $methodId) {
                $methodIds[] = $methodId;
            }
        }

        $analyteIds = array_values(array_unique($analyteIds));
        $methodIds = array_values(array_unique($methodIds));
        if ($analyteIds === []) {
            return collect();
        }

        $query = UncertaintyBudget::query()
            ->whereIn('analyte_id', $analyteIds)
            ->where('active', true);

        if ($companyId !== '') {
            $query->where('company_id', $companyId);
        }

        return $query->orderByDesc('version_number')->get();
    }

    public function formatLoq(AnalysisElements $element): string
    {
        if ($element->lod === null || (float) $element->lod <= 0) {
            return '';
        }

        return rtrim(rtrim(number_format((float) $element->lod, 6, '.', ''), '0'), '.');
    }

    public function formatMuPercent(AnalysisElements $element, ?UncertaintyBudget $budget = null): string
    {
        $budget ??= $this->resolveForElement($element);

        if ($budget !== null && $budget->expanded_uncertainty !== null && (float) $budget->expanded_uncertainty > 0) {
            return rtrim(rtrim(number_format((float) $budget->expanded_uncertainty, 6, '.', ''), '0'), '.');
        }

        if ($element->measurement_uncertainty !== null && (float) $element->measurement_uncertainty > 0) {
            return rtrim(rtrim(number_format((float) $element->measurement_uncertainty, 4, '.', ''), '0'), '.');
        }

        return '';
    }

    public function formatMuPercentForCapturedResult(
        CapturedResult $captured,
        ?Collection $elementsById = null,
        ?Collection $elementsByAnalyteType = null,
        ?Collection $preloadedBudgets = null,
    ): string {
        $manual = trim((string) ($captured->measure_uncertanity ?? ''));
        if ($manual !== '' && $manual !== '0' && (float) $manual > 0) {
            return rtrim(rtrim(number_format((float) $manual, 4, '.', ''), '0'), '.');
        }

        $element = $this->resolveAnalysisElementForCapturedResult(
            $captured,
            $elementsById,
            $elementsByAnalyteType,
        );

        if ($element === null) {
            return '';
        }

        $budget = $this->resolveForElement($element, null, $preloadedBudgets);

        return $this->formatMuPercent($element, $budget);
    }

    /**
     * @param  iterable<int, CapturedResult>  $capturedResults
     * @return array<string, string>
     */
    public function buildMuPercentIndexForCapturedResults(iterable $capturedResults): array
    {
        $results = collect($capturedResults)->filter(fn ($captured): bool => $captured instanceof CapturedResult);
        if ($results->isEmpty()) {
            return [];
        }

        $lookups = $this->preloadElementLookupsForCapturedResults($results);
        $budgets = $this->preloadForElements($lookups['elements']->values());

        $index = [];
        foreach ($results as $captured) {
            $mu = $this->formatMuPercentForCapturedResult(
                $captured,
                $lookups['by_id'],
                $lookups['by_analyte_type'],
                $budgets,
            );

            if ($mu !== '') {
                $index[(string) $captured->id] = $mu;
            }
        }

        return $index;
    }

    /**
     * @param  Collection<int, CapturedResult>  $capturedResults
     * @return array{by_id: Collection<string, AnalysisElements>, by_analyte_type: Collection<string, AnalysisElements>, elements: Collection<string, AnalysisElements>}
     */
    private function preloadElementLookupsForCapturedResults(Collection $capturedResults): array
    {
        $elementIds = [];
        $fallbackPairs = [];

        foreach ($capturedResults as $captured) {
            $elementId = (string) ($captured->analysis_element_id ?? '');
            if ($elementId !== '') {
                $elementIds[] = $elementId;

                continue;
            }

            $analyteId = (string) ($captured->analyte_id ?? '');
            $analysisTypeId = (string) ($captured->analysis_type_id ?? '');
            if ($analyteId !== '' && $analysisTypeId !== '') {
                $fallbackPairs[$analyteId.'|'.$analysisTypeId] = [
                    'analyte_id' => $analyteId,
                    'analysis_type_id' => $analysisTypeId,
                ];
            }
        }

        $elements = collect();

        if ($elementIds !== []) {
            $elements = AnalysisElements::query()
                ->whereIn('id', array_values(array_unique($elementIds)))
                ->get()
                ->keyBy('id');
        }

        $byAnalyteType = collect();
        if ($fallbackPairs !== []) {
            $fallbackElements = AnalysisElements::query()
                ->where('active', 1)
                ->where(function ($query) use ($fallbackPairs): void {
                    foreach ($fallbackPairs as $pair) {
                        $query->orWhere(function ($subQuery) use ($pair): void {
                            $subQuery
                                ->where('analyte_id', $pair['analyte_id'])
                                ->where('analysis_type_id', $pair['analysis_type_id']);
                        });
                    }
                })
                ->get();

            foreach ($fallbackElements as $element) {
                $pairKey = (string) $element->analyte_id.'|'.(string) $element->analysis_type_id;
                $byAnalyteType->put($pairKey, $element);
                if (! $elements->has((string) $element->id)) {
                    $elements->put((string) $element->id, $element);
                }
            }
        }

        return [
            'by_id' => $elements,
            'by_analyte_type' => $byAnalyteType,
            'elements' => $elements,
        ];
    }

    private function resolveAnalysisElementForCapturedResult(
        CapturedResult $captured,
        ?Collection $elementsById = null,
        ?Collection $elementsByAnalyteType = null,
    ): ?AnalysisElements {
        $elementId = (string) ($captured->analysis_element_id ?? '');
        if ($elementId !== '') {
            if ($elementsById !== null) {
                return $elementsById->get($elementId);
            }

            return AnalysisElements::query()->find($elementId);
        }

        if ($captured->relationLoaded('analysisElement') && $captured->analysisElement !== null) {
            return $captured->analysisElement;
        }

        $analyteId = (string) ($captured->analyte_id ?? '');
        $analysisTypeId = (string) ($captured->analysis_type_id ?? '');
        if ($analyteId === '' || $analysisTypeId === '') {
            return null;
        }

        $pairKey = $analyteId.'|'.$analysisTypeId;
        if ($elementsByAnalyteType !== null) {
            return $elementsByAnalyteType->get($pairKey);
        }

        return AnalysisElements::query()
            ->where('analyte_id', $analyteId)
            ->where('analysis_type_id', $analysisTypeId)
            ->where('active', 1)
            ->first();
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    public function enrichLinesWithLabMetrics(array $lines): array
    {
        $elementIds = collect($lines)
            ->pluck('analysis_element_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        if ($elementIds === []) {
            return array_map(function (array $line): array {
                $line['loq'] = '';
                $line['mu_percent'] = '';

                return $line;
            }, $lines);
        }

        $elements = AnalysisElements::query()
            ->whereIn('id', $elementIds)
            ->get()
            ->keyBy('id');

        $budgets = $this->preloadForElements($elements->values());

        return array_map(function (array $line) use ($elements, $budgets): array {
            $elementId = (string) ($line['analysis_element_id'] ?? '');
            $element = $elementId !== '' ? $elements->get($elementId) : null;

            if ($element === null) {
                $line['loq'] = '';
                $line['mu_percent'] = '';

                return $line;
            }

            $budget = $this->resolveForElement($element, null, $budgets);
            $line['loq'] = $this->formatLoq($element);
            $line['mu_percent'] = $this->formatMuPercent($element, $budget);

            return $line;
        }, $lines);
    }

    /**
     * @return list<string>
     */
    private function resolveMethodIds(AnalysisElements $element): array
    {
        $ids = [];

        $ltm = (string) ($element->ltm_method_id ?? '');
        if ($ltm !== '' && $ltm !== '0') {
            $ids[] = $ltm;
        }

        $method = (string) ($element->method ?? '');
        if ($method !== '' && $method !== '0' && ! in_array($method, $ids, true)) {
            $ids[] = $method;
        }

        return $ids;
    }

    private function resolveMethodId(AnalysisElements $element): string
    {
        $ids = $this->resolveMethodIds($element);

        return $ids[0] ?? '';
    }

    /**
     * @param  Collection<int, UncertaintyBudget>  $budgets
     * @param  list<string>  $methodIds
     */
    private function matchFromCollection(Collection $budgets, string $analyteId, array $methodIds): ?UncertaintyBudget
    {
        return $budgets
            ->filter(fn (UncertaintyBudget $budget): bool => (string) $budget->analyte_id === $analyteId
                && $this->methodIdsMatchAny((string) $budget->method_ids, $methodIds))
            ->sortByDesc('version_number')
            ->first();
    }

    /**
     * @param  list<string>  $candidateMethodIds
     */
    private function methodIdsMatchAny(string $methodIds, array $candidateMethodIds): bool
    {
        foreach ($candidateMethodIds as $methodId) {
            if ($this->methodIdsContain($methodIds, $methodId)) {
                return true;
            }
        }

        return false;
    }

    private function methodIdsContain(string $methodIds, string $methodId): bool
    {
        if ($methodId === '') {
            return false;
        }

        foreach (array_filter(array_map('trim', explode(',', $methodIds))) as $id) {
            if ($id === $methodId) {
                return true;
            }
        }

        return str_contains($methodIds, $methodId);
    }
}

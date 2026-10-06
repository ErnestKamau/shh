<?php

namespace App\Services\Lab;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\CapturedResult;
use App\UncertaintyBudget;
use Illuminate\Support\Collection;

final class UncertaintyBudgetResolver
{
    /**
     * @param  Collection<int, UncertaintyBudget>|null  $preloaded
     * @param  Collection<string, Collection<int, AnalysisElements>>|null  $siblingsByAnalyte
     */
    public function resolveForElement(
        AnalysisElements $element,
        ?string $companyId = null,
        ?Collection $preloaded = null,
        ?Collection $siblingsByAnalyte = null,
    ): ?UncertaintyBudget {
        $metricsElement = $this->resolveMetricsElement($element, $siblingsByAnalyte);
        $analyteId = (string) ($metricsElement->analyte_id ?? '');
        if ($analyteId === '') {
            return null;
        }

        $methodIds = $this->resolveMethodIds($metricsElement);
        $companyId ??= (string) (getUserCompany() ?? '');

        if ($preloaded !== null) {
            if ($methodIds !== []) {
                $match = $this->matchFromCollection($preloaded, $analyteId, $methodIds);
                if ($match !== null) {
                    return $match;
                }
            }

            return $preloaded
                ->filter(fn (UncertaintyBudget $budget): bool => (string) $budget->analyte_id === $analyteId)
                ->sortByDesc('version_number')
                ->first();
        }

        if ($methodIds !== []) {
            $match = UncertaintyBudget::query()
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

            if ($match !== null) {
                return $match;
            }
        }

        return UncertaintyBudget::query()
            ->where('analyte_id', $analyteId)
            ->where('active', true)
            ->when($companyId !== '', fn ($query) => $query->where('company_id', $companyId))
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

    public function formatLoq(AnalysisElements $element, ?Collection $siblingsByAnalyte = null): string
    {
        $source = $this->resolveMetricsElement($element, $siblingsByAnalyte);
        $value = $source->hod ?? $source->lod;

        if ($value === null || (float) $value <= 0) {
            return '';
        }

        return rtrim(rtrim(number_format((float) $value, 6, '.', ''), '0'), '.');
    }

    public function formatMuPercent(
        AnalysisElements $element,
        ?UncertaintyBudget $budget = null,
        ?Collection $siblingsByAnalyte = null,
    ): string {
        $metricsElement = $this->resolveMetricsElement($element, $siblingsByAnalyte);
        $budget ??= $this->resolveForElement($metricsElement, null, null, $siblingsByAnalyte);

        if ($budget !== null && $budget->expanded_uncertainty !== null && (float) $budget->expanded_uncertainty > 0) {
            return rtrim(rtrim(number_format((float) $budget->expanded_uncertainty, 6, '.', ''), '0'), '.');
        }

        if ($metricsElement->measurement_uncertainty !== null && (float) $metricsElement->measurement_uncertainty > 0) {
            return rtrim(rtrim(number_format((float) $metricsElement->measurement_uncertainty, 4, '.', ''), '0'), '.');
        }

        return '';
    }

    public function formatTestMethod(AnalysisElements $element, ?Collection $siblingsByAnalyte = null, bool $preferMethodCode = false): string
    {
        $metricsElement = $this->resolveMetricsElement($element, $siblingsByAnalyte);
        $metricsElement->loadMissing(['ltmethod', 'mmethod', 'methodSequence']);

        $format = static function (?object $method) use ($preferMethodCode): string {
            if ($method === null) {
                return '';
            }

            $code = trim((string) ($method->code ?? ''));
            $name = trim((string) ($method->name ?? ''));
            $normalize = static fn (string $value): string => strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $value));

            if ($preferMethodCode && $code !== '') {
                // Treat AMS_M_SOP_047 and AMS/M/SOP/047 as the same label.
                $codeKey = $normalize($code);
                $nameKey = $normalize($name);
                $nameAlreadyIncludesCode = $nameKey !== ''
                    && $codeKey !== ''
                    && (str_contains($nameKey, $codeKey) || str_contains($codeKey, $nameKey));

                if ($name !== '' && $codeKey !== $nameKey && ! $nameAlreadyIncludesCode) {
                    return $code.' ('.$name.')';
                }

                return $code;
            }

            return $name !== '' ? $name : $code;
        };

        if ($metricsElement->ltmethod) {
            $formatted = $format($metricsElement->ltmethod);
            if ($formatted !== '') {
                return $formatted;
            }
        }

        if ($metricsElement->mmethod) {
            $formatted = $format($metricsElement->mmethod);
            if ($formatted !== '') {
                return $formatted;
            }
        }

        if (! empty($metricsElement->method)) {
            $method = AnalysisMethod::query()->find($metricsElement->method);
            $formatted = $format($method);
            if ($formatted !== '') {
                return $formatted;
            }
        }

        if ($metricsElement->methodSequence) {
            return (string) $metricsElement->methodSequence->name;
        }

        return '';
    }

    /**
     * @return array{loq: string, mu_percent: string, test_method: string}
     */
    public function resolveLabMetricsForElement(
        AnalysisElements $element,
        ?Collection $budgets = null,
        ?Collection $siblingsByAnalyte = null,
        bool $preferMethodCode = false,
    ): array {
        $metricsElement = $this->resolveMetricsElement($element, $siblingsByAnalyte);
        $budgets ??= $this->preloadForElements(collect([$element, $metricsElement])->unique('id')->values());
        $budget = $this->resolveForElement($metricsElement, null, $budgets, $siblingsByAnalyte);

        return [
            'loq' => $this->formatLoq($metricsElement),
            'mu_percent' => $this->formatMuPercent($metricsElement, $budget, $siblingsByAnalyte),
            'test_method' => $this->formatTestMethod($metricsElement, $siblingsByAnalyte, $preferMethodCode),
        ];
    }

    /**
     * @param  Collection<string, Collection<int, AnalysisElements>>|null  $siblingsByAnalyte
     */
    public function resolveMetricsElement(AnalysisElements $element, ?Collection $siblingsByAnalyte = null): AnalysisElements
    {
        if ($this->elementHasLabMetrics($element)) {
            return $element;
        }

        $analyteId = (string) ($element->analyte_id ?? '');
        if ($analyteId === '') {
            return $element;
        }

        $siblings = $siblingsByAnalyte?->get($analyteId) ?? $this->preloadActiveElementsByAnalyteIds([$analyteId])->get($analyteId, collect());
        $candidates = $siblings->filter(fn (AnalysisElements $candidate): bool => $this->elementHasLabMetrics($candidate));

        $analysisTypeId = (string) ($element->analysis_type_id ?? '');
        if ($analysisTypeId !== '') {
            $sameType = $candidates->first(
                fn (AnalysisElements $candidate): bool => (string) $candidate->analysis_type_id === $analysisTypeId
            );
            if ($sameType !== null) {
                return $sameType;
            }
        }

        $match = $candidates->first();

        return $match ?? $element;
    }

    /**
     * @param  list<string>  $analyteIds
     * @return Collection<string, Collection<int, AnalysisElements>>
     */
    public function preloadActiveElementsByAnalyteIds(array $analyteIds): Collection
    {
        $analyteIds = array_values(array_unique(array_filter($analyteIds)));
        if ($analyteIds === []) {
            return collect();
        }

        return AnalysisElements::query()
            ->with(['ltmethod', 'mmethod', 'methodSequence'])
            ->whereIn('analyte_id', $analyteIds)
            ->where('active', 1)
            ->get()
            ->groupBy('analyte_id');
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
            ->flatMap(function (array $line): array {
                $ids = [];

                $elementId = (string) ($line['analysis_element_id'] ?? '');
                if ($elementId !== '') {
                    $ids[] = $elementId;
                }

                if (! empty($line['is_package']) && is_array($line['package_element_ids'] ?? null)) {
                    foreach ($line['package_element_ids'] as $packageElementId) {
                        $packageElementId = trim((string) $packageElementId);
                        if ($packageElementId !== '') {
                            $ids[] = $packageElementId;
                        }
                    }
                }

                return $ids;
            })
            ->unique()
            ->values()
            ->all();

        if ($elementIds === []) {
            return array_map(function (array $line): array {
                $line['loq'] = '';
                $line['mu_percent'] = '';
                $line['test_method'] = '';
                if (! empty($line['is_package'])) {
                    $line['package_element_metrics'] = [];
                }

                return $line;
            }, $lines);
        }

        $elements = AnalysisElements::query()
            ->with(['analyte:id,name', 'ltmethod', 'mmethod', 'methodSequence'])
            ->whereIn('id', $elementIds)
            ->get()
            ->keyBy('id');

        $analyteIds = $elements->pluck('analyte_id')->filter()->map(fn ($id) => (string) $id)->unique()->values()->all();
        $siblingsByAnalyte = $this->preloadActiveElementsByAnalyteIds($analyteIds);
        $budgetElements = $elements->values();
        foreach ($siblingsByAnalyte as $siblings) {
            $budgetElements = $budgetElements->merge($siblings);
        }
        $budgets = $this->preloadForElements($budgetElements->unique('id')->values());

        return array_map(function (array $line) use ($elements, $budgets, $siblingsByAnalyte): array {
            if (! empty($line['is_package'])) {
                $line['loq'] = '';
                $line['mu_percent'] = '';
                $line['test_method'] = '';
                $line['package_element_metrics'] = $this->buildPackageElementMetrics(
                    $line,
                    $elements,
                    $budgets,
                    $siblingsByAnalyte,
                );

                return $line;
            }

            $elementId = (string) ($line['analysis_element_id'] ?? '');
            $element = $elementId !== '' ? $elements->get($elementId) : null;

            if ($element === null) {
                $line['loq'] = '';
                $line['mu_percent'] = '';
                $line['test_method'] = '';

                return $line;
            }

            $metrics = $this->resolveLabMetricsForElement($element, $budgets, $siblingsByAnalyte);
            $line['loq'] = $metrics['loq'];
            $line['mu_percent'] = $metrics['mu_percent'];
            $line['test_method'] = $metrics['test_method'];

            return $line;
        }, $lines);
    }

    /**
     * @param  array<string, mixed>  $line
     * @param  Collection<string, AnalysisElements>  $elements
     * @param  Collection<string, mixed>|null  $budgets
     * @param  Collection<string, Collection<int, AnalysisElements>>|null  $siblingsByAnalyte
     * @return list<array{id: string, label: string, loq: string, mu_percent: string, test_method: string}>
     */
    private function buildPackageElementMetrics(
        array $line,
        Collection $elements,
        ?Collection $budgets,
        ?Collection $siblingsByAnalyte,
    ): array {
        $packageElementIds = is_array($line['package_element_ids'] ?? null)
            ? array_values(array_filter(array_map(
                static fn ($id): string => trim((string) $id),
                $line['package_element_ids'],
            )))
            : [];

        $metrics = [];
        foreach ($packageElementIds as $packageElementId) {
            $element = $elements->get($packageElementId);
            if ($element === null) {
                continue;
            }

            $labMetrics = $this->resolveLabMetricsForElement($element, $budgets, $siblingsByAnalyte);
            $metrics[] = [
                'id' => (string) $element->id,
                'label' => (string) (
                    $element->analyte?->name
                    ?? (trim((string) ($element->parametername ?? '')) !== ''
                        ? (string) $element->parametername
                        : 'Parameter')
                ),
                'loq' => $labMetrics['loq'],
                'mu_percent' => $labMetrics['mu_percent'],
                'test_method' => $labMetrics['test_method'],
            ];
        }

        return $metrics;
    }

    private function elementHasLabMetrics(AnalysisElements $element): bool
    {
        if (($element->hod !== null && (float) $element->hod > 0)
            || ($element->lod !== null && (float) $element->lod > 0)) {
            return true;
        }

        if ($element->measurement_uncertainty !== null && (float) $element->measurement_uncertainty > 0) {
            return true;
        }

        if ($this->resolveMethodId($element) !== '') {
            return true;
        }

        return ! empty($element->method_sequence_id);
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

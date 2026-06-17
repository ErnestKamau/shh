<?php

namespace App\Services\Lab;

use App\AnalysisElements;
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
            return $this->matchFromCollection($preloaded, $analyteId, $methodId);
        }

        return UncertaintyBudget::query()
            ->where('analyte_id', $analyteId)
            ->where('active', true)
            ->when($companyId !== '', fn ($query) => $query->where('company_id', $companyId))
            ->where(function ($query) use ($methodId): void {
                $query->where('method_ids', 'LIKE', '%'.$methodId.'%');
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
            $methodId = $this->resolveMethodId($element);

            if ($analyteId !== '') {
                $analyteIds[] = $analyteId;
            }

            if ($methodId !== '') {
                $methodIds[] = $methodId;
            }
        }

        $analyteIds = array_values(array_unique($analyteIds));
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

    private function resolveMethodId(AnalysisElements $element): string
    {
        $ltm = (string) ($element->ltm_method_id ?? '');
        if ($ltm !== '' && $ltm !== '0') {
            return $ltm;
        }

        return (string) ($element->method ?? '');
    }

    /**
     * @param  Collection<int, UncertaintyBudget>  $budgets
     */
    private function matchFromCollection(Collection $budgets, string $analyteId, string $methodId): ?UncertaintyBudget
    {
        return $budgets
            ->filter(fn (UncertaintyBudget $budget): bool => (string) $budget->analyte_id === $analyteId
                && $this->methodIdsContain((string) $budget->method_ids, $methodId))
            ->sortByDesc('version_number')
            ->first();
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

<?php

namespace App\Services\Billing;

use App\AnalysisElements;
use App\AnalysisType;
use App\QuotationHeader;
use App\SampleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Restricts quotation test selection to SampleAnalysisStage lab sections
 * linked on the quotation header (quotation_header_lab_sections).
 *
 * When a quotation has no lab sections, no restriction is applied (legacy quotes).
 */
final class QuotationLabSectionScope
{
    /**
     * @return list<string>
     */
    public function allowedLabSectionIds(QuotationHeader $header): array
    {
        $header->loadMissing('labSections');

        return $header->labSections
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->filter(static fn (string $id): bool => $id !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function restricts(QuotationHeader $header): bool
    {
        return $this->allowedLabSectionIds($header) !== [];
    }

    /**
     * @param  Builder<AnalysisType>  $query
     * @return Builder<AnalysisType>
     */
    public function constrainAnalysisTypes(Builder $query, QuotationHeader $header): Builder
    {
        $sectionIds = $this->allowedLabSectionIds($header);
        if ($sectionIds === []) {
            return $query;
        }

        return $query->whereIn('lab_section_id', $sectionIds);
    }

    /**
     * @param  Builder<AnalysisElements>  $query
     * @return Builder<AnalysisElements>
     */
    public function constrainAnalysisElements(Builder $query, QuotationHeader $header): Builder
    {
        $sectionIds = $this->allowedLabSectionIds($header);
        if ($sectionIds === []) {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($sectionIds): void {
            $inner->whereIn('lab_section_id', $sectionIds)
                ->orWhereNull('lab_section_id')
                ->orWhere('lab_section_id', '');
        })->whereHas('analysis_type', function (Builder $typeQuery) use ($sectionIds): void {
            $typeQuery->whereIn('lab_section_id', $sectionIds);
        });
    }

    /**
     * Sample types that have at least one analysis type in the quotation's lab sections.
     *
     * @return Collection<int, SampleType>
     */
    public function sampleTypesForQuotation(QuotationHeader $header, bool $activeOnly = true): Collection
    {
        $query = SampleType::query();
        if ($activeOnly) {
            $query->where('active', 1);
        }

        $sectionIds = $this->allowedLabSectionIds($header);
        if ($sectionIds === []) {
            return $query->orderBy('name')->get();
        }

        return $query
            ->whereHas('analysis_types', function (Builder $typeQuery) use ($sectionIds): void {
                $typeQuery->whereIn('lab_section_id', $sectionIds);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  list<string>  $analysisTypeIds
     */
    public function assertAnalysisTypesAllowed(QuotationHeader $header, array $analysisTypeIds): void
    {
        $sectionIds = $this->allowedLabSectionIds($header);
        if ($sectionIds === [] || $analysisTypeIds === []) {
            return;
        }

        $analysisTypeIds = array_values(array_unique(array_filter(array_map(
            static fn ($id): string => trim((string) $id),
            $analysisTypeIds,
        ))));

        if ($analysisTypeIds === []) {
            return;
        }

        $disallowed = AnalysisType::query()
            ->whereIn('id', $analysisTypeIds)
            ->where(function (Builder $query) use ($sectionIds): void {
                $query->whereNull('lab_section_id')
                    ->orWhere('lab_section_id', '')
                    ->orWhereNotIn('lab_section_id', $sectionIds);
            })
            ->orderBy('name')
            ->pluck('name')
            ->all();

        if ($disallowed !== []) {
            throw new RuntimeException(
                'These analysis types are outside this quotation\'s lab section(s): '
                .implode(', ', $disallowed)
                .'. Update the quotation lab sections or choose tests from the assigned section(s).'
            );
        }
    }

    /**
     * @param  list<string>  $elementIds
     */
    public function assertElementsAllowed(QuotationHeader $header, array $elementIds): void
    {
        $sectionIds = $this->allowedLabSectionIds($header);
        if ($sectionIds === [] || $elementIds === []) {
            return;
        }

        $elementIds = array_values(array_unique(array_filter(array_map(
            static fn ($id): string => trim((string) $id),
            $elementIds,
        ))));

        if ($elementIds === []) {
            return;
        }

        $elements = AnalysisElements::query()
            ->with('analysis_type')
            ->whereIn('id', $elementIds)
            ->get();

        $disallowed = [];
        foreach ($elements as $element) {
            $typeSectionId = trim((string) ($element->analysis_type?->lab_section_id ?? ''));
            if ($typeSectionId === '' || ! in_array($typeSectionId, $sectionIds, true)) {
                $disallowed[] = (string) ($element->analyte?->name ?? $element->id);
            }
        }

        if ($disallowed !== []) {
            throw new RuntimeException(
                'Some selected parameters are outside this quotation\'s lab section(s).'
            );
        }
    }
}

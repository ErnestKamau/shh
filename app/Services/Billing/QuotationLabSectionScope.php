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
 * Restricts quotation parameter (test) selection to SampleAnalysisStage lab sections
 * linked on the quotation header (quotation_header_lab_sections).
 *
 * Sample types and analysis types stay unrestricted so analysts can browse the full
 * catalogue; only analysis elements (tests/parameters) are filtered by lab section.
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
     * Analysis types are not restricted by quotation lab sections.
     *
     * @param  Builder<AnalysisType>  $query
     * @return Builder<AnalysisType>
     */
    public function constrainAnalysisTypes(Builder $query, QuotationHeader $header): Builder
    {
        return $query;
    }

    /**
     * Keep only analysis elements whose effective lab section is on the quotation.
     * Effective section = element.lab_section_id, else analysis_type.lab_section_id.
     * Elements with no resolvable lab section are excluded when the quote has sections.
     *
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
                ->orWhere(function (Builder $fallback) use ($sectionIds): void {
                    // lab_section_id is UUID on pgsql — never compare to ''.
                    $fallback->whereNull('lab_section_id')
                        ->whereHas('analysis_type', function (Builder $typeQuery) use ($sectionIds): void {
                            $typeQuery->whereIn('lab_section_id', $sectionIds);
                        });
                });
        });
    }

    /**
     * All active sample types (lab sections do not filter the sample type picker).
     *
     * @return Collection<int, SampleType>
     */
    public function sampleTypesForQuotation(QuotationHeader $header, bool $activeOnly = true): Collection
    {
        $query = SampleType::query();
        if ($activeOnly) {
            $query->where('active', 1);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Analysis types are not restricted by quotation lab sections.
     *
     * @param  list<string>  $analysisTypeIds
     */
    public function assertAnalysisTypesAllowed(QuotationHeader $header, array $analysisTypeIds): void
    {
        // Intentionally no-op: scope applies to tests/parameters only.
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
            if (! $this->elementAllowedForSections($element, $sectionIds)) {
                $disallowed[] = (string) ($element->analyte?->name ?? $element->id);
            }
        }

        if ($disallowed !== []) {
            throw new RuntimeException(
                'Some selected parameters are outside this quotation\'s lab section(s): '
                .implode(', ', array_slice($disallowed, 0, 8))
                .(count($disallowed) > 8 ? '…' : '')
                .'. Update the quotation lab sections or choose tests from the assigned section(s).'
            );
        }
    }

    /**
     * Filter a list of element IDs down to those allowed for the quotation's lab sections.
     *
     * @param  list<string>  $elementIds
     * @return list<string>
     */
    public function filterAllowedElementIds(QuotationHeader $header, array $elementIds): array
    {
        $sectionIds = $this->allowedLabSectionIds($header);
        if ($sectionIds === [] || $elementIds === []) {
            return array_values(array_unique(array_filter(array_map(
                static fn ($id): string => trim((string) $id),
                $elementIds,
            ))));
        }

        $elementIds = array_values(array_unique(array_filter(array_map(
            static fn ($id): string => trim((string) $id),
            $elementIds,
        ))));

        if ($elementIds === []) {
            return [];
        }

        $elements = AnalysisElements::query()
            ->with('analysis_type')
            ->whereIn('id', $elementIds)
            ->get()
            ->keyBy(static fn (AnalysisElements $element): string => (string) $element->id);

        $allowed = [];
        foreach ($elementIds as $elementId) {
            $element = $elements->get($elementId);
            if ($element !== null && $this->elementAllowedForSections($element, $sectionIds)) {
                $allowed[] = $elementId;
            }
        }

        return $allowed;
    }

    /**
     * @param  list<string>  $sectionIds
     */
    private function elementAllowedForSections(AnalysisElements $element, array $sectionIds): bool
    {
        $elementSectionId = trim((string) ($element->lab_section_id ?? ''));
        if ($elementSectionId !== '') {
            return in_array($elementSectionId, $sectionIds, true);
        }

        $typeSectionId = trim((string) ($element->analysis_type?->lab_section_id ?? ''));

        return $typeSectionId !== '' && in_array($typeSectionId, $sectionIds, true);
    }
}

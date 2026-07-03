<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormSection;
use Illuminate\Support\Collection;

/**
 * Resolves canonical TRF / submission-form structure when duplicate sections
 * or elements exist in the database (e.g. from repeated seeding).
 */
final class SubmissionFormSchemaHelper
{
    /**
     * @return list<string>
     */
    public static function miscellaneousTrfFieldNames(): array
    {
        return [
            'packaging',
            'sample_weight',
            'sample_information',
            'ship_name',
            'port_of_loading',
            'port_of_discharge',
            'seal_number',
        ];
    }

    public static function isMiscellaneousFieldInCollectionSection(
        SubmissionFormSection $section,
        SubmissionFormElement $element,
    ): bool {
        return mb_strtolower(trim((string) ($section->title ?? ''))) === 'sample collection data'
            && in_array((string) ($element->name ?? ''), self::miscellaneousTrfFieldNames(), true);
    }

    /**
     * Internal TRF fields stored for integration but not shown on capture/read-only views.
     *
     * @return list<string>
     */
    public static function hiddenInternalTrfFieldNames(): array
    {
        return [
            'job_number',
            'crm_contact_id',
            'customer_tax_id',
        ];
    }

    public static function shouldHideFromTrfDisplay(SubmissionFormElement $element): bool
    {
        return in_array((string) ($element->name ?? ''), self::hiddenInternalTrfFieldNames(), true);
    }

    /**
     * Legacy row fields superseded by a canonical column in the same holder.
     *
     * @return array<string, string>
     */
    public static function supersededTrfRowFieldNames(): array
    {
        return [
            'number_of_samples' => 'sample_quantity',
        ];
    }

    /**
     * @param  iterable<int, SubmissionFormElement>  $holderElements
     */
    public static function shouldHideSupersededRowField(
        SubmissionFormElement $element,
        iterable $holderElements,
    ): bool {
        $name = (string) ($element->name ?? '');
        $replacement = self::supersededTrfRowFieldNames()[$name] ?? null;

        if ($replacement === null) {
            return false;
        }

        foreach ($holderElements as $other) {
            if ((string) ($other->name ?? '') === $replacement) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, SubmissionFormSection>
     */
    public function uniqueSections(SubmissionForm $form): Collection
    {
        $form->loadMissing(['sections.elementHolders.elements']);

        /** @var array<string, SubmissionFormSection> $bestByKey */
        $bestByKey = [];

        foreach ($form->sections->sortBy('sort_order') as $section) {
            $key = ($section->sort_order ?? 0).'|'.mb_strtolower(trim((string) ($section->title ?? '')));

            if (! isset($bestByKey[$key]) || $this->sectionScore($section) > $this->sectionScore($bestByKey[$key])) {
                $bestByKey[$key] = $section;
            }
        }

        return collect($bestByKey)
            ->sortBy(fn (SubmissionFormSection $section): int => (int) ($section->sort_order ?? 0))
            ->values();
    }

    private function sectionScore(SubmissionFormSection $section): int
    {
        $elements = $section->elementHolders
            ->flatMap(fn ($holder) => $holder->elements);

        $elementCount = $elements->count();
        $updatedAt = $section->updated_at?->getTimestamp() ?? 0;

        $miscPenalty = 0;
        if (mb_strtolower(trim((string) ($section->title ?? ''))) === 'sample collection data') {
            $miscCount = $elements
                ->filter(fn (SubmissionFormElement $element): bool => in_array(
                    (string) ($element->name ?? ''),
                    self::miscellaneousTrfFieldNames(),
                    true,
                ))
                ->count();

            if ($miscCount > 0) {
                $miscPenalty = 5_000_000_000 + ($miscCount * 1_000_000);
            }
        }

        return ($elementCount * 1_000_000) + $updatedAt - $miscPenalty;
    }

    /**
     * @return Collection<int, SubmissionFormElement>
     */
    public function uniqueElements(SubmissionForm $form): Collection
    {
        /** @var array<string, array{element: SubmissionFormElement, section: SubmissionFormSection}> $bestByName */
        $bestByName = [];

        foreach ($this->uniqueSections($form) as $section) {
            $section->loadMissing(['elementHolders.elements']);

            foreach ($section->elementHolders->sortBy('sort_order') as $holder) {
                foreach ($holder->elements->sortBy('sort_order') as $element) {
                    $name = trim((string) ($element->name ?? ''));
                    if ($name === '') {
                        continue;
                    }

                    $candidateScore = $this->elementScore($element, $section);
                    $currentScore = isset($bestByName[$name])
                        ? $this->elementScore($bestByName[$name]['element'], $bestByName[$name]['section'])
                        : -1;

                    if ($candidateScore > $currentScore) {
                        $bestByName[$name] = [
                            'element' => $element,
                            'section' => $section,
                        ];
                    }
                }
            }
        }

        return collect($bestByName)
            ->map(fn (array $entry): SubmissionFormElement => $entry['element'])
            ->sortBy(function (SubmissionFormElement $element): int {
                $sectionOrder = (int) ($element->holder?->section?->sort_order ?? 0);
                $holderOrder = (int) ($element->holder?->sort_order ?? 0);
                $elementOrder = (int) ($element->sort_order ?? 0);

                return ($sectionOrder * 1_000_000) + ($holderOrder * 1_000) + $elementOrder;
            })
            ->values();
    }

    private function elementScore(SubmissionFormElement $element, SubmissionFormSection $section): int
    {
        $sectionOrder = (int) ($section->sort_order ?? 0);
        $holderOrder = (int) ($element->holder?->sort_order ?? 0);
        $elementOrder = (int) ($element->sort_order ?? 0);
        $updatedAt = $element->updated_at?->getTimestamp() ?? 0;

        return ($sectionOrder * 1_000_000_000)
            + ($holderOrder * 1_000_000)
            + ($elementOrder * 1_000)
            + $updatedAt;
    }
}

<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstanceValue;
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
     * Builder-hidden sections are omitted from fill/preview/portal schemas,
     * but remain available in the form builder and for historical instance data.
     */
    public static function isBuilderHiddenSection(SubmissionFormSection $section): bool
    {
        return (bool) ($section->is_hidden ?? false);
    }

    /**
     * Builder-hidden elements are omitted from fill/preview/portal schemas,
     * but remain available in the form builder and for historical instance data.
     */
    public static function isBuilderHiddenElement(SubmissionFormElement $element): bool
    {
        if ((bool) ($element->is_hidden ?? false)) {
            return true;
        }

        $section = $element->holder?->section;
        if ($section instanceof SubmissionFormSection) {
            return self::isBuilderHiddenSection($section);
        }

        return false;
    }

    /**
     * Whether an element should be skipped when rendering a live fill/preview form.
     */
    public static function shouldOmitFromFillForm(
        SubmissionFormElement $element,
        ?SubmissionFormSection $section = null,
    ): bool {
        if ($section instanceof SubmissionFormSection && self::isBuilderHiddenSection($section)) {
            return true;
        }

        return self::isBuilderHiddenElement($element);
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
     * Field names that define physical sample-card rows (not multi-select expansions).
     *
     * @return list<string>
     */
    public static function sampleRowAnchorFieldNames(): array
    {
        return [
            'sample_description',
            'sample_quantity',
            'sample_quantity_unit',
            'sampling_point',
            'location',
            'batch_number',
            'state_of_sample',
            'production_date',
            'expiration_date',
            'sample_condition',
            'picture_of_samples',
            'picture_of_sample',
        ];
    }

    /**
     * Multi-select row fields that must never inflate sample-card count when stored flat.
     *
     * @return list<string>
     */
    public static function sampleRowMultiSelectFieldNames(): array
    {
        return [
            'parameters',
            'parameter',
            'analysis_type_id',
            'analysis_type',
            'analysis_types',
            'sample_type_id',
            'sample_type',
        ];
    }

    /**
     * Rows section, or a regular section that holds sample-line fields (manual/unlinked TRFs).
     */
    public static function sectionUsesSampleCards(SubmissionFormSection $section): bool
    {
        if (($section->section_type ?? '') === 'rows_section') {
            return true;
        }

        $section->loadMissing('elementHolders.elements');

        foreach ($section->elementHolders->flatMap->elements as $element) {
            $name = strtolower(trim((string) ($element->name ?? '')));
            $type = (string) ($element->element_type ?? '');

            if (in_array($type, ['sample_type_select', 'analysis_type_select', 'analysis_elements_select'], true)) {
                return true;
            }

            if (in_array($name, [
                'sample_type_id',
                'sample_type',
                'analysis_type_id',
                'analysis_type',
                'analysis_types',
                'parameters',
                'parameter',
                'sample_quantity',
                'number_of_samples',
                'sample_description',
            ], true)) {
                return true;
            }
        }

        return false;
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

    /**
     * Keep one section per (sort_order + title), migrate instance values onto the
     * kept elements by field name, then delete duplicate sections.
     *
     * @return array{kept: int, removed: int, migrated_values: int}
     */
    public function deduplicateSections(SubmissionForm $form): array
    {
        $form->load(['sections.elementHolders.elements']);

        $keepers = $this->uniqueSections($form);
        $keeperIds = $keepers->pluck('id')->all();

        $duplicates = $form->sections
            ->reject(fn (SubmissionFormSection $section): bool => in_array($section->id, $keeperIds, true))
            ->values();

        $migrated = 0;
        $removed = 0;

        foreach ($duplicates as $duplicate) {
            $keeper = $keepers->first(function (SubmissionFormSection $section) use ($duplicate): bool {
                return (int) ($section->sort_order ?? 0) === (int) ($duplicate->sort_order ?? 0)
                    && mb_strtolower(trim((string) ($section->title ?? ''))) === mb_strtolower(trim((string) ($duplicate->title ?? '')));
            });

            if ($keeper === null) {
                $keeper = $keepers->first(function (SubmissionFormSection $section) use ($duplicate): bool {
                    return mb_strtolower(trim((string) ($section->title ?? ''))) === mb_strtolower(trim((string) ($duplicate->title ?? '')));
                });
            }

            if ($keeper !== null) {
                $keeperElementsByName = $keeper->elementHolders
                    ->flatMap(fn ($holder) => $holder->elements)
                    ->keyBy(fn (SubmissionFormElement $element): string => trim((string) ($element->name ?? '')));

                foreach ($duplicate->elementHolders as $holder) {
                    foreach ($holder->elements as $element) {
                        $name = trim((string) ($element->name ?? ''));
                        $target = $name !== '' ? $keeperElementsByName->get($name) : null;

                        if ($target instanceof SubmissionFormElement) {
                            $migrated += $this->migrateElementValues((string) $element->id, (string) $target->id);
                        }

                        SubmissionFormInstanceValue::query()
                            ->where('submission_form_element_id', $element->id)
                            ->delete();

                        $element->delete();
                    }

                    $holder->delete();
                }
            } else {
                foreach ($duplicate->elementHolders as $holder) {
                    foreach ($holder->elements as $element) {
                        SubmissionFormInstanceValue::query()
                            ->where('submission_form_element_id', $element->id)
                            ->delete();
                        $element->delete();
                    }
                    $holder->delete();
                }
            }

            $duplicate->delete();
            $removed++;
        }

        $form->unsetRelation('sections');

        return [
            'kept' => $keepers->count(),
            'removed' => $removed,
            'migrated_values' => $migrated,
        ];
    }

    private function migrateElementValues(string $fromElementId, string $toElementId): int
    {
        $moved = 0;

        SubmissionFormInstanceValue::query()
            ->where('submission_form_element_id', $fromElementId)
            ->each(function (SubmissionFormInstanceValue $value) use ($toElementId, &$moved): void {
                $duplicateQuery = SubmissionFormInstanceValue::query()
                    ->where('submission_form_instance_id', $value->submission_form_instance_id)
                    ->where('submission_form_element_id', $toElementId);

                if ($value->array_index !== null) {
                    $duplicateQuery->where('array_index', $value->array_index);
                }

                if ($duplicateQuery->exists()) {
                    $value->delete();

                    return;
                }

                $value->update(['submission_form_element_id' => $toElementId]);
                $moved++;
            });

        return $moved;
    }
}

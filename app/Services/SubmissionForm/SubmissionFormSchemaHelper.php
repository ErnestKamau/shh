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
     * @return Collection<int, SubmissionFormSection>
     */
    public function uniqueSections(SubmissionForm $form): Collection
    {
        $form->loadMissing(['sections.elementHolders.elements']);

        $seen = [];
        $unique = collect();

        foreach ($form->sections->sortBy('sort_order') as $section) {
            $key = ($section->sort_order ?? 0).'|'.mb_strtolower(trim((string) ($section->title ?? '')));
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique->push($section);
        }

        return $unique;
    }

    /**
     * @return Collection<int, SubmissionFormElement>
     */
    public function uniqueElements(SubmissionForm $form): Collection
    {
        $elements = collect();
        $seenNames = [];

        foreach ($this->uniqueSections($form) as $section) {
            $section->loadMissing(['elementHolders.elements']);

            foreach ($section->elementHolders->sortBy('sort_order') as $holder) {
                foreach ($holder->elements->sortBy('sort_order') as $element) {
                    $name = trim((string) ($element->name ?? ''));
                    if ($name === '' || isset($seenNames[$name])) {
                        continue;
                    }

                    $seenNames[$name] = true;
                    $elements->push($element);
                }
            }
        }

        return $elements;
    }
}

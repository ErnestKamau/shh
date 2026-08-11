<?php

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormElementHolder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $documentCodes = [
            'TRF-FOOD-019',
            'TRF-FOOD-FEED-021',
        ];

        foreach ($documentCodes as $documentCode) {
            $form = SubmissionForm::query()->where('document_code', $documentCode)->first();
            if ($form === null) {
                continue;
            }

            $section = $form->sections()
                ->where('section_type', 'rows_section')
                ->where('title', 'Test & sample information')
                ->first();

            if ($section === null) {
                continue;
            }

            /** @var SubmissionFormElementHolder|null $holder */
            $holder = $section->elementHolders()->where('holder_type', 'field')->first();
            if ($holder === null) {
                continue;
            }

            $locationElement = $holder->elements()->where('name', 'sampling_point')->first();
            if ($locationElement !== null) {
                $locationElement->update([
                    'label' => 'Sampling Location',
                    'element_type' => 'customer_sample_point_select',
                    'sort_order' => 2,
                ]);
            }

            $manualElement = $holder->elements()->where('name', 'sampling_point_manual')->first();
            if ($manualElement === null) {
                $holder->elements()->create([
                    'id' => (string) Str::uuid7(),
                    'element_type' => 'text',
                    'label' => 'Sampling Point',
                    'name' => 'sampling_point_manual',
                    'is_required' => false,
                    'is_readonly' => false,
                    'sort_order' => 3,
                ]);
            } else {
                $manualElement->update([
                    'element_type' => 'text',
                    'label' => 'Sampling Point',
                    'sort_order' => 3,
                ]);
            }

            $holder->update([
                'max_elements' => max((int) $holder->max_elements, (int) $holder->elements()->count()),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $documentCodes = [
            'TRF-FOOD-019',
            'TRF-FOOD-FEED-021',
        ];

        foreach ($documentCodes as $documentCode) {
            $form = SubmissionForm::query()->where('document_code', $documentCode)->first();
            if ($form === null) {
                continue;
            }

            $section = $form->sections()
                ->where('section_type', 'rows_section')
                ->where('title', 'Test & sample information')
                ->first();

            if ($section === null) {
                continue;
            }

            foreach ($section->elementHolders as $holder) {
                $holder->elements()
                    ->where('name', 'sampling_point_manual')
                    ->each(function (SubmissionFormElement $element): void {
                        $element->delete();
                    });

                $holder->elements()
                    ->where('name', 'sampling_point')
                    ->update(['label' => 'Sampling point / location']);
            }
        }
    }
};

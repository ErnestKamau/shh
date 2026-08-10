<?php

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Force Sampling Location onto the CRM dropdown control and hide the
     * redundant Water "Location" text field even when historic values exist.
     */
    public function up(): void
    {
        foreach (['TRF-FOOD-019', 'TRF-FOOD-FEED-021', 'TRF-WATER-020', 'TRF-WASTE-036'] as $documentCode) {
            $form = SubmissionForm::query()->where('document_code', $documentCode)->first();
            if ($form === null) {
                continue;
            }

            $section = $form->sections()
                ->where('section_type', 'rows_section')
                ->whereRaw('LOWER(title) = ?', ['test & sample information'])
                ->first();

            if ($section === null) {
                continue;
            }

            foreach ($section->elementHolders as $holder) {
                $holder->elements()
                    ->where('name', 'sampling_point')
                    ->each(function (SubmissionFormElement $element): void {
                        $element->update([
                            'label' => 'Sampling Location',
                            'element_type' => 'customer_sample_point_select',
                        ]);
                    });

                $holder->elements()
                    ->where('name', 'sampling_point_manual')
                    ->each(function (SubmissionFormElement $element): void {
                        $element->update([
                            'label' => 'Sampling Point',
                            'element_type' => 'text',
                        ]);
                    });

                if ($documentCode === 'TRF-WATER-020') {
                    $holder->elements()
                        ->where('name', 'location')
                        ->each(function (SubmissionFormElement $element): void {
                            $element->update([
                                'is_hidden' => true,
                                'is_required' => false,
                                'sort_order' => 99,
                            ]);
                        });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $form = SubmissionForm::query()->where('document_code', 'TRF-WATER-020')->first();
        if ($form === null) {
            return;
        }

        $section = $form->sections()
            ->where('section_type', 'rows_section')
            ->whereRaw('LOWER(title) = ?', ['test & sample information'])
            ->first();

        if ($section === null) {
            return;
        }

        foreach ($section->elementHolders as $holder) {
            $holder->elements()
                ->where('name', 'location')
                ->each(function (SubmissionFormElement $element): void {
                    $element->update([
                        'is_hidden' => false,
                        'sort_order' => 2,
                    ]);
                });
        }
    }
};

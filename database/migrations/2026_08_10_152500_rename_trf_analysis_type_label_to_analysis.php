<?php

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->renameAnalysisLabels('ANALYSIS');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->renameAnalysisLabels('Analysis type');
    }

    private function renameAnalysisLabels(string $label): void
    {
        SubmissionForm::query()
            ->whereHas('sections', function ($query): void {
                $query->where('section_type', 'rows_section')
                    ->whereRaw('LOWER(title) = ?', ['test & sample information']);
            })
            ->with(['sections' => function ($query): void {
                $query->where('section_type', 'rows_section')
                    ->whereRaw('LOWER(title) = ?', ['test & sample information'])
                    ->with('elementHolders.elements');
            }])
            ->each(function (SubmissionForm $form) use ($label): void {
                foreach ($form->sections as $section) {
                    foreach ($section->elementHolders as $holder) {
                        $holder->elements()
                            ->where(function ($query): void {
                                $query->whereIn('name', ['analysis_type_id', 'analysis_type', 'analysis_types'])
                                    ->orWhere('element_type', 'analysis_type_select');
                            })
                            ->each(function (SubmissionFormElement $element) use ($label): void {
                                $element->update(['label' => $label]);
                            });
                    }
                }
            });
    }
};

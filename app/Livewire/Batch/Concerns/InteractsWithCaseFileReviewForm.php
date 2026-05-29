<?php

namespace App\Livewire\Batch\Concerns;

use App\Models\CaseFileReviewForm;
use App\Services\Sampleworkflow\CaseFileReviewFormPrefillService;

trait InteractsWithCaseFileReviewForm
{
    protected function initializeCaseFileFormData(): void
    {
        if (! $this->batch->hasDnaLab()) {
            return;
        }

        $prefillService = app(CaseFileReviewFormPrefillService::class);
        $existingCaseForm = CaseFileReviewForm::where('batch_id', $this->batch->id)->first();

        if ($existingCaseForm) {
            $data = $existingCaseForm->toArray();
        } else {
            $data = $prefillService->defaultBatchFields($this->batch);
            $data = $prefillService->buildPrefill($this->batch, $data);
        }

        $this->assignCaseFileFormArray(array_merge($this->caseFileBooleanDefaults(), $data));
    }

    /**
     * @return array<string, bool>
     */
    protected function caseFileBooleanDefaults(): array
    {
        return [
            'sample_condition_sealed' => false,
            'sample_condition_labelled' => false,
            'screening_sample_type_blood' => false,
            'screening_sample_type_object_with_blood' => false,
            'screening_sample_type_semen' => false,
            'screening_sample_type_object_with_semen' => false,
            'extraction_method_chelex' => false,
            'extraction_method_prepfiler' => false,
            'quantification_no_of_cycles_40' => false,
            'quantification_kit_used_quant_trio' => false,
            'pcr_no_of_cycles_28' => false,
            'pcr_no_of_cycles_29' => false,
            'pcr_no_of_cycles_30' => false,
            'pcr_no_of_cycles_32' => false,
            'pcr_kit_used_identifiler_plus' => false,
            'pcr_kit_used_globalfiler' => false,
            'pcr_kit_used_yfiler_plus' => false,
            'injection_instrument_3500' => false,
            'reporting_reviewed' => false,
            'reporting_corrected' => false,
            'reporting_attachment_real_time_data' => false,
            'reporting_attachment_converge' => false,
            'reporting_attachment_statistical_analysis' => false,
            'manager_review_technical' => false,
            'manager_review_administrative' => false,
            'manager_comments_verified' => false,
            'manager_comments_not_verified' => false,
        ];
    }

    public function refreshCaseFileFromWorksheets(): void
    {
        if (! $this->batch->hasDnaLab()) {
            session()->flash('error', 'Case File Review Form is only available for DNA laboratories.');

            return;
        }

        $prefillService = app(CaseFileReviewFormPrefillService::class);
        $current = $this->caseFileFormArray();
        $merged = $prefillService->buildPrefill($this->batch, $current);

        $this->assignCaseFileFormArray($merged);
        session()->flash('success', 'Empty fields were filled from saved grouped worksheet stages.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    abstract protected function assignCaseFileFormArray(array $data): void;

    /**
     * @return array<string, mixed>
     */
    abstract protected function caseFileFormArray(): array;
}

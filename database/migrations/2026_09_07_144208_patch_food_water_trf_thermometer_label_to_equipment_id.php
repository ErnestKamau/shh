<?php

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Services\SubmissionForm\TrfDocumentCodeForSampleType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->patchLabel('Equipment ID');
    }

    public function down(): void
    {
        $this->patchLabel('Thermometer ID');
    }

    private function patchLabel(string $label): void
    {
        if (! Schema::hasTable('submission_forms') || ! Schema::hasTable('submission_form_elements')) {
            return;
        }

        $documentCodes = [
            TrfDocumentCodeForSampleType::FOOD,
            TrfDocumentCodeForSampleType::FOOD_AND_FEED,
            TrfDocumentCodeForSampleType::WATER,
        ];

        $formIds = SubmissionForm::query()
            ->whereIn('document_code', $documentCodes)
            ->pluck('id');

        if ($formIds->isEmpty()) {
            return;
        }

        SubmissionFormElement::query()
            ->where('name', 'thermometer_id')
            ->whereHas('holder.section', function ($query) use ($formIds): void {
                $query->whereIn('submission_form_id', $formIds);
            })
            ->update(['label' => $label]);
    }
};

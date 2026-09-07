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
        if (! Schema::hasTable('submission_forms') || ! Schema::hasTable('submission_form_elements')) {
            return;
        }

        $formIds = SubmissionForm::query()
            ->whereIn('document_code', [
                TrfDocumentCodeForSampleType::WATER,
                TrfDocumentCodeForSampleType::FOOD,
                TrfDocumentCodeForSampleType::FOOD_AND_FEED,
            ])
            ->pluck('id');

        if ($formIds->isEmpty()) {
            return;
        }

        SubmissionFormElement::query()
            ->whereIn('name', ['Equipment_ID', 'equipment_id', 'Equipment ID'])
            ->whereHas('holder.section', function ($query) use ($formIds): void {
                $query->whereIn('submission_form_id', $formIds);
            })
            ->update([
                'name' => 'thermometer_id',
                'label' => 'Equipment ID',
            ]);
    }

    public function down(): void
    {
        // Non-reversible: original element names may have mixed casing.
    }
};

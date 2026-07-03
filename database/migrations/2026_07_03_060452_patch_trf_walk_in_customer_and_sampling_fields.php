<?php

use App\Models\SubmissionForm;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstanceValue;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $formIds = SubmissionForm::query()
            ->where('name', 'ilike', '%test request form%')
            ->pluck('id');

        if ($formIds->isEmpty()) {
            return;
        }

        $elementIdsForForms = SubmissionFormElement::query()
            ->whereHas('holder.section', fn ($query) => $query->whereIn('submission_form_id', $formIds))
            ->pluck('id');

        if ($elementIdsForForms->isEmpty()) {
            return;
        }

        SubmissionFormElement::query()
            ->whereIn('id', $elementIdsForForms)
            ->where('name', 'contact_person')
            ->update([
                'element_type' => 'client_contact_select',
                'label' => 'Contact person',
            ]);

        SubmissionFormElement::query()
            ->whereIn('id', $elementIdsForForms)
            ->where('name', 'sampling_location')
            ->update([
                'element_type' => 'customer_sample_point_select',
                'label' => 'Sampling location',
            ]);

        SubmissionFormElement::query()
            ->whereIn('id', $elementIdsForForms)
            ->where('name', 'customer_email')
            ->update([
                'label' => 'Email',
                'sort_order' => 6,
            ]);

        SubmissionFormElement::query()
            ->whereIn('id', $elementIdsForForms)
            ->where('name', 'customer_tax_id')
            ->each(function (SubmissionFormElement $element): void {
                $hasValues = SubmissionFormInstanceValue::query()
                    ->where('submission_form_element_id', $element->id)
                    ->exists();

                if (! $hasValues) {
                    $element->delete();
                }
            });
    }

    public function down(): void
    {
        // Non-reversible: prior text field types and removed tax id are not restored.
    }
};

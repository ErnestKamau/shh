<?php

namespace App\Services\Sampleworkflow;

use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Merges "FOR LAB USE ONLY" fields captured at physical check-in into the
 * linked SubmissionFormInstance values and regenerates its PDF.
 *
 * Field names:
 *   lab_received_datetime  — ISO-8601 local datetime string
 *   lab_received_by        — Name of the staff member receiving the samples
 *   lab_sample_condition   — "Acceptable" | "Not Acceptable"
 */
final class TrfLabUseFieldsService
{
    /** @var list<string> */
    private const LAB_FIELDS = [
        'lab_received_datetime',
        'lab_received_by',
        'lab_sample_condition',
    ];

    public function __construct(
        private readonly TestRequestFormPdfService $pdfService,
    ) {}

    /**
     * Merge lab-use field values into SFI element values, then regenerate the PDF.
     *
     * @param  array<string, mixed>  $labFields  Keyed by the three field names above.
     */
    public function mergeIntoInstance(SubmissionFormInstance $instance, array $labFields): void
    {
        $instance->loadMissing(['submissionForm.sections.elementHolders.elements', 'values.element']);

        $elementsByName = $instance->submissionForm?->sections
            ->flatMap(fn ($section) => $section->elementHolders)
            ->flatMap(fn ($holder) => $holder->elements)
            ->keyBy(fn (SubmissionFormElement $element) => (string) $element->name)
            ?? collect();

        DB::transaction(function () use ($instance, $labFields, $elementsByName): void {
            foreach (self::LAB_FIELDS as $key) {
                if (! array_key_exists($key, $labFields) || $labFields[$key] === null || $labFields[$key] === '') {
                    continue;
                }

                /** @var SubmissionFormElement|null $element */
                $element = $elementsByName->get($key);
                if ($element === null) {
                    continue;
                }

                $value = SubmissionFormInstanceValue::query()->firstOrNew([
                    'submission_form_instance_id' => $instance->id,
                    'submission_form_element_id' => $element->id,
                    'array_index' => null,
                ]);

                if (! $value->exists) {
                    $value->id = (string) Str::uuid7();
                }

                $value->value = is_scalar($labFields[$key]) ? (string) $labFields[$key] : json_encode($labFields[$key]);
                $value->save();
            }
        });

        try {
            $this->pdfService->generateAndStore($instance->fresh(['values.element', 'submissionForm.sampleTypeCategories']));
        } catch (\Throwable) {
            // PDF regeneration failure must not block the check-in flow.
        }
    }

    /**
     * @deprecated Use mergeIntoInstance()
     *
     * @param  array<string, mixed>  $labFields
     */
    public function mergeIntoTrfi(SubmissionFormInstance $instance, array $labFields): void
    {
        $this->mergeIntoInstance($instance, $labFields);
    }

    /**
     * @return array<string, string>
     */
    public static function buildFieldsArray(
        string $labReceivedDatetime,
        string $labReceivedBy,
        string $labSampleCondition,
    ): array {
        return [
            'lab_received_datetime' => $labReceivedDatetime,
            'lab_received_by' => $labReceivedBy,
            'lab_sample_condition' => $labSampleCondition,
        ];
    }
}

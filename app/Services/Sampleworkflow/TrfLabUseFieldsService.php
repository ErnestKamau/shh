<?php

namespace App\Services\Sampleworkflow;

use App\Models\TestRequestFormInstance;

/**
 * Merges "FOR LAB USE ONLY" fields captured at physical check-in into the
 * linked TestRequestFormInstance and regenerates its PDF.
 *
 * Field names match the TRF form_data keys defined in TestRequestForm:
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
     * Merge lab-use field values into the TRFI form_data, preserve existing
     * keys, save the model, then regenerate the PDF.
     *
     * @param array<string, mixed> $labFields  Keyed by the three field names above.
     */
    public function mergeIntoTrfi(TestRequestFormInstance $trfi, array $labFields): void
    {
        $existing = is_array($trfi->form_data) ? $trfi->form_data : [];

        foreach (self::LAB_FIELDS as $key) {
            if (array_key_exists($key, $labFields) && $labFields[$key] !== null && $labFields[$key] !== '') {
                $existing[$key] = $labFields[$key];
            }
        }

        $trfi->form_data = $existing;
        $trfi->save();

        try {
            $this->pdfService->generateAndStore($trfi);
        } catch (\Throwable) {
            // PDF regeneration failure must not block the check-in flow.
        }
    }

    /**
     * Build the lab fields array from the three Livewire component properties.
     *
     * @return array<string, string>
     */
    public static function buildFieldsArray(
        string $labReceivedDatetime,
        string $labReceivedBy,
        string $labSampleCondition,
    ): array {
        return [
            'lab_received_datetime' => $labReceivedDatetime,
            'lab_received_by'       => $labReceivedBy,
            'lab_sample_condition'  => $labSampleCondition,
        ];
    }
}

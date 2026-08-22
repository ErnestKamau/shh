<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;

class CapturedResultCaptureService
{
    public function __construct(
        private readonly SampleWorkflowEventRecorder $eventRecorder,
    ) {}

    /**
     * Persist capture attributes onto a CapturedResult.
     *
     * Always assigns the acting user as operator (and analyst for TAT).
     * Links analysis_element_id and backfills blank method/unit from the element
     * without overwriting user-edited values.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function applyOnSave(
        CapturedResult $captured,
        array $attributes,
        ?string $actingUserId,
        ?string $how = null,
    ): CapturedResult {
        $previousResult = trim((string) ($captured->result ?? ''));

        if ($attributes !== []) {
            $captured->fill($attributes);
        }

        $captured->assignOperator($actingUserId);
        $captured->assignAnalyst($actingUserId);
        $captured->ensureAnalysisElementLinked();
        $captured->applyAnalysisElementDefaults();
        $captured->save();

        $newResult = trim((string) ($captured->result ?? ''));
        if ($newResult !== '' && $newResult !== $previousResult) {
            $captured->loadMissing(['sample', 'my_analyte', 'labSection', 'sampleHeader']);
            $batch = $captured->sampleHeader;

            $this->eventRecorder->record(
                subjectType: CapturedResult::class,
                subjectId: (string) $captured->id,
                eventType: 'result_captured',
                what: 'Test result captured',
                how: $how ?? 'Manual entry',
                where: (string) ($captured->labSection?->name ?? 'Laboratory'),
                instanceId: filled($batch?->submission_form_instance_id)
                    ? (string) $batch->submission_form_instance_id
                    : null,
                batchId: filled($captured->sample_header_id) ? (string) $captured->sample_header_id : null,
                workflowStage: (string) ($batch?->status ?? ''),
                metadata: [
                    'sample_code' => (string) ($captured->sample?->sample_code ?? ''),
                    'test' => (string) ($captured->my_analyte?->name ?? $captured->analyte_code ?? ''),
                    'result' => $newResult,
                ],
            );
        }

        return $captured;
    }
}

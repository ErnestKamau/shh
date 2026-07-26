<?php

namespace App\Services\Planner;

use App\Models\SamplingSchedule;
use App\Models\SubmissionFormInstance;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use Illuminate\Support\Str;

/**
 * Tracks scheduled vs collected samples for a sampling schedule.
 * Status stays partial until collected samples reach the scheduled total.
 */
final class SamplingScheduleCollectionProgress
{
    public function __construct(
        private readonly SubmissionFormValueNormalizer $valueNormalizer,
    ) {}

    public function scheduledSamples(SamplingSchedule $schedule): int
    {
        return max(1, (int) ($schedule->number_of_samples ?? 1));
    }

    public function collectedSamples(SamplingSchedule $schedule): int
    {
        $schedule->loadMissing('submissionFormInstances.values.element');

        $total = 0;
        $scheduled = $this->scheduledSamples($schedule);

        foreach ($schedule->submissionFormInstances as $instance) {
            $total += $this->samplesFromInstance($instance, $scheduled);
        }

        return $total;
    }

    /**
     * @return 'pending'|'partial'|'collected'
     */
    public function status(SamplingSchedule $schedule): string
    {
        $scheduled = $this->scheduledSamples($schedule);
        $collected = $this->collectedSamples($schedule);

        if ($collected <= 0) {
            return 'pending';
        }

        if ($collected < $scheduled) {
            return 'partial';
        }

        return 'collected';
    }

    /**
     * @return array{
     *     scheduled: int,
     *     collected: int,
     *     remaining: int,
     *     status: string,
     *     label: string,
     *     is_complete: bool
     * }
     */
    public function progress(SamplingSchedule $schedule): array
    {
        $scheduled = $this->scheduledSamples($schedule);
        $collectedRaw = $this->collectedSamples($schedule);
        $collected = min($collectedRaw, $scheduled);
        $status = $collected <= 0
            ? 'pending'
            : ($collected < $scheduled ? 'partial' : 'collected');

        return [
            'scheduled' => $scheduled,
            'collected' => $collected,
            'remaining' => max(0, $scheduled - $collected),
            'status' => $status,
            'label' => $collected.'/'.$scheduled,
            'is_complete' => $status === 'collected',
        ];
    }

    /**
     * Persist is_collected only when collected samples meet/exceed scheduled.
     *
     * @return array{
     *     scheduled: int,
     *     collected: int,
     *     remaining: int,
     *     status: string,
     *     label: string,
     *     is_complete: bool
     * }
     */
    public function refresh(SamplingSchedule $schedule): array
    {
        $schedule->loadMissing('submissionFormInstances.values.element');
        $progress = $this->progress($schedule);
        $schedule->is_collected = $progress['is_complete'];
        $schedule->save();

        return $progress;
    }

    private function samplesFromInstance(SubmissionFormInstance $instance, int $scheduledTotal): int
    {
        $instance->loadMissing(['values.element']);
        $formData = $this->valueNormalizer->valuesMapFromInstance($instance);

        $rows = is_array($formData['sample_rows'] ?? null) ? $formData['sample_rows'] : [];
        if ($rows !== []) {
            return max(1, count($rows));
        }

        $qty = 0;
        foreach (['number_of_samples', 'sample_quantity', 'qty'] as $key) {
            $raw = $formData[$key] ?? null;
            if (is_array($raw)) {
                $raw = $raw[0] ?? null;
            }
            if (is_numeric($raw) && (int) $raw > 0) {
                $qty = max($qty, (int) $raw);
            }
        }

        if ($qty <= 0) {
            foreach ($instance->values as $value) {
                $element = $value->element;
                if ($element === null || $value->isEmpty()) {
                    continue;
                }
                $elementType = (string) ($element->element_type ?? '');
                $elementName = Str::lower(trim((string) (($element->name ?? '').' '.($element->label ?? ''))));
                if ($elementType === 'number' && Str::contains($elementName, ['qty', 'quantity', 'number of samples', 'no. of samples'])) {
                    $parsed = (int) $value->value;
                    if ($parsed > 0) {
                        $qty = max($qty, $parsed);
                    }
                }
            }
        }

        // Prefilling the full schedule qty into a single TRF must not mark the schedule complete.
        if ($qty >= $scheduledTotal && $scheduledTotal > 1) {
            return 1;
        }

        if ($qty > 0) {
            return $qty;
        }

        return 1;
    }
}

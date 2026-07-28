<?php

namespace App\Services\ShelfLife;

use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\ShelfLife\ShelfLifePullPoint;
use App\Models\ShelfLife\ShelfLifeStudy;
use App\SampleDetails;
use App\SampleHeader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ShelfLifeStudyBootstrapService
{
    public const BATCH_STATUS = 'Shelf Life Study';

    public function createDraftFromAcceptance(AnalysisAcceptanceForm $form, SampleHeader $batch): ShelfLifeStudy
    {
        $existing = ShelfLifeStudy::query()
            ->where('sample_header_id', $batch->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $enquiry = $this->resolveEnquiry($form, $batch);
        $firstSample = SampleDetails::query()
            ->where('sample_header_id', $batch->id)
            ->orderBy('sample_code')
            ->first();

        $code = $this->nextStudyCode();
        $title = trim((string) ($batch->description ?? '')) !== ''
            ? 'Shelf life: '.(string) $batch->description
            : 'Shelf life study — '.(string) ($batch->batch_code ?? $code);

        return ShelfLifeStudy::query()->create([
            'code' => $code,
            'title' => Str::limit($title, 240),
            'company_id' => function_exists('getActiveCompany') ? (getActiveCompany()?->id) : null,
            'sample_header_id' => $batch->id,
            'sample_type_id' => $batch->sample_type_id,
            'company_product_id' => $firstSample?->company_product_id,
            'sample_submission_request_id' => $enquiry?->id ?? $form->sample_submission_request_id,
            'crm_customer_id' => $batch->crm_customer_id ?? $form->crm_customer_id,
            'batch_lot_no' => $firstSample?->batch_lot_no,
            'mfg_date' => $firstSample?->mfg_date,
            'study_type' => ShelfLifeStudy::TYPE_REAL_TIME,
            'start_date' => $batch->receipt_date ? Carbon::parse($batch->receipt_date)->toDateString() : now()->toDateString(),
            'status' => ShelfLifeStudy::STATUS_DRAFT,
            'created_by' => auth()->id() ? (string) auth()->id() : $form->created_by,
        ]);
    }

    /**
     * @param  list<array{label?: string, offset_value: int, offset_unit?: string, is_baseline?: bool}>  $intervals
     * @return list<ShelfLifePullPoint>
     */
    public function generatePullPoints(ShelfLifeStudy $study, array $intervals, ?Carbon $startDate = null): array
    {
        $start = $startDate
            ?? ($study->start_date ? Carbon::parse($study->start_date) : now()->startOfDay());

        $created = [];
        foreach (array_values($intervals) as $index => $interval) {
            $offsetValue = (int) ($interval['offset_value'] ?? 0);
            $offsetUnit = (string) ($interval['offset_unit'] ?? 'months');
            $scheduled = $start->copy();

            match ($offsetUnit) {
                'days' => $scheduled->addDays($offsetValue),
                'weeks' => $scheduled->addWeeks($offsetValue),
                default => $scheduled->addMonths($offsetValue),
            };

            $label = trim((string) ($interval['label'] ?? ''));
            if ($label === '') {
                $label = $offsetValue === 0
                    ? 'T0 (Baseline)'
                    : 'T+'.$offsetValue.' '.Str::singular($offsetUnit);
            }

            $created[] = ShelfLifePullPoint::query()->create([
                'shelf_life_study_id' => $study->id,
                'label' => $label,
                'offset_value' => $offsetValue,
                'offset_unit' => in_array($offsetUnit, ['days', 'weeks', 'months'], true) ? $offsetUnit : 'months',
                'is_baseline' => (bool) ($interval['is_baseline'] ?? $offsetValue === 0),
                'scheduled_date' => $scheduled->toDateString(),
                'status' => ShelfLifePullPoint::STATUS_PENDING,
                'sort_order' => $index,
            ]);
        }

        return $created;
    }

    /**
     * Default pull schedule: 0, 1, 3, 6, 9, 12 months.
     *
     * @return list<array{label: string, offset_value: int, offset_unit: string, is_baseline: bool}>
     */
    public function defaultMonthlyIntervals(): array
    {
        $months = [0, 1, 3, 6, 9, 12];

        return array_map(static function (int $month): array {
            return [
                'label' => $month === 0 ? 'T0 (Baseline)' : 'Month '.$month,
                'offset_value' => $month,
                'offset_unit' => 'months',
                'is_baseline' => $month === 0,
            ];
        }, $months);
    }

    private function nextStudyCode(): string
    {
        $latest = ShelfLifeStudy::query()
            ->where('code', 'like', 'SLS-%')
            ->orderByDesc('code')
            ->value('code');

        $next = 1;
        if (is_string($latest) && preg_match('/SLS-(\d+)/', $latest, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return 'SLS-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function resolveEnquiry(AnalysisAcceptanceForm $form, SampleHeader $batch): ?SampleSubmissionRequest
    {
        if ($form->sample_submission_request_id) {
            return SampleSubmissionRequest::query()->find($form->sample_submission_request_id);
        }

        if (Schema::hasColumn('sample_submission_requests', 'sample_header_id')) {
            return SampleSubmissionRequest::query()
                ->where('sample_header_id', $batch->id)
                ->first();
        }

        return null;
    }
}

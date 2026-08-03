<?php

namespace App\Services\Planner;

use App\Models\SamplingSchedule;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SamplingScheduleRecurrenceGenerator
{
    /**
     * Create future sampling schedule rows for the selected frequency.
     *
     * The provided schedule is treated as the first occurrence. Additional
     * occurrences are cloned with advanced sampling_datetime values through a
     * one-year horizon (two years for Annually so at least one repeat exists).
     *
     * @return list<SamplingSchedule>
     */
    public function generateFollowingOccurrences(SamplingSchedule $first): array
    {
        $frequency = trim((string) ($first->frequency ?? 'One-time'));
        if ($frequency === '' || $frequency === 'One-time') {
            return [];
        }

        $start = Carbon::parse($first->sampling_datetime);
        $horizon = $frequency === 'Annually'
            ? $start->copy()->addYears(2)
            : $start->copy()->addYear();

        $created = [];
        $cursor = $start->copy();

        while (true) {
            $cursor = $this->advance($cursor, $frequency);
            if ($cursor === null || $cursor->gt($horizon)) {
                break;
            }

            $clone = $first->replicate([
                'id',
                'created_at',
                'updated_at',
                'is_collected',
            ]);
            $clone->id = (string) Str::uuid();
            $clone->sampling_datetime = $cursor->copy();
            $clone->is_collected = false;
            $clone->save();
            $created[] = $clone;
        }

        return $created;
    }

    private function advance(Carbon $cursor, string $frequency): ?Carbon
    {
        $next = $cursor->copy();

        return match ($frequency) {
            'Daily' => $next->addDay(),
            'Weekly' => $next->addWeek(),
            'Monthly' => $next->addMonthNoOverflow(),
            'Quarterly' => $next->addMonthsNoOverflow(3),
            'Annually' => $next->addYearNoOverflow(),
            default => null,
        };
    }
}

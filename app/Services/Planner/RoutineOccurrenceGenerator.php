<?php

namespace App\Services\Planner;

use App\CalendarEventsNotification;
use App\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoutineOccurrenceGenerator
{
    /**
     * Horizon (days) used when contract end date is not provided.
     */
    public const DEFAULT_HORIZON_DAYS = 365;

    /**
     * Create child routine occurrences for a parent event based on frequency (days).
     *
     * @param  array<int, string>|null  $durations
     * @param  array<int, string>|null  $rates
     */
    public function generateChildren(
        Event $parent,
        int $frequencyDays,
        ?string $horizonEndDate = null,
        ?array $durations = null,
        ?array $rates = null,
    ): int {
        $frequencyDays = max(1, $frequencyDays);

        $resetStart = Carbon::parse($parent->start_date)->startOfDay();
        $resetEnd = Carbon::parse($parent->end_date)->startOfDay();
        $durationDays = max(0, $resetStart->diffInDays($resetEnd, false));

        $horizonEnd = $this->resolveHorizonEnd($resetStart, $horizonEndDate);

        $created = 0;
        $cursorStart = $resetStart->copy();

        while (true) {
            $nextStart = $cursorStart->copy()->addDays($frequencyDays);
            if ($nextStart->gt($horizonEnd)) {
                break;
            }

            $nextEnd = $nextStart->copy()->addDays($durationDays);

            $child = new Event();
            $child->title = $parent->title;
            $child->description = $parent->description;
            $child->start_date = $nextStart->toDateString();
            $child->end_date = $nextEnd->toDateString();
            $child->start_time = $parent->start_time;
            $child->end_time = $parent->end_time;
            $child->client_id = $parent->client_id;
            $child->location = $parent->location;
            $child->responsible_id = $parent->responsible_id;
            $child->status = 'Pending';
            $child->contract_valid_from = $parent->contract_valid_from;
            $child->contract_valid_to = $parent->contract_valid_to;
            $child->is_routine = 1;
            $child->parent_id = $parent->id;
            $child->frequency = (string) $frequencyDays;
            $child->created_by = $parent->created_by;
            $child->save();

            $this->copyNotifications($child, $durations, $rates);
            $created++;
            $cursorStart = $nextStart;
        }

        return $created;
    }

    /**
     * Build children from an HTTP request after the parent is saved.
     */
    public function generateFromRequest(Event $parent, Request $request): int
    {
        $frequencyDays = (int) ($request->frequency ?? 0);
        if ($frequencyDays < 1) {
            return 0;
        }

        $horizonEnd = $request->contract_valid_to ?: null;

        return $this->generateChildren(
            $parent,
            $frequencyDays,
            is_string($horizonEnd) && $horizonEnd !== '' ? $horizonEnd : null,
            is_array($request->duration ?? null) ? $request->duration : null,
            is_array($request->rate ?? null) ? $request->rate : null,
        );
    }

    private function resolveHorizonEnd(Carbon $start, ?string $horizonEndDate): Carbon
    {
        if (is_string($horizonEndDate) && trim($horizonEndDate) !== '') {
            try {
                $parsed = Carbon::parse($horizonEndDate)->startOfDay();
                if ($parsed->gte($start)) {
                    return $parsed;
                }
            } catch (\Throwable) {
                // fall through to default horizon
            }
        }

        return $start->copy()->addDays(self::DEFAULT_HORIZON_DAYS);
    }

    /**
     * @param  array<int, string>|null  $durations
     * @param  array<int, string>|null  $rates
     */
    private function copyNotifications(Event $event, ?array $durations, ?array $rates): void
    {
        if ($durations === null || $durations === []) {
            return;
        }

        $hasNotification = false;
        foreach ($durations as $index => $duration) {
            if ($duration === null || $duration === '') {
                continue;
            }

            $hasNotification = true;
            $notification = new CalendarEventsNotification();
            $notification->duration = $duration;
            $notification->rate = $rates[$index] ?? null;
            $notification->calendar_event_id = $event->id;
            $notification->save();
        }

        if ($hasNotification) {
            $event->has_notification = 1;
            $event->save();
        }
    }
}

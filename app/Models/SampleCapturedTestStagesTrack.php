<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleCapturedTestStagesTrack extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'sample_captured_test_stages_track';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];

    protected $casts = [
        'equipment_data' => 'array',
        'media_data' => 'array',
        'controls_data' => 'array',
        'diluents_data' => 'array',
        'started_at' => 'datetime',
        'expected_end_at' => 'datetime',
        'ended_at' => 'datetime',
        'overtime_flagged_at' => 'datetime',
        'reading_date' => 'datetime',
        'results_posted_at' => 'datetime',
        'auto_started' => 'boolean',
    ];

    public function stageHeaderRun()
    {
        return $this->belongsTo(StageHeaderRun::class, 'stage_header_run_id');
    }

    public function capturedResult()
    {
        return $this->belongsTo(\App\CapturedResult::class, 'captured_result_id');
    }

    public function sampleDetail()
    {
        return $this->belongsTo(\App\SampleDetails::class, 'sample_detail_id');
    }

    public function stageHeader()
    {
        return $this->belongsTo(StageHeader::class, 'stage_header_id');
    }

    public function testStage()
    {
        return $this->belongsTo(TestStage::class, 'test_stage_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function readBy()
    {
        return $this->belongsTo(\App\User::class, 'read_by');
    }

    public function endedBy()
    {
        return $this->belongsTo(\App\User::class, 'ended_by');
    }

    public function resultsPostedBy()
    {
        return $this->belongsTo(\App\User::class, 'results_posted_by');
    }

    public function mediaResults()
    {
        return $this->hasMany(\App\Models\TrackMediaResult::class, 'track_id');
    }

    public function controlResults()
    {
        return $this->hasMany(\App\Models\TrackControlResult::class, 'track_id');
    }

    public function sampleResults()
    {
        return $this->hasMany(\App\Models\TrackSampleResult::class, 'track_id');
    }

    /**
     * Start the stage
     */
    public function start(string $userId, bool $autoStarted = false): void
    {
        $startTime = now();
        $expectedEnd = $this->calculateExpectedEnd($startTime);

        $this->update([
            'status' => 'running',
            'started_at' => $startTime,
            'expected_end_at' => $expectedEnd,
            'ended_at' => null,
            'user_id' => $userId,
            'auto_started' => $autoStarted,
            'overtime_flagged_at' => null,
            'auto_notes' => $autoStarted ? 'Auto-started at ' . $startTime->format('Y-m-d H:i') : null,
        ]);

        // Dispatch overtime check - but only if queue is not sync to avoid blocking
        // With sync queue, the job runs immediately and can cause timeout
        // So we skip it here and let a scheduled command handle it instead
        if ($expectedEnd && config('queue.default') !== 'sync') {
            $this->dispatchOvertimeCheck($expectedEnd);
        }
    }

    public function restart(string $userId): void
    {
        $this->start($userId, false);
    }

    public function markOverdue(): void
    {
        if (!in_array($this->status, ['running', 'overdue'], true)) {
            return;
        }

        $now = now();

        $this->forceFill([
            'status' => 'overdue',
            'overtime_flagged_at' => $this->overtime_flagged_at ?? $now,
        ])->save();
    }

    public function complete(string $userId, ?string $note = null): void
    {
        $this->update([
            'status' => 'completed',
            'ended_at' => now(),
            'auto_started' => false,
            'overtime_flagged_at' => null,
            'auto_notes' => $note,
        ]);
    }

    public function startNextStageIfAvailable(string $userId): ?self
    {
        $currentTestStage = $this->testStage;

        if (!$currentTestStage) {
            return null;
        }

        $stageHeader = $this->stageHeaderRun
            ->stageHeader()
            ->with(['testStages' => fn($query) => $query->orderBy('order')])
            ->first();

        if (!$stageHeader) {
            return null;
        }

        $nextStage = $stageHeader->testStages
            ->first(function ($stage) use ($currentTestStage) {
                return $stage->order > $currentTestStage->order;
            });

        $nextStageId = $nextStage?->id;

        if (!$nextStageId) {
            return null;
        }

        $nextStage = $this->stageHeaderRun
            ->trackRecords()
            ->where('test_stage_id', $nextStageId)
            ->where('sample_detail_id', $this->sample_detail_id)
            ->first();

        if (!$nextStage) {
            return null;
        }

        \App\Jobs\AutoStartNextStage::dispatch($nextStage, $userId, true)
            ->delay(now());

        return $nextStage;
    }

    public function startNextStageIfAvailableSync(string $userId): ?self
    {
        $currentTestStage = $this->testStage;

        if (!$currentTestStage) {
            return null;
        }

        $stageHeader = $this->stageHeaderRun
            ->stageHeader()
            ->with(['testStages' => fn($query) => $query->orderBy('order')])
            ->first();

        if (!$stageHeader) {
            return null;
        }

        $nextStage = $stageHeader->testStages
            ->first(function ($stage) use ($currentTestStage) {
                return $stage->order > $currentTestStage->order;
            });

        $nextStageId = $nextStage?->id;

        if (!$nextStageId) {
            return null;
        }

        $nextStageTrack = $this->stageHeaderRun
            ->trackRecords()
            ->where('test_stage_id', $nextStageId)
            ->where('sample_detail_id', $this->sample_detail_id)
            ->first();

        if (!$nextStageTrack) {
            return null;
        }

        // Check if it's still pending (not already started)
        if (!in_array($nextStageTrack->status, ['pending', 'overdue'], true)) {
            return $nextStageTrack;
        }

        // Start immediately (synchronously) instead of using queue
        $nextStageTrack->start($userId, true);

        // Optionally send notification (if needed)
        $user = \App\User::find($userId);
        if ($user) {
            $user->notify(new \App\Notifications\StageAutoStarted(
                $nextStageTrack,
                'Automatically started after previous stage completed.'
            ));
        }

        return $nextStageTrack;
    }

    protected function dispatchOvertimeCheck(?Carbon $expectedEnd): void
    {
        if (!$expectedEnd) {
            return;
        }

        // Pass only the track ID to avoid model serialization issues
        \App\Jobs\CheckStageOvertime::dispatch($this->id)->delay($expectedEnd);
    }

    protected function calculateExpectedEnd(Carbon $startTime): ?Carbon
    {
        $durationHours = (int) ($this->testStage->duration_hours ?? 0);

        return $durationHours > 0 ? $startTime->copy()->addHours($durationHours) : null;
    }

    /**
     * Get remaining time in hours (can be negative if overtime)
     */
    public function getRemainingTimeAttribute()
    {
        if (!in_array($this->status, ['running', 'overdue'], true) || !$this->started_at) {
            return null;
        }

        $expectedEnd = $this->expected_end_at ?? $this->calculateExpectedEnd($this->started_at);
        $now = Carbon::now();

        if (!$expectedEnd) {
            return null;
        }

        return $now->diffInHours($expectedEnd, false);
    }

    /**
     * Get elapsed time in human readable format
     */
    public function getElapsedTimeAttribute()
    {
        if (!$this->started_at) {
            return null;
        }

        $end = $this->ended_at ?? Carbon::now();
        return $this->started_at->diffForHumans($end, true);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->status === 'pending') {
            return '<span class="badge badge-secondary">Pending</span>';
        }

        if ($this->status === 'completed') {
            return '<span class="badge badge-success">Completed</span>';
        }

        // Running status - check remaining time
        $remaining = $this->remaining_time;

        if ($remaining === null) {
            return '<span class="badge badge-info">Running</span>';
        }

        if ($remaining < 0) {
            // Overtime
            return '<span class="badge badge-danger"><i class="mdi mdi-timer-alert"></i> Overtime (' . abs($remaining) . 'h over)</span>';
        } elseif ($remaining < 4) {
            // Less than 4 hours remaining
            return '<span class="badge badge-warning"><i class="mdi mdi-timer-sand"></i> ' . round($remaining, 1) . 'h remaining</span>';
        } else {
            // Normal running
            return '<span class="badge badge-success"><i class="mdi mdi-timer"></i> ' . round($remaining, 1) . 'h remaining</span>';
        }
    }

    /**
     * Update result for this stage
     */
    public function updateResult($result, $remarks, $userId)
    {
        $this->update([
            'result' => $result,
            'remarks' => $remarks,
            'reading_date' => now(),
            'read_by' => $userId,
        ]);
    }

    /**
     * Check if stage is overdue
     */
    public function getIsOverdueAttribute()
    {
        if (!in_array($this->status, ['running', 'overdue'], true) || !$this->started_at) {
            return false;
        }

        $expectedEnd = $this->expected_end_at ?? $this->calculateExpectedEnd($this->started_at);

        if (!$expectedEnd) {
            return false;
        }

        return now()->gt($expectedEnd);
    }


    public static function startInitialStageForRun(StageHeaderRun $run, string $userId): void
    {
        $firstStageId = $run->stageHeader
            ->testStages()
            ->orderBy('order')
            ->value('id');

        if (!$firstStageId) {
            return;
        }

        $run->trackRecords()
            ->where('test_stage_id', $firstStageId)
            ->get()
            ->each(function (self $track) use ($userId) {
                $track->start($userId, false);
            });
    }

    public function hasElapsedDuration(): bool
    {
        if (!$this->started_at) {
            return false;
        }

        $expectedEnd = $this->expected_end_at ?? $this->calculateExpectedEnd($this->started_at);

        if (!$expectedEnd) {
            return false;
        }

        return now()->gte($expectedEnd);
    }
}

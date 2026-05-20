<?php

namespace App\Services\Registry;

use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestStatusLog;
use Illuminate\Support\Carbon;

class RegistryTATService
{
    public function enterStage(RegistryRequest $request, string $stageCode): RegistryRequestStatusLog
    {
        return RegistryRequestStatusLog::create([
            'registry_request_id' => $request->id,
            'stage_code' => $stageCode,
            'entered_at' => now(),
        ]);
    }

    public function exitStage(RegistryRequest $request, string $stageCode): void
    {
        $log = RegistryRequestStatusLog::query()
            ->where('registry_request_id', $request->id)
            ->where('stage_code', $stageCode)
            ->whereNull('exited_at')
            ->latest('entered_at')
            ->first();

        if ($log === null) {
            return;
        }

        $exitedAt = now();
        $duration = $log->entered_at->diffInSeconds($exitedAt);

        $log->update([
            'exited_at' => $exitedAt,
            'duration_seconds' => $duration,
            'sla_breached' => $duration > $this->slaSecondsForStage($stageCode),
        ]);
    }

    public function averageTatSeconds(?Carbon $start = null, ?Carbon $end = null): float
    {
        $query = RegistryRequestStatusLog::query()
            ->whereNotNull('duration_seconds');

        if ($start !== null) {
            $query->where('entered_at', '>=', $start);
        }
        if ($end !== null) {
            $query->where('entered_at', '<=', $end);
        }

        return (float) ($query->avg('duration_seconds') ?? 0);
    }

    protected function slaSecondsForStage(string $stageCode): int
    {
        return match ($stageCode) {
            'director' => 86400 * 2,
            'sro' => 86400,
            default => 86400 * 3,
        };
    }
}

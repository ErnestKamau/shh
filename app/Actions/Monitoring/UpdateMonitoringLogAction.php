<?php

namespace App\Actions\Monitoring;

use App\Actions\Monitoring\SyncMonitoringLogCalibrationSnapshotAction;
use App\Models\Monitoring\MonitoringAuditTrail;
use App\Models\Monitoring\MonitoringLog;
use App\Models\Monitoring\MonitoringLogEntry;
use App\Models\Monitoring\MonitoringTemplate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateMonitoringLogAction
{
    public function __construct(
        protected SyncMonitoringLogCalibrationSnapshotAction $syncCalibrationSnapshot,
    ) {
    }

    public function execute(MonitoringLog $log, MonitoringTemplate $template, array $payload): MonitoringLog
    {
        return DB::transaction(function () use ($log, $template, $payload): MonitoringLog {
            $user = Auth::user();

            $equipmentId = Arr::get($payload, 'equipment_id');
            if (empty($equipmentId)) {
                $equipmentId = null;
            }

            $logPayload = Arr::get($payload, 'payload', []);
            if (! is_array($logPayload)) {
                $logPayload = is_array($log->payload) ? $log->payload : [];
            }

            $frequencySlot = Arr::get($payload, 'frequency_slot', $log->resolvedFrequencySlot());
            if ($frequencySlot !== null && $frequencySlot !== '') {
                $logPayload['frequency_slot'] = (int) $frequencySlot;
            }

            $labSectionId = Arr::get($payload, 'lab_section_id', $log->resolvedLabSectionId());
            if (filled($labSectionId)) {
                $logPayload['lab_section_id'] = (string) $labSectionId;
            }

            $remark = Arr::has($payload, 'remark') ? Arr::get($payload, 'remark') : $log->resolvedRemark();
            if ($remark !== null && $remark !== '') {
                $logPayload['remark'] = (string) $remark;
            } elseif (array_key_exists('remark', $payload)) {
                unset($logPayload['remark']);
            }

            $attributes = [
                'equipment_id' => $equipmentId,
                'status' => Arr::get($payload, 'status', $log->status),
                'overall_result' => Arr::get($payload, 'overall_result', $log->overall_result),
                'deviation_triggered' => (bool) Arr::get($payload, 'deviation_triggered', $log->deviation_triggered),
                'payload' => $logPayload,
                'executed_by' => $user?->id ?? $log->executed_by,
                'executed_at' => now(),
            ];

            if (Schema::hasColumn('monitoring_logs', 'remark')) {
                $attributes['remark'] = filled($remark) ? (string) $remark : null;
            }

            if (Schema::hasColumn('monitoring_logs', 'lab_section_id') && filled($labSectionId)) {
                $attributes['lab_section_id'] = (string) $labSectionId;
            }

            if (Schema::hasColumn('monitoring_logs', 'frequency_slot') && isset($logPayload['frequency_slot'])) {
                $attributes['frequency_slot'] = (int) $logPayload['frequency_slot'];
            }

            $existingColumns = array_flip(Schema::getColumnListing('monitoring_logs'));
            $attributes = array_filter(
                $attributes,
                static fn (string $key): bool => isset($existingColumns[$key]),
                ARRAY_FILTER_USE_KEY
            );

            $log->update($attributes);

            $log->entries()->delete();

            foreach (Arr::get($payload, 'entries', []) as $entry) {
                MonitoringLogEntry::create([
                    'log_id' => $log->id,
                    'template_field_id' => Arr::get($entry, 'template_field_id'),
                    'field_key' => (string) Arr::get($entry, 'field_key', ''),
                    'field_label' => (string) Arr::get($entry, 'field_label', Arr::get($entry, 'field_key', 'Field')),
                    'raw_value' => $this->stringifyValue(Arr::get($entry, 'raw_value')),
                    'computed_value' => $this->stringifyValue(Arr::get($entry, 'computed_value')),
                    'status' => Arr::get($entry, 'status'),
                    'pass' => Arr::has($entry, 'pass') ? (bool) Arr::get($entry, 'pass') : null,
                    'meta' => Arr::get($entry, 'meta', []),
                ]);
            }

            $this->syncCalibrationSnapshot->execute($log, $equipmentId);

            MonitoringAuditTrail::create([
                'auditable_type' => MonitoringLog::class,
                'auditable_id' => $log->id,
                'action' => 'updated',
                'old_values' => null,
                'new_values' => [
                    'status' => $log->status,
                    'overall_result' => $log->overall_result,
                ],
                'user_id' => $user?->id,
                'created_at' => now(),
            ]);

            return $log->fresh(['entries', 'executedBy', 'calibrationSnapshots']);
        });
    }

    public function updateRemark(MonitoringLog $log, ?string $remark): MonitoringLog
    {
        return DB::transaction(function () use ($log, $remark): MonitoringLog {
            $logPayload = is_array($log->payload) ? $log->payload : [];

            if (filled($remark)) {
                $logPayload['remark'] = (string) $remark;
            } else {
                unset($logPayload['remark']);
            }

            $attributes = ['payload' => $logPayload];

            if (Schema::hasColumn('monitoring_logs', 'remark')) {
                $attributes['remark'] = filled($remark) ? (string) $remark : null;
            }

            $existingColumns = array_flip(Schema::getColumnListing('monitoring_logs'));
            $attributes = array_filter(
                $attributes,
                static fn (string $key): bool => isset($existingColumns[$key]),
                ARRAY_FILTER_USE_KEY
            );

            $log->update($attributes);

            return $log->fresh(['entries', 'executedBy']);
        });
    }

    protected function stringifyValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value);
    }
}

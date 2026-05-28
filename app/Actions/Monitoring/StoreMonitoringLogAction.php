<?php

namespace App\Actions\Monitoring;

use App\Models\Monitoring\MonitoringAuditTrail;
use App\Models\Monitoring\MonitoringLog;
use App\Models\Monitoring\MonitoringLogEntry;
use App\Models\Monitoring\MonitoringTemplate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StoreMonitoringLogAction
{
    public function __construct(
        protected SyncMonitoringLogCalibrationSnapshotAction $syncCalibrationSnapshot,
    ) {
    }

    public function execute(MonitoringTemplate $template, array $payload): MonitoringLog
    {
        return DB::transaction(function () use ($template, $payload): MonitoringLog {
            $user = Auth::user();

            $equipmentId = Arr::get($payload, 'equipment_id');
            if (empty($equipmentId)) {
                $equipmentId = null;
            }

            $logPayload = Arr::get($payload, 'payload', []);
            if (! is_array($logPayload)) {
                $logPayload = [];
            }

            $frequencySlot = Arr::get($payload, 'frequency_slot');
            if ($frequencySlot !== null && $frequencySlot !== '') {
                $logPayload['frequency_slot'] = (int) $frequencySlot;
            }

            $labSectionId = Arr::get($payload, 'lab_section_id');
            if (filled($labSectionId)) {
                $logPayload['lab_section_id'] = (string) $labSectionId;
            }

            $remark = Arr::get($payload, 'remark');
            if ($remark !== null && $remark !== '') {
                $logPayload['remark'] = (string) $remark;
            }

            $attributes = [
                'template_id' => $template->id,
                'template_version' => (int) $template->version,
                'lab_id' => Arr::get($payload, 'lab_id'),
                'equipment_id' => $equipmentId,
                'log_date' => Arr::get($payload, 'log_date', now()->toDateString()),
                'monitoring_scope' => Arr::get($payload, 'monitoring_scope', $template->monitoring_category),
                'status' => Arr::get($payload, 'status', 'completed'),
                'overall_result' => Arr::get($payload, 'overall_result'),
                'deviation_triggered' => (bool) Arr::get($payload, 'deviation_triggered', false),
                'payload' => $logPayload,
                'executed_by' => $user?->id,
                'executed_at' => now(),
                'company_id' => Arr::get($payload, 'company_id', $template->company_id),
            ];

            if (Schema::hasColumn('monitoring_logs', 'remark')) {
                $attributes['remark'] = $remark ?: null;
            }

            if (Schema::hasColumn('monitoring_logs', 'lab_section_id')) {
                $attributes['lab_section_id'] = filled($labSectionId) ? (string) $labSectionId : null;
            }

            if (Schema::hasColumn('monitoring_logs', 'frequency_slot')) {
                $attributes['frequency_slot'] = isset($logPayload['frequency_slot'])
                    ? (int) $logPayload['frequency_slot']
                    : null;
            }

            $existingColumns = array_flip(Schema::getColumnListing('monitoring_logs'));
            $attributes = array_filter(
                $attributes,
                static fn (string $key): bool => isset($existingColumns[$key]),
                ARRAY_FILTER_USE_KEY
            );

            $log = MonitoringLog::create($attributes);

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
                'action' => 'created',
                'old_values' => null,
                'new_values' => [
                    'status' => $log->status,
                    'overall_result' => $log->overall_result,
                    'monitoring_scope' => $log->monitoring_scope,
                ],
                'user_id' => $user?->id,
                'created_at' => now(),
            ]);

            return $log;
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

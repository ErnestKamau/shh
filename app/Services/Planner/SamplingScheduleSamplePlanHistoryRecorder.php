<?php

namespace App\Services\Planner;

use App\Analyte;
use App\AnalysisType;
use App\Models\SamplingSchedule;
use App\Models\SamplingScheduleSamplePlanHistory;
use App\SampleType;
use App\User;
use Illuminate\Support\Facades\Auth;

/**
 * Records before/after snapshots when a schedule's planned samples change.
 */
final class SamplingScheduleSamplePlanHistoryRecorder
{
    /**
     * @param  list<array{sample_type_id?: string, analysis_type_id?: string, parameters?: list<string>}>  $sampleDetails
     */
    public function recordIfChanged(
        SamplingSchedule $schedule,
        array $previousSampleDetails,
        int $previousNumberOfSamples,
        array $sampleDetails,
        int $numberOfSamples,
        ?User $actor = null,
    ): ?SamplingScheduleSamplePlanHistory {
        $beforeDetails = $this->normalizeSampleDetails($previousSampleDetails);
        $afterDetails = $this->normalizeSampleDetails($sampleDetails);
        $beforeCount = max(1, $previousNumberOfSamples);
        $afterCount = max(1, $numberOfSamples);

        if ($beforeDetails === $afterDetails && $beforeCount === $afterCount) {
            return null;
        }

        $actor ??= Auth::user();

        return SamplingScheduleSamplePlanHistory::query()->create([
            'sampling_schedule_id' => (string) $schedule->id,
            'changed_by' => $actor instanceof User ? (string) $actor->id : null,
            'number_of_samples_before' => $beforeCount,
            'number_of_samples_after' => $afterCount,
            'sample_details_before' => $beforeDetails,
            'sample_details_after' => $afterDetails,
            'display_before' => $this->displaySnapshot($beforeDetails, $beforeCount),
            'display_after' => $this->displaySnapshot($afterDetails, $afterCount),
        ]);
    }

    /**
     * @param  list<array{sample_type_id?: string, analysis_type_id?: string, parameters?: list<string>}>|null  $details
     * @return list<array{sample_type_id: string, analysis_type_id: string, parameters: list<string>}>
     */
    public function normalizeSampleDetails(?array $details): array
    {
        if (! is_array($details) || $details === []) {
            return [];
        }

        $normalized = [];
        foreach ($details as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $sampleTypeId = trim((string) ($entry['sample_type_id'] ?? ''));
            if ($sampleTypeId === '') {
                continue;
            }

            $parameters = array_values(array_unique(array_filter(array_map(
                'strval',
                is_array($entry['parameters'] ?? null) ? $entry['parameters'] : []
            ))));
            sort($parameters);

            $normalized[] = [
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => trim((string) ($entry['analysis_type_id'] ?? '')),
                'parameters' => $parameters,
            ];
        }

        usort($normalized, function (array $a, array $b): int {
            return [$a['sample_type_id'], $a['analysis_type_id'], implode(',', $a['parameters'])]
                <=> [$b['sample_type_id'], $b['analysis_type_id'], implode(',', $b['parameters'])];
        });

        return array_values($normalized);
    }

    /**
     * @param  list<array{sample_type_id: string, analysis_type_id: string, parameters: list<string>}>  $details
     * @return array{number_of_samples: int, entries: list<array{type: string, analysis: string, params_count: int, param_names: list<string>}>}
     */
    public function displaySnapshot(array $details, int $numberOfSamples): array
    {
        $sampleTypeIds = [];
        $analysisTypeIds = [];
        $paramIds = [];

        foreach ($details as $entry) {
            if ($entry['sample_type_id'] !== '') {
                $sampleTypeIds[] = $entry['sample_type_id'];
            }
            if ($entry['analysis_type_id'] !== '') {
                $analysisTypeIds[] = $entry['analysis_type_id'];
            }
            foreach ($entry['parameters'] as $paramId) {
                $paramIds[] = $paramId;
            }
        }

        $sampleTypeNames = $sampleTypeIds !== []
            ? SampleType::query()->whereIn('id', array_values(array_unique($sampleTypeIds)))->pluck('name', 'id')
            : collect();
        $analysisTypeNames = $analysisTypeIds !== []
            ? AnalysisType::query()->whereIn('id', array_values(array_unique($analysisTypeIds)))->pluck('name', 'id')
            : collect();
        $paramNameMap = $paramIds !== []
            ? Analyte::query()->whereIn('id', array_values(array_unique($paramIds)))->pluck('name', 'id')
            : collect();

        $entries = [];
        foreach ($details as $entry) {
            $paramNames = [];
            foreach ($entry['parameters'] as $paramId) {
                $name = trim((string) ($paramNameMap[$paramId] ?? ''));
                if ($name !== '') {
                    $paramNames[] = $name;
                }
            }

            $typeName = trim((string) ($sampleTypeNames[$entry['sample_type_id']] ?? ''));
            $analysisName = trim((string) ($analysisTypeNames[$entry['analysis_type_id']] ?? ''));

            $entries[] = [
                'type' => $typeName !== '' ? $typeName : ($entry['sample_type_id'] !== '' ? 'Unknown sample type' : '—'),
                'analysis' => $analysisName !== '' ? $analysisName : ($entry['analysis_type_id'] !== '' ? 'Unknown analysis type' : ''),
                'params_count' => count($entry['parameters']),
                'param_names' => $paramNames,
            ];
        }

        return [
            'number_of_samples' => max(1, $numberOfSamples),
            'entries' => $entries,
        ];
    }

    /**
     * Build a normalized plan from a schedule's current columns.
     *
     * @return array{sample_details: list<array{sample_type_id: string, analysis_type_id: string, parameters: list<string>}>, number_of_samples: int}
     */
    public function currentPlanFromSchedule(SamplingSchedule $schedule): array
    {
        $details = is_array($schedule->sample_details) ? $schedule->sample_details : [];

        if ($details === [] && ! empty($schedule->sample_type_id)) {
            $details = [[
                'sample_type_id' => (string) $schedule->sample_type_id,
                'analysis_type_id' => (string) ($schedule->analysis_type_id ?? ''),
                'parameters' => array_values(array_filter(array_map('strval', $schedule->parameters ?? []))),
            ]];
        }

        return [
            'sample_details' => $this->normalizeSampleDetails($details),
            'number_of_samples' => max(1, (int) ($schedule->number_of_samples ?? 1)),
        ];
    }
}

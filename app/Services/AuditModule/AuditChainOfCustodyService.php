<?php

namespace App\Services\AuditModule;

use App\Models\AuditModule\Audit;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\AuditActivityLog;
use Carbon\Carbon;

class AuditChainOfCustodyService
{
    /**
     * Get chain of custody timeline for an audit or non-conformance
     */
    public function getChainOfCustody($model): array
    {
        $workflowLogs = $model->activityLogs()
            ->whereNotNull('workflow_step')
            ->orderBy('workflow_step')
            ->orderBy('created_at')
            ->get();

        $timeline = [];
        $previousLog = null;
        $totalDuration = 0;
        $stepDurations = [];
        $bottlenecks = [];

        foreach ($workflowLogs as $index => $log) {
            // Calculate duration from timestamps
            // For the first log, duration is always 0 (no previous step)
            // For subsequent logs, calculate from previous log's created_at to current log's created_at
            $duration = 0;
            
            if ($index > 0 && $previousLog) {
                // Calculate duration from previous log's created_at to current log's created_at
                $duration = $previousLog->created_at->diffInSeconds($log->created_at);
                // Ensure duration is positive (handle any negative values from incorrect logging)
                if ($duration < 0) {
                    $duration = abs($duration);
                }
            } elseif ($log->duration_seconds && $log->duration_seconds > 0) {
                // Use stored duration if it's valid and positive
                $duration = $log->duration_seconds;
            }
            
            // Format duration (first log will show 'N/A', others will show calculated time)
            $durationFormatted = $index > 0 && $duration > 0 ? $this->formatDuration($duration) : ($index == 0 ? 'N/A' : $this->formatDuration($duration));
            
            $timeline[] = [
                'log' => $log,
                'workflow_step' => $log->workflow_step,
                'workflow_step_name' => $log->workflow_step_name,
                'previous_status' => $log->previous_status,
                'current_status' => $log->current_status,
                'duration_seconds' => $duration,
                'duration_formatted' => $durationFormatted,
                'performed_by' => $log->performedBy?->name ?? 'System',
                'performed_at' => $log->created_at,
                'remarks' => $log->remarks ?? $log->description,
            ];

            // Track step durations (skip first log as it has no duration)
            if ($log->workflow_step && $index > 0) {
                if (!isset($stepDurations[$log->workflow_step])) {
                    $stepDurations[$log->workflow_step] = [
                        'step' => $log->workflow_step,
                        'step_name' => $log->workflow_step_name,
                        'total_seconds' => 0,
                        'count' => 0,
                    ];
                }
                $stepDurations[$log->workflow_step]['total_seconds'] += $duration;
                $stepDurations[$log->workflow_step]['count']++;
            }

            // Only add duration to total if it's not the first log
            if ($index > 0) {
                $totalDuration += $duration;
            }
            $previousLog = $log;
        }

        // Calculate average durations and identify bottlenecks
        $averageDurations = [];
        foreach ($stepDurations as $step => $data) {
            $average = $data['count'] > 0 ? $data['total_seconds'] / $data['count'] : 0;
            $averageDurations[$step] = [
                'step' => $step,
                'step_name' => $data['step_name'],
                'total_seconds' => $data['total_seconds'],
                'average_seconds' => $average,
                'average_formatted' => $this->formatDuration($average),
                'count' => $data['count'],
            ];
        }

        // Identify bottlenecks (steps taking longer than average)
        // Only calculate bottlenecks if we have at least 2 steps with valid durations
        if (count($averageDurations) > 1) {
            // Filter out steps with 0 duration for average calculation
            $validDurations = array_filter($averageDurations, function($data) {
                return $data['average_seconds'] > 0;
            });
            
            if (count($validDurations) > 1) {
                $overallAverage = array_sum(array_column($validDurations, 'average_seconds')) / count($validDurations);
                
                // Only identify bottlenecks if average is meaningful (> 0)
                if ($overallAverage > 0) {
                    foreach ($averageDurations as $step => $data) {
                        // Only flag as bottleneck if duration is significantly longer than average and > 0
                        if ($data['average_seconds'] > 0 && $data['average_seconds'] > $overallAverage * 1.5) {
                            $bottlenecks[] = [
                                'step' => $step,
                                'step_name' => $data['step_name'],
                                'duration' => $data['average_formatted'],
                                'average' => $this->formatDuration($overallAverage),
                                'percentage' => round(($data['average_seconds'] / $overallAverage) * 100),
                            ];
                        }
                    }
                }
            }
        }

        // Calculate time between steps
        $timeBetweenSteps = [];
        for ($i = 1; $i < count($timeline); $i++) {
            $current = $timeline[$i];
            $previous = $timeline[$i - 1];
            
            if ($current['log']->created_at && $previous['log']->created_at) {
                $seconds = $current['log']->created_at->diffInSeconds($previous['log']->created_at);
                $timeBetweenSteps[] = [
                    'from_step' => $previous['workflow_step'],
                    'from_step_name' => $previous['workflow_step_name'],
                    'to_step' => $current['workflow_step'],
                    'to_step_name' => $current['workflow_step_name'],
                    'duration_seconds' => $seconds,
                    'duration_formatted' => $this->formatDuration($seconds),
                ];
            }
        }

        return [
            'timeline' => $timeline,
            'total_duration' => $totalDuration,
            'total_duration_formatted' => $this->formatDuration($totalDuration),
            'step_durations' => $averageDurations,
            'time_between_steps' => $timeBetweenSteps,
            'bottlenecks' => $bottlenecks,
            'longest_step' => $this->getLongestStep($averageDurations),
            'fastest_step' => $this->getFastestStep($averageDurations),
        ];
    }

    /**
     * Format duration in seconds to human-readable format
     */
    private function formatDuration(float $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days} day" . ($days > 1 ? 's' : '');
        }
        if ($hours > 0) {
            $parts[] = "{$hours} hour" . ($hours > 1 ? 's' : '');
        }
        if ($minutes > 0 && count($parts) < 2) {
            $parts[] = "{$minutes} minute" . ($minutes > 1 ? 's' : '');
        }
        if ($seconds > 0 && count($parts) < 2) {
            $parts[] = "{$seconds} second" . ($seconds > 1 ? 's' : '');
        }

        return implode(' ', $parts) ?: '0 seconds';
    }

    /**
     * Get the longest step
     */
    private function getLongestStep(array $stepDurations): ?array
    {
        if (empty($stepDurations)) {
            return null;
        }

        $longest = null;
        $maxDuration = 0;

        foreach ($stepDurations as $step => $data) {
            if ($data['average_seconds'] > $maxDuration) {
                $maxDuration = $data['average_seconds'];
                $longest = $data;
            }
        }

        return $longest;
    }

    /**
     * Get the fastest step
     */
    private function getFastestStep(array $stepDurations): ?array
    {
        if (empty($stepDurations)) {
            return null;
        }

        $fastest = null;
        $minDuration = PHP_INT_MAX;

        foreach ($stepDurations as $step => $data) {
            if ($data['average_seconds'] < $minDuration && $data['average_seconds'] > 0) {
                $minDuration = $data['average_seconds'];
                $fastest = $data;
            }
        }

        return $fastest;
    }
}



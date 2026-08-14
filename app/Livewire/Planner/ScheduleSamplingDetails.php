<?php

namespace App\Livewire\Planner;

use App\AnalysisType;
use App\Models\SamplingSchedule;
use App\SampleType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ScheduleSamplingDetails extends Component
{
    public string $scheduleId;

    public string $calendarMonth;

    public bool $showTrfFormsModal = false;

    public ?string $viewingTrfScheduleId = null;

    public string $message = '';

    public string $messageType = '';

    public function mount(string $scheduleId): void
    {
        $this->scheduleId = $scheduleId;
        $schedule = $this->loadSchedule();
        $this->calendarMonth = ($schedule->sampling_datetime ?? now())->format('Y-m');
    }

    protected function loadSchedule(): SamplingSchedule
    {
        return SamplingSchedule::with([
            'client',
            'contact',
            'samplePoint',
            'personnel',
            'sample_type',
            'analysis_type',
            'submissionFormInstances.values.element',
            'submissionFormInstances.submissionForm.sampleTypeCategories',
            'submissionFormInstances.submittedBy',
            'samplePlanHistories.changedByUser',
        ])->visibleTo()->findOrFail($this->scheduleId);
    }

    public function getScheduleProperty(): SamplingSchedule
    {
        return $this->loadSchedule();
    }

    /**
     * @return Collection<int, SamplingSchedule>
     */
    public function getSeriesOccurrencesProperty(): Collection
    {
        $schedule = $this->schedule;
        if (empty($schedule->recurrence_group_id)) {
            return collect([$schedule]);
        }

        return SamplingSchedule::with(['submissionFormInstances'])
            ->visibleTo()
            ->where('recurrence_group_id', $schedule->recurrence_group_id)
            ->orderBy('sampling_datetime')
            ->get();
    }

    public function getViewingTrfScheduleProperty(): ?SamplingSchedule
    {
        if ($this->viewingTrfScheduleId === null || $this->viewingTrfScheduleId === '') {
            return null;
        }

        return SamplingSchedule::with([
            'client',
            'submissionFormInstances' => function ($query) {
                $query->orderByDesc('submitted_at')->orderByDesc('created_at');
            },
            'submissionFormInstances.submissionForm.sampleTypeCategories',
            'submissionFormInstances.submittedBy',
        ])->visibleTo()->find($this->viewingTrfScheduleId);
    }

    /**
     * Mini-calendar cells for the focused month.
     *
     * @return array{
     *     label: string,
     *     year: int,
     *     month: int,
     *     weeks: list<list<null|array{day: int, date: string, occurrences: list<array{id: string, time: string, status: string, is_current: bool}>}>>
     * }
     */
    public function getCalendarProperty(): array
    {
        $cursor = Carbon::createFromFormat('Y-m', $this->calendarMonth)->startOfMonth();
        $start = $cursor->copy()->startOfWeek(Carbon::MONDAY);
        $end = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $byDate = $this->seriesOccurrences
            ->filter(fn (SamplingSchedule $s) => $s->sampling_datetime !== null)
            ->groupBy(fn (SamplingSchedule $s) => $s->sampling_datetime->format('Y-m-d'));

        $weeks = [];
        $day = $start->copy();
        while ($day->lte($end)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                if ($day->month !== $cursor->month) {
                    $week[] = null;
                } else {
                    $key = $day->format('Y-m-d');
                    $dayOccurrences = ($byDate[$key] ?? collect())->map(function (SamplingSchedule $occurrence) {
                        $formCount = $occurrence->submissionFormInstances->count();
                        $status = $occurrence->is_collected
                            ? 'collected'
                            : ($formCount > 0 ? 'partial' : 'pending');

                        return [
                            'id' => (string) $occurrence->id,
                            'time' => $occurrence->sampling_datetime?->format('H:i') ?? '',
                            'status' => $status,
                            'is_current' => (string) $occurrence->id === $this->scheduleId,
                        ];
                    })->values()->all();

                    $week[] = [
                        'day' => $day->day,
                        'date' => $key,
                        'occurrences' => $dayOccurrences,
                    ];
                }
                $day->addDay();
            }
            $weeks[] = $week;
        }

        return [
            'label' => $cursor->format('F Y'),
            'year' => $cursor->year,
            'month' => $cursor->month,
            'weeks' => $weeks,
        ];
    }

    public function previousMonth(): void
    {
        $this->calendarMonth = Carbon::createFromFormat('Y-m', $this->calendarMonth)
            ->subMonthNoOverflow()
            ->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->calendarMonth = Carbon::createFromFormat('Y-m', $this->calendarMonth)
            ->addMonthNoOverflow()
            ->format('Y-m');
    }

    public function goToOccurrenceMonth(string $occurrenceId): void
    {
        $occurrence = $this->seriesOccurrences->firstWhere('id', $occurrenceId);
        if ($occurrence?->sampling_datetime) {
            $this->calendarMonth = $occurrence->sampling_datetime->format('Y-m');
        }
    }

    public function viewTrfForms(string $id): void
    {
        SamplingSchedule::query()->visibleTo()->findOrFail($id);
        $this->viewingTrfScheduleId = $id;
        $this->showTrfFormsModal = true;
    }

    public function closeTrfFormsModal(): void
    {
        $this->showTrfFormsModal = false;
        $this->viewingTrfScheduleId = null;
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function deleteOccurrence(string $id)
    {
        try {
            DB::beginTransaction();

            $target = SamplingSchedule::query()->visibleTo()->findOrFail($id);
            $groupId = $target->recurrence_group_id;
            $wasCurrent = (string) $target->id === $this->scheduleId;
            $target->delete();

            DB::commit();

            if ($wasCurrent) {
                $next = null;
                if (! empty($groupId)) {
                    $next = SamplingSchedule::query()
                        ->visibleTo()
                        ->where('recurrence_group_id', $groupId)
                        ->orderBy('sampling_datetime')
                        ->first();
                }

                if ($next) {
                    return redirect()
                        ->route('system-planner.schedule-sampling.show', ['schedule' => $next->id])
                        ->with('success', 'Scheduling occurrence deleted.');
                }

                return redirect()
                    ->route('system-planner.schedule-sampling')
                    ->with('success', 'Scheduling occurrence deleted.');
            }

            $this->message = 'Scheduling occurrence deleted.';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: '.$e->getMessage();
            $this->messageType = 'error';
        }

        return null;
    }

    public function deleteSeries()
    {
        $groupId = (string) ($this->schedule->recurrence_group_id ?? '');
        if ($groupId === '') {
            return $this->deleteOccurrence($this->scheduleId);
        }

        try {
            DB::beginTransaction();

            $occurrences = SamplingSchedule::query()
                ->visibleTo()
                ->where('recurrence_group_id', $groupId)
                ->get();

            foreach ($occurrences as $occurrence) {
                $occurrence->delete();
            }

            DB::commit();

            return redirect()
                ->route('system-planner.schedule-sampling')
                ->with('success', 'Recurring schedule deleted ('.$occurrences->count().' scheduling occurrence'.($occurrences->count() === 1 ? '' : 's').').');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: '.$e->getMessage();
            $this->messageType = 'error';
        }

        return null;
    }

    /**
     * @return list<array{type: string, analysis: string, params_count: int, param_names: list<string>}>
     */
    public function resolveDetailedSampleDetails(SamplingSchedule $schedule): array
    {
        $details = $schedule->sample_details;
        if (empty($details) || ! is_array($details)) {
            $info = [];
            if ($schedule->sample_type) {
                $parameterIds = array_values(array_unique(array_filter(array_map('strval', $schedule->parameters ?? []))));
                $paramNames = $parameterIds !== []
                    ? \App\Analyte::whereIn('id', $parameterIds)->pluck('name')->toArray()
                    : [];
                $info[] = [
                    'type' => (string) $schedule->sample_type->name,
                    'analysis' => (string) ($schedule->analysis_type->name ?? ''),
                    'params_count' => count($parameterIds),
                    'param_names' => $paramNames,
                ];
            }

            return $info;
        }

        $sampleTypeIds = [];
        $analysisTypeIds = [];
        $allParamIds = [];
        foreach ($details as $entry) {
            if (! empty($entry['sample_type_id'])) {
                $sampleTypeIds[] = (string) $entry['sample_type_id'];
            }
            if (! empty($entry['analysis_type_id'])) {
                $analysisTypeIds[] = (string) $entry['analysis_type_id'];
            }
            foreach (array_values(array_filter(array_map('strval', $entry['parameters'] ?? []))) as $paramId) {
                $allParamIds[] = $paramId;
            }
        }

        $sampleTypeNames = SampleType::query()
            ->whereIn('id', array_values(array_unique($sampleTypeIds)))
            ->pluck('name', 'id');
        $analysisTypeNames = AnalysisType::query()
            ->whereIn('id', array_values(array_unique($analysisTypeIds)))
            ->pluck('name', 'id');
        $paramNameMap = $allParamIds !== []
            ? \App\Analyte::query()->whereIn('id', array_values(array_unique($allParamIds)))->pluck('name', 'id')
            : collect();

        $result = [];
        foreach ($details as $entry) {
            $stId = (string) ($entry['sample_type_id'] ?? '');
            $atId = (string) ($entry['analysis_type_id'] ?? '');
            $parameterIds = array_values(array_unique(array_filter(array_map('strval', $entry['parameters'] ?? []))));
            $paramNames = [];
            foreach ($parameterIds as $paramId) {
                $name = trim((string) ($paramNameMap[$paramId] ?? ''));
                if ($name !== '') {
                    $paramNames[] = $name;
                }
            }

            $stName = trim((string) ($sampleTypeNames[$stId] ?? ''));
            $atName = trim((string) ($analysisTypeNames[$atId] ?? ''));
            if ($stName === '' && $stId !== '') {
                $stName = 'Unknown sample type';
            }
            if ($atName === '' && $atId !== '') {
                $atName = 'Unknown analysis type';
            }

            $result[] = [
                'type' => $stName !== '' ? $stName : '—',
                'analysis' => $atName,
                'params_count' => count($parameterIds),
                'param_names' => $paramNames,
            ];
        }

        return $result;
    }

    public function render()
    {
        return view('livewire.planner.schedule-sampling-details', [
            'schedule' => $this->schedule,
        ]);
    }
}

<?php

namespace App\Livewire\Planner;

use App\AnalysisType;
use App\Models\CRM\CRMCustomer;
use App\Models\SamplingSchedule;
use App\SampleType;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ActualCollectionsManager extends Component
{
    public string $search = '';

    public bool $showFilters = false;

    public string $filterDateFrom = '';

    public string $filterDateTo = '';

    public string $filterClientId = '';

    public string $filterPersonnelId = '';

    public ?SamplingSchedule $viewingSchedule = null;

    public bool $showViewModal = false;

    public function mount(): void
    {
        //
    }

    public function canViewAllCollections(): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        return $user->hasRole('admin') || $user->can('actual-collections.view.all');
    }

    public function getCollectionsProperty(): Collection
    {
        $companyId = getUserCompany();

        $query = SamplingSchedule::query()
            ->with([
                'client',
                'contact',
                'personnel',
                'submissionFormInstances.submissionForm.sampleTypes',
                'submissionFormInstances.submittedBy',
            ])
            ->where('company_id', $companyId)
            ->where('is_collected', true)
            ->orderByDesc('updated_at');

        if (! $this->canViewAllCollections()) {
            $userId = Auth::id();
            $query->where(function ($scopedQuery) use ($userId): void {
                $scopedQuery
                    ->where('personnel_id', $userId)
                    ->orWhereHas('submissionFormInstances', function ($instanceQuery) use ($userId): void {
                        $instanceQuery->where('submitted_by', $userId);
                    });
            });
        }

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($searchQuery) use ($term): void {
                $searchQuery
                    ->where('title', 'like', $term)
                    ->orWhere('location', 'like', $term)
                    ->orWhereHas('client', function ($clientQuery) use ($term): void {
                        $clientQuery->where('name', 'like', $term);
                    });
            });
        }

        if ($this->filterDateFrom !== '') {
            $query->whereDate('sampling_datetime', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo !== '') {
            $query->whereDate('sampling_datetime', '<=', $this->filterDateTo);
        }

        if ($this->filterClientId !== '') {
            $query->where('crm_customer_id', $this->filterClientId);
        }

        if ($this->filterPersonnelId !== '' && $this->canViewAllCollections()) {
            $query->where('personnel_id', $this->filterPersonnelId);
        }

        return $query->get();
    }

    public function getClientsProperty(): Collection
    {
        return CRMCustomer::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get();
    }

    public function getPersonnelProperty(): Collection
    {
        return User::query()
            ->where('is_client', 0)
            ->whereNull('supplier_id')
            ->where('active', 1)
            ->where('is_support_staff', 0)
            ->orderBy('name')
            ->get();
    }

    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function resetFilters(): void
    {
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->filterClientId = '';
        $this->filterPersonnelId = '';
    }

    public function viewCollection(string $id): void
    {
        $schedule = SamplingSchedule::query()
            ->with([
                'client',
                'contact',
                'personnel',
                'submissionFormInstances.submissionForm.sampleTypes',
                'submissionFormInstances.submittedBy',
            ])
            ->where('company_id', getUserCompany())
            ->where('is_collected', true)
            ->findOrFail($id);

        if (! $this->userCanAccessSchedule($schedule)) {
            abort(403);
        }

        $this->viewingSchedule = $schedule;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewingSchedule = null;
    }

    public function collectedAt(SamplingSchedule $schedule): ?string
    {
        $submittedAt = $schedule->submissionFormInstances
            ->pluck('submitted_at')
            ->filter()
            ->sortDesc()
            ->first();

        if ($submittedAt !== null) {
            return $submittedAt->format('Y-m-d H:i');
        }

        return $schedule->updated_at?->format('Y-m-d H:i');
    }

    /**
     * @return list<array{type: string, analysis: string, params_count: int}>
     */
    public function resolveSampleDetails(SamplingSchedule $schedule): array
    {
        $details = $schedule->sample_details;
        if (empty($details) || ! is_array($details)) {
            $info = [];
            if ($schedule->sample_type) {
                $info[] = [
                    'type' => $schedule->sample_type->name,
                    'analysis' => $schedule->analysis_type->name ?? '',
                    'params_count' => count($schedule->parameters ?? []),
                ];
            }

            return $info;
        }

        $result = [];
        foreach ($details as $entry) {
            $stName = '';
            $atName = '';
            $paramsCount = count($entry['parameters'] ?? []);

            if (! empty($entry['sample_type_id'])) {
                $st = SampleType::find($entry['sample_type_id']);
                $stName = $st ? $st->name : '';
            }
            if (! empty($entry['analysis_type_id'])) {
                $at = AnalysisType::find($entry['analysis_type_id']);
                $atName = $at ? $at->name : '';
            }

            $result[] = [
                'type' => $stName,
                'analysis' => $atName,
                'params_count' => $paramsCount,
            ];
        }

        return $result;
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
                $parameterIds = $schedule->parameters ?? [];
                $paramNames = [];
                if (! empty($parameterIds)) {
                    $paramNames = \App\Analyte::whereIn('id', $parameterIds)
                        ->pluck('name')
                        ->toArray();
                }
                $info[] = [
                    'type' => $schedule->sample_type->name,
                    'analysis' => $schedule->analysis_type->name ?? '',
                    'params_count' => count($parameterIds),
                    'param_names' => $paramNames,
                ];
            }

            return $info;
        }

        $result = [];
        foreach ($details as $entry) {
            $stName = '';
            $atName = '';
            $paramNames = [];
            $parameterIds = $entry['parameters'] ?? [];

            if (! empty($entry['sample_type_id'])) {
                $st = SampleType::find($entry['sample_type_id']);
                $stName = $st ? $st->name : '';
            }
            if (! empty($entry['analysis_type_id'])) {
                $at = AnalysisType::find($entry['analysis_type_id']);
                $atName = $at ? $at->name : '';
            }
            if (! empty($parameterIds)) {
                $paramNames = \App\Analyte::whereIn('id', $parameterIds)
                    ->pluck('name')
                    ->toArray();
            }

            $result[] = [
                'type' => $stName,
                'analysis' => $atName,
                'params_count' => count($parameterIds),
                'param_names' => $paramNames,
            ];
        }

        return $result;
    }

    private function userCanAccessSchedule(SamplingSchedule $schedule): bool
    {
        if ($this->canViewAllCollections()) {
            return true;
        }

        $userId = Auth::id();

        if ($schedule->personnel_id === $userId) {
            return true;
        }

        return $schedule->submissionFormInstances
            ->contains(fn ($instance): bool => (string) $instance->submitted_by === (string) $userId);
    }

    public function render()
    {
        return view('livewire.planner.actual-collections-manager');
    }
}

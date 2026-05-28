<?php

namespace App\Livewire\SkillsMatrix;

use App\Livewire\SkillsMatrix\Concerns\HasMatrixListFilters;
use App\Services\SkillsMatrix\MatrixQueryService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Livewire\Component;

class StaffProfilesIndex extends Component
{
    use HasMatrixListFilters;

    public function render(MatrixQueryService $query)
    {
        $filtered = $query->staffOnCapabilityMatrices()
            ->filter(function ($role) {
                if ($this->search === '') {
                    return true;
                }
                $name = $role->user->name ?? '';
                $job = $role->jobdescription->name ?? '';

                return stripos($name, $this->search) !== false
                    || stripos($job, $this->search) !== false;
            })
            ->unique('user_id')
            ->values();

        $page = $this->getPage();
        $paginated = new LengthAwarePaginator(
            $filtered->forPage($page, $this->perPage)->values(),
            $filtered->count(),
            $this->perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );

        $averages = [];
        foreach ($paginated as $role) {
            $userId = (string) $role->user_id;
            if (isset($averages[$userId])) {
                continue;
            }
            $details = $query->userCapabilityDetails($userId);
            $averages[$userId] = $details->isEmpty()
                ? 0
                : round($details->avg(fn ($d) => (int) ($d->proficiency->code ?? 0)), 1);
        }

        return view('livewire.skills-matrix.staff-profiles-index', [
            'staff' => $paginated,
            'averages' => $averages,
        ]);
    }
}

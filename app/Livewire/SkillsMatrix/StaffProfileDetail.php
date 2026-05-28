<?php

namespace App\Livewire\SkillsMatrix;

use App\Services\SkillsMatrix\MatrixQueryService;
use App\User;
use Livewire\Component;

class StaffProfileDetail extends Component
{
    public string $userId;

    public function mount(string $userId): void
    {
        $this->userId = $userId;
    }

    public function render(MatrixQueryService $query)
    {
        $user = User::findOrFail($this->userId);
        $details = $query->userCapabilityDetails($this->userId);
        $byDomain = $details->groupBy(fn ($d) => $d->competency->competencyarea->description ?? 'Other')
            ->map(function ($items) {
                return round($items->avg(fn ($d) => (int) ($d->proficiency->code ?? 0)), 1);
            });

        $avg = $details->isEmpty() ? 0 : round($details->avg(fn ($d) => (int) ($d->proficiency->code ?? 0)), 1);

        return view('livewire.skills-matrix.staff-profile-detail', compact('user', 'byDomain', 'avg'));
    }
}

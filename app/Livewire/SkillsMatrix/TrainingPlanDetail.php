<?php

namespace App\Livewire\SkillsMatrix;

use App\Livewire\SkillsMatrix\Concerns\HasMatrixFlash;
use App\Models\SkillsMatrix\TrainingPlannerDetails;
use App\Models\SkillsMatrix\TrainingPlannerHeader;
use App\Services\SkillsMatrix\EvaluationApprovalService;
use App\Services\SkillsMatrix\MatrixQueryService;
use App\Services\SkillsMatrix\TrainingSessionService;
use Livewire\Component;
use Livewire\WithFileUploads;

class TrainingPlanDetail extends Component
{
    use HasMatrixFlash;
    use WithFileUploads;

    public string $planId;

    public string $activeTab = 'overview';

    public ?string $selectedSessionId = null;

    public string $attendeeSearch = '';

    public bool $showAttendeeDropdown = false;

    public string $selectedAttendeeId = '';

    public $uploadMaterial;

    public string $evaluationUserId = '';

    public string $evaluationCompetencyId = '';

    public string $evaluationProficiencyId = '';

    public string $evaluationNotes = '';

    public function mount(string $planId): void
    {
        $this->planId = $planId;
    }

    public function selectSession(string $sessionId, string $tab = 'attendance'): void
    {
        $this->selectedSessionId = $sessionId;
        $this->activeTab = in_array($tab, ['attendance', 'materials', 'evaluation'], true)
            ? $tab
            : 'attendance';
    }

    public function clearSelectedSession(): void
    {
        $this->selectedSessionId = null;
        $this->activeTab = 'attendance';
        $this->selectedAttendeeId = '';
        $this->evaluationUserId = '';
        $this->evaluationProficiencyId = '';
        $this->evaluationNotes = '';
    }

    public function sessionTitle(TrainingPlannerDetails $session): string
    {
        if ((int) $session->is_others === 1) {
            return $session->other_competency ?: 'Additional training';
        }

        $competency = $session->trainingNeedDetail?->capabilitydetail?->competency;

        return $competency?->competencydescription?->description ?? 'Training session';
    }

    public function sessionArea(TrainingPlannerDetails $session): ?string
    {
        if ((int) $session->is_others === 1) {
            return 'Additional training';
        }

        return $session->trainingNeedDetail?->capabilitydetail?->competency?->competencyarea?->description;
    }

    public function inviteAttendee(TrainingSessionService $service): void
    {
        $this->authorize('skills-matrix.components.training-plan.attend.manage');
        if (! $this->selectedSessionId || ! $this->selectedAttendeeId) {
            return;
        }
        $service->inviteAttendee($this->selectedSessionId, $this->selectedAttendeeId);
        $this->selectedAttendeeId = '';
        $this->flash('Attendee invited.');
    }

    public function confirmAttendance(string $attendanceId, TrainingSessionService $service): void
    {
        $this->authorize('skills-matrix.components.training-plan.attend.confirm');
        $service->confirmAttendance($attendanceId);
        $this->flash('Attendance confirmed.');
    }

    public function markPresent(string $attendanceId, TrainingSessionService $service): void
    {
        $this->authorize('skills-matrix.components.training-plan.attend.manage');
        $service->markPresent($attendanceId);
        $this->flash('Marked as present.');
    }

    public function uploadSessionMaterial(TrainingSessionService $service): void
    {
        $this->authorize('skills-matrix.components.training-plan.materials.manage');
        $this->validate(['uploadMaterial' => 'required|file|max:20480']);
        if ($this->selectedSessionId) {
            $service->uploadMaterial($this->selectedSessionId, $this->uploadMaterial);
            $this->uploadMaterial = null;
            $this->flash('Material uploaded.');
        }
    }

    public function deleteMaterial(string $materialId, TrainingSessionService $service): void
    {
        $this->authorize('skills-matrix.components.training-plan.materials.manage');
        $service->deleteMaterial($materialId);
        $this->flash('Material removed.');
    }

    public function saveEvaluation(bool $submit, TrainingSessionService $service): void
    {
        $this->authorize('skills-matrix.components.training-plan.evaluation.submit');
        if (! $this->selectedSessionId || ! $this->evaluationUserId || ! $this->evaluationProficiencyId) {
            return;
        }
        $service->saveEvaluation(
            $this->selectedSessionId,
            $this->evaluationUserId,
            $this->evaluationCompetencyId ?: null,
            $this->evaluationProficiencyId,
            $this->evaluationNotes,
            $submit,
        );
        $this->flash($submit ? 'Evaluation submitted for approval.' : 'Evaluation draft saved.');
    }

    public function updateSessionMeta(): void
    {
        $this->authorize('skills-matrix.components.training-plan.edit');
        $session = TrainingPlannerDetails::findOrFail($this->selectedSessionId);
        $session->save();
        $this->flash('Session updated.');
    }

    /**
     * @return array{label: string, class: string}
     */
    public function trainingStatusMeta(?int $status): array
    {
        return match ((int) ($status ?? 0)) {
            1 => ['label' => 'Done', 'class' => 'sm-training-status--done'],
            2 => ['label' => 'Cancelled', 'class' => 'sm-training-status--cancelled'],
            default => ['label' => 'Planned', 'class' => 'sm-training-status--planned'],
        };
    }

    public function render(
        MatrixQueryService $matrixQuery,
        TrainingSessionService $sessionService,
    ) {
        $plan = TrainingPlannerHeader::with(['trainneed.capability', 'details', 'others'])->findOrFail($this->planId);
        $proficiencies = $matrixQuery->proficiencies();
        $trainingProficiencies = $matrixQuery->trainingProficiencies();
        $attendance = $this->selectedSessionId
            ? $sessionService->attendanceForSession($this->selectedSessionId)
            : collect();
        $materials = $this->selectedSessionId
            ? $sessionService->materialsForSession($this->selectedSessionId)
            : collect();
        $staffOptions = $sessionService->activeStaffOptions();

        $competencySessionGroups = TrainingPlannerDetails::query()
            ->with([
                'trainingNeedDetail.capabilitydetail.competency.competencydescription',
                'trainingNeedDetail.capabilitydetail.competency.competencyarea',
            ])
            ->where('training_plan_header_id', $this->planId)
            ->where('is_others', 0)
            ->whereNull('deleted_at')
            ->orderBy('week_no')
            ->get()
            ->groupBy(fn (TrainingPlannerDetails $session): string => $session->trainingNeedDetail?->capabilitydetail?->competency_id
                ?? 'session-'.$session->id);

        $otherSessions = TrainingPlannerDetails::query()
            ->where('training_plan_header_id', $this->planId)
            ->where('is_others', 1)
            ->whereNull('deleted_at')
            ->orderBy('week_no')
            ->get();

        $selectedSession = null;
        if ($this->selectedSessionId) {
            $selectedSession = TrainingPlannerDetails::query()
                ->with([
                    'trainingNeedDetail.capabilitydetail.competency.competencydescription',
                    'trainingNeedDetail.capabilitydetail.competency.competencyarea',
                ])
                ->where('training_plan_header_id', $this->planId)
                ->whereNull('deleted_at')
                ->find($this->selectedSessionId);
        }

        $sessionCount = $competencySessionGroups->count() + $otherSessions->count();

        return view('livewire.skills-matrix.training-plan-detail', compact(
            'plan',
            'proficiencies',
            'trainingProficiencies',
            'attendance',
            'materials',
            'staffOptions',
            'competencySessionGroups',
            'otherSessions',
            'selectedSession',
            'sessionCount',
        ));
    }
}

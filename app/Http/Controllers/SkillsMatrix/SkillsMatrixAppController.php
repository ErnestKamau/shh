<?php

namespace App\Http\Controllers\SkillsMatrix;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SkillsMatrixAppController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:matrix.module.access');
    }

    protected function render(string $componentType, string $pageTitle, array $extra = []): View
    {
        return view('livewire.layout.skills-matrix-app', array_merge([
            'componentType' => $componentType,
            'pageTitle' => $pageTitle,
        ], $extra));
    }

    public function dashboard(): View
    {
        return $this->render('dashboard', 'Skills Dashboard');
    }

    public function skillsMatrixIndex(): View
    {
        $this->authorize('skills-matrix.components.skills-matrix.view');

        return $this->render('skills-matrix-index', 'Skills Matrix');
    }

    public function skillsMatrixShow(string $id): View
    {
        $this->authorize('skills-matrix.components.skills-matrix.view');

        return $this->render('skills-matrix-grid', 'Skills Matrix', ['matrixId' => $id]);
    }

    public function capabilityIndex(): View
    {
        $this->authorize('skills-matrix.components.capability.view');

        return $this->render('capability-index', 'Capability Matrix');
    }

    public function capabilityShow(string $id): View
    {
        $this->authorize('skills-matrix.components.capability.view');

        return $this->render('capability-grid', 'Capability Matrix', ['capabilityId' => $id]);
    }

    public function staffProfiles(): View
    {
        $this->authorize('skills-matrix.components.capability.view');

        return $this->render('staff-profiles', 'Staff Profiles');
    }

    public function staffProfileShow(string $userId): View
    {
        $this->authorize('skills-matrix.components.capability.view');

        return $this->render('staff-profile-detail', 'Staff Profile', ['userId' => $userId]);
    }

    public function educationRequirements(): View
    {
        $this->authorize('skills-matrix.components.skills-matrix.view');

        return $this->render('education-requirements', 'Education Requirements');
    }

    public function trainingNeedsIndex(): View
    {
        $this->authorize('skills-matrix.components.training-needs.view');

        return $this->render('training-needs-index', 'Training Needs');
    }

    public function trainingNeedsShow(string $id): View
    {
        $this->authorize('skills-matrix.components.training-needs.view');

        return $this->render('training-needs-grid', 'Training Needs', ['trainingNeedId' => $id]);
    }

    public function trainingPlanIndex(): View
    {
        $this->authorize('skills-matrix.components.training-plan.view');

        return $this->render('training-plan-index', 'Training Plan');
    }

    public function trainingPlanShow(string $id): View
    {
        $this->authorize('skills-matrix.components.training-plan.view');

        return $this->render('training-plan-detail', 'Training Plan', ['planId' => $id]);
    }

    public function evaluationApprovals(): View
    {
        $this->authorize('skills-matrix.components.training-plan.evaluation.approve');

        return $this->render('evaluation-approvals', 'Evaluation Approvals');
    }

    public function modulePreConfigs(string $config): View
    {
        $this->authorize('skills-matrix.components.module-preconfigs.view');

        return $this->render('module-pre-configs', $config . ' Configuration', [
            'config' => $config,
            'module' => 'Skills-Matrix',
        ]);
    }

    public function reports(): View
    {
        $this->authorize('skills-matrix.components.skills-matrix.view');

        return $this->render('reports', 'Reports');
    }

    public function legacyConfigRedirect(): RedirectResponse
    {
        return redirect()
            ->route('matrix.dashboard')
            ->with('warning', 'Legacy matrix configuration has been retired. Use Skills Matrix and Module Configurations.');
    }

    public function downloadTrainingMaterial(string $materialId): StreamedResponse
    {
        $this->authorize('skills-matrix.components.training-plan.view');

        return app(\App\Services\SkillsMatrix\TrainingSessionService::class)
            ->downloadMaterial($materialId);
    }
}

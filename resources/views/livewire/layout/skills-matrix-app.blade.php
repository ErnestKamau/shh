@extends('layouts.skillsmatrix.layout.app', ['dataTable' => false, 'select2' => false])

@section('title2')
<title>{{ $pageTitle ?? 'Skills Matrix' }}</title>
@include('livewire.skills-matrix.partials.matrix-styles')
@endsection

@section('content2')
<main>
    @php
        $breadcrumbItems = [
            ['link' => route('matrix.dashboard'), 'name' => 'Skills Matrix', 'icon' => null],
        ];
        $type = $componentType ?? '';
        if ($type === 'skills-matrix-index') {
            $breadcrumbItems[] = ['link' => route('matrix'), 'name' => 'Skills Matrix', 'icon' => null];
        } elseif ($type === 'skills-matrix-grid' && isset($matrixId)) {
            $breadcrumbItems[] = ['link' => route('matrix'), 'name' => 'Skills Matrix', 'icon' => null];
            $breadcrumbItems[] = ['link' => route('show-matrix', $matrixId), 'name' => 'Matrix Detail', 'icon' => null];
        } elseif ($type === 'capability-index') {
            $breadcrumbItems[] = ['link' => route('capability-index'), 'name' => 'Capability Matrix', 'icon' => null];
        } elseif ($type === 'capability-grid' && isset($capabilityId)) {
            $breadcrumbItems[] = ['link' => route('capability-index'), 'name' => 'Capability Matrix', 'icon' => null];
            $breadcrumbItems[] = ['link' => route('capability.show', $capabilityId), 'name' => 'Capability Detail', 'icon' => null];
        } elseif ($type === 'training-plan-detail' && isset($planId)) {
            $breadcrumbItems[] = ['link' => route('train.plan.index'), 'name' => 'Training Plan', 'icon' => null];
            $breadcrumbItems[] = ['link' => route('train.plan.show', $planId), 'name' => 'Plan Detail', 'icon' => null];
        }
    @endphp
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

    @if($componentType === 'dashboard')
        @livewire('skills-matrix.skills-matrix-dashboard')
    @elseif($componentType === 'skills-matrix-index')
        @livewire('skills-matrix.skills-matrix-manager')
    @elseif($componentType === 'skills-matrix-grid')
        @livewire('skills-matrix.skills-matrix-grid', ['matrixId' => $matrixId])
    @elseif($componentType === 'capability-index')
        @livewire('skills-matrix.capability-matrix-manager')
    @elseif($componentType === 'capability-grid')
        @livewire('skills-matrix.capability-matrix-grid', ['capabilityId' => $capabilityId])
    @elseif($componentType === 'staff-profiles')
        @livewire('skills-matrix.staff-profiles-index')
    @elseif($componentType === 'staff-profile-detail')
        @livewire('skills-matrix.staff-profile-detail', ['userId' => $userId])
    @elseif($componentType === 'education-requirements')
        @livewire('skills-matrix.education-requirements-manager')
    @elseif($componentType === 'training-needs-index')
        @livewire('skills-matrix.training-needs-manager')
    @elseif($componentType === 'training-needs-grid')
        @livewire('skills-matrix.training-needs-grid', ['trainingNeedId' => $trainingNeedId])
    @elseif($componentType === 'training-plan-index')
        @livewire('skills-matrix.training-plan-manager')
    @elseif($componentType === 'training-plan-detail')
        @livewire('skills-matrix.training-plan-detail', ['planId' => $planId])
    @elseif($componentType === 'evaluation-approvals')
        @livewire('skills-matrix.evaluation-approval-queue')
    @elseif($componentType === 'module-pre-configs')
        @livewire('skills-matrix.module-pre-configs-manager', ['config' => $config, 'module' => $module])
    @elseif($componentType === 'reports')
        @livewire('skills-matrix.skills-matrix-reports')
    @endif
</main>
@endsection

@section('script2')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('skills-matrix-charts-updated', () => {
            if (typeof window.initSkillsMatrixDashboardCharts === 'function') {
                window.initSkillsMatrixDashboardCharts();
            }
        });
    });
</script>
@stack('skills-matrix-scripts')
@endsection

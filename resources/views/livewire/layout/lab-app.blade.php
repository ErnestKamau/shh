@extends('layouts.lab.layout.app')

@section('title2')
<title>{{ $pageTitle ?? 'Lab Management' }}</title>
@endsection

@section('content2')
<main>
    <?php
    $breadcrumbItems = [];

    // Always start with Dashboard
    $breadcrumbItems[] = [
        'link' => route('dashboard-lab'),
        'name' => 'Dashboard',
        'icon' => null
    ];

    // Only include Sample Types breadcrumb for sample-related pages
    if (isset($componentType) && in_array($componentType, ['sample-types', 'analytes', 'analysis-types', 'elements'])) {
        $breadcrumbItems[] = [
            'link' => route('livewire.sample-types'),
            'name' => 'Sample Types',
            'icon' => null
        ];
    }

    // Add Analytes if we're in analytes section
    if (isset($componentType) && $componentType === 'analytes') {
        $breadcrumbItems[] = [
            'link' => route('analytes'),
            'name' => 'Analytes',
            'icon' => null
        ];
    }

    // Add Remedies if we're in remedies section
    if (isset($componentType) && $componentType === 'remedies') {
        $breadcrumbItems[] = [
            'link' => route('remedies.index'),
            'name' => 'Remedies',
            'icon' => null
        ];
    }

    // Add Remedy Details if we're in remedy details section
    if (isset($componentType) && $componentType === 'remedy-details') {
        $breadcrumbItems[] = [
            'link' => route('remedies.index'),
            'name' => 'Remedies',
            'icon' => null
        ];
        if (isset($remedyHeader) && $remedyHeader) {
            $breadcrumbItems[] = [
                'link' => '#',
                'name' => $remedyHeader->name,
                'icon' => null
            ];
        }
    }

    // Add Analysis Types if we have a sample type
    if (isset($sampleType) && $sampleType) {
        $breadcrumbItems[] = [
            'link' => '#',
            'name' => $sampleType->name,
            'icon' => null
        ];
    }

    // Add Analysis Elements if we have an analysis type
    if (isset($analysisType) && $analysisType) {
        $breadcrumbItems[] = [
            'link' => route('livewire.analysis-types', ['sampleTypeId' => $analysisType->sample_type_id ?? null]),
            'name' => $analysisType->sample_type->name ?? 'Sample Type',
            'icon' => null
        ];
        $breadcrumbItems[] = [
            'link' => '#',
            'name' => $analysisType->name,
            'icon' => null
        ];
    }

    // Add Rating Hub if we're in ratings section
    if (isset($componentType) && $componentType === 'ratings') {
        $breadcrumbItems[] = [
            'link' => route('ratings.index'),
            'name' => 'Rating Hub',
            'icon' => null
        ];
    }

    // Add Rating Details if we're in rating details section
    if (isset($componentType) && $componentType === 'rating-details') {
        $breadcrumbItems[] = [
            'link' => route('ratings.index'),
            'name' => 'Rating Hub',
            'icon' => null
        ];
        if (isset($ratingHeader) && $ratingHeader) {
            $breadcrumbItems[] = [
                'link' => '#',
                'name' => $ratingHeader->name,
                'icon' => null
            ];
        }
    }

    // Add Standards if we're in standards section
    if (isset($componentType) && $componentType === 'standards') {
        $breadcrumbItems[] = [
            'link' => route('livewire.standards'),
            'name' => 'Standards',
            'icon' => null
        ];
    }

    // Add Standard Analytes if we're in standard analytes section
    if (isset($componentType) && $componentType === 'standard-analytes') {
        $breadcrumbItems[] = [
            'link' => route('livewire.standards'),
            'name' => 'Standards',
            'icon' => null
        ];
        if (isset($standard) && $standard) {
            $breadcrumbItems[] = [
                'link' => '#',
                'name' => $standard->name,
                'icon' => null
            ];
        }
    }

    // Add Report Format Builder breadcrumbs
    if (isset($componentType) && $componentType === 'report-format-builder') {
        $breadcrumbItems[] = [
            'link' => route('livewire.report-formats'),
            'name' => 'Report Formats',
            'icon' => null
        ];
        $breadcrumbItems[] = [
            'link' => '#',
            'name' => 'Report Builder',
            'icon' => null
        ];
    }
    if (isset($componentType) && $componentType === 'workflow-approvals') {
        $breadcrumbItems[] = [
            'link' => route('livewire.workflow-approvals'),
            'name' => 'Checklist Approvals',
            'icon' => null
        ];
    }
    if (isset($componentType) && $componentType === 'zones') {
        $breadcrumbItems[] = [
            'link' => route('module-pre-configs', ['config' => $config, 'module' => $module]),
            'name' => 'Zone',
            'icon' => null
        ];
    }
    if (isset($componentType) && $componentType === 'monitoring') {
        $breadcrumbItems[] = [
            'link' => route('livewire.monitoring'),
            'name' => 'Monitoring',
            'icon' => null
        ];
    }
    ?>
    <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

    <!-- Dynamic Livewire Component -->
    @if($componentType === 'sample-types')
    @livewire('samples.sample-type-manager')
    @elseif($componentType === 'analytes')
    @livewire('lab.analyte-manager')
    @elseif($componentType === 'analysis-types')
    @livewire('analysis.analysis-type-manager', ['sampleTypeId' => $sampleType->id ?? null])
    @elseif($componentType === 'elements')
    @livewire('analysis.element-manager', ['analysisTypeId' => $analysisType->id ?? null])
    @elseif($componentType === 'remedies')
    @livewire('remedies.remedy-manager')
    @elseif($componentType === 'remedy-details')
    @livewire('remedies.remedy-details-manager', ['remedyHeaderId' => $remedyHeaderId ?? null])
    @elseif($componentType === 'ratings')
    @livewire('ratings.rating-manager')
    @elseif($componentType === 'rating-details')
    @livewire('ratings.rating-details-manager', ['ratingHeaderId' => $ratingHeaderId ?? null])
    @elseif($componentType === 'report-formats')
    @livewire('reports.report-format-manager')
    @elseif($componentType === 'workflow-approvals')
    @livewire('lab.approval-config-manager')
    @elseif($componentType === 'zones')
    @livewire('personnel.zones.configuration-manager', ['module' => $module])
    @elseif($componentType === 'standards')
    @livewire('standards.standards-page')
    @elseif($componentType === 'standard-analytes')
    @livewire('standards.standard-analytes-manager', ['standardId' => $standard->id])
    @elseif($componentType === 'standard-manager')
    @elseif($componentType === 'monitoring')
    @livewire('monitoring.monitoring-dashboard')
    @elseif($componentType === 'report-format-builder')
    @livewire('reports.report-format-builder', ['reportFormatId' => $reportFormatId ?? null])
    @elseif($componentType === 'template-create')
    @livewire('monitoring.create-monitoring-template')
    @endif
</main>
@endsection

@section('script2')
@if(isset($componentType) && $componentType === 'report-format-builder')
{{-- Summernote assets for Report Format Builder only --}}
<link rel="stylesheet" href="{{ asset('assets/js/libs/summernote/summernote.css') }}">
<script src="{{ asset('assets/js/libs/summernote/summernote.js') }}"></script>

<script>
    $(document).ready(function() {
        if (!$.isFunction($.fn.summernote)) {
            return;
        }

        $('.summernote-editor').summernote({
            height: 150,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['codeview']]
            ]
        });
    });
</script>
@endif
@endsection
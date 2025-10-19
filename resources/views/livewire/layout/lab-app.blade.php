@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

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
    
    // Add Sample Types
    $breadcrumbItems[] = [
        'link' => route('livewire.sample-types'),
        'name' => 'Sample Types',
        'icon' => null
    ];
    
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
    @elseif($componentType === 'standards')
        @livewire('standards.standards-page')
    @elseif($componentType === 'standard-analytes')
        @livewire('standards.standard-analytes-manager', ['standardId' => $standard->id])
    @elseif($componentType === 'standard-manager')
        @livewire('standards.standard-manager')
    @endif
</main>
@endsection

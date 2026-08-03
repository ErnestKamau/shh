@extends('layouts.risk.layout.app')

@section('title2')
<title>Risk Management Dashboard - JASIRI LIMS</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('risk.dashboard'),
            'name' => 'Risk Management Dashboard',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="mdi mdi-alert-octagon"></i> Risk Management Dashboard
            </h2>
        </div>
    </div>

    @livewire('risk-module.risk-dashboard')
</main>
@endsection


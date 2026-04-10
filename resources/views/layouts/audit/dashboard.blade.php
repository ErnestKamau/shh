@extends('layouts.audit.layout.app')

@section('title2')
<title>Audit & CAPA Dashboard - JASIRI LIMS</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('audit.dashboard'),
            'name' => 'Audit & CAPA Dashboard',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="mdi mdi-clipboard-check"></i> Audit & CAPA Dashboard
            </h2>
        </div>
    </div>

    @livewire('audit-module.audit-dashboard')
</main>
@endsection


@extends('layouts.audit.layout.app')

@section('title2')
<title>Audit Summary Report - JASIRI LIMS</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('audit.dashboard'),
            'name' => 'Audit & CAPA Dashboard',
            'icon' => null
        ),
        array(
            'link' => route('audit.reports.index'),
            'name' => 'Reports & KPIs',
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => 'Audit Summary',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('audit-module.reports.audit-summary-report')
</main>
@endsection

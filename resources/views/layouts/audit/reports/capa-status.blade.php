@extends('layouts.audit.layout.app')

@section('title2')
<title>CAPA Status Report - JASIRI LIMS</title>
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
            'name' => 'CAPA Status',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('audit-module.reports.c-a-p-a-status-report')
</main>
@endsection

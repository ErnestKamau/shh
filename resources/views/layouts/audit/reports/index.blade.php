@extends('layouts.audit.layout.app')

@section('title2')
<title>Audit Reports & KPIs - JASIRI LIMS</title>
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
            'link' => '#',
            'name' => 'Reports & KPIs',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('audit-module.reports.reports-index')
</main>
@endsection

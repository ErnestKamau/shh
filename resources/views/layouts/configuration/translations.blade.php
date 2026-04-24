@extends('layouts.configuration.layout.app')

@section('title2')
<title>Languages & Translations</title>
@endsection

@section('content2')
<div class="container-fluid px-2">
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-3">
            <h4 class="mb-1"><i class="mdi mdi-translate text-primary"></i> Languages &amp; Translations</h4>
            <p class="mb-0 text-muted">Manage languages, translation keys, and bulk imports from a single workspace.</p>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4 px-1" id="translationTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" id="languages-tab" data-toggle="tab" href="#languages" role="tab" aria-controls="languages" aria-selected="true"><i class="mdi mdi-translate mr-1"></i> Languages {{ __('system.system') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link font-weight-bold" id="keys-tab" data-toggle="tab" href="#keys" role="tab" aria-controls="keys" aria-selected="false"><i class="mdi mdi-format-list-bulleted mr-1"></i> Translation Keys</a>
        </li>
        @if(auth()->user()->can('System.components.Translations.Bulk Import') || auth()->user()->can('System.permission'))
        <li class="nav-item">
            <a class="nav-link font-weight-bold" id="import-tab" data-toggle="tab" href="#import" role="tab" aria-controls="import" aria-selected="false"><i class="mdi mdi-database-import mr-1"></i> Bulk Import</a>
        </li>
        @endif
    </ul>

    <div class="tab-content" id="translationTabsContent">
        <div class="tab-pane fade show active" id="languages" role="tabpanel" aria-labelledby="languages-tab">
            @livewire('system.language-list')
        </div>
        <div class="tab-pane fade" id="keys" role="tabpanel" aria-labelledby="keys-tab">
            @livewire('system.translation-list')
        </div>
        @if(auth()->user()->can('System.components.Translations.Bulk Import') || auth()->user()->can('System.permission'))
        <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
            @livewire('system.translation-bulk-upload')
        </div>
        @endif
    </div>
</div>
@endsection

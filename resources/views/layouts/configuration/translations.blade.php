@extends('layouts.configuration.layout.app')

@section('title2')
<title>{{ __('system.languages_and_translations') }}</title>
@endsection

@section('content2')
<div class="container-fluid px-2">
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body py-3">
            <h4 class="mb-1"><i class="mdi mdi-translate text-primary"></i> {{ __('system.languages_and_translations') }}</h4>
            <p class="mb-0 text-muted">{{ __('system.manage_languages_blurb') }}</p>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4 px-1" id="translationTabs" role="tablist">
        @if(auth()->user()->can('system.translations.language.view'))
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" id="languages-tab" data-toggle="tab" href="#languages" role="tab" aria-controls="languages" aria-selected="true"><i class="mdi mdi-translate mr-1"></i> {{ __('system.languages') }}</a>
        </li>
        @endif
        @if(auth()->user()->can('system.translations.keys.view'))
        <li class="nav-item">
            <a class="nav-link font-weight-bold" id="keys-tab" data-toggle="tab" href="#keys" role="tab" aria-controls="keys" aria-selected="false"><i class="mdi mdi-format-list-bulleted mr-1"></i> {{ __('system.translation_keys') }}</a>
        </li>
        @endif
        @if(auth()->user()->can('system.components.translations.bulk import'))
        <li class="nav-item">
            <a class="nav-link font-weight-bold" id="import-tab" data-toggle="tab" href="#import" role="tab" aria-controls="import" aria-selected="false"><i class="mdi mdi-database-import mr-1"></i> {{ __('system.bulk_import') }}</a>
        </li>
        @endif
    </ul>

    <div class="tab-content" id="translationTabsContent">
        @if(auth()->user()->can('system.translations.language.view'))
        <div class="tab-pane fade show active" id="languages" role="tabpanel" aria-labelledby="languages-tab">
            @livewire('system.language-list')
        </div>
        @endif
        @if(auth()->user()->can('system.translations.keys.view'))
        <div class="tab-pane fade" id="keys" role="tabpanel" aria-labelledby="keys-tab">
            @livewire('system.translation-list')
        </div>
        @endif
        @if(auth()->user()->can('system.components.translations.bulk import'))
        <div class="tab-pane fade" id="import" role="tabpanel" aria-labelledby="import-tab">
            @livewire('system.translation-bulk-upload')
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.lab.layout.app')

@section('content2')
    <title>Template Builder - {{ $template->name }}</title>
    @livewire('template-engine::builder', ['template' => $template])
@endsection

@push('scripts')
    {{-- Scripts moved to Livewire component to support @this --}}
    <script src="{{ asset('assets/js/libs/jquery-ui/jquery-ui.min.js') }}"></script>
@endpush

@extends('layouts.lab.layout.app')

@section('content2')
    <title>Template Builder - {{ $template->name }}</title>
    @livewire('template-engine::builder', ['template' => $template])
@endsection

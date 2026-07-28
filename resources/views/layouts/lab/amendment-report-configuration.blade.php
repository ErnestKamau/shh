@extends('layouts.lab.layout.app')

@section('title2')
<title>Amendment Report Configuration</title>
@endsection

@section('content2')
    <div class="container-fluid py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent px-0 mb-3">
                <li class="breadcrumb-item"><a href="{{ route('dashboard-lab') }}">Laboratory</a></li>
                <li class="breadcrumb-item active" aria-current="page">Amendment Report Configuration</li>
            </ol>
        </nav>
        @livewire('lab.amendment-report-configuration-manager')
    </div>
@endsection

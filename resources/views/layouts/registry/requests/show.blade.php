@extends('layouts.registry.layout.app')

@section('title2')
    <title>{{ $registryRequest->reference_no }}</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('registry.dashboard'), 'name' => 'Registry Dashboard', 'icon' => null],
            ['link' => route('registry.requests.index'), 'name' => 'Registry Requests', 'icon' => null],
            ['link' => route('registry.requests.show', $registryRequest->id), 'name' => $registryRequest->reference_no, 'icon' => null],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="container-fluid">
    @livewire('registry.registry-request-details', ['requestId' => $registryRequest->id])
    <div class="row mt-3">
        <div class="col-lg-7">
            @livewire('registry.registry-workflow-timeline', ['requestId' => $registryRequest->id])
            @can('registry.components.audit trail.view')
                @livewire('registry.registry-audit-trail', ['requestId' => $registryRequest->id])
            @endcan
        </div>
        <div class="col-lg-5">
            @can('registry.components.approval queue.edit')
                <div class="card shadow-sm border-0 mb-3" style="border-radius: 15px;">
                    <div class="card-header bg-light border-0">Actions</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('registry.requests.approve', $registryRequest->id) }}" class="mb-2">
                            @csrf
                            <textarea name="comment" class="form-control mb-2" placeholder="Comment"></textarea>
                            <button class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('registry.requests.reject', $registryRequest->id) }}">
                            @csrf
                            <textarea name="comment" class="form-control mb-2" placeholder="Rejection reason" required></textarea>
                            <button class="btn btn-warning btn-sm">Return</button>
                        </form>
                    </div>
                </div>
            @endcan
            @livewire('registry.registry-assignment-panel', ['requestId' => $registryRequest->id])
            @livewire('registry.registry-document-uploader', ['requestId' => $registryRequest->id])
        </div>
    </div>
    </div>
</main>
@endsection

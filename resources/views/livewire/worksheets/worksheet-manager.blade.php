<div class="container-fluid">
    @php
        $activeTabStyle = 'background: linear-gradient(135deg, rgba(40, 167, 69, 0.08) 0%, rgba(32, 201, 151, 0.05) 100%); color: black;';
        $formulaTabCount = $formulas->count() + ($hasNoCaptureSamples ? count($groupedNoCaptureSamples) : 0);
    @endphp

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-clipboard-text text-success"></i>
                                Worksheets for {{ $batch->batch_code }}
                            </h2>
                            <p class="text-muted mb-0">Grouped pipelines, formula worksheets, method sequences, procedure worksheets, and SER capture for this batch</p>
                        </div>
                        <div class="d-flex gap-2">
                            @if($activeTab === 'formulas')
                                <button wire:click="postAllResults" class="btn btn-success">
                                    <i class="mdi mdi-upload"></i> Post Results
                                </button>
                            @elseif($activeTab === 'method-sequences')
                                <button type="button" class="btn btn-success" id="post-results-btn">
                                    <i class="mdi mdi-upload"></i> Post Results
                                </button>
                            @endif
                            <a href="{{ route('view-batch-details', ['batch' => $batch->id]) }}" class="btn btn-outline-secondary">
                                <i class="mdi mdi-arrow-left"></i> Back to Batch
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-3" style="border-radius: 15px 15px 0 0; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        @if($groupedHolders->count() > 0)
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'grouped-pipelines' ? 'active' : '' }}"
                               href="#"
                               wire:click.prevent="switchTab('grouped-pipelines')"
                               role="tab"
                               style="{{ $activeTab === 'grouped-pipelines' ? $activeTabStyle : '' }}">
                                <i class="mdi mdi-folder-multiple-outline"></i> Grouped pipelines
                                <span class="badge badge-success">{{ $groupedHolders->count() }}</span>
                            </a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'formulas' ? 'active' : '' }}"
                               href="#"
                               wire:click.prevent="switchTab('formulas')"
                               wire:loading.class="disabled"
                               role="tab"
                               style="{{ $activeTab === 'formulas' ? $activeTabStyle : '' }}">
                                <i class="mdi mdi-function"></i> Formula Worksheets
                                <span class="badge badge-light ml-1 d-none" wire:loading.class.remove="d-none" wire:target="switchTab">
                                    <i class="mdi mdi-loading mdi-spin"></i> Loading...
                                </span>
                                @if($formulaTabCount > 0)
                                    <span class="badge badge-success">{{ $formulaTabCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'method-sequences' ? 'active' : '' }}"
                               href="#"
                               wire:click.prevent="switchTab('method-sequences')"
                               wire:loading.class="disabled"
                               role="tab"
                               style="{{ $activeTab === 'method-sequences' ? $activeTabStyle : '' }}">
                                <i class="mdi mdi-playlist-plus"></i> Method Sequences
                                <span class="badge badge-light ml-1 d-none" wire:loading.class.remove="d-none" wire:target="switchTab">
                                    <i class="mdi mdi-loading mdi-spin"></i> Loading...
                                </span>
                                @if($stageHeaders->count() > 0)
                                    <span class="badge badge-success">{{ $stageHeaders->count() }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'procedures' ? 'active' : '' }}"
                               href="#"
                               wire:click.prevent="switchTab('procedures')"
                               wire:loading.class="disabled"
                               role="tab"
                               style="{{ $activeTab === 'procedures' ? $activeTabStyle : '' }}">
                                <i class="mdi mdi-flask-outline"></i> Procedure Worksheets
                                <span class="badge badge-light ml-1 d-none" wire:loading.class.remove="d-none" wire:target="switchTab">
                                    <i class="mdi mdi-loading mdi-spin"></i> Loading...
                                </span>
                            </a>
                        </li>
                        @if($hasNoCaptureSamples)
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'ser' ? 'active' : '' }}"
                               href="#"
                               wire:click.prevent="switchTab('ser')"
                               wire:loading.class="disabled"
                               role="tab"
                               style="{{ $activeTab === 'ser' ? $activeTabStyle : '' }}">
                                <i class="mdi mdi-clipboard-text-outline"></i> SER Worksheets
                                <span class="badge badge-light ml-1 d-none" wire:loading.class.remove="d-none" wire:target="switchTab">
                                    <i class="mdi mdi-loading mdi-spin"></i> Loading...
                                </span>
                                <span class="badge badge-success">{{ count($groupedNoCaptureSamples) }}</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
                <div class="card-body p-4">
                    @if($activeTab === 'grouped-pipelines' && $groupedHolders->count() > 0)
                        @if($groupedHolders->count() > 1)
                            <ul class="nav nav-pills mb-3">
                                @foreach($groupedHolders as $gh)
                                    <li class="nav-item">
                                        <button type="button"
                                            class="nav-link {{ (string) $activeGroupedHolderId === (string) $gh->id ? 'active' : '' }}"
                                            wire:click="selectGroupedHolder('{{ $gh->id }}')">
                                            {{ $gh->name }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        @if($activeGroupedHolder)
                            <livewire:worksheets.grouped-worksheet-wizard
                                :batch="$batch"
                                :holder="$activeGroupedHolder"
                                :key="'grouped-wizard-'.$activeGroupedHolder->id"
                            />
                        @endif
                    @endif

                    @if($activeTab === 'formulas')
                        <div class="tab-pane fade show active">
                            @if($formulas->count() > 0 || $hasNoCaptureSamples)
                                <div class="d-flex align-items-center mb-3 border-bottom pb-1 flex-wrap">
                                    <span class="font-weight-bold mr-3">WORKSHEETS :</span>
                                    <ul class="nav nav-pills mb-0" role="tablist">
                                        @foreach($formulas as $formula)
                                            <li class="nav-item">
                                                <button type="button"
                                                    wire:key="formula-pill-{{ $formula->id }}"
                                                    class="nav-link {{ (string) $activeFormulaId === (string) $formula->id ? 'active' : '' }}"
                                                    wire:click.prevent="$set('activeFormulaId', '{{ $formula->id }}')"
                                                    role="tab"
                                                    style="border: none; {{ (string) $activeFormulaId === (string) $formula->id ? $activeTabStyle : '' }}">
                                                    {{ $formula->name }}
                                                </button>
                                            </li>
                                        @endforeach
                                        @foreach($groupedNoCaptureSamples as $analysisId => $group)
                                            <li class="nav-item">
                                                <button type="button"
                                                    class="nav-link"
                                                    wire:click.prevent="switchTab('ser')"
                                                    role="tab"
                                                    style="border: none;">
                                                    {{ $group['name'] }} Worksheet
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                @php
                                    $selectedFormula = $formulas->firstWhere('id', $activeFormulaId) ?? $formulas->first();
                                @endphp
                                @if($selectedFormula)
                                    <div class="tab-content p-3 pt-2">
                                        <div class="tab-pane fade show active" role="tabpanel">
                                            <livewire:worksheets.formula-worksheet
                                                :batch="$batch"
                                                :formula="$selectedFormula"
                                                :key="'formula-' . $selectedFormula->id"
                                            />
                                        </div>
                                    </div>
                                @elseif($formulas->count() === 0 && $hasNoCaptureSamples)
                                    <p class="text-muted mb-0">Select an SER worksheet from the pills above or use the SER Worksheets tab.</p>
                                @endif
                            @else
                                <div class="alert alert-info mb-0">
                                    <i class="mdi mdi-information"></i>
                                    No formulas found for this batch. Captured results must have formula configurations.
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($activeTab === 'method-sequences')
                        <div class="method-sequences-tab-panel">
                            <div wire:ignore wire:key="method-sequences-jquery-panel">
                                @include('worksheets.partials.method-sequences-jquery', [
                                    'batch' => $batch,
                                    'stageHeaders' => $stageHeaders,
                                    'stageHeadersPayload' => $stageHeadersPayload,
                                ])
                            </div>
                        </div>
                    @endif

                    @if($activeTab === 'procedures')
                        @livewire('worksheets.procedure-worksheet-manager', ['batchId' => $batch->id], 'procedure-manager-'.$batch->id)
                    @endif

                    @if($activeTab === 'ser' && $hasNoCaptureSamples)
                        <div class="d-flex align-items-center mb-3 border-bottom pb-1 flex-wrap">
                            <span class="font-weight-bold mr-3">SER WORKSHEETS :</span>
                            <ul class="nav nav-pills mb-0" role="tablist">
                                @foreach($groupedNoCaptureSamples as $analysisId => $group)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                           data-toggle="pill"
                                           href="#ser-worksheet-{{ $analysisId }}"
                                           role="tab"
                                           style="{{ $loop->first ? $activeTabStyle : '' }}">
                                            {{ $group['name'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="tab-content p-3 pt-2">
                            @foreach($groupedNoCaptureSamples as $analysisId => $group)
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                     id="ser-worksheet-{{ $analysisId }}"
                                     role="tabpanel">
                                    @livewire('worksheets.ser-worksheet', [
                                        'batch' => $batch,
                                        'analysisTypeId' => $analysisId,
                                        'samples' => $group['samples'],
                                        'analysisTypeName' => $group['name'],
                                    ], 'ser-worksheet-'.$analysisId)
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

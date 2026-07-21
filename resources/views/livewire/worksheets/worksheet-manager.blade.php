<div>
    {{-- Compact burgundy header (match batch details / receiving) --}}
    <div class="batch-header-bar">
        <div class="batch-header-top">
            <div class="batch-title-group">
                <i class="mdi mdi-clipboard-text"></i>
                <div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                        <span class="batch-code-label">Worksheets for {{ $batch->batch_code }}</span>
                        @if(!empty($batch->status))
                            <span class="batch-stage-pill">
                                <i class="mdi mdi-sitemap" style="font-size:0.7rem;"></i>
                                {{ $batch->status }}
                            </span>
                        @endif
                    </div>
                    <p class="batch-header-subtitle">Grouped pipelines, formulas, method sequences, procedures &amp; SER</p>
                </div>
            </div>
            <div class="d-flex align-items-center flex-wrap batch-header-actions" style="gap: 6px;">
                @if($activeTab === 'formulas')
                    <button wire:click="postAllResults" class="btn btn-sm btn-success btn-action-sm">
                        <i class="mdi mdi-upload"></i> Post Results
                    </button>
                @elseif($activeTab === 'method-sequences')
                    <button type="button" class="btn btn-sm btn-success btn-action-sm" id="post-results-btn">
                        <i class="mdi mdi-upload"></i> Post Results
                    </button>
                @endif
                <a href="{{ route('view-batch-details', ['batch' => $batch->id, 'client' => 0, 'portal' => 0, 'status' => $batch->status]) }}"
                   class="btn btn-sm btn-outline-secondary btn-action-sm">
                    <i class="mdi mdi-arrow-left"></i> Back to Batch
                </a>
            </div>
        </div>
    </div>

    {{-- Main tabs panel --}}
    <div class="ws-panel">
        <div class="ws-panel-header">
            <ul class="nav nav-tabs ws-nav-tabs card-header-tabs" role="tablist">
                @if($groupedHolders->count() > 0)
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'grouped-pipelines' ? 'active' : '' }}"
                       href="#"
                       wire:click.prevent="switchTab('grouped-pipelines')"
                       role="tab">
                        <i class="mdi mdi-folder-multiple-outline"></i> Grouped pipelines
                        <span class="badge badge-success">{{ $groupedHolders->count() }}</span>
                    </a>
                </li>
                @endif
                @if($formulas->count() > 0)
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'formulas' ? 'active' : '' }}"
                       href="#"
                       wire:click.prevent="switchTab('formulas')"
                       wire:loading.class="disabled"
                       role="tab">
                        <i class="mdi mdi-function"></i> Formula Worksheets
                        <span class="badge badge-light ml-1 d-none" wire:loading.class.remove="d-none" wire:target="switchTab">
                            <i class="mdi mdi-loading mdi-spin"></i>
                        </span>
                        <span class="badge badge-success">{{ $formulas->count() }}</span>
                    </a>
                </li>
                @endif
                @if($stageHeaders->count() > 0)
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'method-sequences' ? 'active' : '' }}"
                       href="#"
                       wire:click.prevent="switchTab('method-sequences')"
                       wire:loading.class="disabled"
                       role="tab">
                        <i class="mdi mdi-playlist-plus"></i> Method Sequences
                        <span class="badge badge-light ml-1 d-none" wire:loading.class.remove="d-none" wire:target="switchTab">
                            <i class="mdi mdi-loading mdi-spin"></i>
                        </span>
                        <span class="badge badge-success">{{ $stageHeaders->count() }}</span>
                    </a>
                </li>
                @endif
                @if($logEntryWorksheets->count() > 0)
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'log-entry' ? 'active' : '' }}"
                       href="#"
                       wire:click.prevent="switchTab('log-entry')"
                       role="tab">
                        <i class="mdi mdi-table-edit"></i> Log entry
                        <span class="badge badge-success">{{ $logEntryWorksheets->count() }}</span>
                    </a>
                </li>
                @endif
                @if($procedureWorksheets->count() > 0)
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'procedures' ? 'active' : '' }}"
                       href="#"
                       wire:click.prevent="switchTab('procedures')"
                       wire:loading.class="disabled"
                       role="tab">
                        <i class="mdi mdi-flask-outline"></i> Procedure Worksheets
                        <span class="badge badge-light ml-1 d-none" wire:loading.class.remove="d-none" wire:target="switchTab">
                            <i class="mdi mdi-loading mdi-spin"></i>
                        </span>
                        <span class="badge badge-success">{{ $procedureWorksheets->count() }}</span>
                    </a>
                </li>
                @endif
                @if($hasNoCaptureSamples)
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'ser' ? 'active' : '' }}"
                       href="#"
                       wire:click.prevent="switchTab('ser')"
                       wire:loading.class="disabled"
                       role="tab">
                        <i class="mdi mdi-clipboard-text-outline"></i> SER Worksheets
                        <span class="badge badge-light ml-1 d-none" wire:loading.class.remove="d-none" wire:target="switchTab">
                            <i class="mdi mdi-loading mdi-spin"></i>
                        </span>
                        <span class="badge badge-success">{{ count($groupedNoCaptureSamples) }}</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
        <div class="ws-panel-body">
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

            @if($activeTab === 'formulas' && $formulas->count() > 0)
                <div class="tab-pane fade show active">
                    <div class="d-flex align-items-center mb-3 border-bottom pb-1 flex-wrap">
                        <span class="font-weight-bold mr-3" style="font-size: 0.75rem;">WORKSHEETS :</span>
                        <ul class="nav nav-pills mb-0" role="tablist">
                            @foreach($formulas as $formula)
                                <li class="nav-item">
                                    <button type="button"
                                        wire:key="formula-pill-{{ $formula->id }}"
                                        class="nav-link {{ (string) $activeFormulaId === (string) $formula->id ? 'active' : '' }}"
                                        wire:click.prevent="$set('activeFormulaId', '{{ $formula->id }}')"
                                        role="tab"
                                        style="border: none;">
                                        {{ $formula->name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @php
                        $selectedFormula = $formulas->firstWhere('id', $activeFormulaId) ?? $formulas->first();
                    @endphp
                    @if($selectedFormula)
                        <div class="tab-content pt-2">
                            <div class="tab-pane fade show active" role="tabpanel">
                                <livewire:worksheets.formula-worksheet
                                    :batch="$batch"
                                    :formula="$selectedFormula"
                                    :key="'formula-' . $selectedFormula->id"
                                />
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if($activeTab === 'method-sequences' && $stageHeaders->count() > 0)
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

            @if($activeTab === 'log-entry' && $logEntryWorksheets->count() > 0)
                @if($logEntryWorksheets->count() > 1)
                    <ul class="nav nav-pills mb-3">
                        @foreach($logEntryWorksheets as $lew)
                            <li class="nav-item">
                                <button type="button"
                                    class="nav-link {{ (string) $activeLogEntryWorksheetId === (string) $lew->id ? 'active' : '' }}"
                                    wire:click="$set('activeLogEntryWorksheetId', '{{ $lew->id }}')">
                                    {{ $lew->name }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <livewire:worksheets.log-entry-worksheet-manager
                    :batch="$batch"
                    :worksheet-id="$activeLogEntryWorksheetId ?? $logEntryWorksheets->first()->id"
                    :key="'log-entry-'.($activeLogEntryWorksheetId ?? $logEntryWorksheets->first()->id)"
                />
            @endif

            @if($activeTab === 'ser' && $hasNoCaptureSamples)
                <div class="d-flex align-items-center mb-3 border-bottom pb-1 flex-wrap">
                    <span class="font-weight-bold mr-3" style="font-size: 0.75rem;">SER WORKSHEETS :</span>
                    <ul class="nav nav-pills mb-0" role="tablist">
                        @foreach($groupedNoCaptureSamples as $analysisId => $group)
                            <li class="nav-item">
                                <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                                   data-toggle="pill"
                                   href="#ser-worksheet-{{ $analysisId }}"
                                   role="tab">
                                    {{ $group['name'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="tab-content pt-2">
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


<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-clipboard-text text-primary"></i>
                                Worksheets for {{ $batch->batch_code }}
                            </h2>
                            <p class="text-muted mb-0">Process formulas and method sequences for batch samples</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button wire:click="postAllResults" class="btn btn-success">
                                <i class="mdi mdi-upload"></i> Post Results
                            </button>
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
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'formulas' ? 'active' : '' }}" 
                               href="#" 
                               wire:click.prevent="switchTab('formulas')"
                               wire:loading.class="disabled"
                               role="tab"
                               style="{{ $activeTab === 'formulas' ? 'background: linear-gradient(135deg, rgba(0, 123, 255, 0.08) 0%, rgba(74, 144, 226, 0.05) 100%); color: black;' : '' }}">
                                <i class="mdi mdi-function"></i> Formula Worksheets
                                <span class="badge badge-light ml-1 d-none" wire:loading.class.remove="d-none" wire:target="switchTab">
                                    <i class="mdi mdi-loading mdi-spin"></i> Loading...
                                </span>
                                @if($formulas->count() > 0 || $hasNoCaptureSamples)
                                    <span class="badge badge-primary">{{ $formulas->count() + count($groupedNoCaptureSamples) }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'sequences' ? 'active' : '' }}" 
                               href="#" 
                               wire:click.prevent="switchTab('sequences')"
                               wire:loading.class="disabled"
                               role="tab"
                               style="{{ $activeTab === 'sequences' ? 'background: linear-gradient(135deg, rgba(0, 123, 255, 0.08) 0%, rgba(74, 144, 226, 0.05) 100%); color: black;' : '' }}">
                                <i class="mdi mdi-chart-timeline-variant"></i> Method Sequence Stages
                                <span class="badge badge-light ml-1 d-none" wire:loading.class.remove="d-none" wire:target="switchTab">
                                    <i class="mdi mdi-loading mdi-spin"></i> Loading...
                                </span>
                                @if($methodSequences->count() > 0)
                                    <span class="badge badge-info">{{ $methodSequences->count() }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <!-- Tab Content -->
                    <!-- Formula Worksheets Tab -->
                    @if($activeTab === 'formulas')
                        <div class="tab-pane fade show active">
                            @if($formulas->count() > 0 || $hasNoCaptureSamples)
                                <div class="d-flex align-items-center mb-3 border-bottom pb-1">
                                    <span class="font-weight-bold mr-3">WORKSHEETS :</span>
                                    <ul class="nav nav-pills mb-0" role="tablist">
                                        @foreach($formulas as $index => $formula)
                                            <li class="nav-item">
                                                <a class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                                   data-toggle="pill" 
                                                   href="#formula-{{ $formula->id }}" 
                                                   role="tab"
                                                   style="{{ $index === 0 ? 'background: linear-gradient(135deg, rgba(0, 123, 255, 0.08) 0%, rgba(74, 144, 226, 0.05) 100%); color: black;' : '' }}">
                                                    {{ $formula->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                        
                                        @foreach($groupedNoCaptureSamples as $analysisId => $group)
                                            <li class="nav-item">
                                                <a class="nav-link {{ ($formulas->count() == 0 && $loop->first) ? 'active' : '' }}" 
                                                   data-toggle="pill" 
                                                   href="#ser-worksheet-{{ $analysisId }}" 
                                                   role="tab"
                                                   style="{{ ($formulas->count() == 0 && $loop->first) ? 'background: linear-gradient(135deg, rgba(0, 123, 255, 0.08) 0%, rgba(74, 144, 226, 0.05) 100%); color: black;' : '' }}">
                                                    {{ $group['name'] }} Worksheet
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                                <div class="tab-content p-3 pt-2">
                                    @foreach($formulas as $index => $formula)
                                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                                             id="formula-{{ $formula->id }}" 
                                             role="tabpanel">
                                            @livewire('worksheets.formula-worksheet', [
                                                'batch' => $batch,
                                                'formula' => $formula
                                            ], 'formula-'.$formula->id)
                                        </div>
                                    @endforeach
                                    
                                    @foreach($groupedNoCaptureSamples as $analysisId => $group)
                                        <div class="tab-pane fade {{ ($formulas->count() == 0 && $loop->first) ? 'show active' : '' }}" 
                                             id="ser-worksheet-{{ $analysisId }}" 
                                             role="tabpanel">
                                            @livewire('worksheets.ser-worksheet', [
                                                'batch' => $batch,
                                                'analysisTypeId' => $analysisId,
                                                'samples' => $group['samples'],
                                                'analysisTypeName' => $group['name']
                                            ], 'ser-worksheet-'.$analysisId)
                                        </div>
                                    @endforeach
                            @else
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information"></i> 
                                    No formulas or worksheets found for this batch. Captured results must have formula configurations.
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Method Sequence Worksheets Tab -->
                    @if($activeTab === 'sequences')
                        <div class="tab-pane fade show active">
                            @if($methodSequences->count() > 0)
                                <div class="d-flex align-items-center mb-3 border-bottom pb-1">
                                    <span class="font-weight-bold mr-3">METHOD SEQUENCES :</span>
                                    <ul class="nav nav-pills mb-0" role="tablist">
                                        @foreach($methodSequences as $index => $sequence)
                                            <li class="nav-item">
                                                <a class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                                   data-toggle="pill" 
                                                   href="#sequence-{{ $sequence->id }}" 
                                                   role="tab"
                                                   style="{{ $index === 0 ? 'background: linear-gradient(135deg, rgba(0, 123, 255, 0.08) 0%, rgba(74, 144, 226, 0.05) 100%); color: black;' : '' }}">
                                                    {{ $sequence->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                                <div class="tab-content p-3 pt-2">
                                    @foreach($methodSequences as $index => $sequence)
                                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                                             id="sequence-{{ $sequence->id }}" 
                                             role="tabpanel">
                                            @livewire('worksheets.method-sequence-worksheet', [
                                                'batch' => $batch,
                                                'methodSequence' => $sequence
                                            ], 'sequence-'.$sequence->id)
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information"></i> 
                                    No method sequences found for this batch. Captured results must have method sequence configurations.
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

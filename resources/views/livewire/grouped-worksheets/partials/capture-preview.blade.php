@php
    $preview = $capturePreview ?? null;
@endphp

@if(!$preview)
    <div class="gw-capture-empty text-center py-5">
        <i class="mdi mdi-gesture-tap text-muted d-block mb-2" style="font-size: 2.5rem;"></i>
        <p class="text-muted mb-0">Select a pipeline stage on the timeline to preview sample capture.</p>
    </div>
@else
    <div class="gw-capture-preview">
        <div class="gw-capture-preview-banner">
            <i class="mdi mdi-information-outline"></i>
            <span>Preview only — this is how analysts will see this stage during sample worksheet capture.</span>
        </div>

        <div class="row g-0 gw-capture-layout">
            <div class="col-md-3 gw-capture-sidebar">
                <div class="gw-capture-sidebar-head">
                    <strong class="d-block text-truncate">{{ $preview['pipeline_label'] }}</strong>
                    <span class="small text-muted">Capture navigation</span>
                </div>
                <div class="list-group list-group-flush gw-capture-nav">
                    @foreach($preview['sidebar_stages'] as $index => $nav)
                        <div class="list-group-item gw-capture-nav-item {{ $index === 0 ? 'active' : '' }}">
                            <span class="gw-capture-nav-order">{{ $nav['order'] }}</span>
                            <span class="small text-truncate">{{ $nav['title'] }}</span>
                        </div>
                    @endforeach
                    @if(count($preview['sidebar_stages']) === 0)
                        <div class="list-group-item text-muted small">No inner stages configured</div>
                    @endif
                </div>
            </div>

            <div class="col-md-9 gw-capture-main">
                <div class="gw-capture-main-header">
                    <div>
                        <h5 class="mb-1">{{ $preview['pipeline_label'] }}</h5>
                        <div class="small text-muted">
                            {{ $preview['item_type_label'] }} — {{ $preview['reference_name'] }}
                            @if(!$preview['is_required'])
                                <span class="badge bg-light text-dark ms-1">Optional</span>
                            @endif
                        </div>
                        @if(!empty($preview['pipeline_description']))
                            <p class="small text-muted mb-0 mt-1">{{ $preview['pipeline_description'] }}</p>
                        @endif
                    </div>
                    <div class="gw-capture-actions">
                        @if(!$preview['is_required'])
                            <span class="btn btn-outline-secondary btn-sm disabled">Skip</span>
                        @endif
                        <span class="btn btn-success btn-sm disabled">
                            Complete stage <i class="mdi mdi-arrow-right"></i>
                        </span>
                    </div>
                </div>

                @if(!empty($preview['context']))
                    <div class="gw-capture-context">
                        @foreach($preview['context'] as $key => $value)
                            @if($value)
                                <span class="gw-capture-context-chip">
                                    <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                    {{ $value }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="gw-capture-body">
                    @if($preview['display_mode'] === 'empty')
                        <div class="alert alert-warning mb-0">
                            <i class="mdi mdi-alert-outline"></i>
                            Linked worksheet not found or has no active version.
                        </div>
                    @elseif($preview['display_mode'] === 'blocks')
                        @foreach($preview['blocks'] as $block)
                            <div class="gw-inner-block mb-4">
                                <div class="gw-inner-block-head">
                                    <span class="gw-inner-block-order">{{ $block['order'] }}</span>
                                    <div>
                                        <div class="fw-semibold">{{ $block['title'] }}</div>
                                        <div class="small text-muted">
                                            {{ $block['type_label'] }}
                                            @if($block['reference_name'])
                                                — {{ $block['reference_name'] }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if(count($block['steps']) > 0)
                                    <div class="gw-inner-timeline">
                                        @foreach($block['steps'] as $step)
                                            @include('livewire.grouped-worksheets.partials.capture-preview-step', ['step' => $step])
                                        @endforeach
                                    </div>
                                @else
                                    <p class="small text-muted mb-0 ps-2">No steps in this block.</p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="gw-inner-timeline">
                            @forelse($preview['steps'] as $step)
                                @include('livewire.grouped-worksheets.partials.capture-preview-step', ['step' => $step])
                            @empty
                                <div class="alert alert-info mb-0">
                                    <i class="mdi mdi-information-outline"></i>
                                    No capture steps configured on this worksheet yet.
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

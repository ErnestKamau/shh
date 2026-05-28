<div class="container-fluid wf-config">
    @include('layouts.registry.partials.page-header', [
        'title' => 'Workflow Configuration',
        'description' => 'Select a workflow to view its step sequence from intake through completion.',
        'icon' => 'mdi-sitemap',
    ])

    @if($definitions->isEmpty())
        <div class="wf-config__panel">
            <div class="wf-config__empty">
                <div class="wf-config__empty-icon"><i class="mdi mdi-sitemap"></i></div>
                <p class="text-muted mb-0">No workflow definitions configured.</p>
            </div>
        </div>
    @else
        <div class="wf-config__panel">
            <div class="wf-config__tabs-wrap">
                <ul class="nav wf-config__tabs" id="wf-config-tabs" role="tablist">
                    @foreach($definitions as $def)
                        <li class="nav-item" role="presentation">
                            <a class="nav-link wf-config-tab {{ $loop->first ? 'active' : '' }}"
                               id="wf-tab-{{ $def->id }}"
                               data-toggle="tab"
                               href="#wf-pane-{{ $def->id }}"
                               role="tab"
                               aria-controls="wf-pane-{{ $def->id }}"
                               aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                <i class="mdi mdi-source-branch wf-config-tab__icon"></i>
                                {{ $def->name }}
                                <span class="wf-config-tab__count">{{ $def->steps->count() }} {{ Str::plural('step', $def->steps->count()) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="tab-content" id="wf-config-tabs-content">
                @foreach($definitions as $def)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                         id="wf-pane-{{ $def->id }}"
                         role="tabpanel"
                         aria-labelledby="wf-tab-{{ $def->id }}">
                        <div class="wf-config__body">
                            <div class="wf-config__intro">
                                <div>
                                    <h2 class="wf-config__intro-title">{{ $def->name }}</h2>
                                    @if($def->description)
                                        <p class="wf-config__intro-desc">{{ $def->description }}</p>
                                    @else
                                        <p class="wf-config__intro-desc">
                                            {{ $def->steps->count() }} sequential {{ Str::plural('stage', $def->steps->count()) }} in this workflow path.
                                        </p>
                                    @endif
                                    @if($def->categories->isNotEmpty())
                                        <div class="wf-config__categories">
                                            @foreach($def->categories as $category)
                                                <span class="wf-config__category-pill">{{ $category->name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="wf-config__meta">
                                    <span class="wf-config__code">{{ $def->code }}</span>
                                </div>
                            </div>

                            @if($def->steps->isEmpty())
                                <p class="text-muted mb-0">No steps defined for this workflow.</p>
                            @else
                                <ul class="wf-timeline" aria-label="Workflow stages for {{ $def->name }}">
                                    @foreach($def->steps as $step)
                                        @php
                                            $isFirst = $loop->first;
                                            $isFinal = $step->is_final;
                                            $markerClass = $isFinal
                                                ? 'wf-timeline__marker--final'
                                                : ($isFirst ? 'wf-timeline__marker--start' : 'wf-timeline__marker--middle');
                                        @endphp
                                        <li class="wf-timeline__item">
                                            <div class="wf-timeline__marker {{ $markerClass }}">
                                                @if($isFinal)
                                                    <i class="mdi mdi-flag-checkered"></i>
                                                @else
                                                    {{ $step->sequence }}
                                                @endif
                                            </div>
                                            <div class="wf-timeline__card">
                                                <div class="wf-timeline__header">
                                                    <h3 class="wf-timeline__name">{{ $step->step_name }}</h3>
                                                    <div class="wf-timeline__badges">
                                                        @include('layouts.registry.partials.stage-badge', ['stage' => $step->step_code])
                                                        @if($step->role_name)
                                                            <span class="wf-timeline__role">{{ str_replace('_', ' ', $step->role_name) }}</span>
                                                        @endif
                                                        @if($step->is_final)
                                                            <span class="wf-timeline__final">Final stage</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="wf-timeline__footer">
                                                    <span>Step {{ $step->sequence }} of {{ $def->steps->count() }}</span>
                                                    @if(!$loop->last)
                                                        <i class="mdi mdi-arrow-down wf-timeline__arrow" aria-hidden="true"></i>
                                                        <span>Next: {{ $def->steps[$loop->index + 1]->step_name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

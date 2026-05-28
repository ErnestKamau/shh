@php
    $section = $this->selectedSection;
    $selectedLab = $this->assignedLabs->firstWhere('id', $selectedLabId);
@endphp

@if($section)
    <div class="env-workspace">
        <div class="env-workspace-inset env-section-detail-body">
            <header class="env-workspace__hero">
                <div class="env-workspace__hero-main">
                    <h2 class="env-workspace__title">{{ $section->name }}</h2>
                    <p class="env-workspace__meta">
                        <span class="env-workspace__code">{{ $section->code }}</span>
                        <span class="env-workspace__dot" aria-hidden="true">·</span>
                        <span>{{ $section->formattedOptimumLevel() }}</span>
                        <span class="env-workspace__dot" aria-hidden="true">·</span>
                        <span>{{ $section->readingFrequencyLabel() }}</span>
                    </p>
                </div>
            </header>

            <div class="row g-3 env-summary-panels-row">
                <div class="col-lg-6 env-summary-panel-col env-summary-panel-col--left">
                    @include('livewire.monitoring.partials.environmental.section-config-panel', ['section' => $section])
                </div>
                <div class="col-lg-6 env-summary-panel-col env-summary-panel-col--right">
                    @include('livewire.monitoring.partials.environmental.section-equipment-panel', ['section' => $section])
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs env-detail-tabs mb-0">
            <li class="nav-item">
                <button type="button"
                        class="nav-link {{ $sectionDetailTab === 'logs' ? 'active' : '' }}"
                        wire:click="setSectionDetailTab('logs')">
                    <i class="mdi mdi-table"></i> Logs
                </button>
            </li>
            <li class="nav-item">
                <button type="button"
                        class="nav-link {{ $sectionDetailTab === 'charts' ? 'active' : '' }}"
                        wire:click="setSectionDetailTab('charts')">
                    <i class="mdi mdi-chart-line-variant"></i> Charts
                </button>
            </li>
        </ul>

        <div class="env-workspace-inset env-detail-tab-body">
        @if(count($this->sectionTemplateWorkspaces) > 0)
            @if($sectionDetailTab === 'logs')
                @foreach($this->sectionTemplateWorkspaces as $workspace)
                    @include('livewire.monitoring.partials.environmental.section-template-logs', ['workspace' => $workspace])
                @endforeach
            @else
                <div class="env-chart-toolbar">
                    <div class="env-chart-toolbar__group">
                        <label class="env-chart-toolbar__label" for="chartsDateRange">
                            <i class="mdi mdi-calendar-range" aria-hidden="true"></i>
                            Chart period
                        </label>
                        <select id="chartsDateRange"
                                wire:model.live="chartsDateRange"
                                class="env-chart-toolbar__select">
                            <option value="30">Last 30 days (same as logs)</option>
                            <option value="7">Last 7 days</option>
                            <option value="month">Current month</option>
                            <option value="60">Last 60 days</option>
                            <option value="90">Last 90 days</option>
                            <option value="180">Last 180 days</option>
                        </select>
                    </div>
                    <div class="env-chart-toolbar__divider" aria-hidden="true"></div>
                    <label class="env-chart-toolbar__toggle" for="chartIncludeUncertaintyOnOptimum">
                        <input type="checkbox"
                               class="env-chart-toolbar__toggle-input"
                               id="chartIncludeUncertaintyOnOptimum"
                               wire:model.live="chartIncludeUncertaintyOnOptimum">
                        <span class="env-chart-toolbar__toggle-track" aria-hidden="true">
                            <span class="env-chart-toolbar__toggle-thumb"></span>
                        </span>
                        <span class="env-chart-toolbar__toggle-text">
                            <span class="env-chart-toolbar__toggle-title">Include measurement uncertainty</span>
                            <span class="env-chart-toolbar__toggle-hint">Limits ± U.M and optimum band · from calibration at capture</span>
                        </span>
                    </label>
                </div>
                @foreach($this->sectionTemplateWorkspaces as $workspace)
                    @include('livewire.monitoring.partials.environmental.section-template-chart', ['workspace' => $workspace])
                @endforeach
            @endif
        @else
            <div class="alert alert-warning">
                <i class="mdi mdi-alert-outline"></i>
                No active environmental templates are linked to this section.
            </div>
        @endif
        </div>
    </div>
@endif

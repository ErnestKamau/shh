@include('livewire.skills-matrix.partials.list-page-open')

@component('livewire.skills-matrix.partials.page-header', [
    'title' => 'Reports',
    'subtitle' => 'Skills matrix module summary and exports',
    'icon' => 'mdi-file-chart',
])
@endcomponent

<div class="row">
    <div class="col-12">
        <div class="metric-grid sm-metric-grid">
            <div class="metric-card">
                <div class="metric-label">Skills Matrices</div>
                <div class="metric-value">{{ $matrixCount }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Capability Matrices</div>
                <div class="metric-value">{{ $capabilityCount }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Training Plans</div>
                <div class="metric-value">{{ $planCount }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body p-4">
                <p class="text-muted mb-0">Export to CSV/PDF can be added in a follow-up iteration.</p>
            </div>
        </div>
    </div>
</div>

@include('livewire.skills-matrix.partials.list-page-close')

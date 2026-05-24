@include('livewire.skills-matrix.partials.list-page-open')

@component('livewire.skills-matrix.partials.page-header', [
    'title' => $header->name,
    'subtitle' => 'Training needs assessment — gap analysis',
    'icon' => 'mdi-school',
])
@endcomponent

<div class="row mb-4">
    <div class="col-12">
        <div class="metric-grid sm-metric-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="metric-card">
                <div class="metric-label text-danger">Critical Gaps</div>
                <div class="metric-value text-danger">{{ $analysis['critical'] }}</div>
                <div class="text-muted small mt-1">Skills below required by 2+ levels</div>
            </div>
            <div class="metric-card">
                <div class="metric-label" style="color:#D97706;">Minor Gaps</div>
                <div class="metric-value" style="color:#D97706;">{{ $analysis['minor'] }}</div>
                <div class="text-muted small mt-1">Skills below required by 1 level</div>
            </div>
            <div class="metric-card">
                <div class="metric-label text-primary">Exceeding</div>
                <div class="metric-value text-primary">{{ $analysis['exceeding'] }}</div>
                <div class="text-muted small mt-1">Skills above required level</div>
            </div>
        </div>
    </div>
</div>

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Training needs assessment — all roles',
    'tableSubtitle' => 'Required minus actual capability (negative = training needed, positive = exceeds)',
])
    @slot('body')
        <div class="sm-gap-legend mb-3">
            <span class="sm-gap-legend-item">
                <span class="gap-dot gap-train">−1</span> Training needed
            </span>
            <span class="sm-gap-legend-item">
                <span class="gap-dot gap-ok">✓</span> Meets target
            </span>
            <span class="sm-gap-legend-item">
                <span class="gap-dot gap-exceed">+1</span> Exceeds target
            </span>
            <span class="sm-gap-legend-item">
                <span class="gap-dot gap-neutral">—</span> Not assessed
            </span>
        </div>
        <div style="overflow:auto;">
            <table class="skill-heatmap">
                <thead>
                    <tr style="background:rgba(0,0,0,.03);">
                        <th>Competency</th>
                        @foreach($roles as $role)
                            <th class="text-center">{{ $role->jobdescription->name ?? 'Role' }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($grouped as $area => $rows)
                        <tr class="sm-competency-area-row {{ $loop->even ? 'sm-competency-area-row--alt' : '' }}">
                            <td colspan="{{ $roles->count() + 1 }}" class="area-label">{{ $area }}</td>
                        </tr>
                        @foreach($rows as $row)
                            <tr class="sm-competency-data-row">
                                <td>{{ $row['competency'] }}</td>
                                @foreach($row['gaps'] as $gapCell)
                                    <td class="text-center">
                                        <span class="gap-dot {{ $gapCell['class'] }}">{{ $gapCell['label'] }}</span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="{{ $roles->count() + 1 }}" class="text-center text-muted py-4">
                                No competencies found for this capability matrix.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endslot
@endcomponent

@include('livewire.skills-matrix.partials.list-page-close')

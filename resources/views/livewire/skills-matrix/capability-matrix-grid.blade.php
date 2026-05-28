@include('livewire.skills-matrix.partials.list-page-open')

@if($flashMessage)
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $flashMessage }}
        <button type="button" class="btn-close" wire:click="$set('flashMessage', null)"></button>
    </div>
@endif

@include('livewire.skills-matrix.partials.proficiency-styles', ['proficiencies' => $proficiencies])

@component('livewire.skills-matrix.partials.page-header', [
    'title' => $capability->name,
    'subtitle' => 'Actual competency levels — current team',
    'icon' => 'mdi-account-check-outline',
])
@endcomponent

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 sm-list-filters-card" style="border-radius: 15px;">
            <div class="card-body p-4">
                <label class="form-label fw-bold d-block mb-2">Filter staff</label>
                <div class="tag-select-container">
                    @foreach($capability->roles as $role)
                        <label class="mr-3 mb-1 d-inline-block">
                            <input type="checkbox" wire:model.live="selectedRoleIds" value="{{ $role->id }}">
                            {{ $role->user->name ?? 'User' }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Team Capability Grid',
    'tableSubtitle' => 'Judged competency vs skills matrix requirements',
])
    @slot('body')
    <div style="overflow:auto;">
        <table class="skill-heatmap">
            <thead>
                <tr style="background:#f3f4f6;">
                    <th>Competency</th>
                    @foreach($roles as $role)
                        <th class="text-center">{{ $role->user->name ?? '' }}<br><small>{{ $role->jobdescription->name ?? '' }}</small></th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($grouped as $area => $rows)
                    <tr class="sm-competency-area-row">
                        <td colspan="{{ $roles->count() + 1 }}" class="area-label">{{ $area }}</td>
                    </tr>
                    @foreach($rows as $detail)
                    <tr>
                        <td>{{ $detail->competencydescription->description ?? '' }}</td>
                        @foreach($roles as $role)
                            @php
                                $cells = $userMap[$detail->id] ?? [];
                                $cell = collect($cells)->firstWhere('user_id', $role->user_id);
                                $prof = $cell ? \App\ModulePreConfigs::find($cell['proficiency_id']) : null;
                                $code = $prof ? (int)$prof->code : null;
                            @endphp
                            <td class="text-center">
                                <span class="level-dot {{ $this->levelClass($code) }}">{{ $code ?? '—' }}</span>
                                @can('skills-matrix.components.capability.edit')
                                <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="openEditCell('{{ $cell['id'] ?? '' }}', '{{ $cell['proficiency_id'] ?? '' }}', '{{ $detail->id }}', '{{ $role->user_id }}', '{{ $role->skill_matrix_role_id }}')"><i class="mdi mdi-pencil"></i></button>
                                @endcan
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    @endslot
@endcomponent

@if($editCompetencyId)
    <div class="modal fade show d-block" style="background:rgba(0,0,0,.4);">
        <div class="modal-dialog modal-sm"><div class="modal-content">
            <div class="modal-body">
                <select class="form-control" wire:model="editProficiencyId">
                    @foreach($proficiencies as $p)<option value="{{ $p->id }}">{{ $p->description }}</option>@endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-primary" wire:click="saveCellProficiency">Save</button>
            </div>
        </div></div>
    </div>
    @endif

@include('livewire.skills-matrix.partials.list-page-close')

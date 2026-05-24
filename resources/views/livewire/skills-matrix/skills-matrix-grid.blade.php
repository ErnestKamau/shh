@include('livewire.skills-matrix.partials.list-page-open')

@if($flashMessage)
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $flashMessage }}
        <button type="button" class="btn-close" wire:click="$set('flashMessage', null)"></button>
    </div>
@endif

@include('livewire.skills-matrix.partials.proficiency-styles', ['proficiencies' => $proficiencies])

@component('livewire.skills-matrix.partials.page-header', [
    'title' => $matrix->name ?? 'Skills Matrix',
    'subtitle' => 'Required competency levels by role · ' . ($matrix->department ?? 'TS/LS/007/19'),
    'icon' => 'mdi-account-star-outline',
])
@endcomponent

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Competency Matrix',
    'tableSubtitle' => 'Reference standard for all lab positions',
])
    @slot('body')
    <div style="overflow:auto;">
        <table class="skill-heatmap">
            <thead>
                <tr style="background:#f3f4f6;">
                    <th>Competency</th>
                    @foreach($roles as $role)
                        <th class="text-center">{{ $role->jobdescription->name ?? 'Role' }}</th>
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
                                $cell = $detail->roles->firstWhere('matrix_role_id', $role->id);
                                $code = $cell && $cell->proficiency ? (int)$cell->proficiency->code : null;
                            @endphp
                            <td class="text-center">
                                @if($cell)
                                    <span class="level-dot {{ $this->levelClass($code) }}">{{ $code ?? '—' }}</span>
                                    @can('skills-matrix.components.skills-matrix.edit')
                                    <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit ml-1" wire:click="openEditCell('{{ $cell->id }}', '{{ $cell->proficiency_id }}')"><i class="mdi mdi-pencil"></i></button>
                                    @endcan
                                @else — @endif
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

@if($editingDetailRoleId)
    <div class="modal fade show d-block" style="background:rgba(0,0,0,.4);">
        <div class="modal-dialog modal-sm"><div class="modal-content">
            <div class="modal-body">
                <label>Proficiency</label>
                <select class="form-control" wire:model="editProficiencyId">
                    @foreach($proficiencies as $p)<option value="{{ $p->id }}">{{ $p->description }}</option>@endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-secondary" wire:click="$set('editingDetailRoleId', null)">Cancel</button>
                <button class="btn btn-sm btn-primary" wire:click="saveCellProficiency">Save</button>
            </div>
        </div></div>
    </div>
    @endif

@include('livewire.skills-matrix.partials.list-page-close')

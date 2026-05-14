<div class="container-fluid">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage"><span>&times;</span></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="mdi mdi-home-group text-primary"></i>
                                {{ __('personnel.organizational_departments') }}
                            </h2>
                            <p class="text-muted mb-0">{{ __('personnel.departments_overview') }}</p>
                        </div>
                        <button type="button" class="btn btn-outline-primary" wire:click="openCreateModal">
                            <i class="mdi mdi-plus"></i> {{ __('personnel.add_department') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card tab-card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="{{ __('personnel.search_departments') }}">
                </div>
                <div class="col-md-4">
                    <select class="form-control" wire:model.live="perPage">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ __('personnel.show') }} {{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive bg-light p-3">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
                    <thead class="bg-light p-2">
                        <tr>
                            <th>{{ __('personnel.no') }}</th>
                            <th>{{ __('personnel.name') }}</th>
                            <th>{{ __('personnel.active') }}</th>
                            <th style="width: 180px;">{{ __('personnel.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->departments as $department)
                            <tr>
                                <td>{{ $this->departments->firstItem() + $loop->index }}</td>
                                <td>{{ $department->name }}</td>
                                <td class="text-small">{!! $department->active == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                <td>
                                    <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--edit" wire:click="openEditModal('{{ $department->id }}')" title="{{ __('personnel.edit') }}">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm pm-act-btn pm-act-btn--delete" wire:click="openDeleteModal('{{ $department->id }}')" title="{{ __('personnel.delete') }}">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">{{ __('personnel.no_departments_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    {{ __('personnel.showing_to_of', ['from' => $this->departments->firstItem() ?? 0, 'to' => $this->departments->lastItem() ?? 0, 'total' => $this->departments->total()]) }}
                </small>
                {{ $this->departments->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    @if($showDepartmentModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            <i class="mdi mdi-{{ $editingDepartmentId ? 'pencil-outline' : 'plus' }}"></i>
                            {{ $editingDepartmentId ? __('personnel.edit_department') : __('personnel.add_department') }}
                        </h4>
                        <button type="button" class="close" wire:click="closeDepartmentModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="control-label">{{ __('personnel.department_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="departmentName" placeholder="{{ __('personnel.department_name') }}...">
                            @error('departmentName') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group mb-0">
                            <label class="control-label">
                                <input type="checkbox" wire:model="departmentActive"> {{ __('personnel.active') }}
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="saveDepartment"><i class="mdi mdi-content-save"></i> {{ __('personnel.save') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeDepartmentModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="mdi mdi-delete"></i> {{ __('personnel.delete_department') }}</h4>
                        <button type="button" class="close" wire:click="closeDeleteModal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger mb-0">
                            {!! __('personnel.confirm_delete_item', ['name' => $departmentName]) !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="deleteDepartment"><i class="mdi mdi-delete"></i> {{ __('personnel.delete') }}</button>
                        <button type="button" class="btn btn-default" wire:click="closeDeleteModal">{{ __('personnel.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .pm-act-btn { border-radius: 7px; padding: 4px 8px; margin-right: 3px; font-size: 12px; }
        .pm-act-btn:last-child { margin-right: 0; }
        .pm-act-btn--edit { border: 1px solid #bfdbfe; color: #1d4ed8; background: #eff6ff; }
        .pm-act-btn--edit:hover { background: #dbeafe; border-color: #93c5fd; }
        .pm-act-btn--delete { border: 1px solid #fecdd3; color: #e11d48; background: #fff5f7; }
        .pm-act-btn--delete:hover { background: #ffe4e6; border-color: #fda4af; }
    </style>
</div>

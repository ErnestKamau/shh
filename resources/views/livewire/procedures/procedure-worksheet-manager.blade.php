<div class="container-fluid px-0">
	<div class="workflow-board-panel mb-3">
		<div class="workflow-board-panel-header">
			<h5>
				<i class="mdi mdi-clipboard-text-outline"></i>
				Procedure capture worksheets
			</h5>
			<button type="button" wire:click="create" class="btn btn-primary btn-sm btn-action-sm">
				<i class="mdi mdi-plus"></i> Create procedure
			</button>
		</div>
		<div class="workflow-board-panel-body flush-top">
			<p class="text-muted mb-0" style="font-size: 0.9rem;">
				Create and manage procedure worksheets and steps for laboratory capture.
			</p>
		</div>
	</div>

	@if(session()->has('message'))
		<div class="batch-show-alerts mb-3">
			<div class="alert alert-success alert-dismissible fade show" role="alert">
				{{ session('message') }}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			</div>
		</div>
	@endif

	<div class="workflow-board-panel">
		<div class="workflow-board-panel-header">
			<h5>
				<i class="mdi mdi-format-list-bulleted"></i>
				All worksheets
			</h5>
		</div>
		<div class="workflow-board-panel-body">
			<div class="workflow-board-filter-nested">
				<div class="row align-items-end g-2">
					<div class="col-md-5 col-lg-4">
						<label class="form-label">Search</label>
						<input type="text" wire:model.live="search" class="form-control" placeholder="Search by name or description…">
					</div>
					<div class="col-md-auto ms-md-auto">
						<div class="d-flex align-items-center flex-wrap">
							<label class="form-label mb-0 text-muted mr-2" style="font-size: 0.8rem;">Show</label>
							<select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto; min-width: 4.5rem;">
								<option value="25">25</option>
								<option value="50">50</option>
								<option value="75">75</option>
								<option value="100">100</option>
							</select>
						</div>
					</div>
				</div>
			</div>

			<div class="table-responsive">
				<table id="procedure-worksheets-table" class="table workflow-table table-hover mb-0">
					<thead>
						<tr>
							<th>Actions</th>
							<th>#</th>
							<th>Name</th>
							<th>Description</th>
							<th>Status</th>
							<th>Created</th>
						</tr>
					</thead>
					<tbody>
						@forelse($worksheets as $worksheet)
							<tr>
								<td>
									<div class="d-flex flex-wrap gap-1">
										<a href="{{ route('formulars.procedures.edit', $worksheet->id) }}"
											class="btn btn-sm rm-act-btn rm-act-btn--view"
											title="Manage steps">
											<i class="mdi mdi-format-list-numbered"></i>
										</a>
										<button type="button"
											wire:click="edit('{{ $worksheet->id }}')"
											class="btn btn-sm rm-act-btn rm-act-btn--edit"
											title="Edit details">
											<i class="mdi mdi-pencil"></i>
										</button>
										<button type="button"
											wire:click="toggleActive('{{ $worksheet->id }}')"
											class="btn btn-sm rm-act-btn rm-act-btn--muted"
											title="{{ $worksheet->is_active ? 'Deactivate' : 'Activate' }}">
											<i class="mdi mdi-{{ $worksheet->is_active ? 'pause' : 'play' }}"></i>
										</button>
										<button type="button"
											wire:click="delete('{{ $worksheet->id }}')"
											class="btn btn-sm rm-act-btn rm-act-btn--delete"
											title="Delete"
											onclick="return confirm('Are you sure?')">
											<i class="mdi mdi-delete"></i>
										</button>
									</div>
								</td>
								<td>{{ $loop->iteration }}</td>
								<td><strong>{{ $worksheet->name }}</strong></td>
								<td>{{ Str::limit($worksheet->description, 50) }}</td>
								<td>
									<span
										class="workflow-status-chip"
										style="--chip-accent: {{ $worksheet->is_active ? '#28a745' : '#94a3b8' }};"
									>
										{{ $worksheet->is_active ? 'Active' : 'Inactive' }}
									</span>
								</td>
								<td class="text-muted" style="font-size: 0.875rem;">{{ $worksheet->created_at->format('M d, Y') }}</td>
							</tr>
						@empty
							<tr>
								<td colspan="6" class="text-center py-5 workflow-empty-state">
									<i class="mdi mdi-clipboard-text-outline d-block mb-2" style="font-size: 2.5rem;"></i>
									<h5 class="mb-1">No procedure worksheets</h5>
									<p class="mb-0">Create one to get started.</p>
								</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			<div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-3 workflow-board-legend">
				<span class="text-muted" style="font-size: 0.85rem;">
					Showing {{ $worksheets->firstItem() ?? 0 }} to {{ $worksheets->lastItem() ?? 0 }} of {{ $worksheets->total() }} entries
				</span>
				<div>
					{{ $worksheets->links() }}
				</div>
			</div>
		</div>
	</div>

	@if($showCreateModal || $showEditModal)
		<div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:click.self="closeModal">
			<div class="modal-dialog" wire:click.stop>
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{{ $showEditModal ? 'Edit' : 'Create' }} procedure worksheet</h5>
						<button type="button" class="close" wire:click="closeModal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<form wire:submit.prevent="{{ $showEditModal ? 'update' : 'save' }}">
							<div class="mb-3">
								<label class="form-label">Name <span class="text-danger">*</span></label>
								<input type="text" wire:model="name" class="form-control">
								@error('name') <span class="text-danger small">{{ $message }}</span> @enderror
							</div>
							<div class="mb-3">
								<label class="form-label">Description</label>
								<textarea wire:model="description" class="form-control" rows="3"></textarea>
								@error('description') <span class="text-danger small">{{ $message }}</span> @enderror
							</div>
							<div class="mb-3">
								<div class="form-check">
									<input type="checkbox" wire:model="is_active" class="form-check-input" id="isActive">
									<label class="form-check-label" for="isActive">Active</label>
								</div>
							</div>
							<div class="text-end">
								<button type="button" class="btn btn-secondary btn-sm btn-action-sm me-2" wire:click="closeModal">Cancel</button>
								<button type="submit" class="btn btn-primary btn-sm btn-action-sm">{{ $showEditModal ? 'Update' : 'Create' }}</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	@endif

	<style>
	#procedure-worksheets-table .rm-act-btn {
		border-radius: 7px;
		padding: 4px 8px;
		font-size: 12px;
	}

	#procedure-worksheets-table .rm-act-btn--view {
		border: 1px solid #bbf7d0;
		color: #15803d;
		background: #f0fdf4;
	}

	#procedure-worksheets-table .rm-act-btn--view:hover {
		background: #dcfce7;
		border-color: #86efac;
	}

	#procedure-worksheets-table .rm-act-btn--edit {
		border: 1px solid #bfdbfe;
		color: #1d4ed8;
		background: #eff6ff;
	}

	#procedure-worksheets-table .rm-act-btn--edit:hover {
		background: #dbeafe;
		border-color: #93c5fd;
	}

	#procedure-worksheets-table .rm-act-btn--muted {
		border: 1px solid #e2e8f0;
		color: #475569;
		background: #f8fafc;
	}

	#procedure-worksheets-table .rm-act-btn--muted:hover {
		background: #f1f5f9;
		border-color: #cbd5e1;
	}

	#procedure-worksheets-table .rm-act-btn--delete {
		border: 1px solid #fecaca;
		color: #b91c1c;
		background: #fef2f2;
	}

	#procedure-worksheets-table .rm-act-btn--delete:hover {
		background: #fee2e2;
		border-color: #fca5a5;
	}
	</style>
</div>

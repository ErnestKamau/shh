<div class="container-fluid px-0">
	<div class="workflow-board-panel mb-3">
		<div class="workflow-board-panel-header">
			<h5><i class="mdi mdi-folder-multiple-outline"></i> Grouped worksheet pipelines</h5>
			<button type="button" wire:click="create" class="btn btn-primary btn-sm btn-action-sm">
				<i class="mdi mdi-plus"></i> Create pipeline
			</button>
		</div>
		<div class="workflow-board-panel-body flush-top">
			<p class="text-muted mb-0" style="font-size: 0.9rem;">
				Define ordered multi-stage worksheets (e.g. DNA Analysis: recovery → extraction → amplification).
			</p>
		</div>
	</div>

	@if(session()->has('message'))
		<div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
			{{ session('message') }}
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	@endif

	<div class="workflow-board-panel">
		<div class="workflow-board-panel-body">
			<div class="workflow-board-filter-nested mb-3">
				<div class="row align-items-end g-2">
					<div class="col-md-5">
						<label class="form-label">Search</label>
						<input type="text" wire:model.live="search" class="form-control" placeholder="Search pipelines…">
					</div>
				</div>
			</div>

			<div class="table-responsive">
				<table id="grouped-worksheets-table" class="table workflow-table table-hover mb-0">
					<thead>
						<tr>
							<th>Actions</th>
							<th>#</th>
							<th>Name</th>
							<th>Stages</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						@forelse($holders as $holder)
							<tr>
								<td>
									<div class="d-flex flex-wrap gap-1">
										<a href="{{ route('formulars.grouped-worksheets.edit', $holder->id) }}"
											class="btn btn-sm rm-act-btn rm-act-btn--view"
											title="Manage stages">
											<i class="mdi mdi-format-list-numbered"></i>
										</a>
										<button type="button"
											wire:click="edit('{{ $holder->id }}')"
											class="btn btn-sm rm-act-btn rm-act-btn--edit"
											title="Edit">
											<i class="mdi mdi-pencil"></i>
										</button>
										<button type="button"
											wire:click="toggleActive('{{ $holder->id }}')"
											class="btn btn-sm rm-act-btn rm-act-btn--muted"
											title="{{ $holder->is_active ? 'Deactivate' : 'Activate' }}">
											<i class="mdi mdi-{{ $holder->is_active ? 'pause' : 'play' }}"></i>
										</button>
										<button type="button"
											wire:click="delete('{{ $holder->id }}')"
											class="btn btn-sm rm-act-btn rm-act-btn--delete"
											title="Delete"
											onclick="return confirm('Delete this pipeline?')">
											<i class="mdi mdi-delete"></i>
										</button>
									</div>
								</td>
								<td>{{ $loop->iteration }}</td>
								<td><strong>{{ $holder->name }}</strong></td>
								<td><span class="badge bg-secondary">{{ $holder->items_count }} stage(s)</span></td>
								<td>
									<span class="workflow-status-chip" style="--chip-accent: {{ $holder->is_active ? '#28a745' : '#94a3b8' }};">
										{{ $holder->is_active ? 'Active' : 'Inactive' }}
									</span>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="5" class="text-center py-5 text-muted">No grouped pipelines yet.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>
			<div class="mt-3">{{ $holders->links() }}</div>
		</div>
	</div>

	@if($showCreateModal || $showEditModal)
		<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.4);">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{{ $showEditModal ? 'Edit pipeline' : 'Create pipeline' }}</h5>
						<button type="button" class="btn-close" wire:click="$set('showCreateModal', false); $set('showEditModal', false)"></button>
					</div>
					<div class="modal-body">
						<div class="mb-3">
							<label class="form-label">Name *</label>
							<input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
							@error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
						</div>
						<div class="mb-3">
							<label class="form-label">Description</label>
							<textarea wire:model="description" class="form-control" rows="2"></textarea>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Document control no.</label>
								<input type="text" wire:model="document_control_no" class="form-control">
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Revision</label>
								<input type="text" wire:model="revision" class="form-control">
							</div>
						</div>
						<div class="mb-3">
							<label class="form-label">Issue date</label>
							<input type="date" wire:model="issue_date" class="form-control">
						</div>
						<div class="form-check">
							<input type="checkbox" wire:model="is_active" class="form-check-input" id="holderActive">
							<label class="form-check-label" for="holderActive">Active</label>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" wire:click="$set('showCreateModal', false); $set('showEditModal', false)">Cancel</button>
						<button type="button" class="btn btn-primary" wire:click="{{ $showEditModal ? 'update' : 'save' }}">Save</button>
					</div>
				</div>
			</div>
		</div>
	@endif

	<style>
	#grouped-worksheets-table .rm-act-btn {
		border-radius: 7px;
		padding: 4px 8px;
		font-size: 12px;
	}

	#grouped-worksheets-table .rm-act-btn--view {
		border: 1px solid #bbf7d0;
		color: #15803d;
		background: #f0fdf4;
	}

	#grouped-worksheets-table .rm-act-btn--view:hover {
		background: #dcfce7;
		border-color: #86efac;
	}

	#grouped-worksheets-table .rm-act-btn--edit {
		border: 1px solid #bfdbfe;
		color: #1d4ed8;
		background: #eff6ff;
	}

	#grouped-worksheets-table .rm-act-btn--edit:hover {
		background: #dbeafe;
		border-color: #93c5fd;
	}

	#grouped-worksheets-table .rm-act-btn--muted {
		border: 1px solid #e2e8f0;
		color: #475569;
		background: #f8fafc;
	}

	#grouped-worksheets-table .rm-act-btn--muted:hover {
		background: #f1f5f9;
		border-color: #cbd5e1;
	}

	#grouped-worksheets-table .rm-act-btn--delete {
		border: 1px solid #fecaca;
		color: #b91c1c;
		background: #fef2f2;
	}

	#grouped-worksheets-table .rm-act-btn--delete:hover {
		background: #fee2e2;
		border-color: #fca5a5;
	}
	</style>
</div>

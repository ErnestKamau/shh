<div class="container-fluid px-0">
	<div class="workflow-board-panel mb-3">
		<div class="workflow-board-panel-header">
			<h5><i class="mdi mdi-table-edit"></i> Log entry worksheets</h5>
			<button type="button" wire:click="create" class="btn btn-sm btn-outline-primary log-entry-btn-outline">
				<i class="mdi mdi-plus"></i> Create worksheet
			</button>
		</div>
		<div class="workflow-board-panel-body flush-top">
			<p class="text-muted mb-0" style="font-size: 0.9rem;">
				Define dynamic log tables with configurable columns, row drivers, and mandatory fields.
			</p>
		</div>
	</div>

	@if(session()->has('message'))
		<div class="alert alert-success alert-dismissible fade show mb-3">{{ session('message') }}</div>
	@endif

	<div class="workflow-board-panel">
		<div class="workflow-board-panel-body">
			<div class="mb-3">
				<input type="text" wire:model.live="search" class="form-control" placeholder="Search worksheets…" style="max-width: 320px;">
			</div>
			<div class="table-responsive">
				<table class="table workflow-table table-hover log-entry-data-table mb-0">
					<thead>
						<tr>
							<th class="log-entry-table-actions-col">Actions</th>
							<th>Name</th>
							<th>Row driver</th>
							<th>Columns</th>
							<th>Mandatory</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						@forelse($worksheets as $ws)
							<tr>
								<td class="log-entry-table-actions-col">
									<div class="d-flex gap-1 log-entry-table-actions">
										<a href="{{ route('formulars.log-entry-worksheets.preview', $ws->id) }}"
											class="btn btn-sm rm-act-btn rm-act-btn--preview"
											title="Preview template"
											target="_blank"
											rel="noopener">
											<i class="mdi mdi-eye-outline"></i>
										</a>
										<a href="{{ route('formulars.log-entry-worksheets.edit', $ws->id) }}"
											class="btn btn-sm rm-act-btn rm-act-btn--edit"
											title="Edit template">
											<i class="mdi mdi-pencil"></i>
										</a>
										<button type="button"
											wire:click="toggleActive('{{ $ws->id }}')"
											class="btn btn-sm rm-act-btn rm-act-btn--muted"
											title="{{ $ws->is_active ? 'Deactivate' : 'Activate' }}">
											<i class="mdi mdi-{{ $ws->is_active ? 'pause' : 'play' }}"></i>
										</button>
										<button type="button"
											wire:click="delete('{{ $ws->id }}')"
											class="btn btn-sm rm-act-btn rm-act-btn--delete"
											title="Delete"
											onclick="return confirm('Delete this worksheet template?')">
											<i class="mdi mdi-delete"></i>
										</button>
									</div>
								</td>
								<td><strong>{{ $ws->name }}</strong></td>
								<td><span class="badge badge-light border">{{ str_replace('_', ' ', $ws->row_driver) }}</span></td>
								<td>{{ $ws->columns_count }}</td>
								<td>{{ $ws->mandatory_fields_count }}</td>
								<td>
									<span class="badge badge-{{ $ws->is_active ? 'success' : 'secondary' }}">
										{{ $ws->is_active ? 'Active' : 'Inactive' }}
									</span>
								</td>
							</tr>
						@empty
							<tr><td colspan="6" class="text-center text-muted py-4">No log entry worksheets yet.</td></tr>
						@endforelse
					</tbody>
				</table>
			</div>
			<div class="mt-3">{{ $worksheets->links() }}</div>
		</div>
	</div>

	@if($showCreateModal || $showEditModal)
	<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
		<div class="modal-dialog">
			<div class="modal-content" style="border-radius: 10px;">
				<div class="modal-header">
					<h5 class="modal-title">{{ $showEditModal ? 'Edit' : 'Create' }} worksheet</h5>
					<button type="button" class="close" wire:click="$set('showCreateModal', false); $set('showEditModal', false)"><span>&times;</span></button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>Name <span class="text-danger">*</span></label>
						<input type="text" class="form-control" wire:model="name">
						@error('name') <small class="text-danger">{{ $message }}</small> @enderror
					</div>
					<div class="form-group">
						<label>Description</label>
						<textarea class="form-control" wire:model="description" rows="2"></textarea>
					</div>
					<div class="custom-control custom-checkbox mb-2">
						<input type="checkbox" class="custom-control-input" id="ws-active" wire:model="is_active">
						<label class="custom-control-label" for="ws-active">Active</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary log-entry-btn-outline" wire:click="$set('showCreateModal', false); $set('showEditModal', false)">Cancel</button>
					@if($showEditModal)
						<button type="button" class="btn btn-primary log-entry-btn-outline" wire:click="update">Save</button>
					@else
						<button type="button" class="btn btn-primary log-entry-btn-outline" wire:click="save">Create &amp; configure</button>
					@endif
				</div>
			</div>
		</div>
	</div>
	@endif

	<style>
		.log-entry-data-table .log-entry-table-actions-col {
			width: 11.5rem;
			min-width: 11.5rem;
			max-width: 11.5rem;
			white-space: nowrap;
			vertical-align: middle;
			text-align: left;
		}

		.log-entry-data-table thead .log-entry-table-actions-col,
		.log-entry-data-table tbody .log-entry-table-actions-col {
			padding-left: 0.75rem;
			padding-right: 0.75rem;
		}

		.log-entry-data-table .log-entry-table-actions {
			flex-wrap: nowrap;
			justify-content: flex-start;
			align-items: center;
		}

		.log-entry-btn-outline { border-radius: 6px; }
		.log-entry-data-table .rm-act-btn { padding: 0.2rem 0.45rem; line-height: 1.2; border-radius: 6px; }
		.log-entry-data-table .rm-act-btn--preview { color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.35); background: #fff; }
		.log-entry-data-table .rm-act-btn--preview:hover { background: rgba(99, 102, 241, 0.08); }
		.log-entry-data-table .rm-act-btn--edit { color: #0d6efd; border: 1px solid rgba(13, 110, 253, 0.35); background: #fff; }
		.log-entry-data-table .rm-act-btn--edit:hover { background: rgba(13, 110, 253, 0.08); }
		.log-entry-data-table .rm-act-btn--delete { color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.35); background: #fff; }
		.log-entry-data-table .rm-act-btn--delete:hover { background: rgba(220, 53, 69, 0.06); }
		.log-entry-data-table .rm-act-btn--muted { color: #64748b; border: 1px solid #e2e8f0; background: #fff; }
	</style>
</div>

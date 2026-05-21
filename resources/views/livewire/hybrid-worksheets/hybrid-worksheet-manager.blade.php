<div class="container-fluid px-0">
	<div class="workflow-board-panel mb-3">
		<div class="workflow-board-panel-header">
			<h5><i class="mdi mdi-file-tree"></i> Hybrid worksheets</h5>
			<button type="button" wire:click="create" class="btn btn-primary btn-sm btn-action-sm">
				<i class="mdi mdi-plus"></i> Create hybrid worksheet
			</button>
		</div>
		<div class="workflow-board-panel-body flush-top">
			<p class="text-muted mb-0" style="font-size: 0.9rem;">
				Combine formula, procedure, and method-sequence blocks in a single versioned datasheet.
			</p>
		</div>
	</div>

	@if(session()->has('message'))
		<div class="alert alert-success alert-dismissible fade show mb-3">{{ session('message') }}
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	@endif

	<div class="workflow-board-panel">
		<div class="workflow-board-panel-body">
			<input type="text" wire:model.live="search" class="form-control mb-3" placeholder="Search…">
			<table class="table workflow-table table-hover">
				<thead>
					<tr><th>Name</th><th>Active version</th><th>Status</th><th>Actions</th></tr>
				</thead>
				<tbody>
					@forelse($worksheets as $ws)
						<tr>
							<td><strong>{{ $ws->name }}</strong></td>
							<td>v{{ $ws->activeVersion?->version_number ?? '—' }}</td>
							<td>{{ $ws->is_active ? 'Active' : 'Inactive' }}</td>
							<td>
								@if($ws->activeVersion)
									<a href="{{ route('formulars.hybrid-worksheets.edit', ['hybridWorksheet' => $ws->id, 'hybridWorksheetVersion' => $ws->activeVersion->id]) }}" class="btn btn-sm btn-outline-success me-1">
										<i class="mdi mdi-format-list-numbered"></i> Blocks
									</a>
								@endif
								<button type="button" wire:click="edit('{{ $ws->id }}')" class="btn btn-sm btn-outline-primary me-1"><i class="mdi mdi-pencil"></i></button>
								<button type="button" wire:click="toggleActive('{{ $ws->id }}')" class="btn btn-sm btn-outline-warning me-1"><i class="mdi mdi-pause"></i></button>
								<button type="button" wire:click="delete('{{ $ws->id }}')" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="mdi mdi-delete"></i></button>
							</td>
						</tr>
					@empty
						<tr><td colspan="4" class="text-center text-muted py-4">No hybrid worksheets yet.</td></tr>
					@endforelse
				</tbody>
			</table>
			{{ $worksheets->links() }}
		</div>
	</div>

	@if($showCreateModal || $showEditModal)
		<div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.4)">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">{{ $showEditModal ? 'Edit' : 'Create' }} hybrid worksheet</h5>
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
						<div class="form-check">
							<input type="checkbox" wire:model="is_active" class="form-check-input" id="hybridActive">
							<label class="form-check-label" for="hybridActive">Active</label>
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
</div>

<div class="container-fluid px-0 log-entry-editor worksheet-engine-tag-select">
@include('layouts.lab.partials.worksheet-engine-tag-select-styles')
	@if(session()->has('message'))
		<div class="alert alert-success mb-3">{{ session('message') }}</div>
	@endif

	<div class="workflow-board-panel mb-3">
		<div class="workflow-board-panel-header">
			<h5><i class="mdi mdi-table-edit"></i> {{ $worksheet->name }}</h5>
			<a href="{{ route('formulars.log-entry-worksheets.manage') }}" class="btn btn-sm btn-outline-secondary log-entry-btn-outline">
				<i class="mdi mdi-arrow-left"></i> Back to list
			</a>
		</div>
	</div>

	<ul class="nav nav-tabs mb-3">
		<li class="nav-item">
			<a class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}" href="#" wire:click.prevent="$set('activeTab', 'general')">General</a>
		</li>
		<li class="nav-item">
			<a class="nav-link {{ $activeTab === 'columns' ? 'active' : '' }}" href="#" wire:click.prevent="$set('activeTab', 'columns')">
				Table columns <span class="badge badge-light">{{ count($columns) }}</span>
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link {{ $activeTab === 'mandatory' ? 'active' : '' }}" href="#" wire:click.prevent="$set('activeTab', 'mandatory')">
				Mandatory fields <span class="badge badge-light">{{ count($mandatoryFields) }}</span>
			</a>
		</li>
	</ul>

	@if($activeTab === 'general')
	<div class="workflow-board-panel">
		<div class="workflow-board-panel-body">
			<form wire:submit.prevent="saveGeneral">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Name <span class="text-danger">*</span></label>
							<input type="text" class="form-control" wire:model="name">
							@error('name') <small class="text-danger">{{ $message }}</small> @enderror
						</div>
						<div class="form-group">
							<label>Description</label>
							<textarea class="form-control" wire:model="description" rows="3"></textarea>
						</div>
						<div class="form-group">
							<label>Row driver</label>
							<select class="form-control" wire:model="row_driver">
								@foreach($rowDriverOptions as $value => $label)
									<option value="{{ $value }}">{{ $label }}</option>
								@endforeach
							</select>
							<small class="text-muted">Determines how table rows are generated when capturing data in a batch.</small>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label>Mandatory fields placement</label>
							<select class="form-control" wire:model="mandatory_fields_placement">
								<option value="top">Top of worksheet (above table)</option>
								<option value="bottom">Bottom of worksheet (below table)</option>
							</select>
						</div>
						<div class="row mb-3">
							<div class="col-md-6">
								<div class="custom-control custom-checkbox">
									<input type="checkbox" class="custom-control-input" id="ws-active-edit" wire:model="is_active">
									<label class="custom-control-label" for="ws-active-edit">Active</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="custom-control custom-checkbox">
									<input type="checkbox" class="custom-control-input" id="allow-manual" wire:model="allow_manual_rows">
									<label class="custom-control-label" for="allow-manual">Allow manual add/remove rows at capture</label>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label>Document control no.</label>
							<input type="text" class="form-control" wire:model="document_control_no">
						</div>
						<div class="row">
							<div class="col-6">
								<div class="form-group">
									<label>Revision</label>
									<input type="text" class="form-control" wire:model="revision">
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label>Issue date</label>
									<input type="date" class="form-control" wire:model="issue_date">
								</div>
							</div>
						</div>
					</div>
				</div>
				<button type="submit" class="btn btn-primary log-entry-btn-outline"><i class="mdi mdi-content-save"></i> Save settings</button>
			</form>
		</div>
	</div>
	@endif

	@if($activeTab === 'columns')
	<div class="workflow-board-panel">
		<div class="workflow-board-panel-header">
			<h6 class="mb-0">Table columns</h6>
			<button type="button" class="btn btn-sm btn-outline-primary log-entry-btn-outline" wire:click="showCreateColumnModalInit">
				<i class="mdi mdi-plus"></i> Add column
			</button>
		</div>
		<div class="workflow-board-panel-body">
			<input type="text" wire:model.live="columnSearch" wire:keyup="loadColumns" class="form-control mb-3" style="max-width:280px" placeholder="Search columns…">
			<div class="table-responsive">
				<table class="table table-sm table-hover log-entry-data-table mb-0">
					<thead>
						<tr>
							<th>Order</th>
							<th>Label</th>
							<th>Key</th>
							<th>Type</th>
							<th>Data source</th>
							<th>Required</th>
							<th class="text-right">Actions</th>
						</tr>
					</thead>
					<tbody id="sortable-log-columns">
						@foreach($columns as $col)
							<tr wire:key="col-{{ $col['id'] }}" data-id="{{ $col['id'] }}">
								<td>{{ $col['order'] }}</td>
								<td>{{ $col['label'] }}</td>
								<td><code>{{ $col['key'] }}</code></td>
								<td><span class="badge badge-info">{{ $col['column_type'] }}</span></td>
								<td>
									@if($col['column_type'] === 'dataset')
										<small class="text-muted">{{ \App\Livewire\LogEntryWorksheets\LogEntryWorksheetEditor::formatDatasetSummary($col['dataset_config'] ?? null) }}</small>
									@else
										—
									@endif
								</td>
								<td>{{ $col['is_required'] ? 'Yes' : 'No' }}</td>
								<td class="text-right">
									<div class="d-flex justify-content-end gap-1">
										<button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="showEditColumnModalInit('{{ $col['id'] }}')" title="Edit">
											<i class="mdi mdi-pencil"></i>
										</button>
										<button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete" wire:click="showDeleteColumnModalInit('{{ $col['id'] }}')" title="Delete">
											<i class="mdi mdi-delete"></i>
										</button>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
	@endif

	@if($activeTab === 'mandatory')
	<div class="workflow-board-panel">
		<div class="workflow-board-panel-header">
			<h6 class="mb-0">Mandatory worksheet fields</h6>
			<button type="button" class="btn btn-sm btn-outline-primary log-entry-btn-outline" wire:click="showCreateFieldModalInit">
				<i class="mdi mdi-plus"></i> Add field
			</button>
		</div>
		<div class="workflow-board-panel-body">
			<p class="text-muted small">These fields apply to the whole worksheet instance. They appear <strong>{{ $mandatory_fields_placement === 'top' ? 'above' : 'below' }}</strong> the dynamic table during batch capture.</p>
			<input type="text" wire:model.live="fieldSearch" wire:keyup="loadMandatoryFields" class="form-control mb-3" style="max-width:280px" placeholder="Search fields…">
			<div class="table-responsive">
				<table class="table table-sm table-hover log-entry-data-table mb-0">
					<thead>
						<tr>
							<th>Order</th>
							<th>Label</th>
							<th>Type</th>
							<th>Value name</th>
							<th>Config</th>
							<th>Required</th>
							<th class="text-right">Actions</th>
						</tr>
					</thead>
					<tbody id="sortable-log-fields">
						@foreach($mandatoryFields as $field)
							<tr wire:key="mf-{{ $field['id'] }}" data-id="{{ $field['id'] }}">
								<td>{{ $field['order'] }}</td>
								<td>{{ $field['label'] }}</td>
								<td>{{ $mandatoryFieldTypes[$field['field_type']] ?? $field['field_type'] }}</td>
								<td><code>{{ $field['field_value_name'] }}</code></td>
								<td>
									@if($field['field_type'] === 'dataset_related')
										<small>{{ \App\Livewire\LogEntryWorksheets\LogEntryWorksheetEditor::formatDatasetSummary($field['dataset_config'] ?? null) }}</small>
									@elseif(in_array($field['field_type'], ['checkbox', 'radio']))
										<small>{{ implode(', ', $field['field_options']['options'] ?? []) }}</small>
									@else
										—
									@endif
								</td>
								<td>{{ $field['is_required'] ? 'Yes' : 'No' }}</td>
								<td class="text-right">
									<div class="d-flex justify-content-end gap-1">
										<button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit" wire:click="showEditFieldModalInit('{{ $field['id'] }}')" title="Edit">
											<i class="mdi mdi-pencil"></i>
										</button>
										<button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete" wire:click="showDeleteFieldModalInit('{{ $field['id'] }}')" title="Delete">
											<i class="mdi mdi-delete"></i>
										</button>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
	@endif

	@include('livewire.log-entry-worksheets.partials.column-modals')
	@include('livewire.log-entry-worksheets.partials.column-modal-styles')
	@include('livewire.log-entry-worksheets.partials.mandatory-modals')

	<style>
		.log-entry-editor .log-entry-btn-outline { border-radius: 6px; }
		.log-entry-editor .log-entry-data-table .rm-act-btn {
			padding: 0.2rem 0.45rem;
			line-height: 1.2;
		}
		.log-entry-editor .log-entry-data-table .rm-act-btn--edit {
			color: #0d6efd;
			border: 1px solid rgba(13, 110, 253, 0.35);
			background: #fff;
			border-radius: 6px;
		}
		.log-entry-editor .log-entry-data-table .rm-act-btn--edit:hover {
			background: rgba(13, 110, 253, 0.08);
		}
		.log-entry-editor .log-entry-data-table .rm-act-btn--delete {
			color: #dc3545;
			border: 1px solid rgba(220, 53, 69, 0.35);
			background: #fff;
			border-radius: 6px;
		}
		.log-entry-editor .log-entry-data-table .rm-act-btn--delete:hover {
			background: rgba(220, 53, 69, 0.06);
		}
		.log-entry-editor .log-entry-data-table thead th {
			font-size: 0.72rem;
			text-transform: uppercase;
			letter-spacing: 0.04em;
			color: #64748b;
			background: #f8fafc;
		}
	</style>
</div>

@push('scripts')
<script>
	document.addEventListener('livewire:init', function () {
		function initSortable(id, event) {
			const el = document.getElementById(id);
			if (!el || typeof Sortable === 'undefined') return;
			Sortable.create(el, {
				animation: 150,
				handle: 'tr',
				onEnd: function () {
					const ids = Array.from(el.querySelectorAll('tr[data-id]')).map(r => r.getAttribute('data-id'));
					Livewire.find(@this.id).call(event, ids);
				}
			});
		}
		Livewire.hook('morph.updated', () => {
			initSortable('sortable-log-columns', 'updateColumnOrder');
			initSortable('sortable-log-fields', 'updateFieldOrder');
		});
	});
</script>
@endpush

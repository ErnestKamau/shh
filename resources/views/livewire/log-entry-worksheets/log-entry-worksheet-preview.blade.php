<div class="log-entry-preview">
	<div class="workflow-board-panel mb-3">
		<div class="workflow-board-panel-header">
			<div>
				<h5 class="mb-1"><i class="mdi mdi-eye-outline"></i> Template preview</h5>
				<p class="text-muted mb-0 small">Read-only preview — layout and fields as analysts will see them. Data rows are not shown.</p>
			</div>
			<div class="d-flex flex-wrap log-entry-preview__header-actions">
				<a href="{{ route('formulars.log-entry-worksheets.edit', $worksheet->id) }}" class="btn btn-sm btn-outline-primary log-entry-btn-outline">
					<i class="mdi mdi-pencil"></i> Configure
				</a>
				<a href="{{ route('formulars.log-entry-worksheets.manage') }}" class="btn btn-sm btn-outline-secondary log-entry-btn-outline">
					<i class="mdi mdi-arrow-left"></i> Back to list
				</a>
			</div>
		</div>
		<div class="workflow-board-panel-body">
			<div class="row g-3 log-entry-preview__meta">
				<div class="col-md-4">
					<span class="log-entry-preview__meta-label">Template</span>
					<strong class="d-block">{{ $worksheet->name }}</strong>
					@if($worksheet->description)
						<small class="text-muted">{{ $worksheet->description }}</small>
					@endif
				</div>
				<div class="col-md-2">
					<span class="log-entry-preview__meta-label">Status</span>
					<span class="badge badge-{{ $worksheet->is_active ? 'success' : 'secondary' }}">
						{{ $worksheet->is_active ? 'Active' : 'Inactive' }}
					</span>
				</div>
				<div class="col-md-3">
					<span class="log-entry-preview__meta-label">Row driver</span>
					<span class="d-block">{{ $rowDriverLabel }}</span>
				</div>
				<div class="col-md-3">
					<span class="log-entry-preview__meta-label">Mandatory fields</span>
					<span class="d-block">{{ $worksheet->mandatory_fields_placement === 'top' ? 'Above table' : 'Below table' }}</span>
				</div>
				@if($worksheet->document_control_no || $worksheet->revision || $worksheet->issue_date)
				<div class="col-12">
					<div class="log-entry-preview__doc-strip">
						@if($worksheet->document_control_no)
							<span><strong>Doc no.</strong> {{ $worksheet->document_control_no }}</span>
						@endif
						@if($worksheet->revision)
							<span><strong>Revision</strong> {{ $worksheet->revision }}</span>
						@endif
						@if($worksheet->issue_date)
							<span><strong>Issue date</strong> {{ $worksheet->issue_date->format('Y-m-d') }}</span>
						@endif
					</div>
				</div>
				@endif
			</div>
		</div>
	</div>

	@php $placementTop = $worksheet->mandatory_fields_placement === 'top'; @endphp

	@if($placementTop && $mandatoryFields->isNotEmpty())
		@include('livewire.log-entry-worksheets.partials.preview-mandatory-fields', [
			'mandatoryFields' => $mandatoryFields,
			'title' => 'Mandatory fields (whole worksheet)',
		])
	@endif

	<div class="workflow-board-panel mb-3 log-entry-preview__table-panel">
		<div class="workflow-board-panel-header">
			<h6 class="mb-0"><i class="mdi mdi-table"></i> Data table</h6>
			@if($worksheet->allow_manual_rows)
				<span class="badge badge-light border">Manual rows allowed</span>
			@endif
		</div>
		<div class="workflow-board-panel-body flush-top p-0">
			@if($columns->isEmpty())
				<div class="text-center text-muted py-4 px-3">
					No table columns configured yet. <a href="{{ route('formulars.log-entry-worksheets.edit', $worksheet->id) }}">Add columns</a>.
				</div>
			@else
			<div class="table-responsive">
				<table class="table workflow-table table-hover log-entry-preview-table mb-0">
					<thead>
						<tr>
							<th class="log-entry-preview-table__index">#</th>
							@foreach($columns as $col)
								<th>
									{{ $col->label }}
									@if($col->is_required)<span class="text-danger">*</span>@endif
								</th>
							@endforeach
							@if($worksheet->allow_manual_rows)
								<th class="log-entry-preview-table__actions"></th>
							@endif
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="{{ $columns->count() + ($worksheet->allow_manual_rows ? 2 : 1) }}" class="log-entry-preview__rows-placeholder">
								<i class="mdi mdi-table-row"></i>
								<p class="mb-0"><strong>Rows appear at capture</strong></p>
								<small class="text-muted">Generated from the row driver when this template is used on a batch. No sample data is shown in preview.</small>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			@endif
		</div>
	</div>

	@if(!$placementTop && $mandatoryFields->isNotEmpty())
		@include('livewire.log-entry-worksheets.partials.preview-mandatory-fields', [
			'mandatoryFields' => $mandatoryFields,
			'title' => 'Mandatory fields (whole worksheet)',
		])
	@endif

	<style>
		.log-entry-preview__meta-label {
			display: block;
			font-size: 0.7rem;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			color: #64748b;
			margin-bottom: 0.2rem;
		}
		.log-entry-preview__doc-strip {
			display: flex;
			flex-wrap: wrap;
			gap: 1rem 1.5rem;
			padding: 0.75rem 1rem;
			background: #f8fafc;
			border-radius: 0.5rem;
			border: 1px solid #e2e8f0;
			font-size: 0.875rem;
		}
		.log-entry-preview__header-actions {
			gap: 0.75rem;
		}

		.log-entry-preview__choice-group--inline {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 0.35rem 1.25rem;
		}

		.log-entry-preview__choice-group--inline .custom-control-inline {
			margin-right: 0;
		}

		.log-entry-preview__table-panel .workflow-board-panel-header h6 {
			font-size: 1rem;
			font-weight: 600;
			color: #0f172a;
		}

		.log-entry-preview-table thead th {
			background: #f8fafc !important;
			color: #64748b !important;
			font-size: 0.75rem;
			font-weight: 700;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			border-bottom: 1px solid #e2e8f0 !important;
			padding: 14px 16px;
			vertical-align: middle;
			white-space: nowrap;
		}

		.log-entry-preview-table tbody td {
			padding: 16px;
			border-bottom: 1px solid #e2e8f0;
			vertical-align: middle;
		}

		.log-entry-preview-table.table-hover tbody tr:hover {
			background-color: #f8fafc;
		}

		.log-entry-preview-table__index {
			width: 3rem;
		}

		.log-entry-preview-table__actions {
			width: 3rem;
		}
		.log-entry-preview__rows-placeholder {
			text-align: center;
			padding: 2.5rem 1.5rem !important;
			background: linear-gradient(180deg, #fafbff 0%, #f8fafc 100%);
			color: #64748b;
		}
		.log-entry-preview__rows-placeholder i {
			font-size: 2rem;
			color: #cbd5e1;
			display: block;
			margin-bottom: 0.5rem;
		}
		.log-entry-preview .log-entry-preview-field--disabled,
		.log-entry-preview .log-entry-preview-field--disabled:focus {
			background-color: #f8fafc;
			cursor: not-allowed;
		}
	</style>
</div>

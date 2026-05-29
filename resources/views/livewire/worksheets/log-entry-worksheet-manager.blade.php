<div>
	@if(session()->has('log_entry_message'))
		<div class="alert alert-success mb-3">{{ session('log_entry_message') }}</div>
	@endif

	@if($errors->any())
		<div class="alert alert-danger mb-3">
			<ul class="mb-0">
				@foreach($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	@if($worksheets->isEmpty())
		<div class="alert alert-info">No log entry worksheets are linked to captured results in this batch.</div>
	@else
		<div class="log-entry-toolbar card border-0 shadow-sm mb-4">
			<div class="card-body py-3 px-4">
				<div class="log-entry-toolbar__inner">
					<div class="log-entry-toolbar__template">
						<label class="log-entry-toolbar__label" for="log-entry-template-select">Template</label>
						<div class="log-entry-toolbar__select-wrap">
							<i class="mdi mdi-file-document-outline log-entry-toolbar__select-icon"></i>
							<select id="log-entry-template-select"
								class="form-control form-control-sm log-entry-toolbar__select"
								wire:change="selectWorksheet($event.target.value)">
								@foreach($worksheets as $ws)
									<option value="{{ $ws->id }}" @selected($selectedWorksheetId === $ws->id)>{{ $ws->name }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="log-entry-toolbar__actions">
						<div class="log-entry-toolbar__group">
							<button type="button" class="btn btn-sm log-entry-toolbar__btn log-entry-toolbar__btn--ghost" wire:click="regenerateAutoRows">
								<i class="mdi mdi-sync"></i>
								<span>Sync auto rows</span>
							</button>
							@if($selectedWorksheet?->allow_manual_rows)
								<button type="button" class="btn btn-sm log-entry-toolbar__btn log-entry-toolbar__btn--ghost-success" wire:click="addManualRow">
									<i class="mdi mdi-plus"></i>
									<span>Add row</span>
								</button>
							@endif
						</div>

						<div class="log-entry-toolbar__group log-entry-toolbar__group--primary">
							<button type="button" class="btn btn-sm log-entry-toolbar__btn log-entry-toolbar__btn--save" wire:click="saveWorksheet">
								<i class="mdi mdi-content-save-outline"></i>
								<span>Save</span>
							</button>
							<button type="button" class="btn btn-sm log-entry-toolbar__btn log-entry-toolbar__btn--post" wire:click="postWorksheet">
								<i class="mdi mdi-check-circle-outline"></i>
								<span>Post</span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		@if($selectedWorksheet && $instance)
			@php
				$placementTop = $selectedWorksheet->mandatory_fields_placement === 'top';
			@endphp

			@if($placementTop && $mandatoryFields->isNotEmpty())
				@include('livewire.worksheets.partials.log-entry-mandatory-fields', [
					'mandatoryFields' => $mandatoryFields,
					'title' => 'Mandatory fields (applies to whole worksheet)',
				])
			@endif

			<div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
				<div class="card-body p-0">
					<div class="table-responsive">
						<table class="table table-sm table-hover mb-0 log-entry-capture-table">
							<thead>
								<tr>
									<th style="width: 40px;">#</th>
									@foreach($columns as $col)
										<th>
											{{ $col->label }}
											@if($col->is_required)<span class="text-danger">*</span>@endif
										</th>
									@endforeach
									@if($selectedWorksheet->allow_manual_rows)
										<th style="width: 50px;"></th>
									@endif
								</tr>
							</thead>
							<tbody>
								@forelse($orderedRows as $entry)
									@php $row = $entry['row']; @endphp
									<tr wire:key="ler-{{ $row->id }}">
										<td class="text-muted">{{ $row->row_index }}</td>
										@foreach($columns as $col)
											<td>
												@if($col->column_type === 'derived')
													<span class="text-muted">{{ $tableData[$row->id][$col->key] ?? '—' }}</span>
												@elseif($col->column_type === 'dataset')
													<div class="log-entry-dataset-value">{{ $tableData[$row->id][$col->key] ?? '—' }}</div>
												@elseif($col->input_data_type === 'textarea')
													<textarea class="form-control form-control-sm" rows="2"
														wire:model.lazy="tableData.{{ $row->id }}.{{ $col->key }}"></textarea>
												@elseif($col->input_data_type === 'boolean')
													<select class="form-control form-control-sm"
														wire:model.lazy="tableData.{{ $row->id }}.{{ $col->key }}">
														<option value="">—</option>
														<option value="1">Yes</option>
														<option value="0">No</option>
													</select>
												@elseif($col->input_data_type === 'date')
													<input type="date" class="form-control form-control-sm"
														wire:model.lazy="tableData.{{ $row->id }}.{{ $col->key }}">
												@elseif($col->input_data_type === 'number')
													<input type="number" class="form-control form-control-sm"
														wire:model.lazy="tableData.{{ $row->id }}.{{ $col->key }}">
												@else
													<input type="text" class="form-control form-control-sm"
														wire:model.lazy="tableData.{{ $row->id }}.{{ $col->key }}">
												@endif
											</td>
										@endforeach
										@if($selectedWorksheet->allow_manual_rows)
											<td>
												@if($row->row_source === 'manual')
													<button type="button" class="btn btn-xs btn-outline-danger"
														wire:click="removeManualRow('{{ $row->id }}')"
														onclick="return confirm('Remove this row?')">
														<i class="mdi mdi-delete"></i>
													</button>
												@endif
											</td>
										@endif
									</tr>
								@empty
									<tr>
										<td colspan="{{ $columns->count() + 2 }}" class="text-center text-muted py-4">
											No rows yet. Click “Sync auto rows” to generate rows from the row driver.
										</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>

			@if(!$placementTop && $mandatoryFields->isNotEmpty())
				@include('livewire.worksheets.partials.log-entry-mandatory-fields', [
					'mandatoryFields' => $mandatoryFields,
					'title' => 'Mandatory fields (applies to whole worksheet)',
				])
			@endif
		@endif
	@endif

	<style>
		.log-entry-toolbar {
			border-radius: 12px;
			background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
		}

		.log-entry-toolbar__inner {
			display: flex;
			flex-wrap: wrap;
			align-items: flex-end;
			justify-content: space-between;
			gap: 1rem 1.5rem;
		}

		.log-entry-toolbar__template {
			flex: 1 1 280px;
			max-width: 420px;
		}

		.log-entry-toolbar__label {
			display: block;
			margin-bottom: 0.35rem;
			font-size: 0.7rem;
			font-weight: 700;
			letter-spacing: 0.06em;
			text-transform: uppercase;
			color: #64748b;
		}

		.log-entry-toolbar__select-wrap {
			position: relative;
		}

		.log-entry-toolbar__select-icon {
			position: absolute;
			left: 0.85rem;
			top: 50%;
			transform: translateY(-50%);
			color: #64748b;
			font-size: 1rem;
			pointer-events: none;
			z-index: 2;
		}

		.log-entry-toolbar__select {
			height: 38px;
			padding-left: 2.35rem;
			border: 1px solid #e2e8f0;
			border-radius: 10px;
			background: #fff;
			font-size: 0.875rem;
			font-weight: 500;
			color: #0f172a;
			box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
			transition: border-color 0.15s ease, box-shadow 0.15s ease;
		}

		.log-entry-toolbar__select:focus {
			border-color: #3b82f6;
			box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
		}

		.log-entry-toolbar__actions {
			display: flex;
			flex-wrap: wrap;
			align-items: center;
			justify-content: flex-end;
			gap: 0.75rem;
			margin-left: auto;
		}

		.log-entry-toolbar__group {
			display: inline-flex;
			flex-wrap: wrap;
			align-items: center;
			gap: 0.5rem;
		}

		.log-entry-toolbar__group--primary {
			padding-left: 0.75rem;
			border-left: 1px solid #e2e8f0;
		}

		.log-entry-toolbar__btn {
			display: inline-flex;
			align-items: center;
			gap: 0.35rem;
			height: 38px;
			padding: 0 0.9rem;
			border-radius: 10px;
			font-size: 0.8125rem;
			font-weight: 600;
			line-height: 1;
			transition: all 0.15s ease;
		}

		.log-entry-toolbar__btn--ghost {
			border: 1px solid #dbeafe;
			background: #eff6ff;
			color: #2563eb;
		}

		.log-entry-toolbar__btn--ghost:hover {
			background: #dbeafe;
			border-color: #93c5fd;
			color: #1d4ed8;
		}

		.log-entry-toolbar__btn--ghost-success {
			border: 1px solid #bbf7d0;
			background: #f0fdf4;
			color: #16a34a;
		}

		.log-entry-toolbar__btn--ghost-success:hover {
			background: #dcfce7;
			border-color: #86efac;
			color: #15803d;
		}

		.log-entry-toolbar__btn--save {
			border: none;
			background: #2563eb;
			color: #fff;
			box-shadow: 0 1px 2px rgba(37, 99, 235, 0.25);
		}

		.log-entry-toolbar__btn--save:hover {
			background: #1d4ed8;
			color: #fff;
		}

		.log-entry-toolbar__btn--post {
			border: none;
			background: #059669;
			color: #fff;
			box-shadow: 0 1px 2px rgba(5, 150, 105, 0.25);
		}

		.log-entry-toolbar__btn--post:hover {
			background: #047857;
			color: #fff;
		}

		.log-entry-dataset-value {
			min-height: 31px;
			padding: 0.45rem 0.65rem;
			border-radius: 8px;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			font-size: 0.8125rem;
			color: #334155;
			line-height: 1.4;
			word-break: break-word;
		}

		.log-entry-capture-table thead th {
			background: #f1f5f9;
			font-size: 0.75rem;
			text-transform: uppercase;
			letter-spacing: 0.03em;
			white-space: nowrap;
		}

		@media (max-width: 767.98px) {
			.log-entry-toolbar__group--primary {
				padding-left: 0;
				border-left: none;
				width: 100%;
				justify-content: flex-end;
			}
		}
	</style>
</div>

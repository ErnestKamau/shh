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
		<div class="mb-3 d-flex flex-wrap align-items-center gap-2">
			<label class="mb-0 font-weight-bold">Template:</label>
			<select class="form-control form-control-sm" style="max-width: 320px;" wire:change="selectWorksheet($event.target.value)">
				@foreach($worksheets as $ws)
					<option value="{{ $ws->id }}" @selected($selectedWorksheetId === $ws->id)>{{ $ws->name }}</option>
				@endforeach
			</select>
			<button type="button" class="btn btn-sm btn-outline-primary log-entry-btn-outline" wire:click="regenerateAutoRows">
				<i class="mdi mdi-sync"></i> Sync auto rows
			</button>
			@if($selectedWorksheet?->allow_manual_rows)
				<button type="button" class="btn btn-sm btn-outline-success log-entry-btn-outline" wire:click="addManualRow">
					<i class="mdi mdi-plus"></i> Add row
				</button>
			@endif
			<button type="button" class="btn btn-sm btn-primary ml-auto" wire:click="saveWorksheet">Save</button>
			<button type="button" class="btn btn-sm btn-success" wire:click="postWorksheet">Post</button>
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
													<input type="text" class="form-control form-control-sm bg-light"
														readonly
														value="{{ $tableData[$row->id][$col->key] ?? '' }}"
														placeholder="—">
													@if(empty($tableData[$row->id][$col->key] ?? ''))
														<small class="text-muted">No value resolved</small>
													@endif
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
</div>

<style>
	.log-entry-capture-table thead th {
		background: #f1f5f9;
		font-size: 0.75rem;
		text-transform: uppercase;
		letter-spacing: 0.03em;
		white-space: nowrap;
	}
</style>

<div class="procedure-layout-editor">
	@if($message)
		<div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show mb-3">
			{{ $message }}
			<button type="button" class="btn-close" wire:click="$set('message', '')"></button>
		</div>
	@endif

	<div class="pw-callout mb-3">
		<div class="pw-callout-icon"><i class="mdi mdi-table-large"></i></div>
		<div class="pw-callout-text">
			<strong>Sectioned matrix</strong>
			<span>Define sections, rows, and columns used by phased grouped pipelines. Stock is deducted on pipeline <em>Complete stage</em> for reagent columns with a linked media.</span>
		</div>
	</div>

	<div class="mb-3">
		<label class="form-label fw-semibold">Layout mode</label>
		<select wire:model.live="layoutMode" class="form-control" style="max-width: 28rem;">
			<option value="default">Classic — steps only (no matrix)</option>
			<option value="sectioned_matrix">Sectioned matrix</option>
		</select>
	</div>

	@if($layoutMode === 'sectioned_matrix')
		<div class="d-flex justify-content-between align-items-center mb-3">
			<h6 class="mb-0">Sections</h6>
			<button type="button" class="btn btn-sm btn-outline-primary" wire:click="addSection">
				<i class="mdi mdi-plus"></i> Add section
			</button>
		</div>

		@forelse($sections as $sIndex => $section)
			<div class="card mb-3" wire:key="layout-section-{{ $sIndex }}">
				<div class="card-header d-flex flex-wrap align-items-center gap-2 py-2">
					<button type="button" class="btn btn-link text-dark p-0 text-decoration-none flex-grow-1 text-start"
						wire:click="toggleSection({{ $sIndex }})">
						<i class="mdi mdi-chevron-{{ $expandedSectionIndex === $sIndex ? 'down' : 'right' }}"></i>
						<strong>{{ $section['label'] ?: 'Untitled section' }}</strong>
						<small class="text-muted ms-1">{{ $section['key'] }}</small>
					</button>
					<div class="btn-group btn-group-sm">
						<button type="button" class="btn btn-outline-secondary" wire:click="moveSection({{ $sIndex }}, -1)" title="Move up"><i class="mdi mdi-chevron-up"></i></button>
						<button type="button" class="btn btn-outline-secondary" wire:click="moveSection({{ $sIndex }}, 1)" title="Move down"><i class="mdi mdi-chevron-down"></i></button>
						<button type="button" class="btn btn-outline-danger" wire:click="removeSection({{ $sIndex }})" onclick="return confirm('Remove this section?')"><i class="mdi mdi-delete"></i></button>
					</div>
				</div>

				@if($expandedSectionIndex === $sIndex)
					<div class="card-body">
						<div class="row mb-3">
							<div class="col-md-4">
								<label class="form-label">Key *</label>
								<input type="text" class="form-control form-control-sm" wire:model="sections.{{ $sIndex }}.key">
								@error('sections.'.$sIndex.'.key') <div class="text-danger small">{{ $message }}</div> @enderror
							</div>
							<div class="col-md-8">
								<label class="form-label">Label *</label>
								<input type="text" class="form-control form-control-sm" wire:model="sections.{{ $sIndex }}.label">
								@error('sections.'.$sIndex.'.label') <div class="text-danger small">{{ $message }}</div> @enderror
							</div>
						</div>

						<div class="d-flex justify-content-between align-items-center mb-2">
							<strong class="small">Rows</strong>
							<button type="button" class="btn btn-xs btn-outline-primary btn-sm" wire:click="addRow({{ $sIndex }})"><i class="mdi mdi-plus"></i> Row</button>
						</div>
						@foreach($section['rows'] as $rIndex => $row)
							<div class="row g-2 mb-2 align-items-center" wire:key="layout-row-{{ $sIndex }}-{{ $rIndex }}">
								<div class="col-md-4">
									<input type="text" class="form-control form-control-sm" wire:model="sections.{{ $sIndex }}.rows.{{ $rIndex }}.key" placeholder="key">
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control form-control-sm" wire:model="sections.{{ $sIndex }}.rows.{{ $rIndex }}.label" placeholder="label">
								</div>
								<div class="col-md-2">
									<button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeRow({{ $sIndex }}, {{ $rIndex }})"><i class="mdi mdi-close"></i></button>
								</div>
							</div>
						@endforeach

						<hr>
						<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
							<strong class="small">Columns</strong>
							<div class="btn-group btn-group-sm">
								<button type="button" class="btn btn-outline-primary" wire:click="addColumn({{ $sIndex }}, 'text')">+ Text</button>
								<button type="button" class="btn btn-outline-primary" wire:click="addColumn({{ $sIndex }}, 'reagent_input')">+ Reagent</button>
								<button type="button" class="btn btn-outline-primary" wire:click="addColumn({{ $sIndex }}, 'step_select')">+ Step select</button>
							</div>
						</div>

						@foreach($section['columns'] as $cIndex => $column)
							<div class="border rounded p-2 mb-2 bg-light" wire:key="layout-col-{{ $sIndex }}-{{ $cIndex }}">
								<div class="row g-2 mb-2">
									<div class="col-md-3">
										<label class="form-label small mb-0">Key</label>
										<input type="text" class="form-control form-control-sm" wire:model="sections.{{ $sIndex }}.columns.{{ $cIndex }}.key">
									</div>
									<div class="col-md-3">
										<label class="form-label small mb-0">Label</label>
										<input type="text" class="form-control form-control-sm" wire:model="sections.{{ $sIndex }}.columns.{{ $cIndex }}.label">
									</div>
									<div class="col-md-3">
										<label class="form-label small mb-0">Type</label>
										<select class="form-control form-control-sm" wire:model.live="sections.{{ $sIndex }}.columns.{{ $cIndex }}.type">
											<option value="text">text</option>
											<option value="reagent_input">reagent_input</option>
											<option value="step_select">step_select</option>
										</select>
									</div>
									<div class="col-md-2 d-flex align-items-end">
										<div class="form-check">
											<input type="checkbox" class="form-check-input" wire:model="sections.{{ $sIndex }}.columns.{{ $cIndex }}.shared" id="shared-{{ $sIndex }}-{{ $cIndex }}">
											<label class="form-check-label small" for="shared-{{ $sIndex }}-{{ $cIndex }}">Shared</label>
										</div>
									</div>
									<div class="col-md-1 d-flex align-items-end">
										<button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeColumn({{ $sIndex }}, {{ $cIndex }})"><i class="mdi mdi-delete"></i></button>
									</div>
								</div>

								@if(($column['type'] ?? '') === 'text' || ($column['type'] ?? '') === 'reagent_input')
									<div class="row g-2 mb-2">
										<div class="col-md-4">
											<label class="form-label small mb-0">Default value</label>
											<input type="text" class="form-control form-control-sm" wire:model="sections.{{ $sIndex }}.columns.{{ $cIndex }}.default_value">
										</div>
										@if(($column['type'] ?? '') === 'reagent_input')
											<div class="col-md-3">
												<label class="form-label small mb-0">Default UOM</label>
												<input type="text" class="form-control form-control-sm" wire:model="sections.{{ $sIndex }}.columns.{{ $cIndex }}.default_uom">
											</div>
											<div class="col-md-2 d-flex align-items-end">
												<div class="form-check">
													<input type="checkbox" class="form-check-input" wire:model="sections.{{ $sIndex }}.columns.{{ $cIndex }}.uom_selectable" id="uom-{{ $sIndex }}-{{ $cIndex }}">
													<label class="form-check-label small" for="uom-{{ $sIndex }}-{{ $cIndex }}">UOM selectable</label>
												</div>
											</div>
											<div class="col-md-3 d-flex align-items-end">
												<div class="form-check">
													<input type="checkbox" class="form-check-input" wire:model="sections.{{ $sIndex }}.columns.{{ $cIndex }}.multiply_by_sample_count" id="mult-{{ $sIndex }}-{{ $cIndex }}">
													<label class="form-check-label small" for="mult-{{ $sIndex }}-{{ $cIndex }}">× sample count</label>
												</div>
											</div>
										@endif
									</div>
									@if(count($section['rows']) > 0)
										<details class="mb-2">
											<summary class="small text-muted">Per-row defaults</summary>
											@foreach($section['rows'] as $row)
												<div class="input-group input-group-sm mb-1">
													<span class="input-group-text" style="min-width: 8rem;">{{ $row['label'] }}</span>
													<input type="text" class="form-control"
														wire:model="sections.{{ $sIndex }}.columns.{{ $cIndex }}.default_value_by_row.{{ $row['key'] }}"
														placeholder="default for {{ $row['key'] }}">
												</div>
											@endforeach
										</details>
									@endif
								@endif

								@if(($column['type'] ?? '') === 'reagent_input')
									<div class="mb-2">
										<label class="form-label small mb-1">Stock / media mapping</label>
										<div class="d-flex flex-wrap gap-2 align-items-center mb-1">
											@if(!empty($column['lab_sub_category_id']))
												<span class="badge bg-secondary">
													{{ $column['lab_sub_category_label'] ?? $column['lab_sub_category_id'] }}
													<button type="button" class="btn btn-link btn-sm p-0 text-white ms-1" wire:click="clearMedia({{ $sIndex }}, {{ $cIndex }})">&times;</button>
												</span>
											@else
												<button type="button" class="btn btn-sm btn-outline-secondary" wire:click="openMediaPicker({{ $sIndex }}, {{ $cIndex }})">
													Pick single media
												</button>
											@endif
										</div>
										<details>
											<summary class="small text-muted">Per-row media (overrides single)</summary>
											@foreach($section['rows'] as $row)
												<div class="d-flex align-items-center gap-2 mb-1">
													<span class="small" style="min-width: 8rem;">{{ $row['label'] }}</span>
													@php $rowMediaId = $column['lab_sub_category_id_by_row'][$row['key']] ?? null; @endphp
													@if($rowMediaId)
														<span class="badge bg-light text-dark border">{{ \Illuminate\Support\Str::limit($rowMediaId, 12) }}
															<button type="button" class="btn btn-link btn-sm p-0" wire:click="clearMedia({{ $sIndex }}, {{ $cIndex }}, '{{ $row['key'] }}')">&times;</button>
														</span>
													@else
														<button type="button" class="btn btn-xs btn-outline-secondary btn-sm"
															wire:click="openMediaPicker({{ $sIndex }}, {{ $cIndex }}, '{{ $row['key'] }}')">Pick</button>
													@endif
												</div>
											@endforeach
										</details>
										<small class="text-muted d-block">Deducted when the pipeline stage is completed.</small>
									</div>
								@endif

								@if(($column['type'] ?? '') === 'step_select')
									<div class="mb-1">
										<label class="form-label small">Step binding per row</label>
										@foreach($section['rows'] as $row)
											<div class="input-group input-group-sm mb-1">
												<span class="input-group-text" style="min-width: 8rem;">{{ $row['label'] }}</span>
												<select class="form-control"
													wire:model="sections.{{ $sIndex }}.columns.{{ $cIndex }}.step_key_by_row.{{ $row['key'] }}">
													<option value="">— select step —</option>
													@foreach($stepOptions as $opt)
														<option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
													@endforeach
												</select>
											</div>
										@endforeach
										@if(count($stepOptions) === 0)
											<small class="text-warning">Add steps on the Steps tab first.</small>
										@endif
									</div>
								@endif
							</div>
						@endforeach
					</div>
				@endif
			</div>
		@empty
			<div class="alert alert-info">No sections yet. Add a section to define the matrix.</div>
		@endforelse
	@endif

	<div class="mt-3">
		<button type="button" class="btn btn-primary" wire:click="saveLayout">
			<i class="mdi mdi-content-save"></i> Save layout
		</button>
	</div>

	@if($showMediaDropdown)
		<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.4);">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Select lab media / stock</h5>
						<button type="button" class="btn-close" wire:click="$set('showMediaDropdown', false)"></button>
					</div>
					<div class="modal-body">
						<input type="text" class="form-control mb-2" wire:model.live="mediaSearch" placeholder="Search media…">
						<div class="list-group" style="max-height: 280px; overflow-y: auto;">
							@forelse($mediaOptions as $opt)
								<button type="button" class="list-group-item list-group-item-action"
									wire:click="selectMedia('{{ $opt['id'] }}', @js($opt['name']))">
									{{ $opt['name'] }}
								</button>
							@empty
								<div class="text-muted small p-2">No media found.</div>
							@endforelse
						</div>
					</div>
				</div>
			</div>
		</div>
	@endif
</div>

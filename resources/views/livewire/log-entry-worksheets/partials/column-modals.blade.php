@if($showCreateColumnModal || $showEditColumnModal)
<div class="lec-column-modal-backdrop lec-column-modal" tabindex="-1" wire:keydown.escape="$set('showCreateColumnModal', false); $set('showEditColumnModal', false)">
	<div class="lec-column-modal-dialog">
		<div class="lec-column-modal-content">
			<div class="lec-column-modal-header">
				<div class="d-flex align-items-start gap-3">
					<div class="lec-column-modal-header__icon">
						<i class="mdi mdi-table-column"></i>
					</div>
					<div>
						<h5 class="lec-column-modal-title">{{ $showEditColumnModal ? 'Edit table column' : 'Add table column' }}</h5>
						<p class="lec-column-modal-subtitle">Define how this column appears and where its values come from at capture time.</p>
					</div>
				</div>
				<button type="button" class="lec-column-modal-close" wire:click="$set('showCreateColumnModal', false); $set('showEditColumnModal', false)" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="lec-column-modal-body">
				<div class="row g-4">
					<div class="col-lg-5">
						<section class="lec-form-section">
							<h6 class="lec-section-title">Column identity</h6>
							<div class="form-group mb-3">
								<label class="lec-form-label">Label <span class="text-danger">*</span></label>
								<input type="text" class="form-control" wire:model.live="columnLabel" placeholder="e.g. Lab No">
								@error('columnLabel') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
							</div>
							<div class="form-group mb-3">
								<label class="lec-form-label">Key (variable name) <span class="text-danger">*</span></label>
								<input type="text" class="form-control" wire:model="columnKey" placeholder="e.g. lab_no">
								@error('columnKey') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
								<span class="lec-field-hint">Used in expressions and stored values. Auto-generated from label if left empty.</span>
							</div>
							<div class="form-group mb-0">
								<label class="lec-form-label">Column type</label>
								<select class="form-control" wire:model.live="columnType">
									@foreach($columnTypeOptions as $value => $label)
										<option value="{{ $value }}">{{ $label }}</option>
									@endforeach
								</select>
							</div>
							@if($columnType === 'input')
							<div class="form-group mt-3 mb-0">
								<label class="lec-form-label">Input data type</label>
								<select class="form-control" wire:model="columnInputDataType">
									@foreach($inputDataTypeOptions as $value => $label)
										<option value="{{ $value }}">{{ $label }}</option>
									@endforeach
								</select>
							</div>
							@endif
						</section>

						<section class="lec-form-section">
							<h6 class="lec-section-title">Capture settings</h6>
							<div class="form-group mb-3">
								<label class="lec-form-label">Display order</label>
								<input type="number" class="form-control" wire:model="columnOrder" min="1">
							</div>
							<div class="form-group mb-3">
								<label class="lec-form-label">Help text</label>
								<input type="text" class="form-control" wire:model="columnHelpText" placeholder="Optional hint shown to analysts">
							</div>
							<label class="lec-checkbox-card">
								<input type="checkbox" wire:model="columnIsRequired">
								<span>Required at capture</span>
							</label>
						</section>
					</div>

					<div class="col-lg-7">
						@if($columnType === 'derived')
						<section class="lec-form-section">
							<h6 class="lec-section-title">Derived expression</h6>
							<div class="form-group mb-2">
								<label class="lec-form-label">Expression <span class="text-danger">*</span></label>
								<textarea class="form-control lec-expression-input" wire:model="columnExpression" rows="5" placeholder="e.g. {column_a} + {column_b}"></textarea>
								@error('columnExpression') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
							</div>
							<button type="button" class="btn btn-sm lec-btn-validate" wire:click="validateExpressionPreview">
								<i class="mdi mdi-check-circle-outline"></i> Validate expression
							</button>
							@if($expressionValidationMessage)
								<div class="lec-validation-msg {{ str_contains($expressionValidationMessage, 'valid') ? 'text-success' : 'text-danger' }}">
									{{ $expressionValidationMessage }}
								</div>
							@endif
						</section>
						@elseif($columnType === 'dataset')
						<div class="lec-dataset-panel">
							<div class="lec-dataset-panel__header">
								<span class="lec-dataset-panel__icon"><i class="mdi mdi-database-search"></i></span>
								<div>
									<h6 class="lec-dataset-panel__title">Data source</h6>
									<p class="lec-dataset-panel__desc">Resolve values from your database schema</p>
								</div>
							</div>
							<div class="lec-dataset-panel__body">
								<div class="form-group">
									<label class="lec-form-label">Source table <span class="text-danger">*</span></label>
									<div class="tag-select-container"
										wire:click="$set('showColumnDatasetSourceTableDropdown', true)"
										wire:click.outside="$set('showColumnDatasetSourceTableDropdown', false)">
										<div class="tag-select-input">
											@if($selectedColumnDatasetSourceTableLabel)
												<span class="tag-badge">
													{{ $selectedColumnDatasetSourceTableLabel }}
													<i class="mdi mdi-close-circle" wire:click.stop="clearColumnDatasetSourceTable"></i>
												</span>
											@else
												<input type="text"
													class="tag-input"
													wire:model.live.debounce.200ms="columnDatasetSourceTableSearch"
													placeholder="Search tables..."
													autocomplete="off">
											@endif
										</div>
										@if($showColumnDatasetSourceTableDropdown && count($filteredColumnSchemaTableOptions) > 0)
											<div class="tag-dropdown">
												@foreach($filteredColumnSchemaTableOptions as $opt)
													<div class="tag-dropdown-item" wire:click.stop="selectColumnDatasetSourceTable('{{ $opt['value'] }}')">
														{{ $opt['label'] }} <span class="text-muted">({{ $opt['value'] }})</span>
													</div>
												@endforeach
											</div>
										@elseif($showColumnDatasetSourceTableDropdown && $columnDatasetSourceTableSearch !== '' && count($filteredColumnSchemaTableOptions) === 0)
											<div class="tag-dropdown">
												<div class="tag-dropdown-item text-muted">No matching tables</div>
											</div>
										@endif
									</div>
									@error('columnDatasetSourceTable') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
									<span class="lec-field-hint">Row driver supplies the primary key for this table (e.g. sample_details when driver is sample detail).</span>
								</div>

								@if($columnDatasetSourceTable)
								<div class="form-group">
									<label class="lec-form-label">How to resolve the value</label>
									<select class="form-control" wire:model.live="columnDatasetDisplayMode">
										<option value="direct">Column on source table</option>
										<option value="foreign_key">Foreign key → related table column</option>
									</select>
								</div>
								@endif

								@if($columnDatasetSourceTable && $columnDatasetDisplayMode === 'direct')
								@include('livewire.log-entry-worksheets.partials.schema-option-tag-select', [
									'fieldLabel' => 'Display column',
									'required' => true,
									'class' => 'mb-0 lec-schema-tag-field',
									'selectedLabel' => $selectedColumnDatasetSourceColumnLabel,
									'showDropdown' => $showColumnDatasetSourceColumnDropdown,
									'filteredOptions' => $filteredColumnSourceColumnOptions,
									'emptySearch' => $columnDatasetSourceColumnSearch,
									'placeholder' => 'Search columns...',
									'openDropdownAction' => '$set(\'showColumnDatasetSourceColumnDropdown\', true)',
									'closeDropdownAction' => '$set(\'showColumnDatasetSourceColumnDropdown\', false)',
									'selectMethod' => 'selectColumnDatasetSourceColumn',
									'clearMethod' => 'clearColumnDatasetSourceColumn',
									'searchModel' => 'columnDatasetSourceColumnSearch',
									'errorKey' => 'columnDatasetSourceColumn',
								])
								@endif

								@if($columnDatasetSourceTable && $columnDatasetDisplayMode === 'foreign_key')
								@include('livewire.log-entry-worksheets.partials.schema-option-tag-select', [
									'fieldLabel' => 'Foreign key column',
									'required' => true,
									'class' => 'lec-schema-tag-field',
									'selectedLabel' => $selectedColumnDatasetFkLabel,
									'showDropdown' => $showColumnDatasetFkDropdown,
									'filteredOptions' => $filteredColumnForeignKeyOptions,
									'emptySearch' => $columnDatasetFkSearch,
									'placeholder' => 'Search foreign keys...',
									'openDropdownAction' => '$set(\'showColumnDatasetFkDropdown\', true)',
									'closeDropdownAction' => '$set(\'showColumnDatasetFkDropdown\', false)',
									'selectMethod' => 'selectColumnDatasetFkColumn',
									'clearMethod' => 'clearColumnDatasetFkColumn',
									'searchModel' => 'columnDatasetFkSearch',
									'errorKey' => 'columnDatasetFkColumn',
								])
								@if($columnDatasetReferencedTable)
								<div class="form-group">
									<label class="lec-form-label">Referenced table</label>
									<input type="text" class="form-control" readonly value="{{ $columnDatasetReferencedTable }}">
								</div>
								@include('livewire.log-entry-worksheets.partials.schema-option-tag-select', [
									'fieldLabel' => 'Value to display',
									'required' => true,
									'class' => 'mb-0 lec-schema-tag-field',
									'selectedLabel' => $selectedColumnDatasetReferencedDisplayLabel,
									'showDropdown' => $showColumnDatasetReferencedDisplayDropdown,
									'filteredOptions' => $filteredColumnReferencedDisplayOptions,
									'emptySearch' => $columnDatasetReferencedDisplayColumnSearch,
									'placeholder' => 'Search columns...',
									'openDropdownAction' => '$set(\'showColumnDatasetReferencedDisplayDropdown\', true)',
									'closeDropdownAction' => '$set(\'showColumnDatasetReferencedDisplayDropdown\', false)',
									'selectMethod' => 'selectColumnDatasetReferencedDisplayColumn',
									'clearMethod' => 'clearColumnDatasetReferencedDisplayColumn',
									'searchModel' => 'columnDatasetReferencedDisplayColumnSearch',
									'errorKey' => 'columnDatasetReferencedDisplayColumn',
									'help' => 'e.g. sample_details.sample_header_id → sample_headers.batch_code',
								])
								@endif
								@endif
							</div>
						</div>
						@else
						<div class="lec-type-placeholder">
							<i class="mdi mdi-form-textbox"></i>
							<p class="mb-0"><strong>Manual input column</strong><br>Analysts enter values directly when capturing worksheet rows.</p>
						</div>
						@endif
					</div>
				</div>
			</div>

			<div class="lec-column-modal-footer">
				<button type="button" class="btn lec-btn-ghost" wire:click="$set('showCreateColumnModal', false); $set('showEditColumnModal', false)">Cancel</button>
				@if($showEditColumnModal)
					<button type="button" class="btn lec-btn-primary" wire:click="updateColumn">
						<i class="mdi mdi-content-save-outline"></i> Update column
					</button>
				@else
					<button type="button" class="btn lec-btn-primary" wire:click="createColumn">
						<i class="mdi mdi-plus-circle-outline"></i> Create column
					</button>
				@endif
			</div>
		</div>
	</div>
</div>
@endif

@if($showDeleteColumnModal)
<div class="lec-column-modal-backdrop lec-column-modal" tabindex="-1">
	<div class="lec-column-modal-dialog" style="max-width: 420px;">
		<div class="lec-column-modal-content">
			<div class="lec-column-modal-header">
				<div class="d-flex align-items-center gap-3">
					<div class="lec-column-modal-header__icon" style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);">
						<i class="mdi mdi-delete-outline"></i>
					</div>
					<div>
						<h5 class="lec-column-modal-title">Delete column</h5>
						<p class="lec-column-modal-subtitle mb-0">This cannot be undone.</p>
					</div>
				</div>
				<button type="button" class="lec-column-modal-close" wire:click="$set('showDeleteColumnModal', false)" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="lec-column-modal-body py-4">
				<p class="mb-0 text-secondary">Remove column <strong class="text-dark">{{ $deletingColumn?->label }}</strong> from this worksheet?</p>
			</div>
			<div class="lec-column-modal-footer">
				<button type="button" class="btn lec-btn-ghost" wire:click="$set('showDeleteColumnModal', false)">Cancel</button>
				<button type="button" class="btn lec-btn-primary" style="background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);" wire:click="deleteColumn">
					<i class="mdi mdi-delete"></i> Delete
				</button>
			</div>
		</div>
	</div>
</div>
@endif

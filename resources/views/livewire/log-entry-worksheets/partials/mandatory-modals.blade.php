@php
	$presetLookupTypes = \App\Models\LogEntryWorksheets\LogEntryWorksheetMandatoryField::presetLookupTypes();
	$presetIcons = [
		'equipments' => 'mdi-flask-outline',
		'users' => 'mdi-account-outline',
		'methods' => 'mdi-beaker-outline',
		'sample_types' => 'mdi-test-tube',
		'analytes' => 'mdi-molecule',
	];
	$isPresetLookup = \App\Models\LogEntryWorksheets\LogEntryWorksheetMandatoryField::isPresetLookupType($fieldType);
@endphp

@if($showCreateFieldModal || $showEditFieldModal)
<div class="lec-column-modal-backdrop lec-column-modal lec-mandatory-modal" tabindex="-1"
	wire:keydown.escape="$set('showCreateFieldModal', false); $set('showEditFieldModal', false)">
	<div class="lec-column-modal-dialog">
		<div class="lec-column-modal-content">
			<div class="lec-column-modal-header">
				<div class="d-flex align-items-start gap-3">
					<div class="lec-column-modal-header__icon">
						<i class="mdi mdi-form-select"></i>
					</div>
					<div>
						<h5 class="lec-column-modal-title">{{ $showEditFieldModal ? 'Edit' : 'Add' }} mandatory field</h5>
						<p class="lec-column-modal-subtitle">Fields shown once per worksheet instance, above or below the data table during batch capture.</p>
					</div>
				</div>
				<button type="button" class="lec-column-modal-close"
					wire:click="$set('showCreateFieldModal', false); $set('showEditFieldModal', false)"
					aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="lec-column-modal-body">
				<div class="row g-4 lec-mandatory-modal__top">
					<div class="col-lg-6">
						<section class="lec-form-section h-100">
							<h6 class="lec-section-title">Field identity</h6>
							<div class="form-group mb-3">
								<label class="lec-form-label">Label <span class="text-danger">*</span></label>
								<input type="text" class="form-control" wire:model="fieldLabel" placeholder="e.g. Equipment used">
								@error('fieldLabel') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
							</div>
							<div class="form-group mb-3">
								<label class="lec-form-label">Field value name <span class="text-danger">*</span></label>
								<input type="text" class="form-control" wire:model="fieldValueName" placeholder="e.g. equipment_id">
								@error('fieldValueName') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
								<span class="lec-field-hint">Key used when storing captured values.</span>
							</div>
							<div class="form-group mb-0">
								<label class="lec-form-label">Field type</label>
								<select class="form-control" wire:model.live="fieldType">
									@foreach($mandatoryFieldTypes as $value => $label)
										<option value="{{ $value }}">{{ $label }}</option>
									@endforeach
								</select>
							</div>
						</section>
					</div>

					<div class="col-lg-6">
						<section class="lec-form-section h-100">
							<h6 class="lec-section-title">Capture settings</h6>
							@if($showFieldDefaultCurrentDate)
							<label class="lec-checkbox-card mb-2">
								<input type="checkbox" wire:model="fieldDefaultCurrentDate">
								<span>Default to current date</span>
							</label>
							@endif
							@if($showFieldDefaultAuthenticatedUser)
							<label class="lec-checkbox-card mb-3">
								<input type="checkbox" wire:model="fieldDefaultAuthenticatedUser">
								<span>Default to signed-in user</span>
							</label>
							@endif
							<div class="form-group mb-3">
								<label class="lec-form-label">Display order</label>
								<input type="number" class="form-control" wire:model="fieldOrder" min="1">
							</div>
							<div class="form-group mb-3">
								<label class="lec-form-label">Help text</label>
								<input type="text" class="form-control" wire:model="fieldHelpText" placeholder="Optional hint for analysts">
							</div>
							<label class="lec-checkbox-card">
								<input type="checkbox" wire:model="fieldIsRequired">
								<span>Required at capture</span>
							</label>
						</section>
					</div>
				</div>

				@if(in_array($fieldType, ['checkbox', 'radio'], true))
				<section class="lec-form-section lec-mandatory-modal__full lec-mandatory-modal__choices">
					<div class="lec-choices-panel">
						<div class="lec-choices-panel__header">
							<div class="lec-choices-panel__title-wrap">
								<div class="lec-choices-panel__icon">
									<i class="mdi {{ $fieldType === 'radio' ? 'mdi-radiobox-marked' : 'mdi-checkbox-marked-outline' }}"></i>
								</div>
								<div>
									<h6 class="lec-choices-panel__title mb-0">Answer choices</h6>
									<p class="lec-choices-panel__desc mb-0">Add options one at a time. Analysts will see these when capturing the worksheet.</p>
								</div>
							</div>
							<span class="lec-choices-panel__count">{{ count($fieldChoiceOptions) }} {{ count($fieldChoiceOptions) === 1 ? 'choice' : 'choices' }}</span>
						</div>

						<div class="lec-choices-add">
							<div class="lec-choices-add__input-wrap">
								<span class="lec-choices-add__prefix" aria-hidden="true">
									<i class="mdi mdi-plus-circle-outline"></i>
								</span>
								<input type="text"
									class="form-control lec-choices-add__input"
									wire:model="fieldNewChoice"
									wire:keydown.enter.prevent="addFieldChoice"
									placeholder="Type a choice and press Enter or Add…"
									aria-label="New choice">
							</div>
							<button type="button" class="lec-choices-add__btn" wire:click="addFieldChoice">
								<i class="mdi mdi-plus"></i> Add
							</button>
						</div>
						@error('fieldNewChoice') <small class="text-danger d-block lec-choices-error">{{ $message }}</small> @enderror
						@error('fieldChoiceOptions') <small class="text-danger d-block lec-choices-error">{{ $message }}</small> @enderror

						@if(count($fieldChoiceOptions) > 0)
						<ul class="lec-choices-list" role="list">
							@foreach($fieldChoiceOptions as $index => $choice)
								<li class="lec-choices-list__item" wire:key="field-choice-{{ $index }}">
									<span class="lec-choices-list__order" aria-hidden="true">{{ $index + 1 }}</span>
									<input type="text"
										class="form-control lec-choices-list__input"
										wire:model.blur="fieldChoiceOptions.{{ $index }}"
										placeholder="Choice label"
										aria-label="Choice {{ $index + 1 }}">
									<button type="button"
										class="lec-choices-list__remove"
										wire:click="removeFieldChoice({{ $index }})"
										title="Remove choice"
										aria-label="Remove choice {{ $index + 1 }}">
										<i class="mdi mdi-close"></i>
									</button>
								</li>
							@endforeach
						</ul>
						@else
						<div class="lec-choices-empty">
							<i class="mdi mdi-format-list-checkbox"></i>
							<p class="mb-0">No choices yet. Add your first option above.</p>
						</div>
						@endif
					</div>
				</section>
				@endif

				@if($isPresetLookup)
				<section class="lec-form-section lec-mandatory-modal__full">
					<div class="lec-dataset-panel lec-preset-lookup-panel mb-0">
						<div class="lec-dataset-panel__header">
							<div class="lec-dataset-panel__icon">
								<i class="mdi {{ $presetIcons[$fieldType] ?? 'mdi-format-list-bulleted' }}"></i>
							</div>
							<div>
								<div class="lec-dataset-panel__title">{{ $presetLookupTypes[$fieldType] ?? 'List' }} picker</div>
								<div class="lec-dataset-panel__desc">
									{{ \App\Models\LogEntryWorksheets\LogEntryWorksheetMandatoryField::presetLookupDescription($fieldType) }}
								</div>
							</div>
						</div>
						<div class="lec-dataset-panel__body">
							<div class="lec-preset-lookup-preview">
								<i class="mdi mdi-menu-down"></i>
								<span>Analysts will choose from the live {{ strtolower($presetLookupTypes[$fieldType] ?? 'list') }} list at capture time.</span>
							</div>
						</div>
					</div>
				</section>
				@elseif(in_array($fieldType, ['date', 'datetime'], true))
				<section class="lec-form-section lec-mandatory-modal__full">
					<div class="lec-type-placeholder mb-0">
						<i class="mdi mdi-calendar-clock"></i>
						<p class="mb-0">Analysts enter a {{ $fieldType === 'date' ? 'date' : 'date and time' }} for this worksheet.</p>
					</div>
				</section>
				@elseif($fieldType === 'input')
				<section class="lec-form-section lec-mandatory-modal__full">
					<div class="lec-type-placeholder mb-0">
						<i class="mdi mdi-form-textbox"></i>
						<p class="mb-0">Analysts enter free text when capturing this worksheet.</p>
					</div>
				</section>
				@endif

				@if($fieldType === 'dataset_related')
				<section class="lec-form-section lec-mandatory-modal__full lec-mandatory-modal__dataset">
					<div class="lec-dataset-panel mb-0">
						<div class="lec-dataset-panel__header">
							<div class="lec-dataset-panel__icon">
								<i class="mdi mdi-database-search"></i>
							</div>
							<div>
								<div class="lec-dataset-panel__title">Custom dataset source</div>
								<div class="lec-dataset-panel__desc">Resolve options from any table or related record.</div>
							</div>
						</div>
						<div class="lec-dataset-panel__body">
							<div class="row g-3">
								<div class="col-md-6 col-lg-4">
									<div class="form-group mb-0">
										<label class="lec-form-label">Source table <span class="text-danger">*</span></label>
										<select class="form-control" wire:model.live="fieldDatasetSourceTable">
											<option value="">— Select table —</option>
											@foreach($schemaTableOptions as $opt)
												<option value="{{ $opt['value'] }}">{{ $opt['label'] }} ({{ $opt['value'] }})</option>
											@endforeach
										</select>
										@error('fieldDatasetSourceTable') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
									</div>
								</div>
								@if($fieldDatasetSourceTable)
								<div class="col-md-6 col-lg-4">
									<div class="form-group mb-0">
										<label class="lec-form-label">How to resolve the value</label>
										<select class="form-control" wire:model.live="fieldDatasetDisplayMode">
											<option value="direct">Column on source table</option>
											<option value="foreign_key">Foreign key → related table column</option>
										</select>
									</div>
								</div>
								@endif
								@if($fieldDatasetSourceTable && $fieldDatasetDisplayMode === 'direct')
								<div class="col-md-6 col-lg-4">
									<div class="form-group mb-0">
										<label class="lec-form-label">Column</label>
										<select class="form-control" wire:model="fieldDatasetSourceColumn">
											<option value="">— Select —</option>
											@foreach($fieldSourceColumns as $col)
												<option value="{{ $col['value'] }}">{{ $col['label'] }}</option>
											@endforeach
										</select>
									</div>
								</div>
								@endif
								@if($fieldDatasetSourceTable && $fieldDatasetDisplayMode === 'foreign_key')
								<div class="col-md-6 col-lg-4">
									<div class="form-group mb-0">
										<label class="lec-form-label">Foreign key column</label>
										<select class="form-control" wire:model.live="fieldDatasetFkColumn">
											<option value="">— Select FK —</option>
											@foreach($fieldForeignKeys as $fk)
												<option value="{{ $fk['column'] }}">{{ $fk['label'] }}</option>
											@endforeach
										</select>
									</div>
								</div>
								@if($fieldDatasetReferencedTable)
								<div class="col-md-6 col-lg-4">
									<div class="form-group mb-0">
										<label class="lec-form-label">Display column on {{ $fieldDatasetReferencedTable }}</label>
										<select class="form-control" wire:model="fieldDatasetReferencedDisplayColumn">
											<option value="">— Select —</option>
											@foreach($fieldReferencedColumns as $col)
												<option value="{{ $col['value'] }}">{{ $col['label'] }}</option>
											@endforeach
										</select>
									</div>
								</div>
								@endif
								@endif
							</div>
						</div>
					</div>
				</section>
				@endif
			</div>

			<div class="lec-column-modal-footer">
				<button type="button" class="lec-btn-ghost" wire:click="$set('showCreateFieldModal', false); $set('showEditFieldModal', false)">Cancel</button>
				@if($showEditFieldModal)
					<button type="button" class="lec-btn-primary" wire:click="updateMandatoryField">
						<i class="mdi mdi-content-save-outline"></i> Save changes
					</button>
				@else
					<button type="button" class="lec-btn-primary" wire:click="createMandatoryField">
						<i class="mdi mdi-plus"></i> Create field
					</button>
				@endif
			</div>
		</div>
	</div>
</div>
@endif

@if($showDeleteFieldModal)
<div class="lec-column-modal-backdrop lec-column-modal lec-mandatory-modal" tabindex="-1">
	<div class="lec-column-modal-dialog" style="max-width: 420px;">
		<div class="lec-column-modal-content">
			<div class="lec-column-modal-header">
				<div class="d-flex align-items-start gap-3">
					<div class="lec-column-modal-header__icon lec-column-modal-header__icon--danger">
						<i class="mdi mdi-delete-outline"></i>
					</div>
					<div>
						<h5 class="lec-column-modal-title mb-1">Delete mandatory field?</h5>
						<p class="lec-column-modal-subtitle mb-0">Remove <strong>{{ $deletingField?->label }}</strong> from this worksheet.</p>
					</div>
				</div>
				<button type="button" class="lec-column-modal-close" wire:click="$set('showDeleteFieldModal', false)" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="lec-column-modal-footer">
				<button type="button" class="lec-btn-ghost" wire:click="$set('showDeleteFieldModal', false)">Cancel</button>
				<button type="button" class="lec-btn-primary lec-btn-primary--danger" wire:click="deleteMandatoryField">Delete</button>
			</div>
		</div>
	</div>
</div>
@endif

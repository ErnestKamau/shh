@if($showCreateFieldModal || $showEditFieldModal)
<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" style="border-radius: 10px;">
			<div class="modal-header">
				<h5 class="modal-title">{{ $showEditFieldModal ? 'Edit' : 'Add' }} mandatory field</h5>
				<button type="button" class="close" wire:click="$set('showCreateFieldModal', false); $set('showEditFieldModal', false)"><span>&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label>Label <span class="text-danger">*</span></label>
							<input type="text" class="form-control" wire:model="fieldLabel">
							@error('fieldLabel') <small class="text-danger">{{ $message }}</small> @enderror
						</div>
						<div class="form-group">
							<label>Field value name <span class="text-danger">*</span></label>
							<input type="text" class="form-control" wire:model="fieldValueName" placeholder="e.g. equipment_id">
							@error('fieldValueName') <small class="text-danger">{{ $message }}</small> @enderror
						</div>
						<div class="form-group">
							<label>Field type</label>
							<select class="form-control" wire:model.live="fieldType">
								@foreach($mandatoryFieldTypes as $value => $label)
									<option value="{{ $value }}">{{ $label }}</option>
								@endforeach
							</select>
						</div>
						@if(in_array($fieldType, ['checkbox', 'radio'], true))
						<div class="form-group">
							<label>Options <span class="text-danger">*</span> <small class="text-muted">(one per line)</small></label>
							<textarea class="form-control" wire:model="fieldChoiceOptions" rows="4" placeholder="Option A&#10;Option B"></textarea>
							@error('fieldChoiceOptions') <small class="text-danger">{{ $message }}</small> @enderror
						</div>
						@endif
					</div>
					<div class="col-md-6">
						@if($fieldType === 'dataset_related')
						<div class="card bg-light border-0 mb-3" style="border-radius: 8px;">
							<div class="card-body py-3">
								<h6 class="text-primary mb-3"><i class="mdi mdi-database"></i> Dataset source</h6>
								<div class="form-group">
									<label>Source table <span class="text-danger">*</span></label>
									<select class="form-control" wire:model.live="fieldDatasetSourceTable">
										<option value="">— Select table —</option>
										@foreach($schemaTableOptions as $opt)
											<option value="{{ $opt['value'] }}">{{ $opt['label'] }} ({{ $opt['value'] }})</option>
										@endforeach
									</select>
								</div>
								@if($fieldDatasetSourceTable)
								<div class="form-group">
									<label>How to resolve the value</label>
									<select class="form-control" wire:model.live="fieldDatasetDisplayMode">
										<option value="direct">Column on source table</option>
										<option value="foreign_key">Foreign key → related table column</option>
									</select>
								</div>
								@endif
								@if($fieldDatasetSourceTable && $fieldDatasetDisplayMode === 'direct')
								<div class="form-group">
									<label>Column</label>
									<select class="form-control" wire:model="fieldDatasetSourceColumn">
										<option value="">— Select —</option>
										@foreach($fieldSourceColumns as $col)
											<option value="{{ $col['value'] }}">{{ $col['label'] }}</option>
										@endforeach
									</select>
								</div>
								@endif
								@if($fieldDatasetSourceTable && $fieldDatasetDisplayMode === 'foreign_key')
								<div class="form-group">
									<label>Foreign key column</label>
									<select class="form-control" wire:model.live="fieldDatasetFkColumn">
										<option value="">— Select FK —</option>
										@foreach($fieldForeignKeys as $fk)
											<option value="{{ $fk['column'] }}">{{ $fk['label'] }}</option>
										@endforeach
									</select>
								</div>
								@if($fieldDatasetReferencedTable)
								<div class="form-group mb-0">
									<label>Display column on {{ $fieldDatasetReferencedTable }}</label>
									<select class="form-control" wire:model="fieldDatasetReferencedDisplayColumn">
										<option value="">— Select —</option>
										@foreach($fieldReferencedColumns as $col)
											<option value="{{ $col['value'] }}">{{ $col['label'] }}</option>
										@endforeach
									</select>
								</div>
								@endif
								@endif
							</div>
						</div>
						@if($showFieldDefaultCurrentDate)
						<div class="custom-control custom-checkbox mb-2">
							<input type="checkbox" class="custom-control-input" id="mf-default-date" wire:model="fieldDefaultCurrentDate">
							<label class="custom-control-label" for="mf-default-date">By default, pick current date</label>
						</div>
						@endif
						@if($showFieldDefaultAuthenticatedUser)
						<div class="custom-control custom-checkbox mb-2">
							<input type="checkbox" class="custom-control-input" id="mf-default-user" wire:model="fieldDefaultAuthenticatedUser">
							<label class="custom-control-label" for="mf-default-user">By default, pick authenticated user</label>
						</div>
						@endif
						@endif
						<div class="form-group">
							<label>Order</label>
							<input type="number" class="form-control" wire:model="fieldOrder" min="1">
						</div>
						<div class="form-group">
							<label>Help text</label>
							<input type="text" class="form-control" wire:model="fieldHelpText">
						</div>
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" id="mf-req" wire:model="fieldIsRequired">
							<label class="custom-control-label" for="mf-req">Required</label>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary log-entry-btn-outline" wire:click="$set('showCreateFieldModal', false); $set('showEditFieldModal', false)">Cancel</button>
				@if($showEditFieldModal)
					<button type="button" class="btn btn-primary log-entry-btn-outline" wire:click="updateMandatoryField">Update</button>
				@else
					<button type="button" class="btn btn-primary log-entry-btn-outline" wire:click="createMandatoryField">Create</button>
				@endif
			</div>
		</div>
	</div>
</div>
@endif

@if($showDeleteFieldModal)
<div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
	<div class="modal-dialog modal-sm">
		<div class="modal-content" style="border-radius: 10px;">
			<div class="modal-body">Delete mandatory field <strong>{{ $deletingField?->label }}</strong>?</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary log-entry-btn-outline" wire:click="$set('showDeleteFieldModal', false)">Cancel</button>
				<button type="button" class="btn btn-outline-danger log-entry-btn-outline" wire:click="deleteMandatoryField">Delete</button>
			</div>
		</div>
	</div>
</div>
@endif

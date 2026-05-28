<div class="card border-0 shadow-sm mb-3" style="border-radius: 12px;">
	<div class="card-header bg-white border-0">
		<h6 class="mb-0"><i class="mdi mdi-form-select"></i> {{ $title }}</h6>
	</div>
	<div class="card-body">
		<div class="row">
			@foreach($mandatoryFields as $field)
				<div class="col-md-4 mb-3">
					<label class="font-weight-bold d-block">
						{{ $field->label }}
						@if($field->is_required)<span class="text-danger">*</span>@endif
					</label>

					@if($field->field_type === 'checkbox')
						@php $options = $field->field_options['options'] ?? []; @endphp
						@foreach($options as $option)
							<div class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input"
									id="mf-chk-{{ $field->id }}-{{ $loop->index }}"
									@if($this->isMandatoryCheckboxSelected($field->id, $option)) checked @endif
									wire:click="toggleMandatoryCheckboxOption('{{ $field->id }}', '{{ str_replace("'", "\\'", $option) }}')">
								<label class="custom-control-label" for="mf-chk-{{ $field->id }}-{{ $loop->index }}">{{ $option }}</label>
							</div>
						@endforeach
					@elseif($field->field_type === 'radio')
						@php $options = $field->field_options['options'] ?? []; @endphp
						@foreach($options as $option)
							<div class="custom-control custom-radio">
								<input type="radio" class="custom-control-input"
									name="mf-radio-{{ $field->id }}"
									id="mf-radio-{{ $field->id }}-{{ $loop->index }}"
									value="{{ $option }}"
									wire:model.lazy="sharedMandatoryData.{{ $field->id }}"
									wire:change="autoSaveMandatoryField('{{ $field->id }}')">
								<label class="custom-control-label" for="mf-radio-{{ $field->id }}-{{ $loop->index }}">{{ $option }}</label>
							</div>
						@endforeach
					@elseif($field->field_type === 'datetime')
						<input type="datetime-local" class="form-control form-control-sm"
							wire:model.lazy="sharedMandatoryData.{{ $field->id }}"
							wire:change="autoSaveMandatoryField('{{ $field->id }}')">
					@elseif($field->field_type === 'date')
						<input type="date" class="form-control form-control-sm"
							wire:model.lazy="sharedMandatoryData.{{ $field->id }}"
							wire:change="autoSaveMandatoryField('{{ $field->id }}')">
					@elseif($this->mandatoryFieldUsesSelectList($field))
						<select class="form-control form-control-sm"
							wire:model.lazy="sharedMandatoryData.{{ $field->id }}"
							wire:change="autoSaveMandatoryField('{{ $field->id }}')">
							<option value="">— Select —</option>
							@foreach($this->getMandatoryDatasetOptions($field) as $opt)
								<option value="{{ $opt->id }}">{{ $opt->label }}</option>
							@endforeach
						</select>
					@else
						<input type="text" class="form-control form-control-sm"
							wire:model.lazy="sharedMandatoryData.{{ $field->id }}"
							wire:blur="autoSaveMandatoryField('{{ $field->id }}')">
					@endif

					@if($field->help_text)
						<small class="text-muted d-block">{{ $field->help_text }}</small>
					@endif
					@error('sharedMandatoryData.'.$field->id)
						<small class="text-danger d-block">{{ $message }}</small>
					@enderror
				</div>
			@endforeach
		</div>
	</div>
</div>

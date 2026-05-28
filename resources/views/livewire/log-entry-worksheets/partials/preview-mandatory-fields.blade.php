<div class="card border-0 shadow-sm mb-3 log-entry-preview__mandatory-card" style="border-radius: 12px;">
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
						<small class="text-muted font-weight-normal">({{ $mandatoryFieldTypes[$field->field_type] ?? $field->field_type }})</small>
					</label>

					@if($field->field_type === 'checkbox')
						@php $options = $field->field_options['options'] ?? []; @endphp
						@forelse($options as $option)
							<div class="custom-control custom-checkbox">
								<input type="checkbox" class="custom-control-input log-entry-preview-field--disabled" disabled id="preview-chk-{{ $field->id }}-{{ $loop->index }}">
								<label class="custom-control-label" for="preview-chk-{{ $field->id }}-{{ $loop->index }}">{{ $option }}</label>
							</div>
						@empty
							<small class="text-muted">No choices configured</small>
						@endforelse
					@elseif($field->field_type === 'radio')
						@php $options = $field->field_options['options'] ?? []; @endphp
						<div class="log-entry-preview__choice-group log-entry-preview__choice-group--inline">
							@forelse($options as $option)
								<div class="custom-control custom-radio custom-control-inline">
									<input type="radio" class="custom-control-input log-entry-preview-field--disabled" disabled
										name="preview-radio-{{ $field->id }}"
										id="preview-radio-{{ $field->id }}-{{ $loop->index }}">
									<label class="custom-control-label" for="preview-radio-{{ $field->id }}-{{ $loop->index }}">{{ $option }}</label>
								</div>
							@empty
								<small class="text-muted">No choices configured</small>
							@endforelse
						</div>
					@elseif($field->field_type === 'datetime')
						<input type="datetime-local" class="form-control form-control-sm log-entry-preview-field--disabled" disabled placeholder="Date & time">
					@elseif($field->field_type === 'date')
						<input type="date" class="form-control form-control-sm log-entry-preview-field--disabled" disabled>
					@elseif($this->mandatoryFieldUsesSelectList($field))
						@php
							$options = $this->getMandatoryFieldOptions($field);
							$batchScoped = in_array($field->field_type, ['sample_details', 'captured_results'], true)
								|| in_array($field->model_tied_to ?? '', ['sample_details', 'captured_results'], true);
						@endphp
						<select class="form-control form-control-sm log-entry-preview-field--disabled" disabled>
							<option value="">— Select —</option>
							@foreach($options as $opt)
								<option value="{{ $opt->id }}">{{ $opt->label }}</option>
							@endforeach
						</select>
						@if($batchScoped)
							<small class="text-muted d-block mt-1">Options depend on the batch at capture.</small>
						@elseif($options->isEmpty())
							<small class="text-muted d-block mt-1">No list values available in preview.</small>
						@endif
					@else
						<input type="text" class="form-control form-control-sm log-entry-preview-field--disabled" disabled placeholder="Text value">
					@endif

					@if($field->help_text)
						<small class="text-muted d-block mt-1">{{ $field->help_text }}</small>
					@endif
				</div>
			@endforeach
		</div>
	</div>
</div>

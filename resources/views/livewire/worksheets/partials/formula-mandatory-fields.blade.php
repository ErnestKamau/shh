<div class="card border-0 shadow-sm mb-4 fm-mandatory-card">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="mb-0">
            <i class="mdi mdi-form-select text-primary"></i>
            {{ $title ?? 'Mandatory Fields (Applies to All Samples)' }}
        </h6>
    </div>
    <div class="card-body pt-0">
        <div class="row">
            @foreach($mandatoryFields as $field)
                <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold mb-1">
                        {{ $field->label }}
                        @if($field->is_required)
                            <span class="text-danger">*</span>
                        @endif
                    </label>
                    @if($field->help_text)
                        <small class="text-muted d-block mb-2">{{ $field->help_text }}</small>
                    @endif

                    @if($field->field_type === 'checkbox')
                        @foreach($field->checkboxOptionLabels() as $option)
                            <div class="custom-control custom-checkbox mb-1">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="fmf-chk-{{ $field->id }}-{{ $loop->index }}"
                                       @if($this->isMandatoryCheckboxSelected($field->id, $option)) checked @endif
                                       wire:click="toggleMandatoryCheckboxOption('{{ $field->id }}', '{{ str_replace("'", "\\'", $option) }}')">
                                <label class="custom-control-label" for="fmf-chk-{{ $field->id }}-{{ $loop->index }}">{{ $option }}</label>
                            </div>
                        @endforeach
                    @elseif($field->field_type === 'datetime')
                        <input type="datetime-local"
                               class="form-control form-control-sm"
                               wire:model="sharedMandatoryData.{{ $field->id }}"
                               wire:blur="autoSaveMandatoryField('{{ $field->id }}')">
                    @elseif($field->field_type === 'date')
                        <input type="date"
                               class="form-control form-control-sm"
                               wire:model="sharedMandatoryData.{{ $field->id }}"
                               wire:blur="autoSaveMandatoryField('{{ $field->id }}')">
                    @elseif($field->field_type === 'dataset_related')
                        <select class="form-control form-control-sm no-select2"
                                wire:key="mandatory-dataset-{{ $field->id }}"
                                wire:model.defer="sharedMandatoryData.{{ $field->id }}"
                                wire:change="autoSaveMandatoryField('{{ $field->id }}')">
                            <option value="">Select...</option>
                            @foreach($this->getDatasetOptions($field->model_tied_to) as $option)
                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text"
                               class="form-control form-control-sm"
                               wire:model="sharedMandatoryData.{{ $field->id }}"
                               wire:blur="autoSaveMandatoryField('{{ $field->id }}')"
                               placeholder="{{ $field->label }}">
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

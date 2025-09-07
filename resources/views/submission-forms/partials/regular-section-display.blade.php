@if($section->elementHolders->count() > 0)
    <div class="row">
        @foreach($section->elementHolders as $holder)
            <div class="col-md-{{ $holder->max_elements > 1 ? '12' : '6' }} mb-3">
                <div class="card border-light">
                    <div class="card-body">
                        @if($holder->holder_type === 'text')
                            <div class="text-content">
                                <h6>{{ $holder->title ?? 'Text Content' }}</h6>
                                <p class="text-muted">{{ $holder->description ?? '' }}</p>
                            </div>
                        @else
                            @if($holder->elements->count() > 0)
                                <div class="elements-container">
                                    @foreach($holder->elements as $element)
                                        <div class="field-display">
                                            <div class="field-label">
                                                {{ $element->label }}
                                                @if($element->is_required)
                                                    <span class="required-field">*</span>
                                                @endif
                                                @if($element->isMapped())
                                                    <span class="badge badge-outline-info badge-sm ml-2" 
                                                          title="Mapped to {{ ucfirst(str_replace('_', ' ', $element->mapping_table)) }}.{{ ucfirst(str_replace('_', ' ', $element->mapping_field)) }}">
                                                        <i class="mdi mdi-database"></i> mapped
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="field-value {{ $existingValues->get($element->id) ? '' : 'empty' }}">
                                                @if($existingValues->has($element->id))
                                                    @php
                                                        $value = $existingValues->get($element->id);
                                                        $displayValue = $value->getDisplayValue();
                                                    @endphp
                                                    
                                                    @if($element->element_type === 'file' && $value->file_path)
                                                        <a href="{{ Storage::url($value->file_path) }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="mdi mdi-download"></i> Download File
                                                        </a>
                                                        <small class="text-muted d-block mt-1">
                                                            {{ basename($value->file_path) }}
                                                        </small>
                                                    @else
                                                        {{ $displayValue }}
                                                    @endif
                                                @else
                                                    <span class="text-muted">No value provided</span>
                                                @endif
                                            </div>
                                            @if($element->help_text)
                                                <small class="form-text text-muted">{{ $element->help_text }}</small>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="mdi mdi-information"></i>
                                    No elements configured for this holder.
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-warning">
        <i class="mdi mdi-alert"></i>
        This section doesn't have any element holders configured yet.
    </div>
@endif

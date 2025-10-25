@if($section->elementHolders->count() > 0)
    @foreach($section->elementHolders as $holder)
        @if($holder->elements->count() > 0)
            <div class="rows-section-display">
                <h6 class="mb-3">
                    {{ $holder->title ?? 'Data Rows' }}
                    @if($holder->description)
                        <small class="text-muted d-block">{{ $holder->description }}</small>
                    @endif
                </h6>
                
                @php
                    // Group values by array index for rows section
                    $groupedValues = [];
                    foreach($holder->elements as $element) {
                        $elementValues = $existingValues->where('submission_form_element_id', $element->id);
                        foreach($elementValues as $value) {
                            $arrayIndex = $value->array_index ?? 0;
                            if (!isset($groupedValues[$arrayIndex])) {
                                $groupedValues[$arrayIndex] = [];
                            }
                            $groupedValues[$arrayIndex][$element->id] = $value;
                        }
                    }
                @endphp
                
                @if(count($groupedValues) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered rows-section-table">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    @foreach($holder->elements as $element)
                                        <th>
                                            {{ $element->label }}
                                            @if($element->is_required)
                                                <span class="required-field">*</span>
                                            @endif
                                            @if($element->isMapped())
                                                <span class="badge badge-outline-info badge-sm ml-1" 
                                                      title="Mapped to {{ ucfirst(str_replace('_', ' ', $element->mapping_table)) }}.{{ ucfirst(str_replace('_', ' ', $element->mapping_field)) }}">
                                                    <i class="mdi mdi-database"></i>
                                                </span>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedValues as $rowIndex => $rowValues)
                                    <tr>
                                        <td class="row-number">{{ $rowIndex + 1 }}</td>
                                        @foreach($holder->elements as $element)
                                            <td>
                                                @if(isset($rowValues[$element->id]))
                                                    @php
                                                        $value = $rowValues[$element->id];
                                                        $displayValue = $value->getDisplayValue();
                                                    @endphp
                                                    
                                                    @if($element->element_type === 'file' && $value->file_path)
                                                        <a href="{{ Storage::url($value->file_path) }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="mdi mdi-download"></i> Download
                                                        </a>
                                                        <small class="text-muted d-block mt-1">
                                                            {{ basename($value->file_path) }}
                                                        </small>
                                                    @else
                                                        <div class="field-value {{ $displayValue === '-' ? 'empty' : '' }}">
                                                            {{ $displayValue }}
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="field-value empty">
                                                        <span class="text-muted">No value provided</span>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i>
                        No data has been entered for this section yet.
                    </div>
                @endif
            </div>
        @else
            <div class="alert alert-info">
                <i class="mdi mdi-information"></i>
                No elements configured for this holder.
            </div>
        @endif
    @endforeach
@else
    <div class="alert alert-warning">
        <i class="mdi mdi-alert"></i>
        This section doesn't have any element holders configured yet.
    </div>
@endif

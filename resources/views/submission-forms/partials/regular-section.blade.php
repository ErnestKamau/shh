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
                                        <div class="form-group custom-element {{ $element->is_required ? 'required' : '' }}">
                                            @include('submission-forms.partials.form-element', [
                                                'element' => $element,
                                                'existingValues' => $existingValues,
                                                'isArrayField' => $isArrayField,
                                                'rowIndex' => null
                                            ])
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

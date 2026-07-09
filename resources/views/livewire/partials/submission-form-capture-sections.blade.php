@if($submissionForm)
    <div class="card bg-light border-0 mb-4 shadow-none rounded">
        <div class="card-body p-3">
            @foreach($submissionForm->sections->sortBy('sort_order') as $sectionIndex => $section)
                @php
                    $isCollapsible = ($section->section_type ?? 'regular') === 'regular';
                    $sectionTitle = strtoupper(trim((string) ($section->title ?? '')));
                @endphp
                <div
                    class="form-section mb-3"
                    wire:key="capture-section-{{ $section->id }}"
                    @if($isCollapsible) x-data="{ open: {{ $sectionIndex === 0 ? 'true' : 'false' }} }" @endif
                >
                    @if($isCollapsible)
                        <button
                            type="button"
                            class="form-section-title font-weight-bold text-dark border-bottom pb-2 mb-0 w-100 text-left bg-transparent border-0 d-flex align-items-center justify-content-between"
                            @click="open = !open; $nextTick(() => { if (typeof window.initSubmissionFormSignaturePads === 'function') window.initSubmissionFormSignaturePads(true); if (typeof window.initScheduleTrfSignaturePads === 'function') window.initScheduleTrfSignaturePads(true); if (typeof window.initScheduleTrfParameterSelects === 'function') window.initScheduleTrfParameterSelects(); })"
                        >
                            <span>{{ $section->title }}</span>
                            <i class="mdi" :class="open ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
                        </button>
                        <div class="pt-3" x-show="open" x-collapse>
                    @else
                        <h6 class="form-section-title font-weight-bold text-dark border-bottom pb-2 mb-3">
                            {{ $section->title }}
                        </h6>
                        <div>
                    @endif

                    @if(($section->section_type ?? '') === 'rows_section')
                        @php
                            $rowElements = $section->elementHolders->flatMap->elements->sortBy('sort_order');
                            $rowCount = 1;
                            foreach ($rowElements as $el) {
                                $name = (string) $el->name;
                                if (isset($formData[$name]) && is_array($formData[$name])) {
                                    $rowCount = max($rowCount, count($formData[$name]));
                                }
                            }
                        @endphp
                        @for($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++)
                            <div class="border rounded p-2 mb-2 bg-white" wire:key="row-{{ $section->id }}-{{ $rowIndex }}">
                                <div class="row">
                                    @foreach($rowElements as $element)
                                        <div class="col-md-6 mb-2">
                                            @include('livewire.partials.submission-form-capture-element', [
                                                'element' => $element,
                                                'wirePrefix' => 'formData.' . $element->name . '.' . $rowIndex,
                                                'fieldId' => $element->name . '_' . $rowIndex,
                                            ])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endfor
                        <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addSchemaRow('{{ $section->id }}')">
                            <i class="mdi mdi-plus"></i> Add sample row
                        </button>
                    @else
                        <div class="row">
                            @foreach($section->elementHolders->sortBy('sort_order') as $holder)
                                @foreach($holder->elements->sortBy('sort_order') as $element)
                                    <div class="col-md-6 mb-3">
                                        @include('livewire.partials.submission-form-capture-element', [
                                            'element' => $element,
                                            'wirePrefix' => 'formData.' . $element->name,
                                            'fieldId' => $element->name,
                                        ])
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

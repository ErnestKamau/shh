@php
    use App\Services\Sampleworkflow\WalkInTrfFieldMapper;
    $fieldMapper = app(WalkInTrfFieldMapper::class);
@endphp

@if($submissionForm)
    <div class="card bg-light border-0 mb-4 shadow-none rounded">
        <div class="card-body p-3">
            @foreach($walkInSections as $sectionIndex => $section)
                @php
                    $isRowsSection = ($section->section_type ?? '') === 'rows_section';
                    $sectionKey = 'walk-in-section-'.$section->id;
                @endphp

                @if($isRowsSection)
                    @php
                        $tableColumns = $this->walkInRowTableColumns($section);
                        $rowCount = 1;
                        foreach ($this->uniqueRowElementsForSection($section) as $el) {
                            $name = (string) $el->name;
                            if (isset($formData[$name]) && is_array($formData[$name])) {
                                $rowCount = max($rowCount, count($formData[$name]));
                            }
                        }
                        if (isset($formData['sample_quantity_unit']) && is_array($formData['sample_quantity_unit'])) {
                            $rowCount = max($rowCount, count($formData['sample_quantity_unit']));
                        }
                    @endphp
                    <div class="form-section mb-3" wire:key="walk-in-rows-{{ $section->id }}-{{ $this->selectedSampleTypeId }}" x-data="{ open: false }">
                        <button
                            type="button"
                            class="form-section-title font-weight-bold text-dark border-bottom pb-2 mb-0 w-100 text-left bg-transparent border-0 d-flex align-items-center justify-content-between"
                            @click="open = !open; $nextTick(() => { if (typeof window.initTrfSignaturePads === 'function') window.initTrfSignaturePads(true); if (typeof window.initWalkInTrfParameterSelects === 'function') window.initWalkInTrfParameterSelects(); })"
                        >
                            <span>{{ $section->title }}</span>
                            <i class="mdi" :class="open ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
                        </button>
                        <div class="pt-3" x-show="open" x-collapse>
                            <div class="table-responsive walk-in-trf-rows-table">
                                <table class="table table-bordered table-sm mb-2 walk-in-trf-rows-grid">
                                    <thead class="bg-secondary text-white text-center small">
                                        <tr>
                                            <th class="walk-in-trf-col-sn">S. No.</th>
                                            @foreach($tableColumns as $column)
                                                @php $fieldName = (string) ($column['element']->name ?? ''); @endphp
                                                @if(! in_array($fieldName, ['job_number', 'crm_contact_id'], true))
                                                    <th class="{{ $column['class'] }}">{{ $column['label'] }}</th>
                                                @endif
                                            @endforeach
                                            <th class="walk-in-trf-col-actions"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @for($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++)
                                            <tr wire:key="row-{{ $section->id }}-{{ $rowIndex }}">
                                                <td class="text-center align-middle font-weight-bold walk-in-trf-col-sn">{{ $rowIndex + 1 }}</td>
                                                @foreach($tableColumns as $column)
                                                    @php
                                                        $element = $column['element'];
                                                        $field = $column['field'] ?? $fieldMapper->toField($element);
                                                        $fieldName = (string) ($field['name'] ?? $element->name ?? '');
                                                    @endphp
                                                    @if(in_array($fieldName, ['job_number', 'crm_contact_id'], true))
                                                        @continue
                                                    @endif
                                                    <td class="align-top {{ $column['class'] }}" wire:key="row-el-{{ $section->id }}-{{ $rowIndex }}-{{ $element->id }}">
                                                        @if($column['type'] === 'qty_unit')
                                                            @include('livewire.partials.walk-in-trf-qty-unit-cell', ['rowIndex' => $rowIndex])
                                                        @elseif($fieldName === 'sample_description')
                                                            @include('livewire.partials.trf-sample-description-modal-cell', [
                                                                'rowIdx' => $rowIndex,
                                                                'sectionId' => $section->id,
                                                                'wirePrefix' => 'formData.sample_description.'.$rowIndex,
                                                                'formData' => $formData,
                                                            ])
                                                        @else
                                                            @include('livewire.sampleworkflow.test-request-field-render', [
                                                                'field' => $field,
                                                                'wirePrefix' => 'formData.'.$element->name.'.'.$rowIndex,
                                                                'fieldId' => $element->name.'_'.$rowIndex,
                                                                'rowIndex' => $rowIndex,
                                                                'compact' => true,
                                                            ])
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="text-center align-middle walk-in-trf-col-actions">
                                                    @if($rowCount > 1)
                                                        <button type="button" class="btn btn-danger btn-xs p-1"
                                                            wire:click="removeSchemaRow('{{ $section->id }}', {{ $rowIndex }})"
                                                            title="Remove row">
                                                            <i class="mdi mdi-trash-can"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="addSchemaRow('{{ $section->id }}')">
                                <i class="mdi mdi-plus"></i> Add sample row
                            </button>
                        </div>
                    </div>
                @else
                    <div class="form-section mb-3" wire:key="{{ $sectionKey }}" x-data="{ open: false }">
                        <button
                            type="button"
                            class="form-section-title font-weight-bold text-dark border-bottom pb-2 mb-0 w-100 text-left bg-transparent border-0 d-flex align-items-center justify-content-between"
                            @click="open = !open; $nextTick(() => { if (typeof window.initTrfSignaturePads === 'function') window.initTrfSignaturePads(true); })"
                        >
                            <span>{{ $section->title }}</span>
                            <i class="mdi" :class="open ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
                        </button>
                        <div class="row pt-3" x-show="open" x-collapse>
                            @foreach($section->elementHolders->sortBy('sort_order') as $holder)
                                @foreach($holder->elements->sortBy('sort_order') as $element)
                                    @if(! in_array($element->name ?? '', ['job_number', 'crm_contact_id'], true))
                                        <div class="col-md-6 mb-3" wire:key="field-{{ $element->id }}">
                                            @include('livewire.sampleworkflow.test-request-field-render', [
                                                'field' => $fieldMapper->toField($element),
                                            ])
                                        </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif

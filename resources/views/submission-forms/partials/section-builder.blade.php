@php
if (!function_exists('getElementIcon')) {
    function getElementIcon($elementType) {
        switch($elementType) {
            case 'text':
                return 'form-textbox';
            case 'number':
                return 'numeric';
            case 'email':
                return 'email-outline';
            case 'date':
                return 'calendar';
            case 'datetime':
                return 'calendar-clock';
            case 'textarea':
                return 'text-box-outline';
            case 'select':
                return 'form-dropdown';
            case 'radio':
                return 'radiobox-marked';
            case 'checkbox':
                return 'checkbox-marked';
            case 'file':
                return 'file-upload-outline';
            case 'camera_photo':
                return 'camera';
            case 'image_upload':
                return 'image-plus';
            case 'zone_select':
                return 'map-marker-radius';
            case 'signature':
                return 'draw';
            case 'client_select':
                return 'account-group';
            case 'sample_type_select':
                return 'test-tube';
            case 'client_unit_select':
                return 'office-building';
            case 'client_contact_select':
                return 'account-multiple';
            case 'analysis_type_select':
                return 'flask';
            case 'store_select':
                return 'store';
            case 'store_slot_select':
                return 'view-grid';
            case 'sample_condition_select':
                return 'thermometer';
            case 'standard_select':
                return 'certificate';
            case 'sample_point_select':
                return 'map-marker';
            case 'user_select':
                return 'account';
            case 'calculation':
                return 'calculator';
            case 'pricelist_viewer':
                return 'cash-multiple';
            default:
                return 'form-textbox';
        }
    }
}
@endphp

<div class="section-item"
    data-section-id="{{ $section->id }}"
    data-section-title="{{ $section->title }}"
    data-section-description="{{ $section->description ?? '' }}"
    data-section-type="{{ $section->section_type ?? 'regular' }}"
    data-section-alignment="{{ $section->section_alignment ?? 'left' }}"
    data-section-logos='@json($section->getSectionLogos())'>
    <div class="section-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i class="mdi mdi-drag-horizontal text-muted mr-2" style="cursor: move;"></i>
            <h6 class="mb-0">
                <i class="mdi mdi-{{ $section->isRowsSection() ? 'table' : 'folder-outline' }} text-primary"></i>
                {{ $section->title }}
                @if($section->isRowsSection())
                    <span class="badge badge-info badge-sm ml-2">Rows Section</span>
                @endif
                <span class="badge badge-light badge-sm ml-2">{{ ucfirst($section->section_alignment ?? 'left') }}</span>
                @if(count($section->getSectionLogos()) > 0)
                    <span class="badge badge-outline-primary badge-sm ml-2">
                        <i class="mdi mdi-image-multiple"></i> {{ count($section->getSectionLogos()) }} logo(s)
                    </span>
                @endif
            </h6>
            @if($section->description)
                <small class="text-muted ml-2">{{ Str::limit($section->description, 50) }}</small>
            @endif
        </div>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-primary" onclick='FormBuilder.showSectionModal(@json($section->id))' title="Edit Section">
                <i class="mdi mdi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-info" onclick='FormBuilder.cloneSection(@json($section->id))' title="Clone Section">
                <i class="mdi mdi-content-copy"></i>
            </button>
            <button class="btn btn-sm btn-outline-success" onclick='FormBuilder.showHolderModal(@json($section->id))' title="Add Element Holder">
                <i class="mdi mdi-plus"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#section-{{ $section->id }}" title="Toggle Section">
                <i class="mdi mdi-chevron-down"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick='FormBuilder.deleteSection(@json($section->id))' title="Delete Section">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
    </div>
    
    <div class="collapse show" id="section-{{ $section->id }}">
        <div class="section-content p-3">
            <div class="holders-container sortable-holders" data-section-id="{{ $section->id }}" data-max-holders="20">
                @forelse($section->elementHolders as $holder)
                    <div class="holder-item mb-3" data-holder-id="{{ $holder->id }}">
                        <div class="holder-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="mdi mdi-drag-horizontal text-muted mr-2" style="cursor: move;"></i>
                                <small class="font-weight-bold text-info">
                                    <i class="mdi mdi-{{ $holder->holder_type === 'field' ? 'form-textbox' : 'text' }}"></i>
                                    {{ ucfirst($holder->holder_type) }} Holder
                                    <span class="badge badge-light ml-1">{{ $holder->elements->count() }}/{{ $holder->max_elements }}</span>
                                </small>
                                @if($holder->isAtCapacity())
                                    <span class="badge badge-warning badge-sm ml-2">Full</span>
                                @endif
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick='FormBuilder.showHolderModal(@json($section->id), @json($holder->id))' title="Edit Holder">
                                    <i class="mdi mdi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info" onclick='FormBuilder.cloneElementHolder(@json($holder->id))' title="Clone Holder">
                                    <i class="mdi mdi-content-copy"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success" onclick='FormBuilder.showElementModal(@json($holder->id))' title="Add Element" {{ $holder->isAtCapacity() ? 'disabled' : '' }}>
                                    <i class="mdi mdi-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#holder-{{ $holder->id }}" title="Toggle Holder">
                                    <i class="mdi mdi-chevron-down"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick='FormBuilder.deleteHolder(@json($holder->id))' title="Delete Holder">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="collapse show" id="holder-{{ $holder->id }}">
                            <div class="holder-content">
                                <div class="elements-container sortable-elements p-2" data-holder-id="{{ $holder->id }}" data-max-elements="{{ $holder->max_elements }}">
                                    @forelse($holder->elements as $element)
                                        <div class="element-item d-flex justify-content-between align-items-center py-2 px-3 mb-1 border rounded"
                                             data-element-id="{{ $element->id }}"
                                            data-element-label="{{ $element->label }}"
                                            data-element-type="{{ $element->element_type }}"
                                            data-element-name="{{ $element->name }}"
                                            data-placeholder="{{ $element->placeholder ?? '' }}"
                                            data-default-value="{{ $element->default_value ?? '' }}"
                                            data-help-text="{{ $element->help_text ?? '' }}"
                                            data-options='@json($element->options ?? [])'
                                             data-depends-on-type="{{ $element->depends_on_type ?? '' }}"
                                             data-depends-on-field="{{ $element->depends_on_field ?? '' }}"
                                             data-source-table="{{ $element->source_table ?? '' }}"
                                             data-source-field="{{ $element->source_field ?? '' }}">
                                            <div class="d-flex align-items-center">
                                                <i class="mdi mdi-drag-horizontal text-muted mr-2" style="cursor: move;"></i>
                                                <i class="mdi mdi-{{ getElementIcon($element->element_type) }} text-secondary mr-2"></i>
                                                <div>
                                                    <span class="font-weight-medium">{{ $element->label }}</span>
                                                    @if($element->is_required)
                                                        <span class="text-danger ml-1">*</span>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">{{ $element->name }} ({{ $element->element_type }})</small>
                                                    @if($element->isMapped())
                                                        <br>
                                                        <small class="text-info">
                                                            <i class="mdi mdi-database"></i> 
                                                            Mapped to: <strong>{{ ucfirst(str_replace('_', ' ', $element->mapping_table)) }}</strong> → <strong>{{ ucfirst(str_replace('_', ' ', $element->mapping_field)) }}</strong>
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                @if($element->is_readonly)
                                                    <span class="badge badge-outline-warning badge-sm mr-2">readonly</span>
                                                @endif
                                                @if($element->isMapped())
                                                    <span class="badge badge-outline-info badge-sm mr-2" 
                                                          title="Mapped to {{ ucfirst(str_replace('_', ' ', $element->mapping_table)) }}.{{ ucfirst(str_replace('_', ' ', $element->mapping_field)) }}">
                                                        <i class="mdi mdi-database"></i> mapped
                                                    </span>
                                                @endif
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick='FormBuilder.showElementModal(@json($holder->id), @json($element->id))' title="Edit Element">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick='FormBuilder.cloneElement(@json($element->id))' title="Clone Element">
                                                        <i class="mdi mdi-content-copy"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick='FormBuilder.deleteElement(@json($element->id))' title="Delete Element">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-3 text-muted">
                                            <i class="mdi mdi-plus-circle-outline" style="font-size: 2rem;"></i>
                                            <p class="mb-0 small">No elements added yet</p>
                                            <button class="btn btn-sm btn-outline-primary mt-2" onclick='FormBuilder.showElementModal(@json($holder->id))' {{ $holder->isAtCapacity() ? 'disabled' : '' }}>
                                                <i class="mdi mdi-plus"></i> Add Element
                                            </button>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="mdi mdi-plus-circle-outline" style="font-size: 2.5rem;"></i>
                        <p class="mb-0">No element holders added yet</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick='FormBuilder.showHolderModal(@json($section->id))'>
                            <i class="mdi mdi-plus"></i> Add Element Holder
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
{{-- Enhanced Rows Section Display with jQuery Data Loading --}}
<div class="rows-section-display-enhanced" data-section-id="{{ $section->id }}" data-form-instance-id="{{ $instance->id }}">
    <div class="section-header mb-3">
        <h5 class="text-primary border-bottom pb-2">
            <i class="mdi mdi-table"></i> {{ $section->title }}
        </h5>
        @if($section->description)
            <p class="text-muted small mb-0">{{ $section->description }}</p>
        @endif
    </div>

    @php
        $templateHolder = $section->getTemplateElementHolder();
        $formData = $instance->getFormDataForDisplay();
        $sectionData = collect($formData['sections'])->firstWhere('id', $section->id);
        $rowsData = $sectionData['element_holders'][0]['rows_data'] ?? [];
    @endphp

    @if($templateHolder && $templateHolder->elements->count() > 0)
        <div class="rows-container">
            {{-- Rows Table --}}
            <div class="table-responsive">
                <table class="table table-bordered" id="rows-table-{{ $section->id }}">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">#</th>
                            @foreach($templateHolder->elements as $element)
                                <th>
                                    {{ $element->label }}
                                    @if($element->is_required)
                                        <span class="text-danger">*</span>
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
                    <tbody id="rows-tbody-{{ $section->id }}">
                        {{-- Rows will be dynamically loaded here --}}
                    </tbody>
                </table>
            </div>

            {{-- Hidden template row for cloning --}}
            <template id="row-template-{{ $section->id }}">
                <tr class="row-item" data-row-index="">
                    <td class="row-number"></td>
                    @foreach($templateHolder->elements as $element)
                        <td>
                            <div class="form-group mb-0">
                                @include('submission-forms.partials.form-element-display', [
                                    'element' => $element,
                                    'isArrayField' => true,
                                    'rowIndex' => 'ROW_INDEX_PLACEHOLDER'
                                ])
                            </div>
                        </td>
                    @endforeach
                </tr>
            </template>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="mdi mdi-alert-circle"></i> No elements configured for this rows section. 
            Please add elements to the template holder in the form builder.
        </div>
    @endif
</div>
@section('script2')
{{-- Form Data for JavaScript --}}
<script type="application/json" id="form-data-{{ $section->id }}">
{!! json_encode($formData) !!}
</script>

<script>
$(document).ready(function() {
    const sectionId = {{ $section->id }};
    const formInstanceId = {{ $instance->id }};
    
    // Initialize the enhanced rows section
    initializeEnhancedRowsSection(sectionId, formInstanceId);
});

/**
 * Initialize enhanced rows section with data loading
 */
function initializeEnhancedRowsSection(sectionId, formInstanceId) {
    console.log('Initializing enhanced rows section:', sectionId);
    
    // Get form data
    const formData = JSON.parse($('#form-data-' + sectionId).html());
    const sectionData = formData.sections.find(s => s.id === sectionId);
    
    if (!sectionData) {
        console.error('Section data not found for section:', sectionId);
        return;
    }
    
    const rowsData = sectionData.element_holders[0]?.rows_data || {};
    const elements = sectionData.element_holders[0]?.elements || [];
    const dependencyChain = formData.dependency_chain;
    
    console.log('Rows data:', rowsData);
    console.log('Elements:', elements);
    console.log('Dependency chain:', dependencyChain);
    
    // Load rows data
    loadRowsData(sectionId, rowsData, elements, dependencyChain);
}

/**
 * Load rows data with proper dependency handling
 */
function loadRowsData(sectionId, rowsData, elements, dependencyChain) {
    const tbody = $('#rows-tbody-' + sectionId);
    const template = document.getElementById('row-template-' + sectionId);
    
    // Clear existing rows
    tbody.empty();
    
    // Get unique array indexes
    const arrayIndexes = Object.keys(rowsData).map(Number).sort((a, b) => a - b);
    
    console.log('Array indexes to load:', arrayIndexes);
    
    if (arrayIndexes.length === 0) {
        console.log('No data to load for section:', sectionId);
        return;
    }
    
    // Load each row
    arrayIndexes.forEach((arrayIndex, index) => {
        loadSingleRow(sectionId, arrayIndex, rowsData[arrayIndex], elements, dependencyChain, template, tbody);
    });
}

/**
 * Load a single row with all its data
 */
function loadSingleRow(sectionId, arrayIndex, rowData, elements, dependencyChain, template, tbody) {
    console.log('Loading row for array index:', arrayIndex, 'with data:', rowData);
    
    // Clone template
    const newRow = template.content.cloneNode(true);
    const rowElement = newRow.querySelector('tr');
    const $rowElement = $(rowElement);
    
    // Set row index and number
    $rowElement.attr('data-row-index', arrayIndex);
    $rowElement.find('.row-number').text(arrayIndex + 1);
    
    // Update field names and IDs
    updateRowFieldNames($rowElement, arrayIndex);
    
    // Add data attributes to all elements
    addDataAttributesToRow($rowElement, rowData, elements);
    
    // Append to table
    tbody.append($rowElement);
    
    // Initialize the row with proper dependency loading
    initializeRowWithDependencies($rowElement, arrayIndex, dependencyChain);
}

/**
 * Update field names and IDs for a row
 */
function updateRowFieldNames($rowElement, arrayIndex) {
    const $inputs = $rowElement.find('input, select, textarea');
    
    $inputs.each(function() {
        const $this = $(this);
        
        // Update name attribute
        if ($this.attr('name')) {
            $this.attr('name', $this.attr('name').replace('ROW_INDEX_PLACEHOLDER', arrayIndex));
        }
        
        // Update ID attribute
        if ($this.attr('id')) {
            $this.attr('id', $this.attr('id').replace('ROW_INDEX_PLACEHOLDER', arrayIndex));
        }
    });
    
    // Update labels
    const $labels = $rowElement.find('label');
    $labels.each(function() {
        const $this = $(this);
        if ($this.attr('for')) {
            $this.attr('for', $this.attr('for').replace('ROW_INDEX_PLACEHOLDER', arrayIndex));
        }
    });
}

/**
 * Add data attributes to row elements
 */
function addDataAttributesToRow($rowElement, rowData, elements) {
    elements.forEach(element => {
        const elementId = element.id;
        const elementName = element.name;
        const customType = element.custom_element_type;
        const dependencyInfo = element.dependency_info;
        
        // Find the element in the row
        const $element = $rowElement.find(`[name="${elementName}[${$rowElement.attr('data-row-index')}]"]`);
        
        if ($element.length === 0) {
            console.warn('Element not found in row:', elementName);
            return;
        }
        
        // Add data attributes
        $element.attr('data-element-id', elementId);
        $element.attr('data-element-type', customType);
        $element.attr('data-depends-on', dependencyInfo.depends_on || '');
        $element.attr('data-dependency-level', dependencyInfo.dependency_level);
        $element.attr('data-is-independent', dependencyInfo.is_independent);
        
        // Add saved value if exists
        if (rowData[elementId]) {
            const savedValue = rowData[elementId].value?.value || '';
            $element.attr('data-saved-value', savedValue);
            console.log('Added saved value for', elementName, ':', savedValue);
        }
    });
}

/**
 * Initialize row with proper dependency loading
 */
function initializeRowWithDependencies($rowElement, arrayIndex, dependencyChain) {
    console.log('Initializing row with dependencies for array index:', arrayIndex);
    
    // Load elements by dependency level
    dependencyChain.forEach(level => {
        if (level.elements && level.elements.length > 0) {
            console.log('Loading dependency level:', level.level, 'elements:', level.elements);
            
            level.elements.forEach(elementId => {
                const $element = $rowElement.find(`[data-element-id="${elementId}"]`);
                
                if ($element.length > 0) {
                    initializeElementWithData($element, level.is_independent);
                }
            });
        }
    });
}

/**
 * Initialize a single element with its data
 */
function initializeElementWithData($element, isIndependent) {
    const elementType = $element.attr('data-element-type');
    const savedValue = $element.attr('data-saved-value');
    
    console.log('Initializing element:', $element.attr('name'), 'type:', elementType, 'saved value:', savedValue);
    
    if (isIndependent) {
        // Load options for independent elements
        loadElementOptions($element, elementType);
        
        // Set saved value after options are loaded
        if (savedValue) {
            setTimeout(() => {
                setElementValue($element, savedValue);
                triggerElementChange($element);
            }, 100);
        }
    } else {
        // For dependent elements, set up change handlers
        setupDependentElement($element, elementType);
        
        // Set saved value if parent is already loaded
        if (savedValue) {
            const parentValue = getParentElementValue($element);
            if (parentValue) {
                loadElementOptions($element, elementType, parentValue).then(() => {
                    setElementValue($element, savedValue);
                    triggerElementChange($element);
                });
            }
        }
    }
}

/**
 * Load options for an element using the existing loadDynamicOptions function
 */
function loadElementOptions($element, elementType, parentValue = null) {
    return new Promise((resolve) => {
        console.log('Loading options for element:', $element.attr('name'), 'type:', elementType, 'parent value:', parentValue);
        
        const elementId = $element.attr('id');
        
        // Determine the correct parameters based on element type and parent value
        let clientId = null;
        let sampleTypeId = null;
        let storeId = null;
        let clientUnitId = null;
        let analysisTypeId = null;
        
        // Get parent values based on dependency chain
        const $row = $element.closest('tr');
        
        if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
            clientId = parentValue;
        } else if (elementType === 'sample_point_select') {
            clientUnitId = parentValue;
        } else if (elementType === 'analysis_type_select') {
            sampleTypeId = parentValue;
        } else if (elementType === 'analysis_elements_select') {
            analysisTypeId = parentValue;
        } else if (elementType === 'store_slot_select') {
            storeId = parentValue;
        }
        
        // Use the existing loadDynamicOptions function
        loadDynamicOptions($element, elementId, elementType, clientId, sampleTypeId, storeId, clientUnitId, analysisTypeId).then(() => {
            resolve();
        }).catch((error) => {
            console.error('Error loading options:', error);
            resolve();
        });
    });
}

/**
 * Load dynamic options using the existing API
 */
function loadDynamicOptions($element, elementId, elementType, clientId = null, sampleTypeId = null, storeId = null, clientUnitId = null, analysisTypeId = null) {
    return new Promise((resolve, reject) => {
        console.log('Loading dynamic options for:', { elementId, elementType, clientId, sampleTypeId, storeId, clientUnitId, analysisTypeId });
        
        const select = $element;
        const originalHtml = select.html();
        
        // Show loading state
        select.html('<option value="">Loading...</option>').prop('disabled', true);
        
        // Make AJAX request
        const ajaxUrl = '{{ auth()->check() ? route("submission-forms.dynamic-options") : route("forms.dynamic-options") }}';
        const ajaxData = {
            element_type: elementType,
            client_id: clientId,
            sample_type_id: sampleTypeId,
            store_id: storeId,
            client_unit_id: clientUnitId,
            analysis_type_id: analysisTypeId
        };
        
        console.log('Making AJAX request to:', ajaxUrl, 'with data:', ajaxData);
        
        $.ajax({
            url: ajaxUrl,
            method: 'GET',
            data: ajaxData,
            success: function(response) {
                console.log('Dynamic options loaded successfully:', response);
                let html = '';
                
                // Add placeholder option if not required
                if (!select.prop('required')) {
                    html += '<option value="">Select...</option>';
                }
                
                // Add options from response
                if (response.success && response.options && response.options.length > 0) {
                    response.options.forEach(function(option) {
                        html += '<option value="' + option.id + '">' + option.text + '</option>';
                    });
                } else {
                    console.warn('No options returned for element type:', elementType);
                    html += '<option value="">No options available</option>';
                }
                
                select.html(html).prop('disabled', false);
                resolve();
            },
            error: function(xhr, status, error) {
                console.error('Error loading dynamic options:', error);
                select.html('<option value="">Error loading options</option>').prop('disabled', false);
                reject(error);
            }
        });
    });
}

/**
 * Set element value with support for multiple comma-separated values
 */
function setElementValue($element, value) {
    console.log('Setting value for element:', $element.attr('name'), 'value:', value);
    
    if ($element.is('select')) {
        // Check if this is a sample point field (multiple values)
        const elementType = $element.attr('data-element-type');
        
        if (elementType === 'sample_point_select' && value && value.includes(',')) {
            // Handle multiple comma-separated values
            const values = value.split(',').map(v => v.trim()).filter(v => v);
            console.log('Setting multiple values for sample point:', values);
            $element.val(values);
        } else {
            // Handle single value
            $element.val(value);
        }
    } else if ($element.attr('type') === 'checkbox') {
        $element.prop('checked', value === '1' || value === 'true');
    } else if ($element.attr('type') === 'radio') {
        $element.filter(`[value="${value}"]`).prop('checked', true);
    } else {
        $element.val(value);
    }
}

/**
 * Trigger change event on element
 */
function triggerElementChange($element) {
    console.log('Triggering change event for element:', $element.attr('name'));
    $element.trigger('change');
}

/**
 * Setup dependent element
 */
function setupDependentElement($element, elementType) {
    const dependsOn = $element.attr('data-depends-on');
    
    if (!dependsOn) return;
    
    console.log('Setting up dependent element:', $element.attr('name'), 'depends on:', dependsOn);
    
    // Find parent element in the same row
    const $parent = $element.closest('tr').find(`[data-element-type="${dependsOn}"]`);
    
    if ($parent.length > 0) {
        $parent.on('change', function() {
            const parentValue = $(this).val();
            console.log('Parent element changed:', dependsOn, 'new value:', parentValue);
            
            if (parentValue) {
                loadElementOptions($element, elementType, parentValue);
            } else {
                clearElement($element);
            }
        });
    }
}

/**
 * Get parent element value
 */
function getParentElementValue($element) {
    const dependsOn = $element.attr('data-depends-on');
    if (!dependsOn) return null;
    
    const $parent = $element.closest('tr').find(`[data-element-type="${dependsOn}"]`);
    return $parent.length > 0 ? $parent.val() : null;
}

/**
 * Clear element
 */
function clearElement($element) {
    if ($element.is('select')) {
        $element.html('<option value="">Select...</option>');
        $element.val('');
    } else {
        $element.val('');
    }
}
</script>

/**
 * Custom Field Loader for Submission Forms
 * Handles loading of custom field data with proper dependency management
 */

class CustomFieldLoader {
    constructor() {
        this.loadingPromises = new Map();
        this.elementCache = new Map();
        this.dependencyChain = [];
    }

    /**
     * Initialize form data loading
     */
    initializeFormData(formData) {
        console.log('Initializing form data:', formData);
        
        this.dependencyChain = formData.dependency_chain || [];
        this.elementCache.clear();
        
        // Cache all elements metadata
        if (formData.elements_metadata) {
            Object.keys(formData.elements_metadata).forEach(elementId => {
                this.elementCache.set(elementId, formData.elements_metadata[elementId]);
            });
        }
        
        // Process each section
        formData.sections.forEach(section => {
            this.initializeSection(section);
        });
    }

    /**
     * Initialize a section
     */
    initializeSection(section) {
        console.log('Initializing section:', section.id, section.title);
        
        section.element_holders.forEach(holder => {
            if (holder.holder_type === 'rows') {
                this.initializeRowsSection(section.id, holder);
            } else {
                this.initializeRegularSection(section.id, holder);
            }
        });
    }

    /**
     * Initialize rows section
     */
    initializeRowsSection(sectionId, holder) {
        console.log('Initializing rows section:', sectionId);
        
        const rowsData = holder.rows_data || {};
        const elements = holder.elements || [];
        
        // Load each row
        Object.keys(rowsData).forEach(arrayIndex => {
            this.loadRowData(sectionId, parseInt(arrayIndex), rowsData[arrayIndex], elements);
        });
    }

    /**
     * Initialize regular section (non-rows)
     */
    initializeRegularSection(sectionId, holder) {
        console.log('Initializing regular section:', sectionId);
        
        const elements = holder.elements || [];
        
        // Process elements by dependency level
        this.dependencyChain.forEach(level => {
            if (level.elements && level.elements.length > 0) {
                level.elements.forEach(elementId => {
                    const elementData = this.getElementData(elementId);
                    if (elementData) {
                        this.initializeElementInSection(sectionId, elementData, level.is_independent);
                    }
                });
            }
        });
    }

    /**
     * Initialize element in regular section
     */
    initializeElementInSection(sectionId, elementData, isIndependent) {
        const elementName = elementData.name;
        const elementType = elementData.custom_element_type;
        
        // Find the element
        const $element = $(`[name="${elementName}"]`);
        
        if ($element.length === 0) {
            console.warn('Element not found in section:', elementName);
            return;
        }
        
        // Add data attributes
        this.addElementDataAttributes($element, elementData);
        
        // Get saved value
        const savedValue = elementData.saved_values && elementData.saved_values.length > 0 
            ? elementData.saved_values[0].value 
            : null;
            
        if (savedValue) {
            $element.attr('data-saved-value', savedValue);
        }
        
        // Initialize based on dependency
        if (isIndependent) {
            this.initializeIndependentElement($element, elementData, savedValue);
        } else {
            this.initializeDependentElement($element, elementData, savedValue);
        }
    }

    /**
     * Load data for a single row
     */
    loadRowData(sectionId, arrayIndex, rowData, elements) {
        console.log('Loading row data for section:', sectionId, 'array index:', arrayIndex);
        
        // Find the row element
        const $row = $(`#rows-tbody-${sectionId} tr[data-row-index="${arrayIndex}"]`);
        
        if ($row.length === 0) {
            console.warn('Row not found for array index:', arrayIndex);
            return;
        }
        
        // Load elements by dependency level
        this.dependencyChain.forEach(level => {
            if (level.elements && level.elements.length > 0) {
                level.elements.forEach(elementId => {
                    const elementData = this.getElementData(elementId);
                    if (elementData) {
                        this.loadElementInRow($row, elementData, rowData, level.is_independent);
                    }
                });
            }
        });
    }

    /**
     * Load element data in a row
     */
    loadElementInRow($row, elementData, rowData, isIndependent) {
        const elementName = elementData.name;
        const elementType = elementData.custom_element_type;
        const arrayIndex = $row.attr('data-row-index');
        
        // Find the element
        const $element = $row.find(`[name="${elementName}[${arrayIndex}]"]`);
        
        if ($element.length === 0) {
            console.warn('Element not found in row:', elementName);
            return;
        }
        
        // Add data attributes
        this.addElementDataAttributes($element, elementData);
        
        // Get saved value
        const savedValue = this.getSavedValueForElement(elementData.id, rowData);
        if (savedValue) {
            $element.attr('data-saved-value', savedValue);
        }
        
        // Initialize element
        if (isIndependent) {
            this.initializeIndependentElement($element, elementData, savedValue);
        } else {
            this.initializeDependentElement($element, elementData, savedValue);
        }
    }

    /**
     * Initialize independent element
     */
    initializeIndependentElement($element, elementData, savedValue) {
        const elementType = elementData.custom_element_type;
        
        console.log('Initializing independent element:', $element.attr('name'), 'type:', elementType);
        
        // Load options
        this.loadElementOptions($element, elementType).then(() => {
            // Set saved value
            if (savedValue) {
                this.setElementValue($element, savedValue);
                this.triggerElementChange($element);
            }
        });
    }

    /**
     * Initialize dependent element
     */
    initializeDependentElement($element, elementData, savedValue) {
        const elementType = elementData.custom_element_type;
        const dependsOn = elementData.dependency_info.depends_on;
        
        console.log('Initializing dependent element:', $element.attr('name'), 'depends on:', dependsOn);
        
        // Set up change handler
        this.setupDependentElementHandler($element, elementType, dependsOn);
        
        // Check if parent already has value
        const parentValue = this.getParentElementValue($element, dependsOn);
        if (parentValue) {
            this.loadElementOptions($element, elementType, parentValue).then(() => {
                if (savedValue) {
                    this.setElementValue($element, savedValue);
                    this.triggerElementChange($element);
                }
            });
        }
    }

    /**
     * Load element options
     */
    loadElementOptions($element, elementType, parentValue = null) {
        return new Promise((resolve) => {
            console.log('Loading options for element:', $element.attr('name'), 'type:', elementType, 'parent:', parentValue);
            
            // Clear existing options
            if ($element.is('select')) {
                $element.html('<option value="">Select...</option>');
            }
            
            // Load options based on element type
            this.loadOptionsForElementType($element, elementType, parentValue).then(() => {
                resolve();
            });
        });
    }

    /**
     * Load options for specific element type using the real API
     */
    loadOptionsForElementType($element, elementType, parentValue) {
        return new Promise((resolve, reject) => {
            const elementId = $element.attr('id');
            
            // Determine the correct parameters based on element type and parent value
            let clientId = null;
            let sampleTypeId = null;
            let storeId = null;
            let clientUnitId = null;
            let analysisTypeId = null;
            
            if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
                clientId = parentValue;
            } else if (elementType === 'sample_point_select') {
                clientUnitId = parentValue;
            } else if (elementType === 'user_select') {
                // No additional parameters required
            } else if (elementType === 'analysis_type_select') {
                sampleTypeId = parentValue;
            } else if (elementType === 'analysis_elements_select') {
                analysisTypeId = parentValue;
            } else if (elementType === 'store_slot_select') {
                storeId = parentValue;
            }
            
            // Use the existing loadDynamicOptions function
            this.loadDynamicOptions($element, elementId, elementType, clientId, sampleTypeId, storeId, clientUnitId, analysisTypeId)
                .then(() => resolve())
                .catch((error) => {
                    console.error('Error loading options:', error);
                    reject(error);
                });
        });
    }

    /**
     * Load dynamic options using the real API
     */
    loadDynamicOptions($element, elementId, elementType, clientId = null, sampleTypeId = null, storeId = null, clientUnitId = null, analysisTypeId = null) {
        return new Promise((resolve, reject) => {
            console.log('Loading dynamic options for:', { elementId, elementType, clientId, sampleTypeId, storeId, clientUnitId, analysisTypeId });
            
            const select = $element;
            
            // Show loading state
            select.html('<option value="">Loading...</option>').prop('disabled', true);
            
            // Make AJAX request
            const ajaxUrl = window.location.origin + '/submission-forms/dynamic-options';
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
     * Get default options for element type
     */
    getDefaultOptionsForType(elementType, parentValue) {
        const options = {
            'client_select': [
                { value: '1', label: 'Client 1' },
                { value: '2', label: 'Client 2' },
                { value: '3', label: 'Client 3' }
            ],
            'sample_type_select': [
                { value: '1', label: 'Water' },
                { value: '2', label: 'Soil' },
                { value: '3', label: 'Air' }
            ],
            'client_unit_select': parentValue ? [
                { value: '1', label: `Unit 1 for Client ${parentValue}` },
                { value: '2', label: `Unit 2 for Client ${parentValue}` }
            ] : [],
            'client_contact_select': parentValue ? [
                { value: '1', label: `Contact 1 for Client ${parentValue}` },
                { value: '2', label: `Contact 2 for Client ${parentValue}` }
            ] : [],
            'sample_point_select': parentValue ? [
                { value: '1', label: `Point 1 for Unit ${parentValue}` },
                { value: '2', label: `Point 2 for Unit ${parentValue}` }
            ] : [],
            'analysis_type_select': parentValue ? [
                { value: '1', label: `Analysis 1 for Type ${parentValue}` },
                { value: '2', label: `Analysis 2 for Type ${parentValue}` }
            ] : [],
            'analysis_elements_select': parentValue ? [
                { value: '1', label: `Element 1 for Analysis ${parentValue}` },
                { value: '2', label: `Element 2 for Analysis ${parentValue}` }
            ] : [],
            'user_select': [
                { value: '1', label: 'User One' },
                { value: '2', label: 'User Two' }
            ]
        };
        
        return options[elementType] || [];
    }

    /**
     * Set element value with support for multiple comma-separated values
     */
    setElementValue($element, value) {
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
    triggerElementChange($element) {
        console.log('Triggering change event for element:', $element.attr('name'));
        $element.trigger('change');
    }

    /**
     * Setup dependent element handler
     */
    setupDependentElementHandler($element, elementType, dependsOn) {
        if (!dependsOn) return;
        
        const $parent = $element.closest('tr').find(`[data-element-type="${dependsOn}"]`);
        
        if ($parent.length > 0) {
            $parent.on('change', () => {
                const parentValue = $parent.val();
                console.log('Parent element changed:', dependsOn, 'new value:', parentValue);
                
                if (parentValue) {
                    this.loadElementOptions($element, elementType, parentValue);
                } else {
                    this.clearElement($element);
                }
            });
        }
    }

    /**
     * Get parent element value
     */
    getParentElementValue($element, dependsOn) {
        if (!dependsOn) return null;
        
        const $parent = $element.closest('tr').find(`[data-element-type="${dependsOn}"]`);
        return $parent.length > 0 ? $parent.val() : null;
    }

    /**
     * Clear element
     */
    clearElement($element) {
        if ($element.is('select')) {
            $element.html('<option value="">Select...</option>');
            $element.val('');
        } else {
            $element.val('');
        }
    }

    /**
     * Add data attributes to element
     */
    addElementDataAttributes($element, elementData) {
        $element.attr('data-element-id', elementData.id);
        $element.attr('data-element-type', elementData.custom_element_type);
        $element.attr('data-depends-on', elementData.dependency_info.depends_on || '');
        $element.attr('data-dependency-level', elementData.dependency_info.dependency_level);
        $element.attr('data-is-independent', elementData.dependency_info.is_independent);
    }

    /**
     * Get saved value for element
     */
    getSavedValueForElement(elementId, rowData) {
        if (rowData[elementId]) {
            return rowData[elementId].value?.value || '';
        }
        return null;
    }

    /**
     * Get element data from cache
     */
    getElementData(elementId) {
        if (!this.elementCache.has(elementId)) {
            // This would be populated from the form data
            return null;
        }
        return this.elementCache.get(elementId);
    }

    /**
     * Set element data in cache
     */
    setElementData(elementId, elementData) {
        this.elementCache.set(elementId, elementData);
    }
}

// Global instance
window.customFieldLoader = new CustomFieldLoader();

// Initialize when document is ready
if (typeof $ !== 'undefined') {
    $(document).ready(function() {
        console.log('Custom Field Loader initialized');
    });
} else {
    // Wait for jQuery to load
    document.addEventListener('DOMContentLoaded', function() {
        // Check again after DOM is loaded
        if (typeof $ !== 'undefined') {
            $(document).ready(function() {
                console.log('Custom Field Loader initialized (delayed)');
            });
        } else {
            console.error('jQuery not available for Custom Field Loader');
        }
    });
}

// Custom elements dependency management
function initializeCustomElements() {
    console.log('Initializing custom elements with dependency management...');
    
    // Find all custom elements
    $('.custom-element').each(function() {
        const $element = $(this);
        const elementId = $element.attr('id');
        const elementType = $element.data('element-type');
        const dependsOn = $element.data('depends-on');
        
        console.log('Found custom element:', elementId, elementType, 'depends on:', dependsOn);
        
        // Load initial options for non-dependent elements
        if (!dependsOn) {
            console.log('Loading initial options for independent element:', elementId);
            loadDynamicOptions(elementId, elementType);
        } else {
            // Handle dependent elements
            setupDependentElement($element, elementId, elementType, dependsOn);
        }
    });
}

function setupDependentElement($element, elementId, elementType, dependsOn) {
    console.log('Setting up dependent element:', elementId, 'depends on:', dependsOn);
    
    // First, try to find parent element in the same row
    const $row = $element.closest('tr');
    let $parentElement = null;
    
    if ($row.length > 0) {
        // Look for parent element in the same row first
        $parentElement = $row.find(`select[data-element-type="${dependsOn}"]`);
        console.log('Looking for parent in same row:', $parentElement.length > 0);
    }
    
    // If not found in same row, look globally
    if (!$parentElement || $parentElement.length === 0) {
        $parentElement = $(`select[data-element-type="${dependsOn}"]`);
        console.log('Looking for parent globally:', $parentElement.length > 0);
    }
    
    if ($parentElement.length === 0) {
        console.warn('Parent element not found for dependency:', dependsOn);
        return;
    }
    
    console.log('Found parent element:', $parentElement.attr('id'));
    
    // Set up change handler for parent element
    $parentElement.off('change.dependency-' + elementId).on('change.dependency-' + elementId, function() {
        const parentValue = $(this).val();
        console.log('Parent element changed:', dependsOn, 'new value:', parentValue);
        
        if (parentValue) {
            // Load options based on parent selection
            loadDynamicOptions(elementId, elementType, parentValue);
        } else {
            // Clear dependent options when parent is cleared
            clearDependentElement($element);
            
            // Also clear any elements that depend on this element
            clearChildDependencies(elementType);
        }
    });
    
    // Load initial options if parent already has a value
    const currentParentValue = $parentElement.val();
    if (currentParentValue) {
        console.log('Parent already has value, loading dependent options:', currentParentValue);
        loadDynamicOptions(elementId, elementType, currentParentValue);
    } else {
        // Parent has no value, so clear this element
        clearDependentElement($element);
    }
}

function clearDependentElement($element) {
    const placeholder = $element.find('option:first').text() || 'Select...';
    $element.html(`<option value="">${placeholder}</option>`).prop('disabled', false);
}

function clearChildDependencies(parentElementType) {
    // Find all elements that depend on this element type
    $(`.custom-element[data-depends-on="${parentElementType}"]`).each(function() {
        const $childElement = $(this);
        clearDependentElement($childElement);
        
        // Recursively clear children of children
        const childElementType = $childElement.data('element-type');
        clearChildDependencies(childElementType);
    });
}

function loadDynamicOptions(elementId, elementType, clientId = null, sampleTypeId = null, storeId = null) {
    console.log('Loading dynamic options for:', { elementId, elementType, clientId, sampleTypeId, storeId });
    const select = $('#' + elementId);
    const originalHtml = select.html();

    // alert("Here we are");
    
    // Show loading state
    select.html('<option value="">Loading...</option>').prop('disabled', true);
    
    // Get the route URL - this needs to be set by the calling page
    const routeUrl = window.dynamicOptionsRoute || '/submission-forms/dynamic-options';
    
    // Make AJAX request
    $.ajax({
        url: routeUrl,
        method: 'GET',
        data: {
            element_type: elementType,
            client_id: clientId,
            sample_type_id: sampleTypeId,
            store_id: storeId
        },
        success: function(response) {
            console.log('Dynamic options loaded successfully:', response);
            let html = '';
            
            // Add placeholder option if not required
            if (!select.prop('required')) {
                html += '<option value="">Select...</option>';
            }
            
            // Add options from response
            if (response.options && response.options.length > 0) {
                response.options.forEach(function(option) {
                    html += '<option value="' + option.value + '">' + option.label + '</option>';
                });
            } else {
                console.warn('No options returned for element type:', elementType);
                html += '<option value="">No options available</option>';
            }
            
            select.html(html).prop('disabled', false);
            
            // Reinitialize Select2 after loading new options
            if (select.hasClass('select2-hidden-accessible')) {
                select.select2('destroy');
            }
            select.select2({
                placeholder: select.attr('placeholder') || select.data('placeholder') || 'Select...'
            });
            select.attr('style', 'width: 100%');
        },
        error: function(xhr, status, error) {
            console.error('Error loading options:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                error: error,
                elementType: elementType,
                clientId: clientId
            });
            
            // Restore original HTML and add error message
            let html = '';
            if (!select.prop('required')) {
                html += '<option value="">Select...</option>';
            }
            html += '<option value="">Error loading options</option>';
            
            select.html(html).prop('disabled', false);
            
            // Reinitialize Select2 after error
            if (select.hasClass('select2-hidden-accessible')) {
                select.select2('destroy');
            }
            select.select2({
                placeholder: select.attr('placeholder') || select.data('placeholder') || 'Select...'
            });
            select.attr('style', 'width: 100%');
            
            // Show user-friendly error message
            if (xhr.status === 403) {
                console.warn('Access denied for dynamic options');
            } else if (xhr.status === 404) {
                console.error('Dynamic options endpoint not found');
            } else if (xhr.status === 500) {
                console.error('Server error loading dynamic options');
            } else {
                console.error('Network error loading dynamic options');
            }
        }
    });
}
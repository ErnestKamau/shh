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
    
    // Find the parent element this depends on
    const $parentElement = $(`select[data-element-type="${dependsOn}"]`);
    
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

function loadDynamicOptions(elementId, elementType, clientId = null) {
    console.log('Loading dynamic options for:', { elementId, elementType, clientId });
    const select = $('#' + elementId);
    const originalHtml = select.html();
    
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
            client_id: clientId
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
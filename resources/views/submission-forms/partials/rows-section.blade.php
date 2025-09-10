{{-- Rows Section Rendering --}}
<div class="rows-section mb-4" data-section-id="{{ $section->id }}">
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
  @endphp

  @if($templateHolder && $templateHolder->elements->count() > 0)
    <div class="rows-container">
      {{-- Add Row Button --}}
      <div class="mb-3">
        <button type="button" class="btn btn-success btn-sm" id="add-row-{{ $section->id }}">
          <i class="mdi mdi-plus"></i> Add Row
        </button>
      </div>

      {{-- Rows Table --}}
      <div class="table-responsive">
        <table class="table table-bordered" id="rows-table-{{ $section->id }}">
          <thead class="thead-light">
            <tr>
              @foreach($templateHolder->elements as $element)
                <th>
                  {{ $element->label }}
                  @if($element->is_required)
                    <span class="text-danger">*</span>
                  @endif
                </th>
              @endforeach
              <th width="120">Actions</th>
            </tr>
          </thead>
          <tbody id="rows-tbody-{{ $section->id }}">
            {{-- Rows will be dynamically added here --}}
          </tbody>
        </table>
      </div>

      {{-- Hidden template row for cloning --}}
      <template id="row-template-{{ $section->id }}">
        <tr class="row-item" data-row-index="">
          @foreach($templateHolder->elements as $element)
            <td>
              <div class="form-group mb-0">
                @include('submission-forms.partials.form-element', [
                  'element' => $element,
                  'isArrayField' => true,
                  'rowIndex' => 'ROW_INDEX_PLACEHOLDER'
                ])
              </div>
            </td>
          @endforeach
          <td>
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-primary btn-sm clone-row" title="Clone Row">
                <i class="mdi mdi-content-copy"></i>
              </button>
              <button type="button" class="btn btn-outline-danger btn-sm delete-row" title="Delete Row">
                <i class="mdi mdi-delete"></i>
              </button>
            </div>
          </td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
  const sectionId = {{ $section->id }};
  const addRowBtn = document.getElementById('add-row-' + sectionId);
  const tbody = document.getElementById('rows-tbody-' + sectionId);
  const template = document.getElementById('row-template-' + sectionId);
  let rowIndex = 0;

  // Add new row
  addRowBtn.addEventListener('click', function() {
    addNewRow();
  });

  function addNewRow() {
    const newRow = template.content.cloneNode(true);
    const rowElement = newRow.querySelector('tr');
    
    
    // Set row index
    rowElement.setAttribute('data-row-index', rowIndex);
    
    // Update field names to include array index
    const inputs = newRow.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      if (input.name) {
        input.name = input.name.replace('ROW_INDEX_PLACEHOLDER', rowIndex);
      }
      if (input.id) {
        input.id = input.id.replace('ROW_INDEX_PLACEHOLDER', rowIndex);
      }
    });
    
    // Update labels
    const labels = newRow.querySelectorAll('label');
    labels.forEach(label => {
      if (label.getAttribute('for')) {
        label.setAttribute('for', label.getAttribute('for').replace('ROW_INDEX_PLACEHOLDER', rowIndex));
      }
    });
    
    tbody.appendChild(newRow);
    rowIndex++;
    
    // Initialize custom elements for the new row
    
    // Initialize Select2 on all select elements in the new row
    $(newRow).find('select').not('.hidden').each(function(i, e) {
      if (!$(e).hasClass('no-select2')) {
        $(e).select2({
          placeholder: $(e).attr('placeholder') || $(e).data('placeholder') || 'Select...'
        });
        $(e).attr('style', 'width: 100%');
      }
    });
    initializeRowCustomElements(newRow);
  }

  function initializeRowCustomElements(rowElement) {
    // Find all custom elements in this row
    const customElements = rowElement.querySelectorAll('.custom-element');
    
    customElements.forEach(element => {
      const elementType = element.getAttribute('data-element-type');
      const elementId = element.id;
      
      // Initialize based on element type
      if (['client_select', 'sample_type_select', 'store_select', 'standard_select', 'sample_condition_select'].includes(elementType)) {
        // Independent elements - data is already loaded statically, just initialize Select2
        console.log('Independent element with static data:', elementType, elementId);
      } else if (['client_unit_select', 'client_contact_select'].includes(elementType)) {
        // Set up dependent elements - these depend on client_select
        setupDependentElement(elementId, elementType, 'client_select');
      } else if (elementType === 'sample_point_select') {
        // This depends on client_unit_select
        setupDependentElement(elementId, elementType, 'client_unit_select');
      } else if (elementType === 'analysis_type_select') {
        // This depends on sample_type_select
        setupDependentElement(elementId, elementType, 'sample_type_select');
      } else if (elementType === 'analysis_elements_select') {
        // This depends on analysis_type_select
        setupDependentElement(elementId, elementType, 'analysis_type_select');
      } else if (elementType === 'store_slot_select') {
        // This depends on store_select
        setupDependentElement(elementId, elementType, 'store_select');
      }
    });
  }

  function setupDependentElement(elementId, elementType, dependsOn) {
    // Find the dependency element in the same row first
    const row = document.getElementById(elementId).closest('tr');
    const dependsOnElement = row.querySelector(`[data-element-type="${dependsOn}"]`);
    
    if (dependsOnElement) {
      // Set up change handler for same-row dependency
      dependsOnElement.addEventListener('change', function() {
        const parentId = this.value;
        if (parentId) {
          // Handle different parameter types based on element type and dependency
          if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
            // These depend on client_select
            loadDynamicOptions(elementId, elementType, parentId);
          } else if (elementType === 'sample_point_select') {
            // This depends on client_unit_select
            loadDynamicOptions(elementId, elementType, null, null, null, parentId);
          } else if (elementType === 'analysis_type_select') {
            // This depends on sample_type_select
            loadDynamicOptions(elementId, elementType, null, parentId);
          } else if (elementType === 'analysis_elements_select') {
            // This depends on analysis_type_select
            loadDynamicOptions(elementId, elementType, null, null, null, null, parentId);
          } else if (elementType === 'store_slot_select') {
            // This depends on store_select
            loadDynamicOptions(elementId, elementType, null, null, parentId);
          } else {
            // Default case
            loadDynamicOptions(elementId, elementType, parentId);
          }
        } else {
          // Clear dependent element and all its children
          clearDependentElementAndChildren(elementId);
        }
      });
    } else {
      // Fall back to global dependency
      const globalDependsOnElement = document.querySelector(`[data-element-type="${dependsOn}"]`);
      if (globalDependsOnElement) {
        globalDependsOnElement.addEventListener('change', function() {
          const parentId = this.value;
          if (parentId) {
            // Handle different parameter types based on element type and dependency
            if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
              // These depend on client_select
              loadDynamicOptions(elementId, elementType, parentId);
            } else if (elementType === 'sample_point_select') {
              // This depends on client_unit_select
              loadDynamicOptions(elementId, elementType, null, null, null, parentId);
            } else if (elementType === 'analysis_type_select') {
              // This depends on sample_type_select
              loadDynamicOptions(elementId, elementType, null, parentId);
            } else if (elementType === 'analysis_elements_select') {
              // This depends on analysis_type_select
              loadDynamicOptions(elementId, elementType, null, null, null, null, parentId);
            } else if (elementType === 'store_slot_select') {
              // This depends on store_select
              loadDynamicOptions(elementId, elementType, null, null, parentId);
            } else {
              // Default case
              loadDynamicOptions(elementId, elementType, parentId);
            }
          } else {
            // Clear dependent element and all its children
            clearDependentElementAndChildren(elementId);
          }
        });
      }
    }
  }

  function clearDependentElementsInRow(rowElement) {
    // Clear dependent elements that should be empty in cloned rows
    const dependentElements = rowElement.querySelectorAll('[data-element-type="client_unit_select"], [data-element-type="client_contact_select"], [data-element-type="sample_point_select"], [data-element-type="analysis_type_select"], [data-element-type="analysis_elements_select"], [data-element-type="store_slot_select"]');
    
    dependentElements.forEach(element => {
      if (element.tagName === 'SELECT') {
        element.innerHTML = '<option value="">Select...</option>';
        element.value = '';
      } else {
        element.value = '';
      }
    });
  }

  function clearDependentElementAndChildren(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    // Clear the element itself
    if (element.tagName === 'SELECT') {
      element.innerHTML = '<option value="">Select...</option>';
      element.value = '';
    } else {
      element.value = '';
    }
    
    // Find the row containing this element
    const row = element.closest('tr');
    if (!row) return;
    
    // Clear all dependent elements that depend on this element
    const elementType = element.getAttribute('data-element-type');
    let dependentTypes = [];
    
    // Define dependency chain
    if (elementType === 'client_select') {
      dependentTypes = ['client_unit_select', 'client_contact_select'];
    } else if (elementType === 'client_unit_select') {
      dependentTypes = ['sample_point_select'];
    } else if (elementType === 'sample_type_select') {
      dependentTypes = ['analysis_type_select'];
    } else if (elementType === 'analysis_type_select') {
      dependentTypes = ['analysis_elements_select'];
    } else if (elementType === 'store_select') {
      dependentTypes = ['store_slot_select'];
    }
    
    // Clear dependent elements
    dependentTypes.forEach(depType => {
      const dependentElements = row.querySelectorAll(`[data-element-type="${depType}"]`);
      dependentElements.forEach(depElement => {
        if (depElement.tagName === 'SELECT') {
          depElement.innerHTML = '<option value="">Select...</option>';
          depElement.value = '';
        } else {
          depElement.value = '';
        }
        
        // Recursively clear children of this dependent element
        clearDependentElementAndChildren(depElement.id);
      });
    });
  }

  // Clone row functionality
  tbody.addEventListener('click', function(e) {
    if (e.target.closest('.clone-row')) {
      const row = e.target.closest('tr');
      
      // Prompt user for number of clones
      const numClones = prompt('How many copies would you like to create?', '1');
      
      // Validate input
      if (numClones === null) {
        return; // User cancelled
      }
      
      const num = parseInt(numClones);
      if (isNaN(num) || num < 1 || num > 50) {
        alert('Please enter a valid number between 1 and 50.');
        return;
      }
      
      // Create the specified number of clones
      for (let i = 0; i < num; i++) {
        cloneRow(row);
      }
    } else if (e.target.closest('.delete-row')) {
      const row = e.target.closest('tr');
      deleteRow(row);
    }
  });

  function cloneRow(sourceRow) {
    const newRow = sourceRow.cloneNode(true);
    // sourceRow is already a <tr> element, so newRow is also a <tr> element
    const newRowElement = newRow;
    
    // Set new row index
    newRowElement.setAttribute('data-row-index', rowIndex);
    
    // Update field names and IDs
    const inputs = newRow.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
      if (input.name) {
        const currentIndex = sourceRow.getAttribute('data-row-index');
        input.name = input.name.replace(`[${currentIndex}]`, `[${rowIndex}]`);
      }
      if (input.id) {
        const currentIndex = sourceRow.getAttribute('data-row-index');
        input.id = input.id.replace(`_${currentIndex}`, `_${rowIndex}`);
      }
      
      // Clear values for dependent elements to avoid duplication
      const elementType = input.getAttribute('data-element-type');
      if (['client_unit_select', 'client_contact_select', 'sample_point_select', 'analysis_type_select', 'analysis_elements_select', 'store_slot_select'].includes(elementType)) {
        // Clear dependent element values
        if (input.tagName === 'SELECT') {
          input.innerHTML = '<option value="">Select...</option>';
          input.value = '';
        } else {
          input.value = '';
        }
      }
      
      // Ensure cloned elements are editable (remove disabled attribute)
      input.removeAttribute('disabled');
      input.removeAttribute('readonly');
    });
    
    // Update labels
    const labels = newRow.querySelectorAll('label');
    labels.forEach(label => {
      if (label.getAttribute('for')) {
        const currentIndex = sourceRow.getAttribute('data-row-index');
        label.setAttribute('for', label.getAttribute('for').replace(`_${currentIndex}`, `_${rowIndex}`));
      }
    });
    
    // Insert after source row
    sourceRow.parentNode.insertBefore(newRow, sourceRow.nextSibling);
    rowIndex++;
    
    // Initialize custom elements for the cloned row
    initializeRowCustomElements(newRow);
    
    // Clear dependent elements that should be empty in cloned rows
    clearDependentElementsInRow(newRow);
    
    // Ensure all form elements are properly enabled and editable
    const allFormElements = newRow.querySelectorAll('input, select, textarea, button');
    allFormElements.forEach(element => {
      element.removeAttribute('disabled');
      element.removeAttribute('readonly');
      // Ensure the element is not in a disabled state
      if (element.tagName === 'BUTTON' && element.classList.contains('disabled')) {
        element.classList.remove('disabled');
      }
    });
    
    // Initialize Select2 on all select elements in the cloned row
    $(newRow).find('select').not('.hidden').each(function(i, e) {
      if (!$(e).hasClass('no-select2')) {
        $(e).select2({
          placeholder: $(e).attr('placeholder') || $(e).data('placeholder') || 'Select...'
        });
        $(e).attr('style', 'width: 100%');
      }
    });
  }

  function deleteRow(row) {
    if (confirm('Are you sure you want to delete this row?')) {
      row.remove();
    }
  }

  // Add initial row if none exist
  if (tbody.children.length === 0) {
    addNewRow();
  }
});
</script>

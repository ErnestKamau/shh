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

    const $rowElement = $(rowElement);
    
    
    // Set row index
    $rowElement.attr('data-row-index', rowIndex);
    
    // Update field names to include array index
    const $inputs = $rowElement.find('input, select, textarea');

    $inputs.each(function() {
      const $this = $(this);
      if ($this.attr('name')) {
        $this.attr('name', $this.attr('name').replace('ROW_INDEX_PLACEHOLDER', rowIndex));
      }
      if ($this.attr('id')) {
        $this.attr('id', $this.attr('id').replace('ROW_INDEX_PLACEHOLDER', rowIndex));
      }
    });
    
    // Update labels
    const $labels = $rowElement.find('label');
    $labels.each(function() {
      const $this = $(this);
      if ($this.attr('for')) {
        $this.attr('for', $this.attr('for').replace('ROW_INDEX_PLACEHOLDER', rowIndex));
      }
    });

    
    $(document).find('#rows-tbody-'+sectionId).append($rowElement);
    rowIndex++;
    
    // Initialize custom elements for the new row
    initializeRowCustomElements($rowElement);
    
    // Initialize Select2 on all select elements in the new row
    $rowElement.find('select').not('.hidden').each(function(i, e) {
      if (!$(e).hasClass('no-select2')) {
        $(e).select2({
          placeholder: $(e).attr('placeholder') || $(e).data('placeholder') || 'Select...'
        });
        $(e).attr('style', 'width: 100%');
      }
    });
  }

  function initializeRowCustomElements($rowElement, isCloned = false) {
    // Find all custom elements in this row by data-element-type attribute
    const customElements = $rowElement.find('[data-element-type]');

    
    customElements.each(function() {
      const $this = $(this);
      const elementType = $this.attr('data-element-type');
      const elementId = $this.attr('id');
      
      //console.log('Processing element:', elementType, 'with ID:', elementId);
      
      // Initialize based on element type
      if (['client_select', 'sample_type_select', 'store_select', 'standard_select', 'sample_condition_select'].includes(elementType)) {
        // Independent elements - data is already loaded statically, just initialize Select2
        //console.log('Independent element with static data:', elementType, elementId);
      } else if (['client_unit_select', 'client_contact_select'].includes(elementType)) {
        // Set up dependent elements - these depend on client_select
        setupDependentElement($this, elementId, elementType, 'client_select');
      } else if (elementType === 'sample_point_select') {
        // This depends on client_unit_select
        setupDependentElement($this, elementId, elementType, 'client_unit_select');
      } else if (elementType === 'analysis_type_select') {
        // This depends on sample_type_select
        setupDependentElement($this, elementId, elementType, 'sample_type_select');
      } else if (elementType === 'analysis_elements_select') {
        // This depends on analysis_type_select
        setupDependentElement($this, elementId, elementType, 'analysis_type_select');
      } else if (elementType === 'store_slot_select') {
        // This depends on store_select
        setupDependentElement($this, elementId, elementType, 'store_select');
      }
    });
  }

  function setupDependentElement($this, elementId, elementType, dependsOn) {
    // Find the dependency element in the same row first
    const $row = $this.parents('tr');
    const dependsOnElement = $row.find(`[data-element-type="${dependsOn}"]`);
    
    //console.log('Setting up dependency:', elementType, 'depends on:', dependsOn, 'in row:', row);
    //console.log('Found parent element:', dependsOnElement);
    
    if (dependsOnElement.length > 0) {
      //console.log('Parent element found, setting up change handler');
      // Check if parent already has a value and load options immediately
      const currentParentValue = dependsOnElement.val();
      if (currentParentValue) {
        //console.log('Parent already has value:', currentParentValue, 'loading options for:', elementType);
        // Load options based on current parent value
        if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
          loadDynamicOptions($this, elementId, currentParentValue);
        } else if (elementType === 'sample_point_select') {
          loadDynamicOptions($this, elementId, elementType, null, null, null, currentParentValue);
        } else if (elementType === 'analysis_type_select') {
          loadDynamicOptions($this, elementId, elementType, null, currentParentValue);
        } else if (elementType === 'analysis_elements_select') {
          loadDynamicOptions($this, elementId, elementType, null, null, null, null, currentParentValue);
        } else if (elementType === 'store_slot_select') {
          loadDynamicOptions($this, elementId, elementType, null, null, currentParentValue);
        } else {
          loadDynamicOptions($this, elementId, elementType, currentParentValue);
        }
      }
      
      // Set up change handler for same-row dependency
      dependsOnElement.on('change', function() {
        const parentId = $(this).val();
        //console.log('Parent element changed:', dependsOn, 'new value:', parentId);
        if (parentId) {
          // Handle different parameter types based on element type and dependency
          if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
            // These depend on client_select
            loadDynamicOptions($this, elementId, elementType, parentId);
          } else if (elementType === 'sample_point_select') {
            // This depends on client_unit_select
            loadDynamicOptions($this, elementId, elementType, null, null, null, parentId);
          } else if (elementType === 'analysis_type_select') {
            // This depends on sample_type_select
            loadDynamicOptions($this, elementId, elementType, null, parentId);
          } else if (elementType === 'analysis_elements_select') {
            // This depends on analysis_type_select
            loadDynamicOptions($this, elementId, elementType, null, null, null, null, parentId);
          } else if (elementType === 'store_slot_select') {
            // This depends on store_select
            loadDynamicOptions($this, elementId, elementType, null, null, parentId);
          } else {
            // Default case
            loadDynamicOptions($this, elementId, elementType, parentId);
          }
        } else {
          // Clear dependent element and all its children
          clearDependentElementAndChildren($this);
        }
      });
    } else {
      // Fall back to global dependency
      const globalDependsOnElement = $(document).find(`[data-element-type="${dependsOn}"]`);
      if (globalDependsOnElement) {
        // alert('Sample point select found: ' + parentId);
        globalDependsOnElement.on('change', function() {
          const parentId = $(this).val();
          if (parentId) {
            // Handle different parameter types based on element type and dependency
            if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
              // These depend on client_select
              loadDynamicOptions($this, elementId, elementType, parentId);
            } else if (elementType === 'sample_point_select') {
              // This depends on client_unit_select
              loadDynamicOptions($this, elementId, elementType, null, null, null, parentId);
            } else if (elementType === 'analysis_type_select') {
              // This depends on sample_type_select
              loadDynamicOptions($this, elementId, elementType, null, parentId);
            } else if (elementType === 'analysis_elements_select') {
              // This depends on analysis_type_select
              loadDynamicOptions($this, elementId, elementType, null, null, null, null, parentId);
            } else if (elementType === 'store_slot_select') {
              // This depends on store_select
              loadDynamicOptions($this, elementId, elementType, null, null, parentId);
            } else {
              // Default case
              loadDynamicOptions($this, elementId, elementType, parentId);
            }
          } else {
            // Clear dependent element and all its children
            clearDependentElementAndChildren($this);
          }
        });
      }
    }
  }

  function clearDependentElementsInRow($rowElement) {
    // Clear dependent elements that should be empty in cloned rows
    const dependentElements = $rowElement.find('[data-element-type="client_unit_select"], [data-element-type="client_contact_select"], [data-element-type="sample_point_select"], [data-element-type="analysis_type_select"], [data-element-type="analysis_elements_select"], [data-element-type="store_slot_select"]');
    
    dependentElements.each(function() {
      const $element = $(this);
      if ($element.is('select')) {

        $element.html('<option value="">Select...</option>');
        $element.val('');
      } else {
        $element.val('');
      }
    });
  }

  function clearDependentElementAndChildren($this) {
    const $element = $this;
    if (!$element) return;
    
    // Clear the element itself
    if ($element.is('select')) {
      $element.html('<option value="">Select...</option>');
      $element.val('');
    } else {
      $element.val('');
    }
    
    // Find the row containing this element
    const $row = $this.parents('tr');
    if (!($row.length > 0)) return;
    
    // Clear all dependent elements that depend on this element
    const elementType = $this.attr('data-element-type');
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
      const dependentElements = $row.find(`[data-element-type="${depType}"]`);
      dependentElements.each(function() {
        const $depElement = $(this);
        if ($depElement.is('select')) {
          $depElement.html('<option value="">Select...</option>');
          $depElement.val('');
        } else {
          $depElement.val('');
        }
        
        // Recursively clear children of this dependent element
        clearDependentElementAndChildren($depElement);
      });
    });
  }

  // Clone row functionality
  $('body').on('click', '#rows-tbody-'+sectionId, function(e) {
    if ($(e.target).closest('.clone-row').length) {
      const $row = $(e.target).closest('tr');
      if (!($row.length > 0)) return;
      
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
        cloneRow($row);
      }
    } else if ($(e.target).closest('.delete-row').length) {
      const $row = $(e.target).closest('tr');
      deleteRow($row);
    }
  });

  function cloneRow($sourceRow) {
    //console.log(sourceRow);
    const newRow = $sourceRow.clone(true);
    // sourceRow is already a <tr> element, so newRow is also a <tr> element
    const $newRowElement = newRow;

    $sourceRow.each(function() {
      const $this = $(this);
      console.log($(this).val(), " ::::::::::::::::::::::::> ", $(this).attr('data-element-type'));
    });
    
    // Set new row index
    $newRowElement.attr('data-row-index', rowIndex);
    
    // Update field names and IDs
    const $inputs = $newRowElement.find('input, select, textarea');
    $inputs.each(function() {
      const $this = $(this);
      if ($this.attr('name')) {
        const currentIndex = $sourceRow.attr('data-row-index');
        $this.attr('name', $this.attr('name').replace(`[${currentIndex}]`, `[${rowIndex}]`));
      }
      if ($this.attr('id')) {
        const currentIndex = $sourceRow.attr('data-row-index');
        $this.attr('id', $this.attr('id').replace(`_${currentIndex}`, `_${rowIndex}`));
      }
      // Clear values for dependent elements to avoid duplication
      const elementType = $this.attr('data-element-type');
      
      // Ensure cloned elements are editable (remove disabled attribute)
      $this.removeAttr('disabled');
      $this.removeAttr('readonly');
    });
    
    // Update labels
    const $labels = $newRowElement.find('label');
    $labels.each(function() {
      const $this = $(this);
      if ($this.attr('for')) {
        const currentIndex = $sourceRow.attr('data-row-index');
        $this.attr('for', $this.attr('for').replace(`_${currentIndex}`, `_${rowIndex}`));
      }
    });
    
    // Insert after source row
    $sourceRow.after($newRowElement);
    rowIndex++;
    
    // Ensure all form elements are properly enabled and editable
    const $allFormElements = $newRowElement.find('input, select, textarea, button');
    $allFormElements.each(function() {
      const $element = $(this);
      $element.removeAttr('disabled');
      $element.removeAttr('readonly');
      // Ensure the element is not in a disabled state
      if ($element.is('button') && $element.hasClass('disabled')) {
        $element.removeClass('disabled');
      }
    });
    
    $newRowElement.find('select').not('.hidden').each(function(i, e) {
      let parentTD = $(e).closest('td');
      let clonedSelect = parentTD.find('select').clone();
      parentTD.html('');

      parentTD.html(clonedSelect[0].outerHTML);

      if (!parentTD.find('select').hasClass('no-select2')) {
        parentTD.find('select').select2({
          placeholder: parentTD.find('select').attr('placeholder') || parentTD.find('select').data('placeholder') || 'Select...'
        });
        parentTD.find('select').attr('style', 'width: 100%');
      }
    });

    //  // Clear dependent elements that should be empty in cloned rows first
    // clearDependentElementsInRow($newRowElement);
    
    // // Initialize custom elements for the cloned row (this will set up dependencies)
    initializeRowCustomElements($newRowElement, true);
  }

  function deleteRow($row) {
    if (confirm('Are you sure you want to delete this row?')) {
      $row.remove();
    }
  }

  // Load existing data on page load
  loadExistingData();
  
  // Add initial row if none exist
  if ($('body').find('#rows-tbody-'+sectionId).children().length === 0) {
    addNewRow();
  }
  
  function loadExistingData() {
    @if(isset($existingValues) && $existingValues)
      @php
        // Group existing values by array index for this section
        $sectionValues = [];
        $templateHolder = $section->getTemplateElementHolder();
        if ($templateHolder) {
          foreach($templateHolder->elements as $element) {
            $elementValues = $existingValues->where('submission_form_element_id', $element->id);
            foreach($elementValues as $value) {
              $arrayIndex = $value->array_index ?? 0;
              if (!isset($sectionValues[$arrayIndex])) {
                $sectionValues[$arrayIndex] = [];
              }
              $sectionValues[$arrayIndex][$element->id] = $value;
            }
          }
        }
      @endphp
      
      @if(count($sectionValues) > 0)
        
        @foreach($sectionValues as $arrayIdx => $rowValues)
          (function() {
            // Create row for existing data
            const existingRow = template.content.cloneNode(true);
            const existingRowElement = existingRow.querySelector('tr');
            const $existingRowElement = $(existingRowElement);
            
            const currentRowIndex = {{ $arrayIdx }};
            
            // Set row index
            $existingRowElement.attr('data-row-index', currentRowIndex);
            
            // Update field names and IDs
            const inputs = $existingRowElement.find('input, select, textarea');
            inputs.each(function() {
              const $input = $(this);
              const elementType = $input.attr('data-element-type');
              
              if ($input.attr('name')) {
                $input.attr('name', $input.attr('name').replace('ROW_INDEX_PLACEHOLDER', currentRowIndex));
              }
              if ($input.attr('id')) {
                $input.attr('id', $input.attr('id').replace('ROW_INDEX_PLACEHOLDER', currentRowIndex));
              }
              
              // Get the element ID for this input
              const elementName = $input.attr('name') ? $input.attr('name').split('[')[0] : null;
              
              // Set existing values for each element
              @foreach($rowValues as $elementId => $value)
                @php
                  $element = $templateHolder->elements->find($elementId);
                  $elementName = $element ? $element->name : '';
                  $savedValue = $value->value;
                @endphp
                
                if (elementName === '{{ $elementName }}') {
                  const savedValue = '{{ addslashes($savedValue) }}';
                  
                  if ($input.attr('type') === 'checkbox') {
                    $input.prop('checked', savedValue === '1' || savedValue === 'true');
                  } else if ($input.attr('type') === 'radio') {
                    if ($input.val() === savedValue) {
                      $input.prop('checked', true);
                    }
                  } else if ($input.is('select')) {
                    // For select elements, store the value to be set after options are loaded
                    if ($input.prop('multiple')) {
                      // Handle multiple select
                      $input.attr('data-saved-multiple-values', savedValue);
                    } else {
                      // Handle single select
                      $input.attr('data-saved-value', savedValue);
                    }
                    
                    // If it's a non-dependent select with static options, set value immediately
                    if (!elementType || ['client_select', 'sample_type_select', 'store_select', 'standard_select', 'sample_condition_select'].includes(elementType)) {
                      // Check if option exists and set it
                      if ($input.prop('multiple')) {
                        const values = savedValue.split(',').map(v => v.trim()).filter(v => v);
                        $input.val(values);
                      } else if ($input.find('option[value="' + savedValue + '"]').length > 0) {
                        $input.val(savedValue);
                      }
                    }
                  } else {
                    $input.val(savedValue);
                  }
                }
              @endforeach
            });
            
            // Update labels
            const labels = $existingRowElement.find('label');
            labels.each(function() {
              const $label = $(this);
              if ($label.attr('for')) {
                $label.attr('for', $label.attr('for').replace('ROW_INDEX_PLACEHOLDER', currentRowIndex));
              }
            });
            
            // Append to tbody
            $('#rows-tbody-' + sectionId).append($existingRowElement);
            
            // Initialize custom elements and Select2 for existing row
            setTimeout(() => {
              
              // First initialize custom elements (this will load dependent options)
              initializeRowCustomElements($existingRowElement);
              
              // Then set saved values for dependent selects after options are loaded
              setTimeout(() => {
                $existingRowElement.find('select[data-saved-value], select[data-saved-multiple-values]').each(function() {
                  const $select = $(this);
                  
                  if ($select.prop('multiple')) {
                    const savedValues = $select.attr('data-saved-multiple-values');
                    if (savedValues) {
                      const values = savedValues.split(',').map(v => v.trim()).filter(v => v);
                      $select.val(values);
                    }
                  } else {
                    const savedValue = $select.attr('data-saved-value');
                    if (savedValue) {
                      $select.val(savedValue);
                    }
                  }
                });
                
                // Initialize Select2 on all select elements
                $existingRowElement.find('select').not('.hidden').each(function(i, e) {
                  const $select = $(e);
                  
                  // Destroy existing Select2 if present
                  if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                  }
                  
                  if (!$select.hasClass('no-select2')) {
                    // Get the saved value before initializing Select2
                    const currentValue = $select.val();
                    
                    $select.select2({
                      placeholder: $select.attr('placeholder') || $select.data('placeholder') || 'Select...',
                      width: '100%'
                    });
                    
                    // Restore the value after Select2 initialization
                    if (currentValue) {
                      $select.val(currentValue).trigger('change.select2');
                    }
                  }
                });
              }, 500); // Give time for dependent options to load
            }, 100);
            
            // Update global rowIndex to be higher than the current index
            if (currentRowIndex >= rowIndex) {
              rowIndex = currentRowIndex + 1;
            }
          })();
        @endforeach
        
        console.log('Finished loading existing rows, next rowIndex:', rowIndex);
      @endif
    @endif
  }
});
</script>

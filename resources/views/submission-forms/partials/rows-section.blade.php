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
      @php
        // Count actual rows from existing data
        $actualRowCount = 0;
        if (isset($existingValues) && $existingValues) {
          $rowIndices = [];
          foreach($templateHolder->elements as $element) {
            $elementValues = $existingValues->where('submission_form_element_id', $element->id);
            foreach($elementValues as $value) {
              $arrayIndex = $value->array_index ?? 0;
              $rowIndices[$arrayIndex] = true;
            }
          }
          $actualRowCount = count($rowIndices);
        }
      @endphp
      Template Elements: {{ $templateHolder->elements->count() }} | Actual Rows: {{ $actualRowCount }}
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
                            'rowIndex' => 'ROW_INDEX_PLACEHOLDER',
                            'hideLabel' => true,
                            'allowedSampleTypeIds' => $allowedSampleTypeIds ?? null
                        ])
              </div>
            </td>
          @endforeach
          <td>
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-primary btn-sm clone-row mr-2" title="Clone Row">
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
    </div>
  @endif

  {{-- Clone Row Modal --}}
  <div class="modal fade" id="clone-modal-{{ $section->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Clone Row</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="clone-count-{{ $section->id }}">Number of copies:</label>
            <input type="number" class="form-control" id="clone-count-{{ $section->id }}" value="1" min="1" max="50">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirm-clone-{{ $section->id }}">Clone</button>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
// Wait for jQuery before initializing rows section
(function initRowsSection_{{ $section->id }}() {
    if (typeof $ === 'undefined') {
        setTimeout(initRowsSection_{{ $section->id }}, 50);
        return;
    }
    
$(document).ready(function() {
  const sectionId = {{ $section->id }};
  console.log('Rows initialized for section ' + sectionId);
  // Uncomment the next line to debug if the script is running
  // alert('Rows initialized for section ' + sectionId);

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

    // Get the client_unit value - check both in rows and in the main form
    let previousClientUnitId = null;
    
    // First, try to get from the previous row (if client_unit is in the rows)
    const $previousRow = $('#rows-tbody-'+sectionId).find('tr:last');
    if ($previousRow.length > 0) {
      const $previousClientUnit = $previousRow.find('[data-element-type="client_unit_select"]');
      if ($previousClientUnit.length > 0) {
        previousClientUnitId = $previousClientUnit.val();
        console.log('Found client_unit_id from previous row:', previousClientUnitId);
      }
    }
    
    // If not found in row, check the main form (client_unit might be in a regular section)
    if (!previousClientUnitId) {
      const $formClientUnit = $('select[data-element-type="client_unit_select"]');
      if ($formClientUnit.length > 0) {
        previousClientUnitId = $formClientUnit.first().val();
        console.log('Found client_unit_id from main form:', previousClientUnitId);
      } else {
        console.log('No client_unit_select found in form or rows');
      }
    }
    
    $(document).find('#rows-tbody-'+sectionId).append($rowElement);
    rowIndex++;
    
    // Initialize custom elements for the new row
    initializeRowCustomElements($rowElement);
    
    // Re-initialize global change handlers to include new elements
    if (typeof setupClientChangeHandlers === 'function') {
      setupClientChangeHandlers();
    }
    if (typeof setupClientUnitChangeHandlers === 'function') {
      setupClientUnitChangeHandlers();
    }
    if (typeof setupSampleTypeChangeHandlers === 'function') {
      setupSampleTypeChangeHandlers();
    }
    if (typeof setupAnalysisTypeChangeHandlers === 'function') {
      setupAnalysisTypeChangeHandlers();
    }
    if (typeof setupStoreChangeHandlers === 'function') {
      setupStoreChangeHandlers();
    }
    
    // Initialize Select2 on all select elements in the new row
    $rowElement.find('select').not('.hidden').each(function(i, e) {
      if (!$(e).hasClass('no-select2')) {
        $(e).select2({
          placeholder: $(e).attr('placeholder') || $(e).data('placeholder') || 'Select...'
        });
        $(e).attr('style', 'width: 100%');
        
        // Ensure Select2 change events trigger regular change events for form tracking
        $(e).on('select2:select select2:unselect', function() {
          $(this).trigger('change');
        });
      }
    });
    
    // Update form progress after adding new row
    if (typeof FormFill !== 'undefined' && FormFill.updateProgress) {
      FormFill.updateProgress();
    }
    
    // Trigger change event on the form to update submit button state
    $('#fill-form').trigger('change');
    
    // If we have a client_unit from previous row, automatically load dependent selects for new row
    if (previousClientUnitId) {
      const $newRowSamplePoints = $rowElement.find('[data-element-type="sample_point_select"]');
      if ($newRowSamplePoints.length > 0) {
        const samplePointElementId = $newRowSamplePoints.attr('id');
        
        console.log('=== ADD NEW ROW: AUTO-LOADING SAMPLE POINTS ===');
        console.log('Previous Row Client Unit ID:', previousClientUnitId);
        console.log('New Row Sample Points Element ID:', samplePointElementId);
        console.log('Calling loadDynamicOptions...');
        
        // Load sample points immediately using the previous row's client_unit
        loadDynamicOptions($newRowSamplePoints, samplePointElementId, 'sample_point_select', null, null, null, previousClientUnitId);
      } else {
        console.warn('=== ADD NEW ROW: NO SAMPLE POINTS ELEMENT FOUND ===');
      }

      const $newRowCompanySubUnits = $rowElement.find('[data-element-type="company_sub_unit_select"]');
      if ($newRowCompanySubUnits.length > 0) {
        const companySubUnitElementId = $newRowCompanySubUnits.attr('id');
        loadDynamicOptions($newRowCompanySubUnits, companySubUnitElementId, 'company_sub_unit_select', null, null, null, previousClientUnitId);
      }
    } else {
      console.warn('=== ADD NEW ROW: NO PREVIOUS CLIENT UNIT ID FOUND ===');
    }
    
    // After initializing Select2, check for pre-selected parents and load dependent options
    $rowElement.find('[data-element-type="client_select"]').each(function() {
      const $parentSelect = $(this);
      if ($parentSelect.val()) {
        $parentSelect.trigger('change.custom-elements');
      }
    });
    
    $rowElement.find('[data-element-type="client_unit_select"]').each(function() {
      const $parentSelect = $(this);
      if ($parentSelect.val()) {
        $parentSelect.trigger('change.custom-elements');
      }
    });
    
    $rowElement.find('[data-element-type="sample_type_select"]').each(function() {
      const $parentSelect = $(this);
      if ($parentSelect.val()) {
        $parentSelect.trigger('change.custom-elements');
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
      } else if (['client_unit_select', 'client_contact_select', 'client_submission_officers_select'].includes(elementType)) {
        // Set up dependent elements - these depend on client_select
        setupDependentElement($this, elementId, elementType, 'client_select');
      } else if (elementType === 'sample_point_select') {
        // This depends on client_unit_select
        setupDependentElement($this, elementId, elementType, 'client_unit_select');
      } else if (elementType === 'company_sub_unit_select') {
        // This depends on client_unit_select but should also load when none selected
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
    
    if (dependsOnElement.length > 0) {
      // Check if parent already has a value and load options immediately
      const currentParentValue = dependsOnElement.val();
      if (currentParentValue) {
        // Load options based on current parent value
        if (elementType === 'client_unit_select' || elementType === 'client_contact_select' || elementType === 'client_submission_officers_select') {
          loadDynamicOptions($this, elementId, elementType, currentParentValue);
        } else if (elementType === 'sample_point_select') {
          loadDynamicOptions($this, elementId, elementType, null, null, null, currentParentValue);
        } else if (elementType === 'company_sub_unit_select') {
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
      } else if (elementType === 'company_sub_unit_select') {
        // Load all company sub units when no parent is selected
        loadDynamicOptions($this, elementId, elementType, null, null, null, null);
      }
      
      // Set up change handler for same-row dependency
      // Remove any existing handlers first to avoid duplicates
      dependsOnElement.off('change.row-dependency').on('change.row-dependency', function() {
        const parentId = $(this).val();
        if (parentId) {
          // Handle different parameter types based on element type and dependency
        if (elementType === 'client_unit_select' || elementType === 'client_contact_select' || elementType === 'client_submission_officers_select') {
            loadDynamicOptions($this, elementId, elementType, parentId);
        } else if (elementType === 'sample_point_select') {
          loadDynamicOptions($this, elementId, elementType, null, null, null, parentId);
        } else if (elementType === 'company_sub_unit_select') {
            loadDynamicOptions($this, elementId, elementType, null, null, null, parentId);
          } else if (elementType === 'analysis_type_select') {
            loadDynamicOptions($this, elementId, elementType, null, parentId);
          } else if (elementType === 'analysis_elements_select') {
            loadDynamicOptions($this, elementId, elementType, null, null, null, null, parentId);
          } else if (elementType === 'store_slot_select') {
            loadDynamicOptions($this, elementId, elementType, null, null, parentId);
          } else {
            loadDynamicOptions($this, elementId, elementType, parentId);
          }
        } else {
          if (elementType === 'company_sub_unit_select') {
            // Reload all options when parent cleared
            loadDynamicOptions($this, elementId, elementType, null, null, null, null);
          } else {
            // Clear dependent element and all its children
            clearDependentElementAndChildren($this);
          }
        }
      });
    } else {
      // Fall back to global dependency
      const globalDependsOnElement = $(document).find(`[data-element-type="${dependsOn}"]`);
      if (globalDependsOnElement.length > 0) {
        const handlerNamespace = 'change.global-dependency-' + elementId;

        const handleGlobalDependencyChange = function(parentId) {
          if (parentId) {
            // Handle different parameter types based on element type and dependency
            if (elementType === 'client_unit_select' || elementType === 'client_contact_select') {
              loadDynamicOptions($this, elementId, elementType, parentId);
            } else if (elementType === 'sample_point_select') {
              loadDynamicOptions($this, elementId, elementType, null, null, null, parentId);
            } else if (elementType === 'company_sub_unit_select') {
              loadDynamicOptions($this, elementId, elementType, null, null, null, parentId);
            } else if (elementType === 'analysis_type_select') {
              loadDynamicOptions($this, elementId, elementType, null, parentId);
            } else if (elementType === 'analysis_elements_select') {
              loadDynamicOptions($this, elementId, elementType, null, null, null, null, parentId);
            } else if (elementType === 'store_slot_select') {
              loadDynamicOptions($this, elementId, elementType, null, null, parentId);
            } else {
              loadDynamicOptions($this, elementId, elementType, parentId);
            }
          } else {
            if (elementType === 'company_sub_unit_select') {
              loadDynamicOptions($this, elementId, elementType, null, null, null, null);
            } else {
              // Clear dependent element and all its children
              clearDependentElementAndChildren($this);
            }
          }
        };

        // Remove any existing handlers first to avoid duplicates
        globalDependsOnElement.off(handlerNamespace).on(handlerNamespace, function() {
          handleGlobalDependencyChange($(this).val());
        });

        // Immediately load options if the global dependency already has a value
        const initialParentValue = globalDependsOnElement.first().val();
        handleGlobalDependencyChange(initialParentValue);
      }
    }
  }

  function clearDependentElementsInRow($rowElement) {
    // Clear dependent elements that should be empty in cloned rows
    const dependentElements = $rowElement.find('[data-element-type="client_unit_select"], [data-element-type="client_contact_select"], [data-element-type="client_submission_officers_select"], [data-element-type="sample_point_select"], [data-element-type="analysis_type_select"], [data-element-type="analysis_elements_select"], [data-element-type="store_slot_select"]');
    
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
      dependentTypes = ['client_unit_select', 'client_contact_select', 'client_submission_officers_select'];
    } else if (elementType === 'client_unit_select') {
      dependentTypes = ['sample_point_select', 'company_sub_unit_select'];
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
  // Clone row functionality
  $(document).on('click', '#rows-tbody-' + sectionId + ' .clone-row', function(e) {
    e.preventDefault();
    console.log('Clone button clicked for section ' + sectionId);
    
    // Explicitly find the row
    const $row = $(this).closest('tr');
    
    if ($row.length === 0) {
        console.error('Could not find row for clone button');
        alert('Error: Could not find row to clone.');
        return;
    }
    
    
    // Open modal instead of prompt
    const $modal = $('#clone-modal-' + sectionId);
    
    // Store the source row on the modal for retrieval later
    $modal.data('source-row', $row);
    
    // Reset input to 1
    $('#clone-count-' + sectionId).val(1);
    
    // Show modal
    $modal.modal('show');
  });

  // Modal Confirm Handler
  $(document).on('click', '#confirm-clone-' + sectionId, function() {
    const $modal = $('#clone-modal-' + sectionId);
    const $row = $modal.data('source-row');
    
    if (!$row || $row.length === 0) {
        alert('Error: Source row lost.');
        $modal.modal('hide');
        return;
    }

    const numClones = parseInt($('#clone-count-' + sectionId).val());
    
    if (isNaN(numClones) || numClones < 1 || numClones > 50) {
        alert('Please enter a valid number between 1 and 50.');
        return;
    }
    
    // Create clones
    try {
        console.log('Starting clone loop for ' + numClones + ' copies');
        for (let i = 0; i < numClones; i++) {
            console.log('Creating clone ' + (i + 1));
            cloneRow($row);
        }
    } catch (err) {
        console.error('Error during cloning:', err);
        alert('An error occurred while cloning: ' + err.message);
    } finally {
        // Hide modal regardless of success or failure
        $modal.modal('hide');
        $('.modal-backdrop').remove(); // Force remove backdrop if it gets stuck
    }
  });

  // Delete row functionality
  $(document).on('click', '#rows-tbody-' + sectionId + ' .delete-row', function(e) {
    e.preventDefault();
    const $row = $(this).closest('tr');
    deleteRow($row);
  });

  function cloneRow($sourceRow) {
    const newRow = $sourceRow.clone(true);
    const $newRowElement = newRow;
    
    // Set new row index
    $newRowElement.attr('data-row-index', rowIndex);
    
    // First, iterate through all selects in the source row and copy their options HTML to the new row
    // This preserves dynamically loaded options that aren't in the template
    const $sourceSelects = $sourceRow.find('select');
    const $newSelects = $newRowElement.find('select');
    
    $sourceSelects.each(function(index) {
        if (index < $newSelects.length) {
            const $src = $(this);
            const $dst = $newSelects.eq(index);
            // Copy innerHTML to preserve options
            $dst.html($src.html());
            // Store value to set later
            $dst.data('cloned-value', $src.val());
        }
    });

    // Update field names, IDs, and labels
    const $inputs = $newRowElement.find('input, select, textarea');
    $inputs.each(function() {
      const $this = $(this);
      const currentIndex = $sourceRow.attr('data-row-index');
      
      if ($this.attr('name')) {
        $this.attr('name', $this.attr('name').replace(`[${currentIndex}]`, `[${rowIndex}]`));
      }
      if ($this.attr('id')) {
        $this.attr('id', $this.attr('id').replace(`_${currentIndex}`, `_${rowIndex}`));
      }
      
      // Ensure cloned elements are editable
      $this.removeAttr('disabled');
      $this.removeAttr('readonly');
      
      // Explicitly remove Select2 ID data
      $this.removeData('select2-id');
      $this.removeData('select2');
    });
    
    const $labels = $newRowElement.find('label');
    $labels.each(function() {
      const $this = $(this);
      const currentIndex = $sourceRow.attr('data-row-index');
      if ($this.attr('for')) {
        $this.attr('for', $this.attr('for').replace(`_${currentIndex}`, `_${rowIndex}`));
      }
    });

    // Clean up Select2 artifacts BEFORE appending to DOM
    $newRowElement.find('.select2-container').remove();
    $newRowElement.find('select').each(function() {
      const $select = $(this);
      // Remove all Select2 classes and attributes
      $select.removeClass('select2-hidden-accessible');
      $select.removeAttr('data-select2-id');
      $select.removeAttr('tabindex');
      $select.removeAttr('aria-hidden');
      $select.find('option').removeAttr('data-select2-id');
      $select.show(); // Ensure visibility
    });
    
    // Insert after source row
    $sourceRow.after($newRowElement);
    rowIndex++;
    
    // Now verify and set values from source using robust matching
    // We match elements by their 'data-element-type' or index if type is missing
    const $sourceAllFields = $sourceRow.find('input, textarea, select');
    const $newAllFields = $newRowElement.find('input, textarea, select');
    
    $sourceAllFields.each(function(i, sourceEl) {
        const $src = $(sourceEl);
        const elementType = $src.attr('data-element-type');
        let $dst = null;
        
        // Try to find the corresponding element in the new row
        if (elementType) {
            $dst = $newRowElement.find(`[data-element-type="${elementType}"]`);
            // If multiple elements with same type, fallback to index within that type
            if ($dst.length > 1) {
                 const typeIndex = $sourceRow.find(`[data-element-type="${elementType}"]`).index($src);
                 $dst = $dst.eq(typeIndex);
            }
        } else {
            // Fallback to sequential index
             $dst = $newAllFields.eq(i);
        }

        if ($dst && $dst.length > 0) {
            // console.log(`Copying value for ${elementType || 'unknown'}:`, $src.val());
            
            if ($src.is('select')) {
                 // For selects, value will be set later via data-cloned-value to handle Select2
                 $dst.data('cloned-value', $src.val());
            } else if ($src.attr('type') === 'checkbox' || $src.attr('type') === 'radio') {
                $dst.prop('checked', $src.prop('checked'));
            } else {
                $dst.val($src.val());
            }
        }
    });

    // Re-initialize Select2 and set values for selects
    $newRowElement.find('select').not('.hidden').each(function() {
       const $select = $(this);
       const clonedValue = $select.data('cloned-value');
       
       if (!$select.hasClass('no-select2')) {
        $select.select2({
          placeholder: $select.attr('placeholder') || $select.data('placeholder') || 'Select...',
          width: '100%'
        });
        
        $select.on('select2:select select2:unselect', function() {
          $(this).trigger('change');
        });
       }
       
       // Set the value!
       if (clonedValue) {
           // Ensure the option exists and is selected in the DOM to help Select2 pick it up
           if ($select.find('option[value="' + clonedValue + '"]').length > 0) {
               $select.val(clonedValue);
           }
           $select.trigger('change.select2');
           
           // Double check for Company Unit or specific fields that might be stubborn
           if ($select.data('element-type') === 'client_unit_select' || $select.data('element-type') === 'analysis_type_select') {
               console.log('Force setting value for ' + $select.data('element-type') + ' to ' + clonedValue);
               $select.val(clonedValue).trigger('change');
           }
       }
    });

    // Re-apply values one last time after a short delay to override any auto-clearing by dependencies
    setTimeout(function() {
        $newRowElement.find('select').each(function(index) {
            const $s = $(this);
            const v = $s.data('cloned-value');
            
            // Re-copy options if they were wiped by dependent logic
            if ($s.children('option').length <= 1 && $sourceSelects.eq(index).children('option').length > 1) {
                console.log('Restoring options for ' + $s.attr('name'));
                // Destroy Select2 briefly to update DOM properly if needed, but usually html() works
                const $src = $sourceSelects.eq(index);
                $s.html($src.html());
            }

            if (v) {
                // Determine if we need to set the value
                const currentVal = $s.val();
                let needUpdate = false;
                
                if (Array.isArray(v)) {
                     // For arrays (multiple selects), simpler comparison
                     if (!currentVal || v.sort().toString() !== currentVal.sort().toString()) {
                         needUpdate = true;
                     }
                } else {
                     if (currentVal != v) {
                         needUpdate = true;
                     }
                }
                
                if (needUpdate) {
                    console.log('Restoring value for ' + $s.attr('name') + ' to ' + v);
                    $s.val(v).trigger('change.select2');
                }
            }
         });
    }, 800); // Increased delay slightly to ensures async clears have finished
    
    // Initialize custom elements (dependencies) but prevent them from wiping values
    // We pass a flag 'isCloned' = true to our custom init function if needed,
    // or we rely on the fact that we pre-filled the values so 'loadDynamicOptions' might respect them
    // However, existing 'initializeRowCustomElements' calls 'start from scratch' logic.
    // Let's modify 'initializeRowCustomElements' slightly or just manually attach handlers.
    
    // Actually, 'initializeRowCustomElements' sets up specific dependency listeners.
    // We WANT listeners, but we DON'T want immediate triggering of empty loads.
    initializeRowCustomElements($newRowElement, true);
    
    // Update progress and buttons
    if (typeof FormFill !== 'undefined' && FormFill.updateProgress) {
      FormFill.updateProgress();
    }
    $('#fill-form').trigger('change');
  }

  function deleteRow($row) {
    if (confirm('Are you sure you want to delete this row?')) {
      $row.remove();
      
      // Update form progress after deleting row
      if (typeof FormFill !== 'undefined' && FormFill.updateProgress) {
        FormFill.updateProgress();
      }
      
      // Trigger change event on the form to update submit button state
      $('#fill-form').trigger('change');
    }
  }

  // Load existing data on page load
  loadExistingData();

  // Update rowIndex to ensure it starts after the last existing row
  const existingRows = $(tbody).find('tr[data-row-index]');
  if (existingRows.length > 0) {
      let maxIndex = -1;
      existingRows.each(function() {
          const idx = parseInt($(this).attr('data-row-index'));
          if (!isNaN(idx) && idx > maxIndex) {
              maxIndex = idx;
          }
      });
      rowIndex = maxIndex + 1;
      console.log('Updated rowIndex based on existing data to:', rowIndex);
  }
  
  function loadExistingData() {
    @if(isset($existingValues) && $existingValues)
      @php
        // Group existing values by array index for this section
        $sectionValues = [];
        $templateHolder = $section->getTemplateElementHolder();
        if ($templateHolder) {
          echo "<!-- DEBUG: Processing " . $templateHolder->elements->count() . " template elements -->";
          foreach($templateHolder->elements as $element) {
            $elementValues = $existingValues->where('submission_form_element_id', $element->id);
            echo "<!-- DEBUG: Element " . $element->name . " (ID: " . $element->id . ") has " . $elementValues->count() . " values -->";
            foreach($elementValues as $value) {
              $arrayIndex = $value->array_index ?? 0;
              echo "<!-- DEBUG: Value for " . $element->name . " has array_index: " . $arrayIndex . " -->";
              if (!isset($sectionValues[$arrayIndex])) {
                $sectionValues[$arrayIndex] = [];
              }
              $sectionValues[$arrayIndex][$element->id] = $value;
            }
          }
        }
        
        // Debug: Show what sectionValues contains
        echo "<!-- DEBUG: sectionValues count: " . count($sectionValues) . " -->";
        foreach($sectionValues as $idx => $rowData) {
          echo "<!-- DEBUG: Row $idx has " . count($rowData) . " elements -->";
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
            rowIndex = Math.max(rowIndex, currentRowIndex + 1);
          })();
        @endforeach
        
        console.log('Finished loading existing rows, next rowIndex:', rowIndex);
      @endif
    @endif
    
    // Add initial row if none exist after loading existing data
    setTimeout(() => {
      if ($('#rows-tbody-'+sectionId).children().length === 0) {
        addNewRow();
      }
    }, 1000); // Give enough time for all existing rows to be loaded
  }
});
})(); // End initRowsSection wrapper
</script>

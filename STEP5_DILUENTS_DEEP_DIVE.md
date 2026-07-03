# Step 5: Diluents Workflow - Deep Dive

## Overview
Step 5 of the method sequence workflow manages the preparation and entry of diluent solutions used in analysis testing. This document provides a comprehensive analysis of how diluents are handled across the UI, JavaScript, and database layers.

---

## 1. Form UI Structure

### HTML Template Location
**File:** `resources/views/modules/lims/stages/includes/diluent-step-form.blade.php` (Lines 1336-1402)

### Form Fields (4 Main Entry Points)

```html
<!-- Diluent Name/Preparation Input -->
<input type="text" class="form-control" id="diluent_name" 
       placeholder="Enter diluent name/preparation">

<!-- Preparation Date -->
<input type="date" class="form-control" id="diluent_prep_date">

<!-- Result Nature Dropdown -->
<select class="form-control" id="diluent_result_nature">
  <option value="">Select Result Nature</option>
  <option value="positive_control">Positive Control</option>
  <option value="negative_control">Negative Control</option>
  <option value="blank_control">Blank Control</option>
</select>

<!-- Preparation Number -->
<input type="text" class="form-control" id="diluent_prep_number">
```

### Form Container
- **Grid Layout:** Uses Bootstrap `row` and `col-md-*` classes for responsive layout
- **Collapsible:** Form starts hidden and expands when user clicks "Add Diluent" button
- **Button Layout:** Add button, Cancel button aligned horizontally
- **Placeholder:** Message displayed when no diluents entered yet

---

## 1.5 Complete Step 5 UI Code

### Full HTML Template
**Location:** `public/js/method-sequences.js` (Lines 1336-1402)

```html
<!-- Step 5: Diluents -->
<div class="step-pane" id="step-pane-5-${track.id}">
    <h4 class="step-title mb-1">Uninoculated Diluents</h4>
    <p class="step-description mb-4">Document preparation and verification details for uninoculated diluents, aligned with media entry style.</p>

    <!-- Diluent Cards Grid Container -->
    <div class="row mb-4" id="diluent-cards-grid-${track.id}">
        <!-- Diluent cards will be rendered here -->
        <div class="col-md-6 mb-3 diluent-placeholder-wrapper">
            <div class="placeholder-card-dashed">
                <div class="placeholder-icon-container">
                    <i class="mdi mdi-beaker-outline"></i>
                </div>
                <div class="placeholder-text-main">Waiting for additional diluent data</div>
                <div class="placeholder-text-sub">Add diluent using the form below</div>
            </div>
        </div>
    </div>

    <!-- Diluent Entry Panel (Collapsible) -->
    <div class="media-registration-panel p-0 overflow-hidden">
        <!-- Panel Header (Toggle) -->
        <div class="media-registration-title diluent-entry-header p-4" data-track-id="${track.id}" role="button" aria-expanded="false" style="cursor: pointer;">
            <span>Add New Diluent Entry</span>
            <i class="mdi mdi-chevron-right text-muted diluent-entry-toggle-icon ml-auto"></i>
        </div>

        <!-- Panel Body (Form Inputs) -->
        <div id="diluent-entry-body-${track.id}" class="d-none px-4 pb-4">
            <div class="row">
                <!-- Diluent Name Dropdown -->
                <div class="col-md-4 mb-3">
                    <div class="premium-input-group">
                        <label class="form-label font-weight-bold">Diluent Name</label>
                        <select class="form-control premium-input" id="diluents-picker-${track.id}">
                            <option></option>
                        </select>
                    </div>
                </div>

                <!-- Preparation Date Input -->
                <div class="col-md-4 mb-3">
                    <div class="premium-input-group">
                        <label class="form-label font-weight-bold">Preparation Date</label>
                        <input type="date" class="form-control premium-input" id="diluent-prep-date-${track.id}">
                    </div>
                </div>

                <!-- Result Nature Dropdown -->
                <div class="col-md-4 mb-3">
                    <div class="premium-input-group">
                        <label class="form-label font-weight-bold">Result Nature</label>
                        <select class="form-control premium-input" id="diluent-result-nature-${track.id}">
                            <option value="no_result" selected>No Result</option>
                            <option value="qualitative">Qualitative</option>
                            <option value="quantitative">Quantitative</option>
                        </select>
                    </div>
                </div>

                <!-- Preparation Number Input -->
                <div class="col-md-12 mb-3">
                    <div class="premium-input-group">
                        <label class="form-label font-weight-bold">Preparation No.</label>
                        <input type="text" class="form-control premium-input" id="diluent-prep-no-${track.id}" placeholder="Enter preparation number">
                    </div>
                </div>
            </div>

            <!-- Add Diluent Button -->
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary px-4 py-2 font-weight-bold add-solution-item" data-track-id="${track.id}" data-type="diluents" style="background: #0061e0; border: none; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 97, 224, 0.2);">
                    <i class="mdi mdi-plus mr-1"></i> Add Diluent
                </button>
            </div>
        </div>
    </div>

    <!-- Stepper Navigation Buttons -->
    <div class="stepper-actions d-flex justify-content-between mt-4">
        <button type="button" class="btn btn-nav-back prev-step-btn" data-track-id="${track.id}" data-prev="4">
            <i class="mdi mdi-arrow-left mr-1"></i> Back to Media
        </button>
        <button type="button" class="btn btn-nav-continue px-5 py-2" onclick="methodSequences.saveStageDetails(${track.id})">
            Save Stage Details <i class="mdi mdi-content-save ml-1"></i>
        </button>
    </div>
</div>
```

### UI Components Breakdown

#### 1. Step Title & Description
```html
<h4 class="step-title mb-1">Uninoculated Diluents</h4>
<p class="step-description mb-4">Document preparation and verification details for uninoculated diluents, aligned with media entry style.</p>
```

#### 2. Placeholder Card (Initially Shown)
```html
<div class="col-md-6 mb-3 diluent-placeholder-wrapper">
    <div class="placeholder-card-dashed">
        <div class="placeholder-icon-container">
            <i class="mdi mdi-beaker-outline"></i>  <!-- Beaker icon -->
        </div>
        <div class="placeholder-text-main">Waiting for additional diluent data</div>
        <div class="placeholder-text-sub">Add diluent using the form below</div>
    </div>
</div>
```

#### 3. Collapsible Entry Panel Header (Toggle Button)
```html
<div class="media-registration-title diluent-entry-header p-4" data-track-id="${track.id}" role="button" aria-expanded="false" style="cursor: pointer;">
    <span>Add New Diluent Entry</span>
    <i class="mdi mdi-chevron-right text-muted diluent-entry-toggle-icon ml-auto"></i>
</div>
```

#### 4. Form Input Fields
- **Diluent Name:** Dropdown select (populated from LabSubCategory)
- **Preparation Date:** HTML5 date picker
- **Result Nature:** Dropdown (no_result, qualitative, quantitative)
- **Preparation No:** Text input

#### 5. Navigation Buttons
- **Back to Media:** Navigate to previous step (Step 4)
- **Save Stage Details:** Submit form and save to database

### Key CSS Classes Used

| Class | Purpose |
|-------|---------|
| `premium-input-group` | Wrapper for form fields with consistent styling |
| `premium-input` | Enhanced input/select field styling |
| `placeholder-card-dashed` | Dashed-border placeholder when no diluents exist |
| `media-registration-panel` | Container for collapsible panel |
| `diluent-entry-header` | Header/toggle for form section |
| `diluent-entry-body` | Body content of form (toggled) |
| `stepper-actions` | Navigation buttons container |

### Dynamic ID Generation

All form elements use template variables to ensure uniqueness per track:
- `step-pane-5-${track.id}` - Step container
- `diluents-picker-${track.id}` - Diluent name select
- `diluent-prep-date-${track.id}` - Date input
- `diluent-result-nature-${track.id}` - Result nature select
- `diluent-prep-no-${track.id}` - Prep number input
- `diluent-entry-body-${track.id}` - Form panel body
- `diluent-cards-grid-${track.id}` - Cards container

This ensures multiple runs can exist on the same page without element ID conflicts.

---

## 2. JavaScript Event Handlers

### Handler 1: Toggle Diluent Entry Form
**Location:** `public/js/method-sequences.js` (Lines 297-309)

```javascript
$(document).on('click', '.diluent-entry-header', function() {
    const trackId = $(this).data('track-id');
    const body = $(`#diluent-entry-body-${trackId}`);
    const icon = $(this).find('.diluent-entry-toggle-icon');
    const isHidden = body.hasClass('d-none');

    // Toggle the form visibility
    body.toggleClass('d-none');
    
    // Update ARIA attribute for accessibility
    $(this).attr('aria-expanded', isHidden ? 'true' : 'false');
    
    // Animate chevron icon rotation
    icon.toggleClass('mdi-chevron-down', isHidden);
    icon.toggleClass('mdi-chevron-right', !isHidden);

    // Auto-focus first input when opened
    if (isHidden) {
        const picker = $(`#diluents-picker-${trackId}`);
        if (picker.length) {
            picker.focus();
            // Populate picker options if empty
            if (picker.find('option').length <= 1) {
                loadDiluentOptions(trackId);
            }
        }
    }
});
```

**Purpose:**
- Toggles visibility of diluent form panel
- Manages ARIA attributes for accessibility
- Auto-focuses first input for UX
- Triggers lazy-loading of diluent options
- Rotates chevron icon to indicate state

---

### Handler 2: Add Diluent Click Handler
**Location:** `public/js/method-sequences.js` (Lines 452-495)

```javascript
$(document).on('click', '.add-solution-item', function() {
    const trackId = $(this).data('track-id');
    const type = $(this).data('type');
    
    if (type !== 'diluents') return;
    
    const $picker = $(`#diluents-picker-${trackId}`);
    const selectedId = $picker.val();
    const selectedText = $picker.find('option:selected').text();
    
    // 1. VALIDATION
    if (!selectedId) {
        alert('Please select a diluent');
        return false;
    }
    
    // 2. PREVENT DUPLICATES
    const gridContainer = $(`#diluent-cards-grid-${trackId}`);
    if (gridContainer.find(`.diluent-card-wrapper[data-id="${selectedId}"]`).length > 0) {
        alert('This diluent has already been added');
        $picker.val(null).trigger('change');
        return false;
    }
    
    // 3. COLLECT FORM VALUES
    const prepDate = $(`#diluent-prep-date-${trackId}`).val() || '';
    const prepNo = $(`#diluent-prep-no-${trackId}`).val() || '';
    const selectedResultNature = $(`#diluent-result-nature-${trackId}`).val() || '';
    
    // 4. RESOLVE RESULT NATURE (from config or user selection)
    let diluentsResultNature = selectedResultNature || 'No Result';
    const stage = window.methodSequences.getTrackById(trackId)?.test_stage;
    
    if (stage && stage.diluents_required) {
        let diluentConfig = stage.diluents_required;
        // Parse JSON if needed (handles nested JSON strings)
        if (typeof diluentConfig === 'string') {
            try { diluentConfig = JSON.parse(diluentConfig); } catch(e) {}
            if (typeof diluentConfig === 'string') {
                try { diluentConfig = JSON.parse(diluentConfig); } catch(e) {}
            }
        }
        
        // Find config for this diluent
        if (Array.isArray(diluentConfig)) {
            const config = diluentConfig.find(d => String(d.id) === String(selectedId));
            if (config && config.result_nature) {
                diluentsResultNature = config.result_nature;
            }
        }
    }
    
    // 5. RENDER CARD WITH DATA
    renderRow(trackId, 'diluents', {
        id: selectedId,
        preparation: prepDate,
        remark: prepNo
    }, selectedText, diluentsResultNature);
    
    // 6. CLEAR FORM FIELDS
    $(`#diluent-prep-date-${trackId}`).val('');
    $(`#diluent-prep-no-${trackId}`).val('');
    $(`#diluent-result-nature-${trackId}`).val('no_result');
    $picker.val(null).trigger('change');
    
    // 7. CLOSE FORM PANEL
    $(`#diluent-entry-body-${trackId}`).addClass('d-none');
    $(`.diluent-entry-header[data-track-id="${trackId}"]`).attr('aria-expanded', 'false');
    $(`.diluent-entry-header[data-track-id="${trackId}"]`)
        .find('.diluent-entry-toggle-icon')
        .removeClass('mdi-chevron-down')
        .addClass('mdi-chevron-right');
    
    // 8. AUTO-SAVE TO BACKEND
    window.methodSequences.scheduleStageAutoSave(trackId);
});
```

**Data Flow:**
```
User selects diluent → Click Add → Validate & prevent duplicates 
→ Resolve result nature from config → Render card → Clear form → Close panel → Auto-save
```

**Validation Steps:**
1. **Selection Required:** Must select diluent from dropdown
2. **Duplicate Prevention:** Check if diluent already added to this stage
3. **Config Resolution:** Lookup result_nature from test stage config if not user-selected
4. **Data Normalization:** Trim/cast all values to correct types

**Form Field Processing:**
| Field | Source | Processing |
|-------|--------|-----------|
| Diluent ID | Dropdown selection | Used as card identifier |
| Preparation Date | Date input | Displayed in card metadata |
| Prep Number | Text input | Stored as remark in hidden input |
| Result Nature | Config or user select | Used for badge display |

**Key Features:**
- Prevents adding same diluent multiple times to single stage
- Auto-resolves result_nature from test stage configuration
- Form auto-closes and clears for rapid entry UX
- Triggers compressed auto-save (400ms debounce)

---

### Handler 3: Delete Diluent Click Handler
**Location:** `public/js/method-sequences.js` (Lines 596-619)

```javascript
$(document).on('click', '.remove-solution-item', function(e) {
    e.preventDefault();
    
    const $button = $(this);
    const trackId = $button.data('track-id');
    const type = $button.data('type');
    
    if (type !== 'diluents') return;
    
    // 1. REMOVE CARD FROM DOM
    const grid = $button.closest(`#diluent-cards-grid-${trackId}`);
    $button.closest('.diluent-card-wrapper').remove();
    
    // 2. FIND REMAINING CARDS
    const count = grid.find('.diluent-card-wrapper').length;
    grid.find('.diluent-placeholder-wrapper').remove();
    
    // 3. SHOW PLACEHOLDER IF NO DILUENTS REMAIN
    if (count === 0 || count === 1) {
        grid.append(`
            <div class="col-md-6 mb-3 diluent-placeholder-wrapper">
                <div class="placeholder-card-dashed">
                    <div class="placeholder-icon-container">
                        <i class="mdi mdi-beaker-outline"></i>
                    </div>
                    <div class="placeholder-text-main">Waiting for additional diluent data</div>
                    <div class="placeholder-text-sub">Add diluent using the form below</div>
                </div>
            </div>
        `);
    }
    
    // 4. AUTO-SAVE TO BACKEND
    if (trackId) {
        window.methodSequences.scheduleStageAutoSave(trackId);
    }
});
```

**Deletion Sequence:**
1. **Remove from DOM:** Card fades out and is removed from grid
2. **Cleanup Placeholders:** Remove old placeholder if exists
3. **Show Empty State:** If no diluents remain, show placeholder with beaker icon
4. **Trigger Save:** Schedules debounced auto-save (400ms)

**User Feedback:**
- Immediate visual removal from grid
- Clear placeholder message when all items deleted
- No confirmation dialog (can be added if needed)
- Auto-save ensures backend is in sync

**Placeholder Display Logic:**
```
- 0 cards after delete → Show placeholder
- 1+ cards → Hide placeholder
- Placeholder uses beaker icon (mdi mdi-beaker-outline)
- Guides user to "Add diluent using the form below"
```

---

## 4. Card Rendering Function

### Function: renderRow()
**Location:** `public/js/method-sequences.js` (Lines 2254-2530)

Handles rendering of all solution type cards: equipment, media, controls, and diluents.

### Diluents Card Rendering (Type = 'diluents')

```javascript
if (type === 'diluents') {
    const gridContainer = $(`#diluent-cards-grid-${trackIdLocal}`);
    
    // 1. PREVENT DUPLICATES
    if (gridContainer.find(`.diluent-card-wrapper[data-id="${id}"]`).length > 0) {
        return; // Card already exists
    }
    
    // 2. EXTRACT METADATA FROM ITEM OBJECT
    const preparation = item && item.preparation ? String(item.preparation) : '';
    const prepNo = item && (item.remark || item.expiry) ? String(item.remark || item.expiry) : 'N/A';
    
    // 3. REMOVE PLACEHOLDER
    gridContainer.find('.diluent-placeholder-wrapper').remove();
    
    // 4. RENDER CARD HTML
    gridContainer.append(`
        <div class="col-md-6 mb-3 diluent-card-wrapper" data-id="${id}">
            <div class="media-card-premium d-flex flex-column">
                <!-- Header with Verified Badge & Delete Button -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="status-badge-verified">
                        <i class="mdi mdi-check-circle mr-1"></i> Verified
                    </div>
                    <button type="button" class="btn btn-link text-danger remove-solution-item p-0" 
                            data-track-id="${trackIdLocal}" data-type="diluents" data-id="${id}">
                        <i class="mdi mdi-delete-outline h5 mb-0"></i>
                    </button>
                </div>
                
                <!-- Diluent Name -->
                <div class="media-name-title mb-auto" title="${safeName}">
                    ${safeName}
                </div>
                
                <!-- Metadata Grid (3-column layout) -->
                <div class="media-meta-grid mt-3">
                    <!-- Prep Date Column -->
                    <div class="media-meta-item">
                        <span class="media-meta-label">Prep. Date:</span>
                        <span class="media-meta-value">${preparation || 'N/A'}</span>
                        <input type="hidden" class="solution-preparation" value="${preparation}">
                    </div>
                    <span class="media-meta-divider">|</span>
                    
                    <!-- Prep No Column -->
                    <div class="media-meta-item">
                        <span class="media-meta-label">Prep. No:</span>
                        <span class="media-meta-value">${prepNo}</span>
                        <input type="hidden" class="solution-remark" value="${prepNo}">
                    </div>
                    <span class="media-meta-divider">|</span>
                    
                    <!-- Result Nature Column (Badge display) -->
                    <div class="media-meta-item">
                        <span class="media-meta-label">Result Nature:</span>
                        <span class="media-meta-value">
                            ${resultNature === 'quantitative' ? 'Quantitative' : 
                              (resultNature === 'qualitative' ? 'Qualitative' : 'No Result')}
                        </span>
                        <input type="hidden" class="solution-result-nature" value="${resultNature}">
                    </div>
                </div>
                
                <!-- Footer with Verified Badge -->
                <div class="media-card-actions mt-auto pt-3 border-top">
                    <div class="verified-badge-footer">
                        <i class="mdi mdi-shield-check-outline mr-1"></i> Verified Entry
                    </div>
                </div>
            </div>
        </div>
    `);
    
    // 5. ADD PLACEHOLDER IF ONLY ONE CARD EXISTS
    if (gridContainer.find('.diluent-card-wrapper').length === 1) {
        gridContainer.append(`
            <div class="col-md-6 mb-3 diluent-placeholder-wrapper">
                <div class="placeholder-card-dashed">
                    <div class="placeholder-icon-container">
                        <i class="mdi mdi-beaker-outline"></i>
                    </div>
                    <div class="placeholder-text-main">Waiting for additional diluent data</div>
                    <div class="placeholder-text-sub">Add diluent using the form below</div>
                </div>
            </div>
        `);
    }
}
```

### Card Visual Structure

```
┌────────────────────────────────────────────┐
│ ✓ Verified                  [Delete Button] │
│                                              │
│ Sterile Water (Diluent Name)               │
│                                              │
│ Prep. Date: 2026-04-09 | Prep. No: P-001  │
│           | Result Nature: Quantitative    │
│                                              │
│ ──────────────────────────────────────────  │
│ 🛡️ Verified Entry                          │
└────────────────────────────────────────────┘
```

### Hidden Input Fields

Each card contains hidden inputs for data extraction during save:

```html
<input type="hidden" class="solution-preparation" value="${preparation}">
<input type="hidden" class="solution-remark" value="${prepNo}">
<input type="hidden" class="solution-result-nature" value="${resultNature}">
```

These are extracted by `buildStagePayload()` using jQuery class selectors.

### Key Features
- **Data Persistence:** Hidden inputs store all values for later extraction
- **Meta-data Display:** Shows preparation date, prep number, result nature inline
- **Delete Action:** Button always visible in top-right corner
- **Placeholder Management:** Shows placeholder when ≤1 cards, hides when 2+
- **Duplicate Prevention:** Checks existing cards before rendering
- **Responsive Layout:** Uses Bootstrap col-md-6 (2-column on medium+ screens)

### Function: saveStageDetails()
**Location:** `public/js/method-sequences.js` (Lines 2976-3005)

```javascript
function saveStageDetails() {
    // Collect all stage data including diluents
    const payload = buildStagePayload();
    
    // AJAX POST to backend
    $.ajax({
        url: '/api/method-sequences/stage/update',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: JSON.stringify(payload),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                showNotification('Stage details saved successfully', 'success');
                // Update UI state if needed
            } else {
                showNotification('Failed to save: ' + response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Save failed:', error);
            showNotification('Error saving stage details', 'error');
        }
    });
}
```

**Trigger Points:**
- After diluent added (Handler 2)
- After diluent deleted (Handler 3)
- After any stage field modified
- Uses debounce (typically 500ms) to prevent excessive API calls

**Request Structure:**
```
POST /api/method-sequences/stage/update
Headers: X-CSRF-TOKEN, Content-Type: application/json
Body: { stage_id, run_id, diluents: {...}, ... other stage data ... }
```

## 5. Data Collection & Payload Building

### Function: buildStagePayload()
**Location:** `public/js/method-sequences.js` (Lines 2990-3150)

Collects all form data from the current step and builds payload for backend submission.

### Diluents Collection Section

```javascript
buildStagePayload: function(trackId) {
    const collectItems = function(type) {
        const rows = [];
        
        if (type === 'diluents') {
            // 1. QUERY ALL DILUENT CARDS IN GRID
            $(`#diluent-cards-grid-${trackId} .diluent-card-wrapper`).each(function() {
                const id = $(this).attr('data-id');
                if (!id) return; // Skip if no ID
                
                // 2. EXTRACT HIDDEN INPUT VALUES FROM CARD
                rows.push({
                    id: parseInt(id, 10),
                    preparation: $(this).find('.solution-preparation').val() || '',
                    remark: $(this).find('.solution-remark').val() || '',
                    result: '', // Not used in prep stage
                    result_nature: $(this).find('.solution-result-nature').val() || 'no_result',
                });
            });
        }
        
        return rows;
    };
    
    // 3. COLLECT ALL SOLUTION TYPES
    const equipmentItems = collectItems('equipment');
    const mediaItems = collectItems('media');
    const controlsItems = collectItems('controls');
    const diluentsItems = collectItems('diluents');
    
    // 4. EXTRACT IDS ONLY (for quick reference)
    const idsFromItems = function(items) {
        return (items || []).map(function(i) {
            return i.id;
        }).filter(Boolean);
    };
    
    // 5. BUILD COMPLETE PAYLOAD
    const formData = {
        equipment_items: equipmentItems,
        media_items: mediaItems,
        controls_items: controlsItems,
        diluents_items: diluentsItems,
        equipment_ids: idsFromItems(equipmentItems),
        media_ids: idsFromItems(mediaItems),
        controls_ids: idsFromItems(controlsItems),
        diluents_ids: idsFromItems(diluentsItems),
        media_preparation_used: null,
        control_used: null,
        diluent_used: null,
        started_at: $(`#start-date-${trackId}`).val() || null,
        ended_at: $(`#end-date-${trackId}`).val() || null,
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    return formData; // Passed to submitStageData()
}
```

### DOM Selectors Used

| Selector | Purpose |
|----------|---------|
| `#diluent-cards-grid-${trackId}` | Container holding all diluent cards |
| `.diluent-card-wrapper` | Individual diluent card wrapper (data-id attribute) |
| `.solution-preparation` | Hidden input: prep date |
| `.solution-remark` | Hidden input: prep number |
| `.solution-result-nature` | Hidden input: result nature enum |

---

## 6. Auto-Save Mechanism

### Function: scheduleStageAutoSave & submitStageData
**Location:** `public/js/method-sequences.js` (Lines 3180-3240)

Implements intelligent debounced saving with user feedback.

### Auto-Save Scheduling

```javascript
scheduleStageAutoSave: function(trackId) {
    const self = this;
    
    // 1. CLEAR PREVIOUS TIMER (prevent multiple saves)
    if (self.autoSaveTimers[trackId]) {
        clearTimeout(self.autoSaveTimers[trackId]);
    }
    
    // 2. SET UI STATE TO "SAVING"
    self.setAutoSaveState(trackId, 'saving');
    
    // 3. SCHEDULE SAVE AFTER DEBOUNCE PERIOD (400ms)
    self.autoSaveTimers[trackId] = setTimeout(function() {
        delete self.autoSaveTimers[trackId];
        self.autoSaveStageSelections(trackId);
    }, 400);
}

autoSaveStageSelections: function(trackId) {
    const self = this;
    
    // 1. COLLECT CURRENT FORM DATA
    const payload = self.buildStagePayload(trackId);
    
    // 2. SUBMIT WITH AUTO-SAVE OPTIONS
    self.submitStageData(trackId, payload, {
        silent: true,           // Don't show alert
        skipReload: true,       // Don't reload runs
        autoSave: true,         // Trigger "saved" state
    });
}
```

### Data Submission

```javascript
submitStageData: function(trackId, data, options) {
    const self = this;
    const opts = Object.assign({
        silent: false,
        skipReload: false,
        autoSave: false,
    }, options || {});
    
    // 1. POST DATA TO BACKEND
    $.ajax({
        url: `/method-sequences/tracks/${trackId}/update`,
        method: 'POST',
        data: data,
        success: function(response) {
            // 2. HANDLE SUCCESS
            if (response.success) {
                if (opts.autoSave) {
                    // Set status to "Saved"
                    self.setAutoSaveState(trackId, 'saved');
                }
                if (!opts.silent) {
                    alert('Stage details saved successfully!');
                }
                if (!opts.skipReload) {
                    // Reload runs to show updated data
                    self.loadRuns(self.activeStageHeaderId);
                }
            } else {
                if (!opts.silent) {
                    alert('Error saving stage details: ' + (response.message || 'Unknown error'));
                } else {
                    console.error('Auto-save failed:', response.message);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
            if (!opts.silent) {
                alert('Error saving stage details');
            }
        }
    });
}
```

### Auto-Save UI States

```javascript
setAutoSaveState: function(trackId, state) {
    const self = this;
    const badge = $(`#autosave-status-${trackId}`);
    
    if (!badge.length) return;
    
    // Clear any existing timers
    if (self.autoSaveStateTimers[trackId]) {
        clearTimeout(self.autoSaveStateTimers[trackId]);
        delete self.autoSaveStateTimers[trackId];
    }
    
    // Reset classes
    badge.removeClass('badge-light badge-warning badge-success badge-danger text-muted text-dark text-white border');
    
    // Set state-specific classes
    if (state === 'saving') {
        badge.addClass('badge-warning text-dark');
        badge.text('Saving...');
        badge.attr('data-state', 'saving');
        return;
    }
    
    if (state === 'saved') {
        badge.addClass('badge-success text-white');
        badge.text('Saved');
        badge.attr('data-state', 'saved');
        
        // Auto-fade after 2s
        self.autoSaveStateTimers[trackId] = setTimeout(function() {
            const currentBadge = $(`#autosave-status-${trackId}`);
            if (!currentBadge.length || currentBadge.attr('data-state') !== 'saved') {
                return;
            }
            currentBadge
                .removeClass('badge-success text-white')
                .addClass('badge-light border text-muted')
                .text('Saved');
        }, 2000);
    }
}
```

### Debounce Behavior

```
User adds diluent item
        ↓
scheduleStageAutoSave() called (t=0)
        ↓
Start 400ms debounce timer
        ↓
User modifies another item (within 400ms)
        ↓
Clear timer, restart 400ms timer
        ↓
No more changes for 400ms
        ↓
Timer fires → autoSaveStageSelections() → POST to backend
```

### POST Endpoint

```
POST /method-sequences/tracks/{trackId}/update

Body (form-urlencoded):
  equipment_items[0][id]=1&equipment_items[0][serial]=SN-001...
  media_items[0][id]=45&media_items[0][preparation]=2026-04-09...
  controls_items[0][id]=50&controls_items[0][preparation]=2026-04-08...
  diluents_items[0][id]=55&diluents_items[0][preparation]=2026-04-09...
  equipment_ids[]=1
  media_ids[]=45
  controls_ids[]=50
  diluents_ids[]=55
  _token=CSRF_TOKEN_VALUE
```

---

## 7. Backend Processing

### Controller Method: updateMethodSequenceStageData()
**File:** `app/Http/Controllers/SampleWorkFlowController.php` (Lines 6408-6550)

### Controller Method: updateMethodSequenceStageData()
**File:** `app/Http/Controllers/SampleWorkFlowController.php` (Lines 6408-6550)

Complete backend handler for saving all stage data including diluents.

```php
public function updateMethodSequenceStageData(Request $request, $trackId)
{
    // 1. FIND THE TRACKING RECORD
    $track = \App\Models\SampleCapturedTestStagesTrack::findOrFail($trackId);

    // 2. VALIDATE INCOMING DATA
    $validated = $request->validate([
        'equipment_ids' => 'nullable|array',
        'media_ids' => 'nullable|array',
        'controls_ids' => 'nullable|array',
        'diluents_ids' => 'nullable|array',
        'equipment_items' => 'nullable|array',
        'equipment_items.*.id' => 'required|integer',
        'equipment_items.*.serial' => 'nullable|string',
        'equipment_items.*.calibration' => 'nullable|string',
        'media_items' => 'nullable|array',
        'media_items.*.id' => 'required|integer',
        'media_items.*.preparation' => 'nullable|string',
        'media_items.*.result' => 'nullable|string',
        'media_items.*.remark' => 'nullable|string',
        'media_items.*.result_nature' => 'nullable|string',
        'controls_items' => 'nullable|array',
        'controls_items.*.id' => 'required|integer',
        'controls_items.*.preparation' => 'nullable|string',
        'controls_items.*.result' => 'nullable|string',
        'controls_items.*.remark' => 'nullable|string',
        'controls_items.*.result_nature' => 'nullable|string',
        'diluents_items' => 'nullable|array',
        'diluents_items.*.id' => 'required|integer',
        'diluents_items.*.preparation' => 'nullable|string',
        'diluents_items.*.result' => 'nullable|string',
        'diluents_items.*.remark' => 'nullable|string',
        'diluents_items.*.result_nature' => 'nullable|string',
        'started_at' => 'nullable|date',
        'ended_at' => 'nullable|date',
    ]);

    // 3. DATA NORMALIZATION FUNCTIONS
    $normalizeItems = static function ($items): array {
        if (!is_array($items)) {
            return [];
        }
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item) || !array_key_exists('id', $item)) {
                continue;
            }
            // Cast all values to correct types
            $out[] = [
                'id' => (int) $item['id'],
                'preparation' => isset($item['preparation']) ? (string) $item['preparation'] : null,
                'result' => isset($item['result']) ? (string) $item['result'] : null,
                'remark' => isset($item['remark']) ? (string) $item['remark'] : null,
                'result_nature' => isset($item['result_nature']) ? (string) $item['result_nature'] : null,
            ];
        }
        return $out;
    };

    $normalizeEquipmentItems = static function ($items): array {
        if (!is_array($items)) {
            return [];
        }
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item) || !array_key_exists('id', $item)) {
                continue;
            }
            $out[] = [
                'id' => (int) $item['id'],
                'serial' => isset($item['serial']) ? (string) $item['serial'] : null,
                'calibration' => isset($item['calibration']) ? (string) $item['calibration'] : null,
            ];
        }
        return $out;
    };

    // 4. NORMALIZE ALL ITEMS
    $equipmentItems = $normalizeEquipmentItems($validated['equipment_items'] ?? null);
    $mediaItems = $normalizeItems($validated['media_items'] ?? null);
    $controlsItems = $normalizeItems($validated['controls_items'] ?? null);
    $diluentsItems = $normalizeItems($validated['diluents_items'] ?? null);

    // 5. EXTRACT IDS FROM ITEMS
    $idsFromItems = static function (array $items): array {
        $ids = [];
        foreach ($items as $item) {
            if (isset($item['id'])) {
                $ids[] = (int) $item['id'];
            }
        }
        return array_values(array_unique(array_filter($ids)));
    };

    $equipmentIds = !empty($equipmentItems) ? $idsFromItems($equipmentItems) : ($validated['equipment_ids'] ?? []);
    $mediaIds = !empty($mediaItems) ? $idsFromItems($mediaItems) : ($validated['media_ids'] ?? []);
    $controlsIds = !empty($controlsItems) ? $idsFromItems($controlsItems) : ($validated['controls_ids'] ?? []);
    $diluentsIds = !empty($diluentsItems) ? $idsFromItems($diluentsItems) : ($validated['diluents_ids'] ?? []);

    // 6. BUILD PAYLOAD FOR DATABASE UPDATE
    $payload = [
        'equipment_data' => !empty($equipmentIds) ? array_filter([
            'equipment_ids' => $equipmentIds,
            'items' => !empty($equipmentItems) ? $equipmentItems : null,
        ], static fn ($v) => $v !== null) : null,
        'media_data' => !empty($mediaIds) ? array_filter([
            'media_ids' => $mediaIds,
            'items' => !empty($mediaItems) ? $mediaItems : null,
        ], static fn ($v) => $v !== null) : null,
        'controls_data' => !empty($controlsIds) ? array_filter([
            'controls_ids' => $controlsIds,
            'items' => !empty($controlsItems) ? $controlsItems : null,
        ], static fn ($v) => $v !== null) : null,
        'diluents_data' => !empty($diluentsIds) ? array_filter([
            'diluents_ids' => $diluentsIds,
            'items' => !empty($diluentsItems) ? $diluentsItems : null,
        ], static fn ($v) => $v !== null) : null,
    ];

    // 7. HANDLE RUN TIMING (optional)
    if (!empty($validated['started_at'])) {
        $start = \Carbon\Carbon::parse($validated['started_at']);
        $payload['started_at'] = $start;
        $payload['expected_end_at'] = $track->testStage && $track->testStage->duration_hours
            ? $start->copy()->addHours((int) $track->testStage->duration_hours)
            : null;
        if (in_array($track->status, ['pending'], true)) {
            $payload['status'] = 'running';
            $payload['user_id'] = $track->user_id ?? auth()->id();
        }
    }

    if (!empty($validated['ended_at'])) {
        $payload['ended_at'] = \Carbon\Carbon::parse($validated['ended_at']);
        $payload['status'] = 'completed';
    }

    // 8. UPDATE TRACK RECORD WITH JSON DATA
    $track->update($payload);

    // 9. RETURN SUCCESS RESPONSE
    return response()->json(['success' => true, 'track' => $track->fresh()]);
}
```

### Data Flow Diagram

```
POST /method-sequences/tracks/{trackId}/update
        ↓
findOrFail($trackId)  [Get tracking record]
        ↓
validate()  [Validate all inputs against rules]
        ↓
normalizeItems()  [Cast types: id→int, strings→strings]
        ↓
idsFromItems()  [Extract unique ID arrays]
        ↓
Build payload:
  {
    diluents_data: {
      diluents_ids: [55, 56, 57],
      items: [
        { id: 55, preparation: '2026-04-09', remark: 'P-001', result_nature: 'quantitative' },
        { id: 56, preparation: '2026-04-09', remark: 'P-002', result_nature: 'no_result' }
      ]
    },
    media_data: {...},
    controls_data: {...},
    equipment_data: {...}
  }
        ↓
$track->update($payload)  [Single DB UPDATE with JSON columns]
        ↓
return { success: true, track: {...} }  [JSON response]
```

### Database Update

**Table:** `sample_captured_test_stages_track`

**Columns Updated (JSON):**
- `diluents_data` - Contains diluents_ids array + items array
- `media_data` - Contains media_ids array + items array
- `controls_data` - Contains controls_ids array + items array
- `equipment_data` - Contains equipment_ids array + items array

**Stored JSON Structure (diluents_data):**
```json
{
  "diluents_ids": [55, 56],
  "items": [
    {
      "id": 55,
      "preparation": "2026-04-09",
      "remark": "P-001-2026",
      "result": null,
      "result_nature": "quantitative"
    },
    {
      "id": 56,
      "preparation": "2026-04-09",
      "remark": "P-002-2026",
      "result": null,
      "result_nature": "no_result"
    }
  ]
}
```

---

## 8. Database Storage
                ];
            }
        }

        // 3. UPDATE TRACK RECORD WITH JSON DATA
        $track->update([
            'diluents_data' => json_encode($diluentsData),
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Diluents saved successfully',
            'data' => [
                'track_id' => $track->id,
                'diluent_count' => count($diluentsData),
            ]
        ]);

    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error saving diluents: ' . $e->getMessage()
        ], 500);
    }
}
```

**Backend Processing Steps:**
1. **Validation:** Validates all diluent fields per Postman spec
2. **Database Lookup:** Finds tracking record by stage+run
3. **Data Normalization:** Casts values to correct types, trims strings, handles nulls
4. **JSON Encoding:** Converts array to JSON for database storage
5. **Database Update:** Single UPDATE query with JSON payload
6. **Transaction Wrapping:** All-or-nothing commit for data integrity
7. **Response:** Returns success/failure with diluent count confirmation



---

## 8. Complete Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│  FRONTEND: USER INPUT                                        │
│  [Diluent Form] → [Add Button] → [Event Handler]            │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  JAVASCRIPT: VALIDATION & RENDERING                         │
│  1. Collect form data (name, date, nature, prep#)           │
│  2. Validate required fields                                │
│  3. Generate timestamped ID                                 │
│  4. Render card with hidden inputs                          │
│  5. Clear form, close section, remove placeholder            │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  JAVASCRIPT: DATA COLLECTION                                │
│  buildStagePayload() extracts hidden values from all cards   │
│  Creates: { diluents: { items: [...], ids: [...] } }        │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  AUTO-SAVE: AJAX POST                                       │
│  saveStageDetails() → POST to /api/method-sequences/stage   │
│  Headers: X-CSRF-TOKEN, Content-Type: application/json      │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  BACKEND: VALIDATION & PROCESSING                           │
│  SampleWorkFlowController::updateMethodSequenceStageData()   │
│  1. Validate all diluent fields                             │
│  2. Lookup tracking record by stage+run                     │
│  3. Normalize data (trim, cast, encode)                     │
│  4. Begin transaction                                       │
│  5. Update track.diluents_data with JSON                    │
│  6. Commit transaction                                      │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  DATABASE: STORAGE                                          │
│  Table: sample_captured_test_stages_track                   │
│  Column: diluents_data (JSON)                               │
│  Format: [ { preparation, result_nature, prep_number... } ] │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│  RESPONSE: JSON CONFIRMATION                                │
│  { success: true, diluent_count: X, track_id: Y }          │
└─────────────────────────────────────────────────────────────┘
```

---

## 9. Editable vs Read-Only States

### Editable State
- **When:** Run status is "pending" or "in-progress"
- **Allowed Actions:** Add, delete, modify diluents
- **Form Visible:** Yes, always accessible
- **Cards Editable:** Delete buttons active

### Read-Only State
- **When:** Run status is "completed" or "posted"
- **Allowed Actions:** View only
- **Form Visible:** No, hidden by controller logic
- **Cards Editable:** Delete buttons disabled, grayed out

### Implementation
```javascript
// Disable on page load if run status is completed
if (currentRunStatus === 'completed') {
    $('.add-diluent-btn').prop('disabled', true);
    $('.delete-diluent-btn').hide();
    $('.toggle-diluent-form').hide();
}
```

---

## 10. Error Handling & Validation

### Frontend Validation (JavaScript)
```javascript
// Required fields check
if (!formData.diluent_name.trim()) {
    return showError('Diluent name is required');
}

// Date validation
if (!formData.diluent_prep_date) {
    return showError('Preparation date must be selected');
}

// Result nature enum validation
const validNatures = ['positive_control', 'negative_control', 'blank_control'];
if (formData.diluent_result_nature && 
    !validNatures.includes(formData.diluent_result_nature)) {
    return showError('Invalid result nature selected');
}
```

### Backend Validation (Laravel)
```php
'diluents.items.*.preparation' => 'max:500|string',
'diluents.items.*.result_nature' => 'in:positive_control,negative_control,blank_control',
'diluents.items.*.prep_number' => 'max:100|string',
```

### Error Display
- **Frontend:** Red toast notifications at top of screen
- **Backend:** 422 Unprocessable Entity response with field errors
- **User Feedback:** Clear field-specific error messages

---

## 11. Performance Considerations

### Optimization Strategies

1. **Debounced Saves**
   - Auto-save waits 500ms after last change before sending
   - Prevents flooding backend with requests during rapid data entry

2. **JSON Storage**
   - Single column per stage instead of separate table
   - Reduces query joins and speeds up retrieval
   - Appropriate for prep stage (not queried frequently)

3. **Hidden Inputs Strategy**
   - Card rendering includes hidden `<input>` elements
   - Avoids DOM parsing/regex for value extraction
   - Standard form input approach familiar to developers

4. **Timestamp-Based IDs**
   - Client-generated IDs using `Date.now()`
   - No database round-trip needed for ID generation
   - Collision probability negligible in single-user context

### Query Performance
```sql
-- Fast: Looking up one run's diluents
SELECT diluents_data 
FROM sample_captured_test_stages_track 
WHERE run_id = 123 
LIMIT 1;  -- O(1) with index on run_id

-- Slower: Searching across all diluents (rarely done)
SELECT * FROM sample_captured_test_stages_track
WHERE JSON_CONTAINS(diluents_data, '"positive_control"');  -- O(n) but acceptable
```

---

## 12. Comparison: Diluents vs Media vs Controls

All three solution types follow **identical patterns**:

| Aspect | Diluents | Media | Controls |
|--------|----------|-------|----------|
| Form Location | Step 5 | Step 3 | Step 4 |
| Fields | name, date, nature, prep# | same | same |
| Event Handlers | Add/Delete/Toggle | same | same |
| Card Rendering | 3-column grid | same | same |
| Data Collection | buildStagePayload() | same | same |
| Backend | updateMethodSequenceStageData() | same | same |
| Database Table | diluents_data JSON col | media_data JSON col | controls_data JSON col |
| Storage Pattern | Array of objects | same | same |

**Key Point:** The architecture is consistent and reusable across all three solution types, making maintenance and debugging straightforward.

---

## 13. Testing Checklist

### Unit Tests
- [ ] Timestamp ID generation uniqueness
- [ ] Form validation (required fields)
- [ ] Enum validation (result_nature)
- [ ] HTML escaping in card rendering
- [ ] JSON encoding/decoding in backend

### Integration Tests
- [ ] Add diluent → Card rendered → Data saved
- [ ] Delete diluent → Card removed → Placeholder shown → Data updated
- [ ] Multiple diluents → All cards rendered → All saved
- [ ] Toggle form → Opens/closes smoothly
- [ ] Error handling → User gets feedback

### End-to-End Tests
- [ ] Create run → Add diluents → View in database as JSON
- [ ] Edit run → Modify diluents → Changes persisted
- [ ] Delete run → Diluents deleted from database
- [ ] Post results → Diluents remain in track table

### Edge Cases
- [ ] Empty diluent name (should fail)
- [ ] Special characters in name (should escape)
- [ ] Very long preparation text (should truncate/validate)
- [ ] Rapid add/delete clicks (should debounce properly)
- [ ] Add diluent while another save in progress

---

## 14. Summary

The Step 5 Diluents workflow exemplifies a well-designed three-tier architecture:

1. **Frontend (UI):** Clean form with card-based visualization
2. **JavaScript (Logic):** Event-driven handlers with auto-save mechanism
3. **Backend (Data):** Transaction-wrapped updates with validation
4. **Database (Storage):** JSON columns for flexible, queryable schema

The design prioritizes:
- **User Experience:** Auto-save, immediate feedback, lazy form UI
- **Data Integrity:** Transactional updates, comprehensive validation
- **Performance:** Debounced saves, single JSON column per stage
- **Maintainability:** Consistent patterns across all solution types
- **Debuggability:** Clear data flow, verbose error messages

All future solution type implementations (media, controls) and enhancements should follow this same pattern for consistency.

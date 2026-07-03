# Deep Dive: Saving Stage Sample Results & Posting Results

## Overview
This document provides a comprehensive understanding of:
1. **Tables involved** in saving stage sample results
2. **Data flow** when posting results (where modal data comes from)
3. **Complete workflow** from stage tracking to final results posting

---

## 1. Core Tables Involved

### 1.1 `captured_results` Table
**Purpose:** Official results storage - posted results end up here

**Key Columns:**
```sql
CREATE TABLE `captured_results` (
  `id` BIGINT PRIMARY KEY,
  `sample_detail_code` VARCHAR(255),     -- Sample identifier
  `sample_detail_id` INT,                -- Links to sample_details
  `sample_header_id` INT,                -- Links to sample_header (batch)
  `analyte_id` INT,                      -- Links to analytes
  `analyte_code` VARCHAR(255),           -- Analyte code
  `analysis_type_id` INT,                -- Type of analysis
  `result` VARCHAR(100),                 -- THE ACTUAL RESULT VALUE
  `operator_id` INT,                     -- Lab technician who recorded it
  `method_id` INT,                       -- Analysis method used
  `remark` VARCHAR(100),                 -- Pass/Fail/Warning
  `reporting_unit_id` VARCHAR(100),      -- Unit of measurement
  `result_reporting_symbol` VARCHAR(100),-- <, >, ≤, ≥, = etc
  `main_standard_id` INT,                -- Primary standard used
  `main_value` VARCHAR(500),             -- Primary standard value
  `secondary_standard_id` INT,           -- Secondary standard
  `secondary_value` VARCHAR(500),        -- Secondary standard value
  `third_standard_id` INT,               -- Tertiary standard
  `third_value` VARCHAR(255),            -- Tertiary standard value
  `main_remark` VARCHAR(100),            -- Remark for main standard
  `sec_remark` VARCHAR(100),             -- Remark for secondary standard
  `third_remark` VARCHAR(100),           -- Remark for tertiary standard
  `scienctific_result` TEXT,             -- Scientific notation version
  `supercsript_base` VARCHAR(100),       -- For exponential display
  `superscript_number` INT,              -- Power
  `superscript_negative` INT,            -- Negative exponent flag
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP
);
```

---

### 1.2 `sample_captured_test_stages_track` Table
**Purpose:** Temporary tracking of results during stage workflow

**Key Columns:**
```sql
CREATE TABLE `sample_captured_test_stages_track` (
  `id` BIGINT PRIMARY KEY,
  `stage_header_run_id` BIGINT,          -- Links to stage_header_runs
  `captured_result_id` BIGINT,           -- FOREIGN KEY to captured_results
  `sample_detail_id` BIGINT,             -- Links to sample_details
  `stage_header_id` BIGINT,              -- Links to stage_headers
  `test_stage_id` BIGINT,                -- Links to test_stages
  `user_id` INT,                         -- User running the stage
  `result` VARCHAR(255),                 -- WORKING RESULT VALUE
  `remark` VARCHAR(500),                 -- Pass/Fail indicator
  `method_id` INT,                       -- Method selected
  `reporting_unit_id` INT,               -- Unit selected
  `read_by` INT,                         -- User who read the result
  `reading_date` DATETIME,               -- When result was recorded
  `started_at` TIMESTAMP,
  `ended_at` TIMESTAMP,
  `results_posted_at` TIMESTAMP,         -- When posted to captured_results
  `results_posted_by` INT,               -- Who posted results
  `status` VARCHAR(50),                  -- pending, running, completed
  `equipment_data` JSON,                 -- Equipment settings
  `media_data` JSON,                     -- Media prep details
  `controls_data` JSON,                  -- QC controls data
  `diluents_data` JSON,                  -- Diluents prep data
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP
);
```

---

### 1.3 `sample_details` Table
**Purpose:** Individual sample information within a batch

**Key Columns:**
```sql
CREATE TABLE `sample_details` (
  `id` INT PRIMARY KEY,
  `sample_code` VARCHAR(255),            -- Unique sample code
  `sample_header_id` INT,                -- Links to sample_header (batch)
  `lab_id` INT,                          -- Lab handling sample
  `status` VARCHAR(100),                 -- Current stage
  `material_received_date` DATETIME,
  ...
);
```

---

### 1.4 `stage_header_runs` Table
**Purpose:** Individual runs of a stage (multiple runs possible per stage)

**Key Columns:**
```sql
CREATE TABLE `stage_header_runs` (
  `id` BIGINT PRIMARY KEY,
  `stage_header_id` BIGINT,              -- Links to stage_headers
  `batch_id` BIGINT,                     -- Links to sample_header
  `created_by` INT,
  `created_at` TIMESTAMP,
  `updated_at` TIMESTAMP
);
```

---

### 1.5 Related Tables (Reference)

| Table | Purpose | Key Column |
|-------|---------|-----------|
| `stage_headers` | Configuration of each stage | `id`, `name`, `code` |
| `test_stages` | Test/analysis stages | `id`, `name` |
| `analytes` | Measurable substances | `id`, `code`, `name` |
| `sample_header` | Batch/order information | `id`, `sample_collection_date` |
| `standards` | Reference standards (QC) | `id`, `name` |
| `standard_analytes` | Standard + Analyte + Limits | `id`, `standard_id`, `analyte_id`, `low`, `high` |

---

## 2. Data Flow: Where Modal Data Comes From

### 2.1 Frontend Flow - Loading Post Results Modal

```
📱 User clicks "Post Results" button
        ↓
JavaScript: showPostResultsModal()
        ↓
AJAX GET: /method-sequences/batch/{batchId}/tracking-results
        ↓
Backend: getMethodSequenceTrackingResults($batchId)
        ↓
Query executed (see 2.2)
        ↓
Tracking records returned as JSON
        ↓
JavaScript: renderPostResultsTable()
        ↓
Modal table populated with data
```

### 2.2 Backend Query - `getMethodSequenceTrackingResults()`

**File:** [app/Http/Controllers/SampleWorkFlowController.php](app/Http/Controllers/SampleWorkFlowController.php#L7065)

```php
public function getMethodSequenceTrackingResults(Request $request, $batchId)
{
    // Step 1: Get all tracking records for this batch that HAVE RESULTS
    $trackingRecords = \App\Models\SampleCapturedTestStagesTrack::whereHas('capturedResult', function($q) use ($batchId) {
        $q->where('sample_header_id', $batchId);
    })
    ->whereNotNull('result')              // Only include if result is filled
    ->where('result', '!=', '')           // And not empty string
    ->with([
        'capturedResult.sample',          // Load sample info
        'capturedResult.analyte',         // Load analyte info
        'testStage',                      // Load test stage
        'readBy'                          // Load user who read
    ])
    ->get();
    
    // Step 2: ENRICH each tracking record with additional data
    foreach ($trackingRecords as $track) {
        $captured = $track->capturedResult;
        $sample = $captured->sample;
        
        // *** This is where modal data comes from: ***
        
        // Get standards from sample definition
        $mainStandard = \App\Models\Standards::find($sample->main_standard);
        $secStandard = \App\Models\Standards::find($sample->secondary_standard);
        $thirdStandard = \App\Models\Standards::find($sample->third_standard_id);
        
        // Get standard analytes with LIMITS
        if ($mainStandard) {
            $track->main_standard_analyte = \App\Models\StandardAnalytes::where('analyte_id', $captured->analyte_id)
                ->where('standard_id', $mainStandard->id)
                ->first();  // Gets: low, high, standard_value_type
            $track->main_standard = $mainStandard;
        }
        
        if ($secStandard) {
            $track->sec_standard_analyte = \App\Models\StandardAnalytes::where('analyte_id', $captured->analyte_id)
                ->where('standard_id', $secStandard->id)
                ->first();  // Gets: low, high, standard_value_type
            $track->sec_standard = $secStandard;
        }
        
        if ($thirdStandard) {
            $track->third_standard_analyte = \App\Models\StandardAnalytes::where('analyte_id', $captured->analyte_id)
                ->where('standard_id', $thirdStandard->id)
                ->first();  // Gets: low, high, standard_value_type
            $track->third_standard = $thirdStandard;
        }
        
        // Get method and reporting unit
        $track->method = \App\Models\Method::find($captured->method_id);
        $track->reporting_unit = \App\Models\ReportingUnit::find($captured->reporting_unit_id);
    }
    
    return response()->json($trackingRecords);
}
```

---

## 3. Modal Table Structure & Data Mapping

### 3.1 Post Results Modal HTML

**File:** [resources/views/layouts/lab/method-sequences/index.blade.php](resources/views/layouts/lab/method-sequences/index.blade.php#L1703)

```html
<table class="table">
  <thead>
    <tr>
      <th>Sample Code</th>
      <th>Analyte</th>
      <th>Result</th>
      <th>Reporting Symbol</th>
      <th>Standard</th>
      <th>Standard Limits</th>
      <th>Remark</th>
      <th>Method</th>
      <th>Reporting Unit</th>
    </tr>
  </thead>
  <tbody id="post-results-table-body">
    <!-- Populated by JavaScript -->
  </tbody>
</table>
```

### 3.2 Data Source Mapping

| Modal Column | Data Source | Table |
|---|---|---|
| **Sample Code** | `track.captured_result.sample.sample_code` | `sample_details` |
| **Analyte** | `track.captured_result.analyte.name` | `analytes` |
| **Result** | `track.result` | `sample_captured_test_stages_track.result` |
| **Reporting Symbol** | Dropdown (edited by user) | User input → POST payload |
| **Standard** | `track.main_standard.name` | `standards` |
| **Standard Limits** | `track.main_standard_analyte.low/high` | `standard_analytes` |
| **Remark** | Calculated from result vs limits | Derived calculation |
| **Method** | `track.method` | `analysis_methods` |
| **Reporting Unit** | `track.reporting_unit` | `reporting_units` |

---

### 3.3 JavaScript Rendering

**File:** [public/js/method-sequences.js](public/js/method-sequences.js#L1917)

```javascript
renderPostResultsTable: function(trackingRecords) {
    const tbody = $('#post-results-table-body');
    tbody.empty();
    
    trackingRecords.forEach(track => {
        const row = `
            <tr data-track-id="${track.id}">
                <!-- Sample Code from captured_result -->
                <td>${track.captured_result.sample.sample_code}</td>
                
                <!-- Analyte from captured_result -->
                <td>${track.captured_result.analyte.name}</td>
                
                <!-- Result from tracking record -->
                <td>${track.result}</td>
                
                <!-- Reporting Symbol - EDITABLE -->
                <td>
                    <select class="form-control form-control-sm reporting-symbol" 
                            name="reporting_symbol[${track.id}]">
                        <option value="">=</option>
                        <option value="<"><</option>
                        <option value=">">></option>
                        <option value="≤">≤</option>
                        <option value="≥">≥</option>
                    </select>
                </td>
                
                <!-- Standard from main_standard -->
                <td>${track.main_standard ? track.main_standard.name : 'N/A'}</td>
                
                <!-- Standard Limits - EDITABLE -->
                <td>${this.renderEditableStandardLimits(track)}</td>
                
                <!-- Remark - CALCULATED -->
                <td class="remark-cell">${this.calculateRemark(track)}</td>
                
                <!-- Method - EDITABLE -->
                <td>
                    <select class="form-control form-control-sm method-select" 
                            name="method[${track.id}]">
                        ${this.renderMethodOptions(track.method)}
                    </select>
                </td>
                
                <!-- Reporting Unit - EDITABLE -->
                <td>
                    <select class="form-control form-control-sm unit-select" 
                            name="unit[${track.id}]">
                        ${this.renderUnitOptions(track.reporting_unit)}
                    </select>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}
```

---

## 4. Editable Fields in Modal

### 4.1 Fields That Can Be Modified

The modal allows editing of these fields:

1. **Reporting Symbol** (Dropdown)
   - Source: User selection
   - Values: `=`, `<`, `>`, `≤`, `≥`
   - Stored in: `captured_results.result_reporting_symbol`

2. **Standard Limits** (Numeric Input Fields)
   - Source: `StandardAnalytes::low` and `StandardAnalytes::high`
   - Values: Editable numbers
   - Stored in: `standard_analytes.low` and `standard_analytes.high`

3. **Method** (Dropdown)
   - Source: `AnalysisMethods` list
   - Stored in: `captured_results.method_id`

4. **Reporting Unit** (Dropdown)
   - Source: `ReportingUnits` list
   - Stored in: `captured_results.reporting_unit_id`

### 4.2 Fields That Are Read-Only

These fields display but cannot be changed:
- Sample Code (from sample_details)
- Analyte (from analytes)
- Result value (from tracking)
- Remark (auto-calculated)

---

## 5. Posting Results Flow

### 5.1 Complete Posting Workflow

```
📱 User clicks "Yes, Post Results" button
        ↓
JavaScript: confirmPostResults()
        ↓
Collect modal form data:
├─ For each row:
│  ├─ track_id
│  ├─ result value (from tracking)
│  ├─ reporting_symbol (edited)
│  ├─ method_id (selected)
│  ├─ reporting_unit_id (selected)
│  ├─ remark (from modal)
│  ├─ main_value, secondary_value, third_value (standards)
│  ├─ standard limits (low/high) - EDITED
│  └─ standard_analyte IDs
        ↓
AJAX POST: /method-sequences/post-results
with data = {
    batch_id: batchId,
    tracking_data: [array of edited records]
}
        ↓
Backend: postMethodSequenceResults($request)
        ↓
For EACH tracking record:
├─ 1. Find SampleCapturedTestStagesTrack by track_id
├─ 2. Get associated CapturedResult
├─ 3. UPDATE captured_result with:
│  ├─ result = track.result
│  ├─ remark = data.remark
│  ├─ method_id = data.method_id
│  ├─ operator_id = track.read_by
│  ├─ reporting_unit_id = data.reporting_unit_id
│  ├─ result_reporting_symbol = data.reporting_symbol
│  ├─ main_standard_id = data.main_standard_analyte_id
│  ├─ secondary_standard_id = data.sec_standard_analyte_id
│  ├─ third_standard_id = data.third_standard_analyte_id
│  ├─ main_value = data.main_value
│  ├─ secondary_value = data.secondary_value
│  ├─ third_value = data.third_value
│  └─ Scientific notation fields
├─ 4. UPDATE standard_analytes if limits were edited
├─ 5. UPDATE tracking record:
│  ├─ results_posted_at = now()
│  └─ results_posted_by = auth().id()
└─ 6. SAVE captured_result to database
        ↓
Response: { success: true, message: 'Results posted successfully' }
        ↓
Frontend: Modal closes, UI updates
```

---

### 5.2 Backend Implementation

**File:** [app/Http/Controllers/SampleWorkFlowController.php](app/Http/Controllers/SampleWorkFlowController.php#L7124)

```php
public function postMethodSequenceResults(Request $request)
{
    $batchId = $request->batch_id;
    $trackingData = $request->tracking_data; // Array of records
    
    foreach ($trackingData as $data) {
        // Get the tracking record
        $track = \App\Models\SampleCapturedTestStagesTrack::find($data['track_id']);
        $captured = $track->capturedResult;
        
        // UPDATE captured_result table with:
        $captured->result = $track->result;  // From tracking
        $captured->remark = $data['remark'];  // From modal
        $captured->method_id = $data['method_id'];  // Selected method
        $captured->operator_id = $track->read_by;  // Who read it
        $captured->reporting_unit_id = $data['reporting_unit_id'];  // Selected unit
        $captured->result_reporting_symbol = $data['reporting_symbol'];  // <, >, etc
        
        // Handle scientific notation if numeric
        if (is_numeric($track->result)) {
            $scientific_arr = $this->toScientificNotation($track->result);
            $captured->scienctific_result = $scientific_arr['scientific'];
            $captured->supercsript_base = number_format($scientific_arr['value'], 1);
            $captured->superscript_number = $scientific_arr['to_power'];
            $captured->superscript_negative = round(intval($track->result)) >= 1 ? 0 : 1;
        } else {
            $captured->scienctific_result = $track->result;
        }
        
        // Update standard values
        $captured->main_value = $data['main_value'];
        $captured->secondary_value = $data['secondary_value'] ?? '';
        $captured->third_value = $data['third_value'] ?? '';
        
        // Save standard analyte IDs
        $captured->main_standard_id = $data['main_standard_analyte_id'] ?? 0;
        $captured->secondary_standard_id = $data['sec_standard_analyte_id'] ?? 0;
        $captured->third_standard_id = $data['third_standard_analyte_id'] ?? 0;
        
        $captured->save();  // ⭐ Results now in captured_results table
        
        // UPDATE standard_analytes if limits were edited
        if (isset($data['main_standard_analyte_id'])) {
            $mainStdAnalyte = \App\Models\StandardAnalytes::find($data['main_standard_analyte_id']);
            if ($mainStdAnalyte && isset($data['main_limit_low']) && isset($data['main_limit_high'])) {
                $mainStdAnalyte->low = $data['main_limit_low'];
                $mainStdAnalyte->high = $data['main_limit_high'];
                $mainStdAnalyte->save();
            }
        }
        
        // Similar for secondary and tertiary standards...
        
        // Mark tracking record as posted
        $track->update([
            'results_posted_at' => now(),
            'results_posted_by' => auth()->id()
        ]);
    }
    
    return response()->json(['success' => true, 'message' => 'Results posted successfully']);
}
```

---

## 6. Key Relationships & Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                        POSTING WORKFLOW                             │
└─────────────────────────────────────────────────────────────────────┘

PHASE 1: DATA COLLECTION (During stage execution)
═════════════════════════════════════════════════
sample_header (Batch)
     └─ sample_details (Sample in batch)
          └─ captured_results (Initial empty record)
               ├─ analytes (What's being measured)
               ├─ standards (Reference values)
               └─ analysis_methods (How it's measured)

PHASE 2: RESULT RECORDING (During stage)
═════════════════════════════════════════
sample_captured_test_stages_track (Temporary workspace)
     ├─ result ← User enters/records result
     ├─ read_by ← User ID
     ├─ reading_date ← When recorded
     └─ capturedResult → FOREIGN KEY to captured_results
     
PHASE 3: MODAL LOADING (Before posting)
═══════════════════════════════════════
getMethodSequenceTrackingResults() queries:
     
     sample_captured_test_stages_track ──┐
     └─ WHERE result NOT NULL           │
        └─ Loads associated data:        │
           ├─ captured_result           │
           │  ├─ sample_details         │→ Modal displays
           │  ├─ analytes               │  this information
           │  └─ analysis_type          │
           ├─ standards                 │
           │  └─ standard_analytes      │
           │     (low, high limits) ────┘
           └─ analysis_methods
           
PHASE 4: USER EDITS (In modal)
══════════════════════════════
User can modify:
├─ reporting_symbol (=, <, >, ≤, ≥)
├─ standard_limits (low/high) → updates standard_analytes
├─ method_id
└─ reporting_unit_id

PHASE 5: POSTING (Save to official table)
═══════════════════════════════════════════
For each edited record in modal:
     
     sample_captured_test_stages_track
     ├─ Read: result, read_by
     └─ Update: results_posted_at, results_posted_by
     
     └─→ captured_results (WRITE TO THIS TABLE)
         ├─ result ← from tracking
         ├─ remark ← from modal
         ├─ method_id ← selected method
         ├─ operator_id ← read_by user
         ├─ reporting_unit_id ← selected unit
         ├─ result_reporting_symbol ← selected symbol
         ├─ main_standard_id ← standard reference
         ├─ main_value ← standard value
         ├─ main_limit_low/high ← from modal (EDITABLE)
         ├─ secondary_standard_id
         ├─ secondary_value
         ├─ third_standard_id
         ├─ third_value
         └─ scienctific_result ← calculated
         
     standard_analytes (IF LIMITS EDITED)
     └─ Update: low, high
```

---

## 7. Where Modal Data Actually Comes From - Summary

### 7.1 For Each Row in Post Results Modal

```
SAMPLE CODE
  ↓
  FROM: sample_captured_test_stages_track
        └─ captured_result
           └─ sample_details.sample_code

ANALYTE
  ↓
  FROM: sample_captured_test_stages_track
        └─ captured_result
           └─ analytes.name

RESULT
  ↓
  FROM: sample_captured_test_stages_track.result
        (This is what the operator entered during stage)

REPORTING SYMBOL
  ↓
  USER INPUT IN MODAL (Dropdown)
  Default from: captured_results.result_reporting_symbol OR empty

STANDARD
  ↓
  FROM: sample_details
        └─ main_standard → standards.name
  AND: standard_analytes
        └─ Where analyte_id = captured_result.analyte_id
           AND standard_id = sample.main_standard

STANDARD LIMITS (LOW/HIGH)
  ↓
  FROM: standard_analytes
        ├─ standard_analyte.low
        └─ standard_analyte.high
  EDITABLE: User can change these in the modal

REMARK
  ↓
  CALCULATED: By comparing result against limits
  Formula: 
    if result < low OR result > high
      → "OUT OF RANGE" (Red)
    else if result == limit
      → "AT LIMIT" (Yellow)
    else
      → "PASS" (Green)

METHOD
  ↓
  FROM: sample_captured_test_stages_track
        └─ captured_result
           └─ analysis_methods.name
  EDITABLE: User can select different method

REPORTING UNIT
  ↓
  FROM: sample_captured_test_stages_track
        └─ captured_result
           └─ reporting_units.name
  EDITABLE: User can select different unit
```

---

## 8. Database Write Operations During Posting

### 8.1 Tables That Get Updated

**When "Post Results" is submitted:**

| Table | Columns Updated | Source | Operation |
|-------|---|---|---|
| `captured_results` | `result`, `remark`, `method_id`, `operator_id`, `reporting_unit_id`, `result_reporting_symbol`, `main_standard_id`, `secondary_standard_id`, `third_standard_id`, `main_value`, `secondary_value`, `third_value`, `scienctific_result`, `supercsript_base`, `superscript_number`, `superscript_negative`, `updated_at` | Modal + tracking + calculation | UPDATE |
| `standard_analytes` | `low`, `high`, `updated_at` | Modal user edits | UPDATE (if changed) |
| `sample_captured_test_stages_track` | `results_posted_at`, `results_posted_by`, `updated_at` | System + auth()->id() | UPDATE |

---

## 9. Critical Points

### ✅ Key Takeaways

1. **Data Comes From Multiple Sources:**
   - Sample/Analyte info: From `sample_details` and `analytes`
   - Result value: From `sample_captured_test_stages_track.result`
   - Standards: From `standards` and `standard_analytes`
   - Methods/Units: From `analysis_methods` and `reporting_units`

2. **Modal is EDITABLE:**
   - Reporting symbol, method, unit, standard limits can all be changed
   - These edits get saved back when posting

3. **Results Posted To:**
   - `captured_results` table is the OFFICIAL storage
   - Once posted, results appear in reports

4. **Tracking Table is TEMPORARY:**
   - `sample_captured_test_stages_track` is a workspace during stage execution
   - Gets marked with `results_posted_at` timestamp when posted

5. **Standards Can Be Updated:**
   - If user edits standard limits in modal, they're saved to `standard_analytes`
   - This permanently changes the reference limits

---

## 10. SQL Queries for Inspection

### 10.1 Find All Results for a Batch

```sql
SELECT 
  cr.id,
  cr.sample_detail_code,
  cr.result,
  c.name as analyte,
  cr.remark,
  cr.results_posted_at,
  u.name as posted_by
FROM captured_results cr
LEFT JOIN analytes c ON cr.analyte_id = c.id
LEFT JOIN users u ON cr.operator_id = u.id
WHERE cr.sample_header_id = {BATCH_ID}
ORDER BY cr.created_at DESC;
```

### 10.2 Find Tracking Records Not Yet Posted

```sql
SELECT 
  t.id,
  t.captured_result_id,
  t.result,
  t.status,
  t.results_posted_at
FROM sample_captured_test_stages_track t
WHERE t.sample_detail_id = {SAMPLE_ID}
  AND t.results_posted_at IS NULL;
```

### 10.3 Check Standard Analytes for a Sample

```sql
SELECT 
  sa.id,
  st.name as standard_name,
  a.name as analyte_name,
  sa.low,
  sa.high,
  sa.standard_value_type
FROM standard_analytes sa
LEFT JOIN standards st ON sa.standard_id = st.id
LEFT JOIN analytes a ON sa.analyte_id = a.id
WHERE sa.standard_id = {STANDARD_ID}
  AND sa.analyte_id = {ANALYTE_ID};
```

---

This deep dive should give you a complete understanding of how results are saved, where modal data comes from, and how the posting process integrates with the database!

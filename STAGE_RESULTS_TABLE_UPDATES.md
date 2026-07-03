# Stage Results: Tables Updated During Save Operations

**Date:** April 9, 2026  
**Reference:** Method Sequences Module - Results Workflow

---

## 📊 Tables Updated When Saving Stage Results

### **PRIMARY: `sample_captured_test_stages_track` (The Master Track Table)**

This is **always updated** - it stores the core track data with nested JSON for equipment/media/controls/diluents:

| Column | What Gets Saved | When Updated |
|--------|-----------------|------|
| `equipment_data` | JSON with equipment IDs + items (serial, calibration) | When equipment added |
| `media_data` | JSON with media IDs + items (prep date, result, remark, result_nature) | When media added |
| `controls_data` | JSON with control IDs + items (prep, result, remark, result_nature) | When controls added |
| `diluents_data` | JSON with diluent IDs + items (prep, result, remark, result_nature) | When diluents added |
| `result` | Aggregate test result (if result stage) | When sample results entered |
| `started_at` | Timestamp when stage was started | When analyst starts stage |
| `ended_at` | Timestamp when stage was ended | When analyst ends stage |
| `status` | pending → running → completed | State transitions |
| `read_by` | User ID who recorded results | When results entered |
| `remarks` | Analyst notes/comments | When results entered |

---

### **JSON Structure Example**

Data saved in `sample_captured_test_stages_track` as JSON:

```json
{
  "equipment_data": {
    "equipment_ids": [1, 2],
    "items": [
      {
        "id": 1,
        "serial": "ABC123",
        "calibration": "2026-04-15"
      },
      {
        "id": 2,
        "serial": "XYZ789",
        "calibration": "2026-05-01"
      }
    ]
  },
  "media_data": {
    "media_ids": [5],
    "items": [
      {
        "id": 5,
        "preparation": "2026-04-08",
        "result": "Clear",
        "remark": "Normal preparation",
        "result_nature": "qualitative"
      }
    ]
  },
  "controls_data": {
    "controls_ids": [10, 11],
    "items": [
      {
        "id": 10,
        "preparation": "2026-04-07",
        "result": "Growth",
        "remark": "Positive control",
        "result_nature": "qualitative"
      },
      {
        "id": 11,
        "preparation": "2026-04-06",
        "result": "No-Growth",
        "remark": "Negative control",
        "result_nature": "qualitative"
      }
    ]
  },
  "diluents_data": {
    "diluents_ids": [20],
    "items": [
      {
        "id": 20,
        "preparation": "2026-04-08",
        "result": "Prepared",
        "remark": "1:10 dilution",
        "result_nature": "quantitative"
      }
    ]
  }
}
```

---

## 🔴 SECONDARY: Result-Specific Tables (Only for Result Stages)

These tables are **created/updated ONLY if the test stage is a result stage** (`is_result_stage = true`):

### **1️⃣ `track_sample_results` - Sample Measurement Values**

**Updated by:** `updateTrackResult()` method  
**When:** Only for stages with `is_result_stage = true`

**Columns Saved:**
```php
TrackSampleResult::updateOrCreate(
    [
        'track_id' => $track->id,
        'captured_result_id' => $capturedResult->id,  // Links to captured_results
    ],
    [
        'sample_code' => $sample->code,
        'parameter' => $analyteParameter,
        'result' => "45",                             // The actual measurement
        'standard_limit' => "< 100",
        'method' => $methodName,
        'reporting_unit' => "CFU/mL",
        'analyst_id' => Auth::id(),
        'recorded_at' => now(),  // When entered
    ]
);
```

**Example Data:**

| track_id | captured_result_id | sample_code | parameter | result | standard_limit | recorded_at |
|----------|-------------------|-------------|-----------|--------|----------------|-------------|
| 3 | 500 | S-001 | Colony Count | "45" | "< 100" | 2026-04-09 14:30 |
| 6 | 501 | S-002 | Colony Count | "62" | "< 100" | 2026-04-09 14:30 |
| 9 | 502 | S-003 | Colony Count | "38" | "< 100" | 2026-04-09 14:30 |

---

### **2️⃣ `track_media_results` - Media Pass/Fail Results**

**Updated by:** `updateTrackResult()` method  
**When:** If media results provided

**Columns Saved:**
```php
TrackMediaResult::create([
    'track_id' => $track->id,
    'media_id' => $mediaId,
    'result' => "Growth",          // or "No-Growth"
    'analyst_id' => Auth::id(),
    'recorded_at' => now(),
]);
```

**Example Data:**

| track_id | media_id | result | analyst_id | recorded_at |
|----------|----------|--------|-----------|-------------|
| 3 | 1 | Growth | 5 | 2026-04-09 14:30 |
| 3 | 2 | No-Growth | 5 | 2026-04-09 14:30 |

---

### **3️⃣ `track_control_results` - Control Test Results**

**Updated by:** `updateTrackResult()` method  
**When:** If control results provided

**Columns Saved:**
```php
TrackControlResult::create([
    'track_id' => $track->id,
    'control_id' => $controlId,
    'result' => "Growth",          // or "No-Growth"
    'analyst_id' => Auth::id(),
    'recorded_at' => now(),
]);
```

**Example Data:**

| track_id | control_id | result | analyst_id | recorded_at |
|----------|-----------|--------|-----------|-------------|
| 3 | 10 | Growth | 5 | 2026-04-09 14:30 |
| 3 | 11 | No-Growth | 5 | 2026-04-09 14:30 |

---

## 🔄 Complete Data Flow: Which Tables Get Updated When

```
STEP 1: ANALYST SAVES STAGE DETAILS (Equipment, Media, Controls, Diluents)
                         │
                         ▼
         updateMethodSequenceStageData()
                         │
                         ▼
        ALWAYS UPDATE: sample_captured_test_stages_track
        ├─ equipment_data (JSON) ← Equipment serial + calibration
        ├─ media_data (JSON) ← Media prep + result + nature
        ├─ controls_data (JSON) ← Control prep + result + nature
        ├─ diluents_data (JSON) ← Diluent prep + result + nature
        ├─ started_at (timestamp)
        ├─ ended_at (timestamp)
        └─ status (pending/running/completed)

═══════════════════════════════════════════════════════════════

STEP 2: ANALYST ENTERS SAMPLE RESULTS (Only for Result Stages)
                         │
                         ▼
         updateTrackResult()
                         │
                         ▼
        ALWAYS UPDATE: sample_captured_test_stages_track
        ├─ result = "45 CFU/mL"
        ├─ read_by = analyst_id
        ├─ remarks = analyst notes
        └─ updated_at = now()
                         │
                         ▼
        IF is_result_stage = true:
        ├─ CREATE/UPDATE: track_sample_results
        │  ├─ result = "45"
        │  ├─ standard_limit = "< 100"
        │  ├─ method = "TCDD-1"
        │  └─ analyst_id = Auth::id()
        │
        ├─ CREATE: track_media_results (if media passed/failed)
        │  ├─ media_id = [1, 2]
        │  └─ result = ["Growth", "No-Growth"]
        │
        └─ CREATE: track_control_results (if controls tested)
           ├─ control_id = [10, 11]
           └─ result = ["Growth", "No-Growth"]

═══════════════════════════════════════════════════════════════

STEP 3: ANALYST CLICKS "POST RESULTS"
                         │
                         ▼
         postMethodSequenceResults()
                         │
                         ▼
        ✅ NOW UPDATE: captured_results (FIRST TIME!)
        ├─ result = track_sample_results.result
        ├─ method_id = selected_method
        ├─ operator_id = track.read_by
        ├─ results_posted_at = now()
        └─ results_posted_by = auth()->id()
                         │
                         ▼
        ALSO UPDATE: sample_captured_test_stages_track
        ├─ results_posted_at = now()
        └─ results_posted_by = auth()->id()
```

---

## 📋 Summary Table

| Table | Updates During Save? | When? | Purpose |
|-------|----------------------|-------|---------|
| **sample_captured_test_stages_track** | ✅ YES | Always (step 1 & 2) | Master track record (equipment/media/controls as JSON) |
| **track_sample_results** | ✅ YES | When results entered (step 2, result stage only) | Sample measurement values (editable staging area) |
| **track_media_results** | ✅ YES | When media results provided (step 2, result stage only) | Media pass/fail results |
| **track_control_results** | ✅ YES | When control results provided (step 2, result stage only) | Control solution pass/fail results |
| **captured_results** | ❌ NO | **NOT until POST** (step 3 only) | Official results table (updated ONLY when analyst clicks "POST RESULTS") |

---

## 🔴 Critical Understanding: NOT Updated Until POST

⚠️ **The `captured_results` table is NOT updated when you save stage details or results!**

### Timeline

```
┌──────────────────────────────────────────────────────┐
│ SAVE STAGE DETAILS (Step 5)                         │
│ Equipment, Media, Controls, Diluents               │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
        ✅ Updates: sample_captured_test_stages_track
        (equipment/media/controls stored as JSON)
        ❌ Does NOT touch: captured_results

┌──────────────────────────────────────────────────────┐
│ ENTER SAMPLE RESULTS (Result Stage Only)            │
│ (Analyst enters: 45 CFU/mL)                        │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
        ✅ Updates: sample_captured_test_stages_track (result field)
        ✅ Creates: track_sample_results, track_media_results
        ❌ Still NOT in: captured_results!

                 ⚠️ RESULTS STILL IN STAGING AREA

┌──────────────────────────────────────────────────────┐
│ CLICK "POST RESULTS" (Official Posting)             │
│ Analyst reviews and confirms                        │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
        ✅ NOW Updates: captured_results (FIRST TIME!)
        ✅ Sets: results_posted_at, results_posted_by
        ✅ Updates: sample_captured_test_stages_track

                 ✅ RESULTS ARE NOW OFFICIAL
```

---

## 🎯 Practical Examples

### Example 1: Adding Equipment

**User Action:** Enters equipment serial "ABC123", calibration "2026-04-15"

**Tables Updated:**
```
sample_captured_test_stages_track:
  equipment_data = {
    "equipment_ids": [1],
    "items": [{
      "id": 1,
      "serial": "ABC123",
      "calibration": "2026-04-15"
    }]
  }
```

**Result:** ✅ Saved immediately (no other tables affected)

---

### Example 2: Entering Sample Result (Result Stage)

**User Action:** Enters result "45 CFU/mL" for Sample A, Colony Count

**Tables Updated:**

1. **sample_captured_test_stages_track:**
   ```
   result = "45 CFU/mL"
   read_by = 5 (analyst ID)
   remarks = "Normal processing"
   ```

2. **track_sample_results:** (NEW rows created)
   ```
   track_id = 3
   captured_result_id = 500
   result = "45"
   standard_limit = "< 100"
   analyst_id = 5
   recorded_at = 2026-04-09 14:30
   ```

3. **track_media_results & track_control_results:** (If provided)
   ```
   Created with media/control pass/fail data
   ```

**Result:** ✅ Results ready for posting (but NOT yet in captured_results)

---

### Example 3: Posting Results

**User Action:** Clicks "POST RESULTS" button

**Tables Updated:**

1. **captured_results:** (FINALLY updated!)
   ```
   result = "45"
   method_id = 1
   operator_id = 5
   reporting_unit_id = 2
   results_posted_at = 2026-04-09 15:00
   results_posted_by = 7 (admin/approver ID)
   ```

2. **sample_captured_test_stages_track:**
   ```
   results_posted_at = 2026-04-09 15:00
   results_posted_by = 7
   ```

**Result:** ✅ Results are now officially posted (visible in reports)

---

## 📝 Implementation Reference

### Backend Methods

| Method | File | Purpose | Tables Updated |
|--------|------|---------|-----------------|
| `updateMethodSequenceStageData()` | SampleWorkFlowController | Save equipment/media/controls/diluents | sample_captured_test_stages_track |
| `updateTrackResult()` | MethodSequenceRunsController | Save sample/media/control results | sample_captured_test_stages_track + track_*_results |
| `postMethodSequenceResults()` | SampleWorkFlowController | Post results officially | captured_results + sample_captured_test_stages_track |

### Key Validation Rules

```php
// Sample Result Validation
'sample_results.*.captured_result_id' => 'required|exists:captured_results,id',
'sample_results.*.result' => 'nullable|string',
'sample_results.*.standard_limit' => 'nullable|string',

// Media Result Validation
'media_results.*.media_id' => 'required|exists:lab_sub_category,id',
'media_results.*.result' => 'required|in:Growth,No-Growth',

// Control Result Validation
'control_results.*.control_id' => 'required|exists:lab_sub_category,id',
'control_results.*.result' => 'required|in:Growth,No-Growth',

// Equipment Data Validation (NEW - April 9, 2026)
'equipment_items.*.serial' => 'nullable|string',
'equipment_items.*.calibration' => 'nullable|string',

// Result Nature (NEW - April 9, 2026)
'media_items.*.result_nature' => 'nullable|string',      // no_result, qualitative, quantitative
'controls_items.*.result_nature' => 'nullable|string',
'diluents_items.*.result_nature' => 'nullable|string',
```

---

## 🔒 Data Integrity Notes

1. **Editable Until Posted:** Results in `track_sample_results` can be edited before posting
2. **Unposting on Delete:** When a run is deleted, `captured_results` is cleared (unposting)
3. **Transaction Safe:** All updates wrapped in DB transactions with rollback on error
4. **Audit Trail:** All columns track `analyst_id`, `recorded_at`, `results_posted_by`, `results_posted_at`

---

**End of Document**

# SIMPLIFIED: Stage Header Runs & Results Module

**Polucon LIMS - Stage-Based Testing Workflow**  
**Focus:** Saving Sample Results Per Stage & Posting Results  
**Last Updated:** April 8, 2026

---

## Quick Overview

This module manages **multi-stage laboratory testing protocols** on batches of samples:
- Tracks samples through multiple sequential stages
- Captures results only at designated "result stages"
- Stores editable results in a staging table
- Posts finalized results to the official results table

---

## 🔴 CRITICAL UNDERSTANDING: What "POST RESULTS" Actually Does

**The key insight:**

```
Multi-Stage Protocol (5 stages):
├─ Stage 1-3: Non-result stages (no results captured)
├─ Stage 4: LAST RESULT STAGE (is_result_stage=true, is_end_stage=true)
│  └─ Results entered here ONLY
└─ Stage 5: Would never be reached in normal flow

When analyst clicks "POST RESULTS":
→ System finds Stage 4 (the stage with actual results)
→ Copies those results to captured_results table
→ Marks as officially posted

Key point: It posts the LAST stage that has BOTH:
  1. is_result_stage = true
  2. is_end_stage = true
  3. Actual results entered
```

**In practice:**
- Intermediate stages store media/control data (not results)
- Only final stage captures actual sample results
- Post Results finds the final result stage and posts it

---

## Core Concept in One Picture

```
┌─────────────────┐
│  BATCH (3 samples)
└────────┬────────┘
         │
     ▼▼▼▼▼ CREATE RUN
┌──────────────────────────────────────┐
│ Stage Header Run = 1 Protocol Run     │  Creates:
│                                      │  - 15 track records total
│ Sample A ─→ Stage 1 ─→ Stage 2 ─→ Stage 3 (RESULT)
│ Sample B ─→ Stage 1 ─→ Stage 2 ─→ Stage 3 (RESULT)
│ Sample C ─→ Stage 1 ─→ Stage 2 ─→ Stage 3 (RESULT)
└──────────────────────────────────────┘
         │
     ▼▼▼ SAVE RESULTS (per stage)
┌──────────────────────────────────────┐
│ When analyst enters results:         │
│                                      │
│ For RESULT stages only:              │
│ → Create TrackSampleResult rows      │
│   (3 rows if 3 samples)              │
│                                      │
│ For non-result stages:               │
│ → Just track media/controls          │
│   (no sample results saved)          │
└──────────────────────────────────────┘
         │
     ▼▼▼ POST RESULTS
┌──────────────────────────────────────┐
│ Analyst clicks "POST RESULTS"        │
│                                      │
│ For EACH TrackSampleResult:          │
│   Copy result value → CapturedResult │
│   Update captured_results table      │
└──────────────────────────────────────┘
```

---

## Part 1: The 3 Key Tables

### 1. `sample_captured_test_stages_track` (The Orchestrator)

**What it is:** ONE row per sample-stage combination

**Example with 3 samples × 3 stages = 15 rows:**

```
id | sample_detail_id | test_stage_id | status    | result  | read_by | results_posted_at
───|──────────────────|───────────────|───────────|─────────|─────────┼──────────────────
1  | Sample A         | Stage 1       | completed | -       | -       | NULL
2  | Sample A         | Stage 2       | completed | -       | -       | NULL  
3  | Sample A         | Stage 3 [*]   | completed | "45"    | Matt(5) | 2026-04-08 14:30
4  | Sample B         | Stage 1       | completed | -       | -       | NULL
5  | Sample B         | Stage 2       | completed | -       | -       | NULL
6  | Sample B         | Stage 3 [*]   | completed | "62"    | Matt(5) | 2026-04-08 14:30
7  | Sample C         | Stage 1       | completed | -       | -       | NULL
8  | Sample C         | Stage 2       | completed | -       | -       | NULL
9  | Sample C         | Stage 3 [*]   | completed | "38"    | Matt(5) | 2026-04-08 14:30

[*] = is_result_stage = true (only result stages store actual sample results)
```

**Key columns:**
- `result` - Aggregate result value (if result stage)
- `read_by` - Who entered the results
- `results_posted_at` - When posted (gets populated on POST)
- `results_posted_by` - Who posted

**Full Schema:**

```sql
CREATE TABLE `sample_captured_test_stages_track` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `stage_header_run_id` bigint NOT NULL,
  `captured_result_id` bigint NOT NULL,
  `sample_detail_id` bigint NOT NULL,
  `stage_header_id` bigint NOT NULL,
  `test_stage_id` bigint NOT NULL,
  `user_id` unsignedInteger DEFAULT NULL,
  `read_by` unsignedBigInteger DEFAULT NULL,
  `status` varchar(255) DEFAULT 'pending',
  `started_at` timestamp NULL,
  `ended_at` timestamp NULL,
  `reading_date` timestamp NULL,
  `result` text NULL,
  `remarks` text NULL,
  `results_posted_at` timestamp NULL,
  `results_posted_by` unsignedBigInteger NULL,
  `equipment_data` json NULL,
  `media_data` json NULL,
  `controls_data` json NULL,
  `diluents_data` json NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`stage_header_run_id`) REFERENCES `stage_header_runs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`captured_result_id`) REFERENCES `captured_results`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sample_detail_id`) REFERENCES `sample_details`(`id`) ON DELETE CASCADE,
  INDEX (`stage_header_run_id`),
  INDEX (`test_stage_id`),
  INDEX (`status`)
) ENGINE=InnoDB;
```

---

### 2. `track_sample_results` (Editable Result Storage)

**What it is:** Stores EDITABLE results only for result stages

**Created ONLY when:**
- `test_stage.is_result_stage = true`
- AND analyst enters results

**Example for Stage 3 (result stage) with 3 samples:**

```
id  | track_id | captured_result_id | parameter     | result  | standard_limit
────|──────────|────────────────────|───────────────|─────────┼────────────────
100 | 3        | 500                | Colony Count  | "45"    | "< 100"
101 | 6        | 501                | Colony Count  | "62"    | "< 100"
102 | 9        | 502                | Colony Count  | "38"    | "< 100"
```

**Key insight:** This table stores **EDITABLE** results. Before posting, analyst can edit values here.

**Full Schema:**

```sql
CREATE TABLE `track_sample_results` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `track_id` bigint NOT NULL,
  `captured_result_id` bigint NOT NULL,
  `sample_code` varchar(255) NOT NULL,
  `parameter` varchar(255) NOT NULL,
  `method` varchar(255) DEFAULT NULL,
  `reporting_unit` varchar(255) DEFAULT NULL,
  `result` text DEFAULT NULL,
  `standard_limit` varchar(255) DEFAULT NULL,
  `analyst_id` bigint DEFAULT NULL,
  `recorded_at` timestamp NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`track_id`) REFERENCES `sample_captured_test_stages_track`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`captured_result_id`) REFERENCES `captured_results`(`id`) ON DELETE CASCADE,
  INDEX (`track_id`),
  INDEX (`captured_result_id`)
) ENGINE=InnoDB;
```

---

### 3. `captured_results` (Final Results Table)

**What it is:** Official test results (gets updated ONLY during POST)

**Before POST:**

```
id  | sample_detail_code | result | results_posted_at
────|────────────────────|────────|──────────────────
500 | S-001              | NULL   | NULL (not posted yet)
501 | S-002              | NULL   | NULL
502 | S-003              | NULL   | NULL
```

**After POST:**

```
id  | sample_detail_code | result | results_posted_at        | results_posted_by
────|────────────────────|────────|──────────────────────────┼──────────────────
500 | S-001              | "45"   | 2026-04-08 14:30:00      | 7 (admin user)
501 | S-002              | "62"   | 2026-04-08 14:30:00      | 7
502 | S-003              | "38"   | 2026-04-08 14:30:00      | 7
```

---

## Part 2: How Saving Results Works

### When Analyst Enters Results: `updateTrackResult()`

**File:** `app/Http/Controllers/MethodSequenceRunsController.php` (Lines 216-330)

**Simplified code:**

```php
public function updateTrackResult(Request $request, SampleCapturedTestStagesTrack $track)
{
    // Analyst sends form data:
    // - sample_results[] = array of { captured_result_id, result, standard_limit }
    // - media_results[] = array of { media_id, result (Growth/No-Growth) }
    
    DB::transaction(function () use ($validated, $track) {
        
        // 1. Update track record itself
        $track->updateResult(
            result: $validated['result'],
            remarks: $validated['remarks'],
            read_by: Auth::id()
        );
        
        // 2. ONLY FOR RESULT STAGES: Create TrackSampleResult rows
        foreach ($validated['sample_results'] as $sampleResult) {
            TrackSampleResult::updateOrCreate(
                [
                    'track_id' => $track->id,
                    'captured_result_id' => $sampleResult['captured_result_id'],
                ],
                [
                    'result' => $sampleResult['result'],              // EDITABLE
                    'standard_limit' => $sampleResult['standard_limit'],
                    'analyst_id' => Auth::id(),
                    'recorded_at' => now(),
                ]
            );
        }
        
        // 3. Save media pass/fail
        foreach ($validated['media_results'] as $media) {
            TrackMediaResult::create([
                'track_id' => $track->id,
                'media_id' => $media['media_id'],
                'result' => $media['result'],  // Growth or No-Growth
            ]);
        }
    });
}
```

**What gets saved to database:**

```
track_sample_results table:
├─ track_id=3 (Sample A, Stage 3)
│  ├─ captured_result_id=500
│  ├─ result="45"
│  └─ standard_limit="< 100"
│
├─ track_id=6 (Sample B, Stage 3)
│  ├─ captured_result_id=501
│  ├─ result="62"
│  └─ standard_limit="< 100"
│
└─ track_id=9 (Sample C, Stage 3)
   ├─ captured_result_id=502
   ├─ result="38"
   └─ standard_limit="< 100"
```

### Frontend Data Sent

```javascript
{
    result: "45 CFU/mL",
    remarks: "Normal processing",
    sample_results: [
        {
            captured_result_id: 500,
            result: "45",
            standard_limit: "< 100"
        }
    ],
    media_results: [
        { media_id: 1, result: "Growth" },
        { media_id: 2, result: "No-Growth" }
    ]
}
```

---

## Part 3: How Posting Results Works

### When Analyst Clicks "POST RESULTS": `postMethodSequenceResults()`

**File:** `app/Http/Controllers/SampleWorkFlowController.php` (Lines 6888-6968)

**What happens:**

1. **Frontend loads post modal** → `getMethodSequenceTrackingResults()`
   - Fetches ALL tracks where `result` is NOT NULL
   - Typically only the LAST result stage has results
   - Displays these in a modal for final review/edits

2. **Backend processes post** → `postMethodSequenceResults()`
   - Gets the tracks from the modal
   - For each track (typically from final stage only):
     - Copy `result` to `captured_results`
     - Set `results_posted_at` timestamp
     - Mark as official/locked

**Simplified code:**

```php
public function postMethodSequenceResults(Request $request)
{
    // tracking_data contains only tracks with results
    // (typically from LAST result stage only)
    
    foreach ($request->tracking_data as $data) {
        // 1. Get the track record
        $track = SampleCapturedTestStagesTrack::find($data['track_id']);
        $captured = $track->capturedResult;
        
        // 2. COPY result to final table
        $captured->update([
            'result' => $track->result,                      // From track.result
            'remark' => $data['remark'],
            'method_id' => $data['method_id'],
            'operator_id' => $track->read_by,               // Who recorded it
            'reporting_unit_id' => $data['reporting_unit_id'],
            'result_reporting_symbol' => $data['reporting_symbol'],
            'main_value' => $data['main_value'],
            'secondary_value' => $data['secondary_value'],
            
            // CRITICAL - Mark as posted
            'results_posted_at' => now(),                   // ← Timestamp
            'results_posted_by' => auth()->id(),            // ← Admin user
        ]);
        
        // 3. Mark track as posted
        $track->update([
            'results_posted_at' => now(),
            'results_posted_by' => auth()->id()
        ]);
    }
}
```

**What happens in database:**

```
BEFORE POST:
captured_results: result=NULL, results_posted_at=NULL

         ▼▼▼ POST CLICKED ▼▼▼

AFTER POST (Last stage results now official):
captured_results: result="45", results_posted_at="2026-04-08 14:30:00"
                  results_posted_by=7
```

---

## Part 4: The Data Flow - Concrete Example

### Scenario: 3-stage protocol, 2 samples, only Stage 3 has `is_result_stage=true`

#### Step 1: Create Run

```
Input: 2 samples, 3 stages
Output: 6 track records created
├─ track_id=1: S1 (Sample A, Stage 1)
├─ track_id=2: S2 (Sample A, Stage 2)
├─ track_id=3: S3 (Sample A, Stage 3) ← Result stage
├─ track_id=4: S1 (Sample B, Stage 1)
├─ track_id=5: S2 (Sample B, Stage 2)
└─ track_id=6: S3 (Sample B, Stage 3) ← Result stage
```

#### Step 2: Stage 1 Completes (non-result stage)

```
Analyst ends Stage 1
No TrackSampleResult rows created (is_result_stage=false)
Status: Stage 1 → auto-start Stage 2
```

#### Step 3: Stage 2 Completes (non-result stage)

```
Analyst ends Stage 2
No TrackSampleResult rows created (is_result_stage=false)
Status: Stage 2 → auto-start Stage 3
```

#### Step 4: Stage 3 Complete - ANALYST ENTERS RESULTS 🔴 **KEY MOMENT**

```
Analyst inputs:
├─ Sample A: result="45 CFU/mL"
├─ Sample B: result="62 CFU/mL"

updateTrackResult() called:
├─ TrackSampleResult.create(track_id=3, result="45")  ← Stored as editable
├─ TrackSampleResult.create(track_id=6, result="62")  ← Stored as editable
└─ track.result updated to aggregate value

Results are NOW SAVED but NOT YET POSTED
captured_results still has result=NULL
```

#### Step 5: ANALYST CLICKS "POST RESULTS" 🔴 **KEY MOMENT**

```
postMethodSequenceResults() called:

For each TrackSampleResult:
├─ Sample A: TrackSampleResult(result="45")
│  ├─ CapturedResult.result = "45"
│  ├─ CapturedResult.results_posted_at = NOW
│  └─ Save CapturedResult
│
└─ Sample B: TrackSampleResult(result="62")
   ├─ CapturedResult.result = "62"
   ├─ CapturedResult.results_posted_at = NOW
   └─ Save CapturedResult

Results are NOW POSTED (official)
```

---

## Part 5: Which Results Get Posted?

### Rule: POST Posts the LAST Result Stage (that is also End Stage)

**How it works:**
1. Frontend calls `getMethodSequenceTrackingResults()` 
2. This returns ALL tracks where `result` is NOT NULL
3. Typically, only the LAST result stage has results entered
4. System posts results from whatever stage has results filled in

**Example Protocol:**

```
Stage 1: Dilution Prep           is_result_stage = FALSE
Stage 2: Incubation (48h)        is_result_stage = FALSE
Stage 3: Check Contamination     is_result_stage = FALSE
Stage 4: Count Colonies ←─────── is_result_stage = TRUE ✅ is_end_stage = TRUE ✅
(This is the only stage with results)

Only Stage 4 results get posted because:
- It's the LAST result stage
- It's marked as is_end_stage = true
- Results were entered here
```

**Key Insight:**
- Unlike intermediate stages (1-3) which have `is_result_stage=false` (no results captured)
- The final stage (4) has both:
  - `is_result_stage = true` (results are captured)
  - `is_end_stage = true` (protocol ends here)
- Results are only posted from stages where data was actually entered
- Post skips stages with NULL/empty results

---

## Part 6: Key Files

| What | File | Lines |
|------|------|-------|
| Save Results | `app/Http/Controllers/MethodSequenceRunsController.php` | 216-330 |
| Post Results | `app/Http/Controllers/SampleWorkFlowController.php` | 6888-6968 |
| Track Table Migration | `database/migrations/2025_10_23_071506_create_sample_captured_test_stages_track_table.php` | - |
| Results Table Migration | `database/migrations/2025_11_12_153131_create_track_sample_results_table.php` | - |
| Frontend | `public/js/method-sequences.js` | - |
| View | `resources/views/layouts/lab/method-sequences/index.blade.php` | - |

---

## Summary: Answers to Key Questions

### ❓ Q: Does post results pick the last stage's results?

✅ **A:** YES - Post Results picks the **LAST STAGE that is BOTH an END STAGE and a RESULT STAGE**

Process:
1. `getMethodSequenceTrackingResults()` fetches all tracks with `result` NOT NULL
2. Typical scenario: only the final stage has results entered
3. System posts those results to `captured_results`
4. Stages with empty results are skipped

**Why this works:**
- Intermediary stages (`is_result_stage=false`) don't capture results
- Only final stage (`is_result_stage=true` + `is_end_stage=true`) has results
- Post Results automatically finds and posts the final stage's data

---

### ❓ Q: Are stage results stored in TrackSampleResults?

✅ **A:** YES - only for result stages (`is_result_stage=true`)

---

### ❓ Q: When saving stage results per stage, where do they go?

✅ **A:** `track_sample_results` table (editable staging area)

Steps:
1. Analyst enters results in UI
2. `updateTrackResult()` is called
3. Records inserted/updated in `track_sample_results`
4. Multiple rows possible if multiple samples/analytes in one stage

---

### ❓ Q: Does post results pick the last result stage?

✅ **A:** YES - it gets all `TrackSampleResult` records from ALL result stages, then copies them to `captured_results`

**Important:** It's not "the last stage" - it's "all stages marked as result stages"

---

### ❓ Q: How does posting update captured results?

✅ **A:** Copies `result` from `TrackSampleResult.result` → `CapturedResult.result`, sets `results_posted_at` timestamp

Process:
1. Analyst clicks "POST RESULTS"
2. For each `TrackSampleResult`:
   - Find corresponding `CapturedResult` row
   - Copy `result`, `method_id`, `operator_id`, etc.
   - Set `results_posted_at = now()`
   - Set `results_posted_by = auth()->id()`
3. Update `sample_captured_test_stages_track.results_posted_at`
4. Results become "official"

---

## Visual Summary

### Data Movement

```
┌──────────────────────────────────────────────────────────┐
│                                                          │
│  Frontend Form                                           │
│  (Analyst enters: result="45 CFU/mL")                   │
│           │                                              │
│           ▼                                              │
│  POST /method-sequence-runs/tracks/{track}/result       │
│           │                                              │
│           ▼                                              │
│  updateTrackResult()                                     │
│           │                                              │
│    ┌──────┴──────┬─────────────┐                         │
│    ▼             ▼             ▼                         │
│  TRACK     TRACK_SAMPLE   TRACK_MEDIA                   │
│  (update)  RESULTS        RESULTS                       │
│            (insert/       (insert)                       │
│            update)                                       │
│                                                          │
│  [STAGING AREA - Analyst can still edit]                │
│                                                          │
│           ▼                                              │
│  Analyst clicks "POST RESULTS"                           │
│           │                                              │
│           ▼                                              │
│  POST /method-sequences/post-results                    │
│           │                                              │
│           ▼                                              │
│  postMethodSequenceResults()                            │
│           │                                              │
│    ┌──────┴──────────────┬──────────────────┐            │
│    ▼                     ▼                  ▼            │
│  CAPTURED_RESULTS   TRACK                STANDARD      │
│  (update with       (update              ANALYTES       │
│   result="45")      results_posted_at)   (update)      │
│                                                          │
│  [OFFICIAL - Results now posted]                        │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

---

## Key Takeaways

1. **Two-Tier Storage:**
   - `track_sample_results` = editable staging area
   - `captured_results` = official locked table

2. **Result Stages Only:**
   - Only stages with `is_result_stage=true` create `TrackSampleResult` rows
   - Non-result stages just track media/controls

3. **Multi-Sample Processing:**
   - Each sample gets its own track per stage
   - Results saved independently per sample
   - All results posted together

4. **Timestamps Matter:**
   - `results_posted_at` marks when posted
   - `results_posted_by` shows who posted
   - Provides audit trail

5. **Editable Until Posted:**
   - Results can be edited in `track_sample_results`
   - Once posted to `captured_results`, they're official
   - New entries created for subsequent edits

---

---

## BONUS: What Happens When You Press "Save Stage Details" (Step 5)

### Frontend: `saveStageDetails()` - Collects All Stage Data

**File:** `public/js/method-sequences.js` (Line 2680)

**Step 5 collects:**

```javascript
// 1. Equipment data (serial numbers, calibration dates)
equipment_items: [
    { id: 1, serial: "ABC123", calibration: "2026-04-15" },
    { id: 2, serial: "XYZ789", calibration: "2026-05-01" }
]

// 2. Media/Solution data (preparation notes, remarks)
media_items: [
    { id: 1, preparation: "Sterilized at 121°C", remark: "Normal prep" },
    { id: 2, preparation: "Pre-mixed", remark: "Store in fridge" }
]

// 3. Controls data (preparation, expiry, result, remark)
controls_items: [
    { id: 1, preparation: "Prepared fresh", expiry: "2026-04-15", result: "Growth", remark: "Normal" },
    { id: 2, preparation: "From vial", expiry: "2026-03-01", result: "No-Growth", remark: "Negative control" }
]

// 4. Diluents data (preparation, remark)
diluents_items: [
    { id: 1, preparation: "1:10 dilution", remark: "Standard prep" }
]

// 5. IF RESULT STAGE: Also collect sample results
sample_results: [
    { captured_result_id: 500, result: "45", method: "TCDD-1", reporting_unit: "CFU/mL" }
]
```

### Sends POST to Backend

**Endpoint:** `POST /method-sequences/tracks/{trackId}/update`

**File:** `app/Http/Controllers/SampleWorkFlowController.php::updateMethodSequenceStageData()` (Line 6271)

### Backend: Updates Track Record

**What gets saved to database:**

```
UPDATE sample_captured_test_stages_track SET
    equipment_data = {
        "equipment_ids": [1, 2],
        "items": [
            { "id": 1, "serial": "ABC123", "calibration": "2026-04-15" },
            { "id": 2, "serial": "XYZ789", "calibration": "2026-05-01" }
        ]
    },
    media_data = {
        "media_ids": [1, 2],
        "items": [...]
    },
    controls_data = {...},
    diluents_data = {...}
WHERE id = {trackId}
```

**Key observation:** All data is stored as JSON in the track record

### If Result Stage: Also Saves Sample Results

```
If is_result_stage = true:
    Create/Update TrackSampleResult rows
    ├─ For Sample A: result="45"
    └─ For Sample B: result="62"
```

### Complete Data Flow for Step 5

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  Step 5: Analyst enters                                 │
│  ├─ Equipment: serial, calibration date                 │
│  ├─ Media: preparation, remarks                         │
│  ├─ Controls: preparation, result, remark               │
│  ├─ Diluents: preparation, remarks                      │
│  └─ (If result stage) Sample results                    │
│                                                         │
│         ▼                                               │
│  Click "Save Stage Details"                            │
│         │                                               │
│  saveStageDetails() collects all data                   │
│         │                                               │
│  POST /method-sequences/tracks/{trackId}/update        │
│         │                                               │
│  updateMethodSequenceStageData() validates & saves      │
│         ▼                                               │
│  ┌──────────────────────────────────────────────┐      │
│  │ sample_captured_test_stages_track table:     │      │
│  │ - equipment_data (JSON)  ✅ SAVED            │      │
│  │ - media_data (JSON)      ✅ SAVED            │      │
│  │ - controls_data (JSON)   ✅ SAVED            │      │
│  │ - diluents_data (JSON)   ✅ SAVED            │      │
│  │                                              │      │
│  │ If result stage:                             │      │
│  │ - track_sample_results ✅ CREATED/UPDATED   │      │
│  └──────────────────────────────────────────────┘      │
│         │                                               │
│  Success! Page reloads or continues                    │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

### Summary: Step 5 "Save Stage Details" Does

| Action | Where Saved | Purpose |
|--------|-------------|---------|
| Equipment registration | `equipment_data` JSON | Track which equipment used |
| Media/solution prep | `media_data` JSON | Track media used and prep notes |
| Controls status | `controls_data` JSON | Track control samples |
| Diluents info | `diluents_data` JSON | Track diluent solutions |
| Sample results (if result stage) | `track_sample_results` table | Store sample measurement values |

All data is tied to ONE track record = One sample in one stage

---

**End of Document**

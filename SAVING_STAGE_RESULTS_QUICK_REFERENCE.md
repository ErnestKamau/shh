# Stage Results Posting - Quick Reference Guide

## 🎯 Quick Answer: Where Does Modal Data Come From?

### **When you click "Post Results":**

1. **Frontend** calls `getMethodSequenceTrackingResults()`
2. **Backend** queries `sample_captured_test_stages_track` for all records with `result NOT NULL`
3. **For each tracking record**, it fetches related data from:
   - `sample_details` → Sample code
   - `analytes` → Analyte name
   - `standards` → Standard reference names
   - `standard_analytes` → Low/High limits
   - `analysis_methods` → Available methods
   - `reporting_units` → Available units
4. **Results are enriched** with lookups and sent as JSON to modal
5. **Modal renders table** with this data

---

## 📊 Core Tables (Remember These!)

| Table | Purpose | Key for Posting |
|-------|---------|---|
| `sample_captured_test_stages_track` | **Temporary workspace** during stage | Result source, tracking reference |
| `captured_results` | **Official results** (final destination) | Where posted results go |
| `sample_details` | Sample info | Sample code display |
| `analytes` | What's being measured | Analyte name display |
| `standards` | Reference values for QC | Standard name, limits source |
| `standard_analytes` | Standard + Analyte + Limits | The **low/high** values shown in modal |
| `analysis_methods` | Available methods | Method dropdown options |
| `reporting_units` | UN codes/abbreviations | Unit dropdown options |

---

## 🔄 The Three Key Moments

### **MOMENT 1: Loading Modal (Enrichment)**
```
sample_captured_test_stages_track
  ├─ Get result
  └─ capturedResult → Load all related data
     ├─ sample_details (sample code)
     ├─ analytes (analyte name)
     ├─ standards (standard name)
     ├─ standard_analytes (limits: low/high)
     ├─ analysis_methods (method name)
     └─ reporting_units (unit name)
```

### **MOMENT 2: User Edits Modal**
```
User can change:
✏️  Reporting Symbol (=, <, >, ≤, ≥)
✏️  Standard Limits (low/high) 
✏️  Method (dropdown)
✏️  Unit (dropdown)

Cannot change:
❌ Sample Code
❌ Analyte
❌ Result
❌ Remark (auto-calculated)
```

### **MOMENT 3: Posting Results**
```
For each row in modal:
  1. Save to captured_results table
  2. Update standard_analytes if limits were edited
  3. Mark tracking record as posted (results_posted_at)
  4. Mark who posted it (results_posted_by)
```

---

## 🎬 Workflow Sequence

```
┌─────────────────────────────────────────────────────────────┐
│ Stage execution: Result entered into sample_captured_test_ │
│ stages_track.result by lab technician                      │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ Analyst clicks "Post Results" button                        │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ Frontend: showPostResultsModal()                            │
│ → AJAX GET /method-sequences/batch/{id}/tracking-results   │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ Backend: getMethodSequenceTrackingResults()                 │
│ Query:                                                      │
│   SELECT * FROM sample_captured_test_stages_track          │
│   WHERE result NOT NULL                                     │
│   AND sample_header_id = {batchId}                         │
│                                                             │
│ For each: Fetch and enrich with:                           │
│   - sample_details.sample_code                             │
│   - analytes.name                                          │
│   - standards.name                                         │
│   - standard_analytes.low/high                             │
│   - analysis_methods                                       │
│   - reporting_units                                        │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ Response: JSON with enriched tracking records              │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ Frontend: renderPostResultsTable()                          │
│ Modal displays table with all data                         │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ User edits:                                                 │
│ - Reporting symbol (dropdown)                              │
│ - Standard limits (number inputs) ← EDIT standard_analytes │
│ - Method (dropdown)                                        │
│ - Unit (dropdown)                                          │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ User clicks "Yes, Post Results"                            │
│ confirmPostResults()                                        │
│ → AJAX POST /method-sequences/post-results                 │
│    With: batch_id, tracking_data[]                         │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ Backend: postMethodSequenceResults()                        │
│ For each tracking_data record:                             │
│   1. UPDATE captured_results SET:                          │
│      - result = track.result                               │
│      - remark = data.remark                                │
│      - method_id = data.method_id                          │
│      - operator_id = track.read_by                         │
│      - reporting_unit_id = data.reporting_unit_id          │
│      - result_reporting_symbol = data.reporting_symbol     │
│      - main_value = data.main_value                        │
│      - etc...                                              │
│   2. UPDATE standard_analytes (if limits changed)          │
│   3. UPDATE sample_captured_test_stages_track SET:         │
│      - results_posted_at = now()                           │
│      - results_posted_by = auth().id()                     │
└────────────────────────┬────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ ✅ Results now official in captured_results table          │
│ ✅ Modal closes                                             │
│ ✅ UI updates                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## 📋 Modal Table Structure

| Column | Type | Source | Editable? | Saves To |
|--------|------|--------|-----------|----------|
| **Sample Code** | Text | `sample_details.sample_code` | ❌ No | — |
| **Analyte** | Text | `analytes.name` | ❌ No | — |
| **Result** | Text | `sample_captured_test_stages_track.result` | ❌ No | `captured_results.result` |
| **Reporting Symbol** | Dropdown | User selection | ✏️ YES | `captured_results.result_reporting_symbol` |
| **Standard** | Text | `standards.name` | ❌ No | — |
| **Standard Limits** | Number inputs | `standard_analytes.low` / `standard_analytes.high` | ✏️ YES | `standard_analytes.low` / `.high` |
| **Remark** | Text | Calculated (result vs limits) | ❌ No | `captured_results.remark` |
| **Method** | Dropdown | `analysis_methods` list | ✏️ YES | `captured_results.method_id` |
| **Reporting Unit** | Dropdown | `reporting_units` list | ✏️ YES | `captured_results.reporting_unit_id` |

---

## 🔍 Quick SQL Queries

### Get unposted results for a batch:
```sql
SELECT t.id, t.result, t.status, c.analyte_id 
FROM sample_captured_test_stages_track t
LEFT JOIN captured_results c ON t.captured_result_id = c.id
WHERE c.sample_header_id = {BATCH_ID}
AND t.results_posted_at IS NULL;
```

### Get posted results for a batch:
```sql
SELECT cr.id, cr.result, cr.remark, a.name as analyte
FROM captured_results cr
LEFT JOIN analytes a ON cr.analyte_id = a.id
WHERE cr.sample_header_id = {BATCH_ID}
ORDER BY cr.created_at DESC;
```

### Get standard limits for an analyte:
```sql
SELECT sa.id, st.name, sa.low, sa.high
FROM standard_analytes sa
LEFT JOIN standards st ON sa.standard_id = st.id
WHERE sa.analyte_id = {ANALYTE_ID};
```

---

## 🎓 Key Concepts

### **sample_captured_test_stages_track = Temporary Workspace**
- Created when stage runs
- Holds `result` entered by technician
- Gets marked with `results_posted_at` when posted
- Links to both `captured_results` AND sample via `captured_result_id`

### **captured_results = Official Results**
- Empty until results are posted
- Once populated, is the source of truth for reports
- Contains all metadata about the result (remark, method, unit, etc)

### **Standard Analytes = Limits Reference**
- Defines acceptable range (low/high) for analyte with standard
- Can be edited in modal and saved back
- Used to calculate remark (PASS/FAIL/WARNING)

### **Enrichment = Lookup & Join Process**
- Modal loads empty at first
- Backend fetches tracking record
- Backend looks up all related data (sample, analyte, standards, etc)
- All combined into rich JSON object
- Frontend renders (displays multiple lookups per row efficiently)

---

## ⚡ Performance Notes

- **Modal loading:** Queries `sample_captured_test_stages_track` with related data eagerly loaded
- **Posting:** Batch updates to `captured_results` and `standard_analytes`
- **Limits:** Works well up to ~100 results per batch (typical use case)

---

## 🛠️ Common Issues & Troubleshooting

### **Issue: Modal shows no data**
- **Check:** Are there records in `sample_captured_test_stages_track` with `result NOT NULL`?
- **Check:** Is the `captured_result_id` properly linked?

### **Issue: Standard limits not showing**
- **Check:** Does `sample_details.main_standard` point to valid `standards.id`?
- **Check:** Do `standard_analytes` records exist for that standard+analyte combo?

### **Issue: Remark calculation wrong**
- **Check:** Are the `low`/`high` values in `standard_analytes` correct?
- **Check:** Is the remark calculation logic in JavaScript updated?

### **Issue: Posted data doesn't match what I entered**
- **Check:** Verify the `POST` payload has correct field names
- **Check:** Check `captured_results` table directly with SQL

---

## 📚 Related Files

- [Backend Controller](app/Http/Controllers/SampleWorkFlowController.php#L7065) - `getMethodSequenceTrackingResults()`
- [Backend Posting](app/Http/Controllers/SampleWorkFlowController.php#L7124) - `postMethodSequenceResults()`
- [Frontend Modal](resources/views/layouts/lab/method-sequences/index.blade.php#L1703) - Modal HTML
- [Frontend Logic](public/js/method-sequences.js#L1900) - Modal rendering & posting
- [Models](app/Models/) - `SampleCapturedTestStagesTrack.php`, `CapturedResult.php`
- [Migrations](database/migrations/) - Table schemas

---

**Last Updated:** 2026-04-10
**Document:** SAVING_STAGE_RESULTS_DEEP_DIVE.md + SAVING_STAGE_RESULTS_QUICK_REFERENCE.md

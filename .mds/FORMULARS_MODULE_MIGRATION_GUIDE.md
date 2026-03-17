# Formulars Module Migration Guide

This document captures every prerequisite, schema detail, configuration hook, and integration touch-point needed to port the Formulars (worksheet) module into another Laravel 12 / Livewire 3 application.

---

## 1. Platform & Package Requirements

- **Laravel**: 12.x, PHP 8.2+
- **Livewire**: `livewire/livewire:^3.6` (all UI is Livewire-driven)
- **Expression Engine**: `symfony/expression-language:^7.3` (formula evaluator)
- **Spreadsheet Import/Export** *(optional but used for lookup tables)*: `maatwebsite/excel:^3.1`
- **Front-End**: Bootstrap 4/5 compatible (current templates rely on bootstrap classes and MDI icons)
- **Queues & Events**: No dedicated queues required; all execution is synchronous.

> Install packages through Composer and publish necessary Livewire assets before migrating UI components.

---

## 2. Database Schema Summary

> Run migrations in the order below (foreign keys assume upstream tables already exist: `users`, `analytes`, `lookup_tables`, `sample_headers`, `sample_details`, `captured_results`, `analysis_elements`, `method_sequences`).

### 2.1 Core Formula Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `formulas` | Master formula definitions. | `id`, `name` (unique per soft delete), `description`, `is_active`, timestamps, soft deletes. |
| `formula_versions` | Version history per formula. | `id`, `formula_id` (FK→`formulas`), `version_number`, `is_active`, `created_by` & `approved_by` (FK→`users`), `approved_at`, timestamps, soft deletes, unique `(formula_id, version_number, deleted_at)`. |
| `formula_steps` | Ordered steps executed by the evaluator. | `id`, `formula_version_id` (FK), `step_number`, `variable_name`, `step_type` ENUM (`input`, `derived`, `lookup`, `parameter_result`), `expression`, `label`, `description`, `lookup_config` (JSON), `analyte_id` (FK→`analytes`), timestamps, soft deletes, unique `(formula_version_id, variable_name, deleted_at)`. |
| `formula_mandatory_fields` | Worksheet-level metadata fields captured with each execution. | `id`, `formula_version_id` (FK), `label`, `field_type` ENUM (`input`, `datetime`, `date`, `dataset_related`), `field_value_name`, `order`, `help_text`, `model_tied_to`, `is_required`, timestamps, soft deletes. |

### 2.2 Worksheet Capture Tables

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `sample_captured_worksheet_formulas` | Header per captured result × formula × sample. | `id`, `sample_header_id` (FK→`sample_headers`), `sample_detail_id` (FK→`sample_details`), `captured_result_id` (FK→`captured_results`), `formular_id` (FK→`formulas`), `date`, `lab_no`, `sample_details`, `time_in`, `done_by_user_id`, `time_out`, `read_by_user_id`, `read_date`, `final_result`, `posted_at`, `posted_by_user_id`, timestamps, soft deletes. |
| `sample_worksheet_formular_step_data` | Stores step-level values per worksheet run. | `id`, `worksheet_formular_id` (FK→`sample_captured_worksheet_formulas`), `formula_step_id` (FK→`formula_steps`), `step_value`, `overridden_lookup_table_id` (FK→`lookup_tables`, allows per-run override), timestamps. |
| `sample_worksheet_formular_mandatory_data` | Stores mandatory field values per run. | `id`, `worksheet_formular_id`, `formula_mandatory_field_id` (FK→`formula_mandatory_fields`), `field_value`, timestamps. |

### 2.3 Integration Columns

| Migration | Description |
|-----------|-------------|
| `analysis_elements` additions | Adds `formular_id` (nullable FK), `has_method_sequence` (bool), `method_sequence_id` (nullable FK). |
| `captured_results` additions | Adds `analysis_element_id`, `formular_id`, `method_sequence_id` to tie results back to their configuration. |
| `formula_steps` enum updates | Ensure `step_type` ENUM includes `parameter_result` where required. |

> Review additional maintenance migrations (e.g., range overrides, posting metadata) to match target schema exactly.

---

## 3. Services & Business Logic

| Service | Location | Responsibility |
|---------|----------|----------------|
| `App\Services\Formulars\FormulaEvaluator` | Evaluates a `FormulaVersion` using Symfony ExpressionLanguage, executes derived and lookup steps, returns final result and execution trace. |
| `App\Services\Formulars\LookupService` | Resolves lookup table values (range-based and key-based), handles import/export helpers. |

These services rely on:

- `App\Models\Formulars\LookupTable` & `LookupTableEntry`
- Global constants provided by `App\Models\Formulars\GlobalVariable`

Ensure these models & relationships are migrated alongside the services.

---

## 4. Livewire Components & Views

| Component | Purpose | Key View |
|-----------|---------|----------|
| `App\Livewire\Formulars\FormulaManager` | CRUD for formulas and versions. | `resources/views/livewire/formulars/formula-manager.blade.php` |
| `App\Livewire\Formulars\FormulaStepEditor` | Rich UI for maintaining steps (drag/drop, search, modals). | `resources/views/livewire/formulars/formula-step-editor.blade.php` |
| `App\Livewire\Formulars\GlobalVariableManager` | Manage constants consumed by evaluator. | `resources/views/livewire/formulars/global-variable-manager.blade.php` |
| `App\Livewire\Formulars\LookupTableManager` & `LookupTableEntryManager` | Define lookup tables and manage entries (supports imports & manual edits). | `resources/views/livewire/formulars/lookup-table-manager.blade.php`, `lookup-table-entry-manager.blade.php` |
| `App\Livewire\Formulars\WorksheetExecutor` | Standalone executor/test harness for formulas. | `resources/views/formulars/execute.blade.php` |
| `App\Livewire\Formulars\WorksheetHistory` | Audit trail of executions. | `resources/views/formulars/history.blade.php` |
| `App\Livewire\Worksheets\WorksheetManager` | Entry point for per-batch worksheet management. | `resources/views/livewire/worksheets/worksheet-manager.blade.php` |
| `App\Livewire\Worksheets\FormulaWorksheet` | Core worksheet UI embedded in batch workflow (captures, auto-saves, posts results). | `resources/views/livewire/worksheets/formula-worksheet.blade.php` |

> When migrating, copy both component classes and their corresponding Blade templates. The templates expect Bootstrap styles, Alpine helpers, and Livewire directives introduced in 3.6 (e.g., `wire:model.live`).

---

## 5. HTTP Layer & Routing

### 5.1 Controllers

- `App\Http\Controllers\Formulars\FormulaController`
  - Presents admin pages for managing formulas, steps, global variables, lookup tables, executor, history.
- `App\Http\Controllers\WorksheetsController`
  - Wraps `WorksheetManager` component for batch-level access (`/sample-workflow/batch/{batch}/worksheets`).

### 5.2 Routes

Add to `routes/web.php` (guarded by `auth` middleware):

```php
Route::middleware(['auth'])->prefix('formulars')->name('formulars.')->group(function () {
    Route::get('/', [FormulaController::class, 'index'])->name('index');
    Route::get('/manage', [FormulaController::class, 'manage'])->name('manage');
    Route::get('/steps/{formulaVersion}', [FormulaController::class, 'steps'])->name('steps');
    Route::get('/execute/{formulaVersion}', [FormulaController::class, 'execute'])->name('execute');
    Route::get('/history', [FormulaController::class, 'history'])->name('history');
    Route::get('/global-variables', [FormulaController::class, 'globalVariables'])->name('global-variables');
    Route::get('/lookup-tables', [FormulaController::class, 'lookupTables'])->name('lookup-tables');
    Route::get('/lookup-tables/{lookupTable}/entries', [FormulaController::class, 'lookupTableEntries'])->name('lookup-table-entries');
});

Route::middleware(['auth'])->get(
    '/sample-workflow/batch/{batch}/worksheets',
    [WorksheetsController::class, 'index']
)->name('batch-worksheets');
```

### 5.3 UI Entry Point

Within the batch view (`resources/views/layouts/lab/sample-workflow/show.blade.php`) add a shortcut:

```blade
<a href="{{ route('batch-worksheets', ['batch' => $batch->id]) }}" class="btn btn-info btn-sm">
    <i class="mdi mdi-clipboard-text"></i> Worksheets
&lt;/a>
```

This routes users to `worksheets/index.blade.php`, which simply mounts the `WorksheetManager` Livewire component.

---

## 6. Observer & Model Hooks

### 6.1 Captured Results Observer

- **File**: `App\Observers\CapturedObserver`
- **Registration**: In `App\Providers\AppServiceProvider::boot()`, call `CapturedResult::observe(CapturedObserver::class);`

**Behavior**:

1. On creation, locates the `AnalysisElements` record matching `analysis_type_id` + `analyte_id`.
2. Automatically stamps `analysis_element_id`.
3. If the analysis element is configured with `result_is_calculated` and a `formular_id`, copies that `formular_id` onto the `CapturedResult`.
4. Also carries across method sequence pointers when present.

> This linkage is essential for the worksheet UI: `WorksheetManager` queries captured results with a non-null `formular_id`. Ensure the observer is registered in the target app and that `analysis_elements` records are populated with the intended `formular_id`.

---

## 7. Tying Formulas to Analysis Elements & Sample Workflow

1. **Assign Formulars**  
   - Extend your `analysis_elements` seeding/management UI so lab analysts can choose a formula (`formular_id`) per analyte/analysis type.
   - Set `result_is_calculated = true` for analytes that should derive results via worksheets.

2. **Captured Result Flow**  
   - When results are captured (manually or via instruments), the observer ensures each `CapturedResult` gains the `formular_id`.
   - `WorksheetManager` groups captured results by formula for a batch and renders `FormulaWorksheet` per formula tab.

3. **Worksheet Execution**  
   - Analysts enter step inputs. Livewire component calls `FormulaEvaluator`, populating derived and lookup steps in real time.
   - On auto-save/posting, `SampleCapturedWorksheetFormula`, step data, and mandatory fields persist.
   - Posting transfers the worksheet’s `final_result` and optional `result_reporting_symbol` back onto the `CapturedResult` and recalculates remarks (lookups can override standard-based remarks).

4. **Display in Sample Workflow**  
   - The “Worksheets” button from the sample workflow detail page (`show.blade.php`) links to the embedded worksheet hub where analysts manage formula-driven samples alongside existing sample context.

---

## 8. Step-by-Step Migration Procedure

1. **Copy Migrations**  
   - Move all `database/migrations/formulars/*.php` files.
   - Include supporting migrations that modify existing tables (`analysis_elements`, `captured_results`, `formula_steps`, etc.).
   - Verify naming collisions and adjust timestamps if necessary so Laravel runs them after prerequisite tables.

2. **Sync Models**  
   - Copy `app/Models/Formulars/**/*`, `app/Models/Worksheets/**/*`, and any referenced models (`LookupTable`, `GlobalVariable`).
   - Ensure namespaces align and update `composer.json` autoload if directory structure differs.

3. **Bring Over Services & Helpers**  
   - Copy `App\Services\Formulars\FormulaEvaluator` and `LookupService`. Confirm the service container bindings (if any) or instantiate via `app()` as in the original code.

4. **Register Livewire Components**  
   - Copy component classes (under `app/Livewire/Formulars` and `app/Livewire/Worksheets`).
   - Copy all Blade views under `resources/views/formulars`, `resources/views/livewire/formulars`, and `resources/views/livewire/worksheets`.
   - Ensure Livewire asset build pipeline is configured (Vite or Mix). The templates make use of Alpine.js helpers; include Alpine if not already present.

5. **Routes & Controllers**  
   - Register the routes described in section §5.2.
   - Copy `FormulaController` and `WorksheetsController`.

6. **Observer Registration**  
   - Copy `App\Observers\CapturedObserver`.
  - Register it in `AppServiceProvider::boot()`. If you already extend `boot()`, merge the registration logic carefully.

7. **Front-End Entry Points**  
   - Add the worksheet link button to the sample workflow detail view (or equivalent entry point in the target system).
   - If your layout differs, wire the button or navigation item to `route('batch-worksheets', $batch)` accordingly.

8. **Seed/Configure Reference Data**  
   - Populate global variables, lookup tables, and formula definitions (manual entry via UI or migration-based seeds).
   - Update analysis elements to reference the correct formulas.

9. **Testing**  
   - Validate formula creation, step editing, and expression validation.
   - Execute a worksheet in standalone mode (`/formulars/execute/{version}`) to confirm evaluator setup.
   - Capture sample results to ensure observer wiring and worksheet posting loops are working.

---

## 9. Additional Considerations

- **Soft Deletes**: All core tables leverage soft deletes to preserve history. Ensure your target app handles `withTrashed`/`withoutTrashed` where necessary.
- **Authorization**: Existing routes are behind `auth`. Introduce policies or middleware if your permission model differs.
- **Lookup Table Compatibility**: Range-based tables require `low`, `high`, and optionally `value_interpretation` keys. Key-value tables rely on the `key_columns` JSON definition—verify CSV import/export flows if replicating bulk management.
- **UI Enhancements**: The module uses drag-and-drop SortableJS via CDN (loaded directly in `formula-step-editor.blade.php`). Include the CDN script or bundle an equivalent asset.
- **Posting Remarks**: Formula worksheets integrate with existing standards logic (see `FormulaWorksheet::calculateRemark`). Ensure related models (`Standards`, `StandardAnalytes`, `StandardValue`) exist if you expect identical remark behavior.

---

## 10. Verification Checklist After Migration

- [ ] Migrations run successfully; all new columns present.
- [ ] Observer fires and stamps `formular_id` on new `CapturedResult` records.
- [ ] Formula manager UI loads and saves drafts/versions.
- [ ] Step editor drag/drop, search, and modals operate correctly (Livewire events, SortableJS).
- [ ] Worksheet manager displays tabs per formula for a batch with captured results.
- [ ] Derived and lookup values calculate instantly during data entry.
- [ ] Posting workflow writes final results back to `captured_results`, including reporting symbols and remarks.
- [ ] Worksheet history reflects saved executions.
- [ ] Lookup table overrides persist per worksheet run.

Once each box is checked, the Formulars module should behave equivalently in the new environment.

---

**Last updated:** 2025-11-09 (update this timestamp manually when revising the guide)


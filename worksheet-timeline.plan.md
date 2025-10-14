<!-- 644e4856-a3c8-4456-abfa-c7838bc37fda 6a2488b9-c2f8-466f-a5bb-b97ac1e6a44a -->
# Method Sequence Worksheet Redesign Plan

## Overview

Transform the worksheet interface into a modern, collapsible card-based layout with automated timers, notifications, and streamlined data entry.

## 1. Database Schema Updates

### Add columns to `method_sequence_runs` table:

- `analyst_id` (foreign key to users)
- `run_date` (date field)

### Add columns to `method_sequence_stage_data` table:

- `safe_duration_hours` (decimal, nullable)
- `duration_hours` (decimal, nullable)  
- `safe_duration_alert_sent` (boolean, default false)
- `duration_alert_sent` (boolean, default false)

### Add columns to `formular_steps` table:

- `is_end_stage` (boolean, default false)
- `is_end_stage_if_pass` (boolean, default false)

### Create new tables:

- `method_sequence_equipment_usage` (id, stage_data_id, equipment_id, equipment_name)
- `method_sequence_media_usage` (id, stage_data_id, media_id, media_name, volume, unit, batch_number)
- `method_sequence_control_usage` (id, stage_data_id, control_id, control_name, volume, unit, batch_number)

## 2. Backend Updates

### Update Livewire Component: `app/Http/Livewire/Worksheets/MethodSequenceWorksheet.php`

**Properties to add:**

- `expandedRunIds` (array) - tracks which runs are expanded
- `expandedStageIds` (array) - tracks which stages are expanded
- `analysts` (collection) - list of analyst users

**Methods to update/add:**

- `createRun()` - add analyst_id and run_date fields
- `toggleRunExpansion($runId)` - expand/collapse run cards
- `toggleStageExpansion($stageId)` - expand/collapse stage cards
- `loadStageDetails($stageId)` - lazy load stage data when expanded
- `autoSaveStageField($stageDataId, $field, $value)` - auto-save on blur
- `addEquipment($stageDataId, $equipmentId)` - add equipment with searchable dropdown
- `removeEquipment($usageId)` - remove equipment
- `addMedia($stageDataId)` - add media row
- `updateMedia($usageId, $data)` - update media with volume/batch
- `removeMedia($usageId)` - remove media
- `addControl($stageDataId)` - add control row
- `updateControl($usageId, $data)` - update control with volume/batch
- `removeControl($usageId)` - remove control
- `updateStageStatus($stageDataId)` - auto-complete previous stage when next starts
- `saveResultStageData($stageDataId, $results)` - save results and update captured_results

**Computed properties:**

- `getStageTimer($stageData)` - calculate remaining time
- `getStageTimerStatus($stageData)` - return 'safe', 'warning', or 'expired'

### Create Command: `app/Console/Commands/CheckStageTimers.php`

- Run every minute via scheduler
- Check all in-progress stages
- Send notifications when safe_duration or duration expires
- Update alert flags to prevent duplicate notifications

### Create Notification Classes:

- `app/Notifications/StageSafeDurationExpired.php` (database, mail, broadcast)
- `app/Notifications/StageDurationExpired.php` (database, mail, broadcast)

### Update Models:

**`MethodSequenceRun` model:**

- Add relationship: `analyst()` → User
- Add `run_date` to fillable/casts

**`MethodSequenceStageData` model:**

- Add relationships: `equipmentUsage()`, `mediaUsage()`, `controlUsage()`
- Add timer calculation methods
- Add `safe_duration_hours`, `duration_hours` to fillable

**Create new models:**

- `MethodSequenceEquipmentUsage`
- `MethodSequenceMediaUsage`  
- `MethodSequenceControlUsage`

## 3. Frontend View Updates

### File: `resources/views/livewire/worksheets/method-sequence-worksheet.blade.php`

**Structure changes:**

```
├── Alert Messages
├── Method Sequence Header (keep existing)
├── Create Run Button
├── Runs Section (NEW DESIGN)
│   └── For each run:
│       ├── Collapsed Card Header
│       │   ├── Run name, date, analyst name
│       │   └── Actions: Edit, Delete, Expand/Collapse toggle
│       └── Expanded Card Body (lazy loaded)
│           ├── Run metadata
│           ├── Alert banners (timer warnings)
│           └── Vertical Timeline (styled like image)
│               └── For each stage:
│                   ├── Collapsed Stage Block
│                   │   ├── Large colored number badge (gray/blue/green)
│                   │   ├── Stage name, status badge
│                   │   ├── Duration display / Timer
│                   │   └── Expand/Collapse button
│                   └── Expanded Stage Form
│                       ├── Timing fields (3 per row)
│                       ├── Equipment section (searchable dropdown)
│                       ├── Media section (table with volume, batch, actions)
│                       ├── Controls section (table with volume, batch, actions)
│                       └── Results section (if result stage)
```

**Key styling:**

- Vertical timeline with connecting line
- Colored stage blocks: `#6b7280` (gray-pending), `#3b82f6` (blue-progress), `#10b981` (green-complete)
- Large stage number badges with icons
- Timer display with color coding (green → amber → red)
- Searchable dropdowns like `element-manager.blade.php` for equipment/media/controls

### File: `resources/views/livewire/worksheets/create-run-modal.blade.php` (extract from main file)

**Add fields:**

- Analyst dropdown (filtered, current user pre-selected if analyst)
- Run date picker (default to today)
- Keep existing sample selection

## 4. JavaScript Enhancements

**Add to worksheet blade file:**

- Auto-save on blur: `wire:blur="autoSaveStageField(...)"` on inputs
- Timer countdown display (updates every minute via Livewire polling)
- Searchable dropdown implementation (reuse from element-manager)
- Collapse/expand animations
- Browser notification permission request
- Real-time notification listener (Laravel Echo + Pusher)

## 5. Formula Step Editor Updates

### File: `resources/views/livewire/formulars/formula-step-editor.blade.php`

**Around line 356 (after Step Type field in Create Modal):**

Add two new checkbox fields:

- `is_end_stage` - "This is an end stage"
- `is_end_stage_if_pass` - "End stage only if result is Pass"

**Around line 646 (after Step Type field in Edit Modal):**

Add the same two checkbox fields

### File: `app/Http/Livewire/Formulars/FormulaStepEditor.php`

**Add properties:**

- `$isEndStage` (boolean)
- `$isEndStageIfPass` (boolean)

**Update methods:**

- `createStep()` - include new fields
- `showEditStepModalInit()` - load new fields
- `updateStep()` - save new fields

## 6. Notification System Setup

### Routes (`routes/web.php` or `routes/channels.php`):

```php
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

### Scheduler (`app/Console/Kernel.php`):

```php
$schedule->command('check:stage-timers')->everyMinute();
```

## 7. CSS Styling

**Add styles for:**

- Vertical timeline with connecting lines
- Colored stage blocks matching image reference
- Timer displays with color transitions
- Collapsible card animations
- Alert banners for stage warnings
- Searchable dropdown styling (from element-manager)
- Badge styling for stage numbers

## 8. Key Implementation Files

**Migration files needed:**

- `xxxx_add_analyst_and_date_to_runs.php`
- `xxxx_add_timer_fields_to_stage_data.php`
- `xxxx_add_end_stage_fields_to_formular_steps.php`
- `xxxx_create_stage_usage_tables.php`

**Core logic files:**

- `app/Http/Livewire/Worksheets/MethodSequenceWorksheet.php` (main component)
- `app/Console/Commands/CheckStageTimers.php` (timer checker)
- `app/Notifications/StageSafeDurationExpired.php`
- `app/Notifications/StageDurationExpired.php`

**View files:**

- `resources/views/livewire/worksheets/method-sequence-worksheet.blade.php` (complete redesign)
- `resources/views/livewire/formulars/formula-step-editor.blade.php` (add 2 fields)

## 9. Testing Considerations

- Test run creation with analyst assignment
- Test stage auto-completion when next stage starts
- Test timer calculations and notifications
- Test auto-save functionality
- Test collapsible interactions
- Test captured results updates at result stages
- Test searchable dropdowns for equipment/media/controls

### To-dos

- [x] Create database migrations for runs (analyst_id, run_date), stage_data (timer fields), formular_steps (end stage flags), and new usage tables
- [x] Update/create models: MethodSequenceRun, MethodSequenceStageData, and new usage models with relationships
- [x] Create notification classes for safe duration and duration expiration (database, mail, broadcast)
- [x] Create CheckStageTimers artisan command and register in scheduler
- [x] Update MethodSequenceWorksheet component: add properties, methods for collapsible UI, auto-save, stage management, timer calculations
- [x] Update FormulaStepEditor component to handle is_end_stage and is_end_stage_if_pass fields
- [x] Redesign method-sequence-worksheet.blade.php: collapsible runs/stages, vertical timeline, timer displays, searchable dropdowns, auto-save fields
- [x] Add is_end_stage and is_end_stage_if_pass checkbox fields to formula-step-editor.blade.php (create and edit modals)
- [x] Add CSS for vertical timeline, colored stage blocks (gray/blue/green), timer styling, collapsible animations, searchable dropdowns
- [x] Configure broadcast channels and ensure Laravel Echo is set up for real-time notifications
- [x] Test all functionality: run creation, stage expansion, timers, notifications, auto-save, result updates, stage auto-completion

## ✅ IMPLEMENTATION COMPLETE

All tasks have been successfully implemented! See `IMPLEMENTATION_SUMMARY.md` for detailed documentation.


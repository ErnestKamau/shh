# Equipment Checks Implementation Guide

## Overview

The **Equipment Checks** feature (formerly "Equipment Daily Log") tracks equipment usage during analysis runs in worksheets. This system records:

1. **Regular Equipment Checks**: Scheduled readings/verifications based on frequency (e.g., once daily, twice daily)
2. **Runtime Equipment Tracking**: Automatic logging of equipment usage during method sequence analysis runs

## Key Features

### 1. Equipment Checks Page
- Accessible at `/equipment-checks`
- Displays equipment requiring scheduled checks by frequency
- Allows recording of readings on a per-day basis
- Shows non-conformance reports for readings outside expected ranges
- Supports multiple instances of the same equipment type used by different analysts

### 2. Runtime Equipment Tracking
- Automatically tracks when equipment is turned on (analysis starts)
- Automatically tracks when equipment is turned off (analysis completes)
- Records duration of equipment usage for each analysis run
- Tracks which analyst started and completed each equipment usage

## Database Schema

### Main Table: `method_sequence_stage_equipment_usage`

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT | Primary key |
| `run_stage_data_id` | BIGINT | Foreign key to stage data |
| `equipment_id` | BIGINT | Foreign key to equipment |
| `equipment_name` | STRING | Equipment name (snapshot) |
| `started_at` | DATETIME | When equipment was turned on |
| `completed_at` | DATETIME | When equipment was turned off |
| `started_by_user_id` | BIGINT | User who started the equipment |
| `completed_by_user_id` | BIGINT | User who completed the equipment |
| `created_at` | TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | Record update time |

### Related Table: `method_sequence_run_stage_data`

Tracks stage execution times:
- `date_in`, `time_in` - When stage started
- `date_out`, `time_out` - When stage ended
- `started_by_user_id` - Who started the stage
- `completed_by_user_id` - Who completed the stage

## Implementation Guide

### Step 1: Run the Migration

```bash
php artisan migrate --path=database/migrations/2026_04_24_000001_add_equipment_checks_timestamps_to_method_sequence_stage_equipment_usage_table.php
```

### Step 2: Update Worksheet Controllers/Services

When starting a stage in your worksheet/run controller:

```php
use App\Services\EquipmentChecksService;

$equipmentChecksService = app(EquipmentChecksService::class);

// When starting a stage
$equipmentChecksService->startStageEquipmentUsage($stageData, auth()->id());

// When completing a stage
$equipmentChecksService->completeStageEquipmentUsage($stageData, auth()->id());
```

### Step 3: Display Equipment Checks in Worksheets

In your worksheet blade view:

```blade
@php
    $equipmentChecksService = app(App\Services\EquipmentChecksService::class);
    $equipmentChecks = $equipmentChecksService->getRunEquipmentChecks($run->id);
    $summary = $equipmentChecksService->getRunEquipmentChecksSummary($run->id);
@endphp

<div>
    <h5>Equipment in Use</h5>
    <p>{{ $summary['completed'] }} Completed | {{ $summary['in_progress'] }} In Progress | {{ $summary['not_started'] }} Not Started</p>
    
    @foreach($equipmentChecks as $check)
        <div class="equipment-check-item">
            <strong>{{ $check['equipment_name'] }}</strong>
            <span>Status: {{ ucfirst($check['status']) }}</span>
            @if($check['duration_formatted'])
                <span>Duration: {{ $check['duration_formatted'] }}</span>
            @endif
        </div>
    @endforeach
</div>
```

## Supported Use Cases

### Multiple Analysts, Same Equipment Type

When two different analysts use two different instances (e.g., Thermometer A and Thermometer B):

```php
// Equipment Checks tracks each separately:
[
    [
        'equipment_id' => 1001,  // Thermometer A
        'equipment_name' => 'Thermometer - Lab 1',
        'started_at' => '2026-04-24 08:00:00',
        'completed_at' => '2026-04-24 08:45:00',
        'started_by_user_id' => 5,  // Analyst 1
    ],
    [
        'equipment_id' => 1002,  // Thermometer B
        'equipment_name' => 'Thermometer - Lab 2',
        'started_at' => '2026-04-24 08:05:00',
        'completed_at' => '2026-04-24 09:15:00',
        'started_by_user_id' => 7,  // Analyst 2
    ]
]
```

### Equipment Check History

Retrieve all equipment checks for a specific equipment instance:

```php
$history = $equipmentChecksService->getEquipmentCheckHistory(
    equipmentId: 1001,
    fromDate: '2026-04-01',
    toDate: '2026-04-24'
);

// Returns array of check records with dates, times, durations, and analysts
```

## Model Methods

### MethodSequenceStageEquipmentUsage

**Get Duration:**
```php
$usage = MethodSequenceStageEquipmentUsage::find($id);
$minutes = $usage->getDurationMinutes();  // Integer or null
$formatted = $usage->getFormattedDuration();  // "02:30:00" or null
```

**Check Status:**
```php
$usage->isInProgress();  // true if started but not completed
$usage->isCompleted();   // true if both started and completed
```

**Mark Equipment:**
```php
$usage->markAsStarted(auth()->id());      // Set started_at and started_by_user_id
$usage->markAsCompleted(auth()->id());    // Set completed_at and completed_by_user_id
```

## File Structure

### New/Modified Files

```
app/
├── Livewire/Equipment/
│   ├── EquipmentChecks.php              [NEW] - Livewire component for checks page
│   └── EquipmentDetail.php              [MODIFIED] - Updated parameter names
├── Models/Worksheets/
│   └── MethodSequenceStageEquipmentUsage.php [MODIFIED] - Added methods and fields
├── Services/
│   └── EquipmentChecksService.php       [NEW] - Service for managing equipment checks
└── Http/Controllers/LivewireControllers/
    └── EquipmentAppController.php       [MODIFIED] - Added checksIndex() method

database/
└── migrations/
    └── 2026_04_24_000001_add_equipment_checks_timestamps...php [NEW]

resources/views/
├── livewire/equipment/
│   ├── equipment-checks.blade.php       [NEW] - Renamed from equipment-daily-log
│   ├── equipment-detail.blade.php       [MODIFIED] - Updated references
│   └── equipment-daily-log.blade.php    [DEPRECATED] - Kept for backwards compatibility
└── livewire/layout/
    └── equipment-app.blade.php          [MODIFIED] - Updated component mapping

routes/
└── web.php                              [MODIFIED] - Added /equipment-checks route
```

## Migration Path

### Backwards Compatibility

- Old route `/equipment-daily-log` works but redirects to `/equipment-checks`
- `equipment-daily-log` view still exists but internally uses new component
- Old parameter names `fromDailyLog` still work in some places for now
- Database fields remain named with `daily_log_` prefix for now (can be migrated later)

### Deprecation Notice

- `EquipmentDailyLog` Livewire class remains for backwards compatibility
- Use `EquipmentChecks` component for new implementations
- Old parameter names will be removed in a future version

## Testing the Implementation

### Test 1: Verify Route Works
```bash
php artisan tinker
> Route::get('/equipment-checks')?->getName()
# Should return 'equipment-checks'
```

### Test 2: Verify Timestamps Are Recorded
```php
// Create a test run and stage
$usage = MethodSequenceStageEquipmentUsage::first();
$usage->markAsStarted(1);
$usage->markAsCompleted(1);

echo $usage->getDurationMinutes();    // Should show minutes
echo $usage->getFormattedDuration();  // Should show HH:MM:SS
```

### Test 3: Verify Multi-Equipment Tracking
```php
// Query same equipment used in same run by different analysts
$checks = MethodSequenceStageEquipmentUsage::whereIn('equipment_id', [1001, 1002])
    ->get();
# Should show separate records for each equipment instance
```

## Next Steps for Integration

1. **Worksheet Controller**: Update to call `startStageEquipmentUsage()` and `completeStageEquipmentUsage()`
2. **Worksheet Views**: Add UI to display active equipment checks during runs
3. **Equipment Dashboard**: Create a dashboard view showing:
   - Equipment checks history per equipment
   - Usage statistics and trends
   - Top equipment by usage time
4. **Reports**: Generate equipment usage reports by:
   - Date range
   - Analyst
   - Equipment
   - Analysis type

## API Endpoints (Optional Future Enhancement)

Suggested REST endpoints for equipment checks:

```
GET    /api/equipment-checks                  - List all recent checks
GET    /api/equipment-checks/{id}             - Get check details
GET    /api/equipment/{id}/checks             - Get check history for equipment
GET    /api/runs/{id}/equipment-checks        - Get equipment checks for a run
POST   /api/equipment-checks/{id}/start       - Start equipment usage
POST   /api/equipment-checks/{id}/complete    - Complete equipment usage
```

## Troubleshooting

**Issue**: Equipment check times not recording
- Ensure migration has been run
- Check that `startStageEquipmentUsage()` is being called when stage starts
- Verify `completed_by_user_id` is set properly

**Issue**: Multiple equipment instances not tracked separately
- Confirm each equipment has a unique `equipment_id`
- Check that `MethodSequenceStageEquipmentUsage` records exist for each equipment
- Verify query uses specific `equipment_id` not just equipment type

**Issue**: Duration showing null
- Ensure both `started_at` and `completed_at` are set
- Check that `completed_at` is after `started_at`
- Verify datetime format is correct

## Support

For questions about Equipment Checks implementation, refer to:
- Model: `app/Models/Worksheets/MethodSequenceStageEquipmentUsage.php`
- Service: `app/Services/EquipmentChecksService.php`
- Component: `app/Livewire/Equipment/EquipmentChecks.php`
- View: `resources/views/livewire/equipment/equipment-checks.blade.php`
